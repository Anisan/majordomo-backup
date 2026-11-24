<?php
require_once("IProvider.php");

/**
 * Google Drive provider using OAuth 2.0 refresh token.
 * Store the refresh token in BACKUP_GDRIVE_REFRESH_TOKEN and the target
 * folder id in BACKUP_GDRIVE_FOLDER (empty means "My Drive" root).
 */
class GoogleDriveBackup implements IProvider
{
    public $error;
    public $supportUpload = 1;

    private $clientId;
    private $clientSecret;
    private $refreshToken;
    private $folder;
    private $logger;
    private $accessToken = '';

    function __construct($clientId, $clientSecret, $refreshToken, $folder, $logger)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->refreshToken = $refreshToken;
        $this->folder = trim($folder);
        $this->logger = $logger;
    }

    private function request($url, $method = 'GET', $headers = array(), $body = null, $outFile = '')
    {
        $ch = curl_init($url);
        $options = array(
            CURLOPT_RETURNTRANSFER => $outFile === '',
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_FOLLOWLOCATION => true,
        );
        if ($body !== null) {
            $options[CURLOPT_POSTFIELDS] = $body;
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
            $this->error = 'Google Drive HTTP ' . $code . ($error ? ': ' . $error : '');
            $this->logger->log($this->error);
            return false;
        }
        return $result;
    }

    private function token()
    {
        if ($this->accessToken !== '') {
            return $this->accessToken;
        }
        if ($this->refreshToken === '' || $this->clientId === '' || $this->clientSecret === '') {
            $this->error = 'Google Drive OAuth settings are incomplete';
            return '';
        }
        $body = http_build_query(array(
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $this->refreshToken,
            'grant_type' => 'refresh_token',
        ));
        $res = $this->request('https://oauth2.googleapis.com/token', 'POST',
            array('Content-Type: application/x-www-form-urlencoded'), $body);
        if (!$res) {
            return '';
        }
        $data = json_decode($res, true);
        if (empty($data['access_token'])) {
            $this->error = 'Cannot obtain Google Drive access token';
            return '';
        }
        $this->accessToken = $data['access_token'];
        return $this->accessToken;
    }

    private function authHeader()
    {
        return 'Authorization: Bearer ' . $this->token();
    }

    private function fileId($backup)
    {
        $files = $this->getList();
        if (!is_array($files)) {
            return '';
        }
        foreach ($files as $file) {
            if ($file['NAME'] === $backup) {
                return $file['ID'];
            }
        }
        return '';
    }

    public function getFreeSpace()
    {
        if (!$this->token()) {
            return -1;
        }
        $res = $this->request('https://www.googleapis.com/drive/v3/about?fields=storageQuota', 'GET',
            array($this->authHeader()));
        if (!$res) {
            return -1;
        }
        $data = json_decode($res, true);
        $quota = isset($data['storageQuota']) ? $data['storageQuota'] : array();
        $limit = isset($quota['limit']) ? (float)$quota['limit'] : 0;
        $used = isset($quota['usage']) ? (float)$quota['usage'] : 0;
        return $limit > 0 ? (int)($limit - $used) : -1;
    }

    public function getList()
    {
        if (!$this->token()) {
            return array();
        }
        $query = "name starts with 'backup_' and trashed = false";
        if ($this->folder !== '') {
            $query .= " and '" . str_replace("'", "\\'", $this->folder) . "' in parents";
        }
        $url = 'https://www.googleapis.com/drive/v3/files?'
            . http_build_query(array(
                'q' => $query,
                'fields' => 'files(id,name,size,createdTime)',
                'orderBy' => 'createdTime desc',
                'pageSize' => 1000,
            ));
        $res = $this->request($url, 'GET', array($this->authHeader()));
        if (!$res) {
            return array();
        }
        $data = json_decode($res, true);
        $files = array();
        foreach (isset($data['files']) ? $data['files'] : array() as $item) {
            $files[] = array(
                'NAME' => $item['name'],
                'CREATED' => date('Y-m-d H:i:s', strtotime($item['createdTime'])),
                'SIZE' => isset($item['size']) ? (int)$item['size'] : 0,
                'ID' => $item['id'],
            );
        }
        return $files;
    }

    public function uploadBackup($file, $backup)
    {
        if (!$this->token()) {
            return;
        }
        $metadata = array('name' => $backup);
        if ($this->folder !== '') {
            $metadata['parents'] = array($this->folder);
        }
        $boundary = '----BackupBoundary' . md5(uniqid('', true));
        $body = "--" . $boundary . "\r\n"
            . "Content-Type: application/json; charset=UTF-8\r\n\r\n"
            . json_encode($metadata) . "\r\n"
            . "--" . $boundary . "\r\n"
            . "Content-Type: application/octet-stream\r\n\r\n"
            . file_get_contents($file) . "\r\n"
            . "--" . $boundary . "--\r\n";
        $this->request('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart',
            'POST', array($this->authHeader(), 'Content-Type: multipart/related; boundary=' . $boundary), $body);
    }

    public function deleteBackup($backup)
    {
        $id = $this->fileId($backup);
        if ($id === '' || !$this->token()) {
            return;
        }
        $this->request('https://www.googleapis.com/drive/v3/files/' . rawurlencode($id), 'DELETE',
            array($this->authHeader()));
    }

    public function downloadBackup($backup, $file)
    {
        $id = $this->fileId($backup);
        if ($id === '' || !$this->token()) {
            return;
        }
        $this->request('https://www.googleapis.com/drive/v3/files/' . rawurlencode($id) . '?alt=media',
            'GET', array($this->authHeader()), null, $file);
    }
}
