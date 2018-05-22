<?php
require_once("IProvider.php");
require 'CloudMailRu.php';


class MailRuBackup implements IProvider
{
    public $error;
    public $supportUpload = 0;
        
    function __construct($login, $password, $path, $logger)
    {
        $this->path = $path;
        $this->logger = $logger;
        $this->cloud = new CloudMailRu($login, $password);
        if (!$this->cloud->login()) {
            $this->logger->log('Error autorization');
            $this->error='Error autorization';
        }
    }
    
    public function getFreeSpace()
    {
        $res = $this->cloud->getSpace($this->path);
        $this->logger->debug("getFreeSpace - ".$res);
        $used = $res['body']['used'];
        $total = $res['body']['total'];
        $free = $total - $used;
        return $free * 1024 * 1024;
    }
    
    public function getList()
    {
        $res = $this->cloud->getDir($this->path);
        $this->logger->debug("getList - ".$res);
        $list = $res['body']['list'];
        foreach ($list as $fileCloud) {
            $file = array();
            $file["NAME"] = $fileCloud['name'];
            $file["CREATED"] = date("Y-m-d H:i:s", $fileCloud['mtime']);
            $file["SIZE"] = $fileCloud['size'];
            //print_r($fileCloud);
            $file["URL"] = "https://cloud.mail.ru/home".$fileCloud['home'];
            $files[] = $file;
        }
        return $files;
    }
    
    public function uploadBackup($file, $backup)
    {
        $res = $this->cloud->loadFile($file,$this->path."/".$backup);
        $this->logger->debug("uploadBackup - ".$res);
    }
    
    public function deleteBackup($backup)
    {
        $filename = $this->path ."/". $backup;
        $res = $this->cloud->removeFile_from_cloud($filename);
        $this->logger->debug("deleteBackup - ".$res);
    }
    
    public function downloadBackup($backup, $file)
    {
        $this->logger->debug("downloadBackup - not supported");
    }
}

