<?php
//namespace dvcarrot\WebDAV;

/**
 * Class Client
 * @package dvcarrot \WebDAV
 */
class Client
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var resource
     */
    private $curl;

    /**
     * Sets variables
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Kill staff
     */
    public function __destruct()
    {
        if ($this->curl)
            curl_close($this->curl);
    }

    /**
     * @param $file
     * @return Result
     */
    public function get($file)
    {
        return $this->request(
            $this->config->hostname . $file,
            array(''),
            'GET'
        );
    }

    /**
     * @param $fileOut
     * @param $fileIn
     * @return Result
     */
    public function put($fileOut, $fileIn)
    {
        return $this->request(
            $this->config->hostname . $fileOut,
            array('Content-type: application/octet-stream'),
            'PUT',
            $fileIn
        );
    }

    /**
     * @param $fileOut
     * @param $fileIn
     * @return Result
     */
    public function delete($file)
    {
        return $this->request(
            $this->config->hostname . $file,
            array(''),
            'DELETE'
        );
    }
    /**
     * @param $folder
     * @param $depth
     * @return Result
     */
    public function propfind($folder, $depth = 1)
    {
        return $this->request(
            $this->config->hostname . $folder,
            array('Depth: ' . $depth),
            'PROPFIND'
        );
    }

    /**
     * @param $folder
     * @return Result
     */
    public function mkcol($folder)
    {
        return $this->request(
            $this->config->hostname . $folder,
            array(),
            'MKCOL'
        );
    }
    
    /**
     * @param $folder
     * @return Result
     */
    public function getSize()
    {
        return $this->request(
            $this->config->hostname,
            array('Depth: ' . 0, 'Content-Type: text/xml', 'Accept: */*'),
            'PROPFIND',
            null,
            '<?xml version="1.0" encoding="utf-8" ?><D:propfind xmlns:D="DAV:"><D:prop><D:quota-available-bytes/><D:quota-used-bytes/></D:prop></D:propfind>'
        );
    }


    /**
     * Executes queries to the cloud
     * @param string $url
     * @param array $headers
     * @param string $method
     * @param string $file
     * @return Result
     * @access private
     * @final
     */
    final private function request($url, $headers = array(), $method = '', $file = null, $body = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERPWD, implode(':', array($this->config->username, $this->config->password)));

        if (empty($this->config->authtype) === false) {
            curl_setopt($ch, CURLOPT_HTTPAUTH, $this->config->authtype);
        }
        if (empty($headers) === false) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        if (empty($method) === false) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }
        if (is_null($file) === false) {
            curl_setopt($ch, CURLOPT_PUT, true);
            curl_setopt($ch, CURLOPT_INFILE, fopen($file, 'r'));
            curl_setopt($ch, CURLOPT_INFILESIZE, filesize($file));
        }
        if (is_null($body) === false) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        //echo $statusCode;
        //echo $response;
        curl_close($ch);
        
        $result = new Result($statusCode, $response);

        return $result;
    }
}