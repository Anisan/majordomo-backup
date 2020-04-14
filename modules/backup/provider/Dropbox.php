<?php
require_once("IProvider.php");
require_once 'DropboxClient.php';


class DropboxBackup implements IProvider
{
    public $error;
    private $dropbox;
    public $supportUpload = 1;
    
    function __construct($app_key, $app_secret, $account, $token, $logger)
    {
        $this->dropbox = new DropboxClient( array(
            'app_key'         => $app_key,
            'app_secret'      => $app_secret,
            'app_full_access' => false,
        ) );
        $this->logger = $logger;
        if (!empty( $token ))
        {
            $bearer_token = array( 't' => $token, 'account_id' => $account );
            $this->dropbox->SetBearerToken( $bearer_token );
        }
    }
    
    public function getInfo()
    {
        if (!$this->dropbox->IsAuthorized()) return "";
        $info = $this->dropbox->GetAccountInfo();
        return "<img src='".$info->profile_photo_url."'  width='24' height='24'> ". $info->name->display_name;
    }
    
    public function getToken($code)
    {
        return $this->dropbox->GetBearerToken( $code, "");
    }
    
    public function getFreeSpace()
    {
        if (!$this->dropbox->IsAuthorized()) return -1;
        
        $res = $this->dropbox->GetFreeSpace();
        //print_r($res);
        $this->logger->debug("getFreeSpace - ".json_encode($res));
        $used = $res->used;
        $total = $res->allocation->allocated;
        $free = $total - $used;
        return $free;
    }
    
    public function getList()
    {
        if (!$this->dropbox->IsAuthorized()) return $files;
        
        $res = $this->dropbox->GetFiles( "", false );
        //print_r ($res);
        $this->logger->debug("getList - ".json_encode($res));
        foreach ($res as $fileCloud) {
            $file = array();
            $file["NAME"] = $fileCloud->name;
            $time = strtotime($fileCloud->client_modified.' UTC');
            $dateInLocal = date("Y-m-d H:i:s", $time);
            $file["CREATED"] = $dateInLocal;
            $file["SIZE"] = $fileCloud->size;
            $file["URL"] = $this->dropbox->GetLink( $fileCloud );
            $files[] = $file;
        }
        return $files;
    }
    
    public function uploadBackup($file, $backup)
    {
        if (!$this->dropbox->IsAuthorized()) return;
        
        $res = $this->dropbox->UploadFile($file,$backup);
        $this->logger->debug("uploadBackup - ".json_encode($res));
    }
    
    public function deleteBackup($backup)
    {
        if (!$this->dropbox->IsAuthorized()) return;
        
        $filename = "/". $backup;
        $res = $this->dropbox->Delete($filename);
        $this->logger->debug("deleteBackup - ".json_encode($res));
    }
    
    public function downloadBackup($backup, $file)
    {
        if (!$this->dropbox->IsAuthorized()) return;
        $filename = "/". $backup;
        $res = $this->dropbox->DownloadFile($filename,$file);
        $this->logger->debug("downloadBackup - ".json_encode($res));
    }
}

