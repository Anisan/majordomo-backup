<?php
require_once("IProvider.php");
require 'CloudMailRu.php';


class MailRuBackup implements IProvider
{
    public $error;
    
    function __construct($login, $password, $path)
    {
        $this->path = $path;
        $this->cloud = new CloudMailRu($login, $password);
        if (!$this->cloud->login()) {
            $this->error='Error autorization';
        }
    }
    
    public function getFreeSpace()
    {
        $res = $this->cloud->getSpace($this->path);
        //print_r($res);
        $used = $res['body']['used'];
        $total = $res['body']['total'];
        $free = $total - $used;
        return $free * 1024 * 1024;
    }
    
    public function getList()
    {
        $res = $this->cloud->getDir($this->path);
        //print_r($res);
        $list = $res['body']['list'];
        foreach ($list as $fileCloud) {
            $file = array();
            $file["NAME"] = $fileCloud['name'];
            $file["CREATED"] = date("d/m/Y H:i:s", $fileCloud['mtime']);
            $file["SIZE"] = $fileCloud['size'];
            //$file["URL"] = $filename;
            $files[] = $file;
        }
        return $files;
    }
    
    public function addBackup($file, $backup)
    {
        $res = $this->cloud->loadFile($file,$this->path."/".$backup);
        print_r($res);
    }
    
    public function deleteBackup($backup)
    {
        $filename = $this->path ."/". $backup;
        $res = $this->cloud->removeFile_from_cloud($filename);
        print_r($res);
    }
}

