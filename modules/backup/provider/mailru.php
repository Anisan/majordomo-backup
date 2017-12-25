<?php
require_once("IProvider.php");
require 'CloudMailRu.php';


class MailRuBackup implements IProvider
{
    public $error;
    
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
            //$file["URL"] = $filename;
            $files[] = $file;
        }
        return $files;
    }
    
    public function addBackup($file, $backup)
    {
        $res = $this->cloud->loadFile($file,$this->path."/".$backup);
        $this->logger->debug("addBackup - ".$res);
    }
    
    public function deleteBackup($backup)
    {
        $filename = $this->path ."/". $backup;
        $res = $this->cloud->removeFile_from_cloud($filename);
        $this->logger->debug("deleteBackup - ".$res);
    }
}

