<?php
require_once("IProvider.php");

/**
 * Amazon S3 provider (AWS Signature V4, no external SDK).
 * BACKUP_S3_ENDPOINT may point to any S3-compatible service.
 */
class AmazonS3Backup implements IProvider
{
    public $error;
    public $supportUpload = 1;

    private $endpoint;
    private $bucket;
    private $region;
    private $key;
    private $secret;
    private $prefix;
    private $logger;

    function __construct($endpoint, $bucket, $region, $key, $secret, $prefix, $logger)
    {
        $this->endpoint = preg_replace('#^https?://#', '', rtrim((string)$endpoint, '/'));
        $this->bucket = trim((string)$bucket);
        $this->region = trim((string)$region);
        if ($this->region === '') {
            $this->region = 'us-east-1';
        }
        if ($this->endpoint === '') {
            $this->endpoint = 's3.' . $this->region . '.amazonaws.com';
        }
        $this->key = $key;
        $this->secret = $secret;
        $this->prefix = trim((string)$prefix, '/');
        $this->logger = $logger;
    }

    private function canonicalUri($objectKey)
    {
        $parts = explode('/', $objectKey);
        foreach ($parts as $i => $part) {
            $parts[$i] = rawurlencode($part);
        }
        return '/' . $this->bucket . '/' . implode('/', $parts);
    }

    private function authHeader($method, $canonicalUri, $canonicalQuery, $payload, $timestamp)
    {
        $amzDate = gmdate('Ymd\THis\Z', $timestamp);
        $dateStamp = gmdate('Ymd', $timestamp);
        $payloadHash = hash('sha256', $payload);
        $canonicalHeaders = 'host:' . $this->endpoint . "\n"
            . 'x-amz-content-sha256:' . $payloadHash . "\n"
            . 'x-amz-date:' . $amzDate . "\n";
        $signedHeaders = 'host;x-amz-content-sha256;x-amz-date';
        $canonicalRequest = $method . "\n" . $canonicalUri . "\n" . $canonicalQuery . "\n"
            . $canonicalHeaders . "\n" . $signedHeaders . "\n" . $payloadHash;
        $scope = $dateStamp . '/' . $this->region . '/s3/aws4_request';
        $stringToSign = "AWS4-HMAC-SHA256\n" . $amzDate . "\n" . $scope . "\n"
            . hash('sha256', $canonicalRequest);
        $kDate = hash_hmac('sha256', $dateStamp, 'AWS4' . $this->secret, true);
        $kRegion = hash_hmac('sha256', $this->region, $kDate, true);
        $kService = hash_hmac('sha256', 's3', $kRegion, true);
        $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);
        $signature = hash_hmac('sha256', $stringToSign, $kSigning);
        return 'AWS4-HMAC-SHA256 Credential=' . $this->key . '/' . $scope
            . ', SignedHeaders=' . $signedHeaders . ', Signature=' . $signature;
    }

    private function request($method, $objectKey, $query, $payload, $outFile = '')
    {
        $timestamp = time();
        $amzDate = gmdate('Ymd\THis\Z', $timestamp);
        $canonicalUri = $this->canonicalUri($objectKey);
        $canonicalQuery = $query;
        $payloadHash = hash('sha256', $payload);
        $auth = $this->authHeader($method, $canonicalUri, $canonicalQuery, $payload, $timestamp);
        $url = 'https://' . $this->endpoint . $canonicalUri . ($query !== '' ? '?' . $query : '');

        $ch = curl_init($url);
        $options = array(
            CURLOPT_RETURNTRANSFER => $outFile === '',
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => array(
                'Authorization: ' . $auth,
                'x-amz-date: ' . $amzDate,
                'x-amz-content-sha256: ' . $payloadHash,
            ),
            CURLOPT_TIMEOUT => 300,
            CURLOPT_SSL_VERIFYPEER => true,
        );
        if ($payload !== '' && $outFile === '') {
            $options[CURLOPT_POSTFIELDS] = $payload;
        }
        if ($outFile !== '') {
            $handle = @fopen($outFile, 'wb');
            if (!$handle) {
                $this->error = 'Cannot open ' . $outFile;
                return false;
            }
            $options[CURLOPT_FILE] = $handle;
            $options[CURLOPT_RETURNTRANSFER] = false;
        }
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        if ($outFile !== '' && isset($handle)) {
            fclose($handle);
        }
        if ($result === false || $code < 200 || $code >= 300) {
            $this->error = 'S3 HTTP ' . $code . ($error ? ': ' . $error : '');
            $this->logger->log($this->error);
            return false;
        }
        return $result;
    }

    private function objectKey($backup)
    {
        return $this->prefix === '' ? $backup : $this->prefix . '/' . $backup;
    }

    public function getFreeSpace()
    {
        return -1;
    }

    public function getList()
    {
        $query = http_build_query(array(
            'list-type' => '2',
            'prefix' => $this->prefix === '' ? 'backup_' : $this->prefix . '/backup_',
        ));
        $res = $this->request('GET', '', $query, '');
        if (!$res) {
            return array();
        }
        $xml = @simplexml_load_string($res);
        if (!$xml) {
            $this->error = 'Cannot parse S3 response';
            return array();
        }
        $files = array();
        foreach ($xml->Contents as $item) {
            $name = basename((string)$item->Key);
            if (!preg_match('/^backup_\d{14}\.(tar|tgz|tar\.gz)$/', $name)) {
                continue;
            }
            $files[] = array(
                'NAME' => $name,
                'CREATED' => date('Y-m-d H:i:s', strtotime((string)$item->LastModified)),
                'SIZE' => (int)$item->Size,
            );
        }
        return $files;
    }

    public function uploadBackup($file, $backup)
    {
        $this->request('PUT', $this->objectKey($backup), '', file_get_contents($file));
    }

    public function deleteBackup($backup)
    {
        $this->request('DELETE', $this->objectKey($backup), '', '');
    }

    public function downloadBackup($backup, $file)
    {
        $this->request('GET', $this->objectKey($backup), '', '', $file);
    }
}
