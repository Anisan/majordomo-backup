<?php
require_once("IProvider.php");

class FtpBackup implements IProvider
{
    public $error;
    public $supportUpload = 1;
    
    function __construct($ftp_server, $login, $password, $path, $logger)
    {
        $this->path = $path;
        $this->ftp_server = $ftp_server;
        $this->logger = $logger;
        $this->conn_id = ftp_connect($ftp_server);
        $this->login_result = ftp_login($this->conn_id, $login, $password);
        if (!$this->login_result) {
            $this->logger->log('Error autorization');
            $this->error='Error autorization';
        }
    }
    
    public function getFreeSpace()
    {
        return -1;
    }
    
    public function getList()
    {
        if ($this->error) return;
        $pattern = IsWindowsOS() ? 'tar' : 'tgz';
        if (ftp_chdir($this->conn_id, $this->path) == false) {
            $this->logger->log('Change dir failed:'. $this->path);
            $this->error='Change dir failed:'. $this->path;
            return;
        }
        $systype = ftp_systype ( $this->conn_id );
        $this->logger->log('FTP system:'. $systype);
        $list = ftp_rawlist($this->conn_id, $this->path);
        $files = array();
        foreach ($list as $current) {
          switch ( $systype ) {
            case "Windows_NT" :
                $split = preg_split("[ ]", $current, 9, PREG_SPLIT_NO_EMPTY);
                if ($split[0] != "total") {
                    if ($split[0]{0} === "d") continue 2;
                    if (!strpos($split[3],$pattern)) continue 2;
                    $file = array();
                    $file["NAME"] = $split[3];
                    $time=substr($split[3],15,2).':'.substr($split[3],17,2);
                    $month = substr($split[3],11,2);
                    $day = substr($split[3],13,2);
                    $year=substr($split[3],7,4);
                    if (strpos($time, ':'))
                        $timestamp = strtotime($day . "-" . $month. "-". $year. " ". $time);
                    else
                        $timestamp = strtotime($day. "-". $month ."-". $year . " 00:00");
                    $date = date('Y-m-d H:i',  $timestamp);
                    $file["CREATED"] = $date;
                    $file["SIZE"] = $split[2];
                    $file["URL"] = "ftp://".$this->ftp_server."$this->path".$file["NAME"];
                    $files[] = $file;
                }
            break;
            case "UNIX" :
            default:
                $split = preg_split("[ ]", $current, 9, PREG_SPLIT_NO_EMPTY);
                if ($split[0] != "total") {
                    if ($split[0]{0} === "d") continue 2;
                    if (!strpos($split[8],$pattern)) continue 2;
                    $file = array();
                    $file["NAME"] = $split[8];
                    $time = $split[7];
                    $month = $split[5];
                    $day = $split[6];
                    if (strpos($time, ':'))
                        $timestamp = strtotime($day . " " . $month. " ". date("Y"). " ". $time . " UTC");
                    else
                        $timestamp = strtotime($day. " ". $month ." ". $time . " 00:00 UTC");
                    $date = date('Y-m-d H:i',  $timestamp);
                    $file["CREATED"] = $date;
                    $file["SIZE"] = $split[4];
                    $file["URL"] = "ftp://".$this->ftp_server."$this->path".$file["NAME"];
                    $files[] = $file;
                }
            break;
          }
        }
        return $files;
    }
    
    public function uploadBackup($file, $backup)
    {
        if ($this->error) return;
        
        if (ftp_chdir($this->conn_id, $this->path) == false) {
            $this->logger->log('Change dir failed:'. $this->path);
            $this->error='Change dir failed:'. $this->path;
            return;
        }
        if (!ftp_put($this->conn_id, $this->path."/".$backup, $file, FTP_BINARY)) {
            $this->logger->log('Upload failed:'. $this->path.$backup);
            $this->error='Upload failed:'. $this->path.$backup;
        } 
    }
    
    public function deleteBackup($backup)
    {
        if ($this->error) return;
        
        $filename = $backup;
        echo $filename;
        if (ftp_chdir($this->conn_id, $this->path) == false) {
            $this->logger->log('Change dir failed:'. $this->path);
            $this->error='Change dir failed:'. $this->path;
            return;
        }
        if (!ftp_delete($this->conn_id, $filename)) {
            $this->logger->log('Delete failed:'. $filename);
            $this->error='Delete failed:'. $filename;
        }
    }
	
    public function downloadBackup($backup, $file)
    {
        if ($this->error) return;
        $filename = $backup;
        if (ftp_chdir($this->conn_id, $this->path) == false) {
            $this->logger->log('Change dir failed:'. $this->path);
            $this->error='Change dir failed:'. $this->path;
            return;
        }
        if (!ftp_get($this->conn_id, $file, $this->path."/".$backup, FTP_BINARY)) {
            $this->logger->log('Upload failed:'. $this->path.$backup);
            $this->error='Upload failed:'. $this->path.$backup;
        } 
    }

}
