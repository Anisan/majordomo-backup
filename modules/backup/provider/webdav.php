<?php
require_once("IProvider.php");

class WebDavBackup implements IProvider
{
    public $error;
    public $supportUpload = 1;
        
    function __construct($url, $login, $password, $path, $logger)
    {
        $this->url = $url;
        $this->login = $login;
        $this->password = $password;
        $this->path = $path;
        $this->logger = $logger;
        require_once(ROOT . 'modules/backup/provider/WebDav/Config.php');
        $config = new Config($this->url,$this->login,$this->password);
        require_once(ROOT . 'modules/backup/provider/WebDav/Client.php');
        require_once(ROOT . 'modules/backup/provider/WebDav/Result.php');
        $this->client = new Client($config);
    }
    
    public function getFreeSpace()
    {
        $result = $this->client->getSize();
        if ($result->code != 200 && $result->code != 207)
        {
            $this->error = $result->code. " - " .$result->response;
            $this->logger->log("getFreeSpace - ".$result->code. " - " .$result->response);
            return;
        }
        $result = $result->getResponseArray();
        $this->logger->debug("getFreeSpace - ".$result);
        if ($result->code != 200)
        {
            $this->error = $result->response;
            return;
        }
        //echo print_r($result);
        if ($result) { 
            if (!isset($result["propstat"]["prop"]["quota-available-bytes"])) return -1;
            return $result["propstat"]["prop"]["quota-available-bytes"] - $item["propstat"]["prop"]["quota-used-bytes"];
        }
        return -1;
    }
    
    
    public function getList()
    {
        $result = $this->client->propfind($this->path);
        if ($result->code != 200 && $result->code != 207)
        {
            $this->error = $result->code. " - " .$result->response;
            $this->logger->log("getList - ".$result->code. " - " .$result->response);
            return;
        }
        $result = $result->getResponseArray();
        $this->logger->debug("getList - ".$result);
        //echo print_r($result);
        if ($result) { 
            $files = array();
            foreach ($result as $item) {
                if (!isset($item["propstat"]["prop"]["getcontenttype"])) continue;
                if (isset($item["propstat"]["prop"]["getcontentlength"]) ||
                    isset($item["propstat"]["prop"]["resourcetype"]["getcontentlength"]))
                {
                $file = array();
                if (isset($item["propstat"]["prop"]["displayname"]))
                    $file["NAME"] = $item["propstat"]["prop"]["displayname"];
                else
                    $file["NAME"] = basename($item["href"]);
                $time = strtotime($item["propstat"]["prop"]["creationdate"].' UTC');
                $dateInLocal = date("Y-m-d H:i:s", $time);
                $file["CREATED"] = $dateInLocal;
                if (isset($item["propstat"]["prop"]["getcontentlength"]))
                    $file["SIZE"] = $item["propstat"]["prop"]["getcontentlength"];
                //$file["URL"] = "";
                $files[] = $file;
                //echo print_r($item);
                }
            }
            return $files;
        }
    }
    
    public function uploadBackup($file, $backup)
    {
        $result = $this->client->put($this->path."/".$backup,$file);
        if ($result->code != 201)
        {
            $this->error = $result->response;
            $this->logger->log("uploadBackup - ".$result->code. " - " .$result->response);
            return;
        }
        $result = $result->getResponseArray();
        $this->logger->debug("uploadBackup - ".$result);
        //echo print_r($result);
    }
    
    public function deleteBackup($backup)
    {
        $filename = $this->path ."/". $backup;
        $result = $this->client->delete($filename);
        if ($result->code != 204)
        {
            $this->error = $result->response;
            $this->logger->log("deleteBackup - ".$result->code. " - " .$result->response);
            return;
        }
        $result = $result->getResponseArray();
        $this->logger->debug("deleteBackup - ".$result);
        //echo print_r($result);
    }
    
    public function downloadBackup($backup, $file)
    {
        $result = $this->client->get($this->path."/".$backup);
        if ($result->code != 200)
        {
            $this->error = $result->response;
            $this->logger->log("downloadBackup - ".$result->code. " - " .$result->response);
            return;
        }
        $Handle = fopen($file, 'w');
        fwrite($Handle, $result->response);
        fclose($Handle);
    }
}