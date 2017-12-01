<?php
require_once("IProvider.php");

class WebDavBackup implements IProvider
{
    public $error;
    
    function __construct($url, $login, $password, $path)
    {
        $this->url = $url;
        $this->login = $login;
        $this->password = $password;
        $this->path = $path;
        
        require_once(DIR_MODULES . 'backup/provider/WebDav/Config.php');
        $config = new Config($this->url,$this->login,$this->password);
        require_once(DIR_MODULES . 'backup/provider/WebDav/Client.php');
        require_once(DIR_MODULES . 'backup/provider/WebDav/Result.php');
        $this->client = new Client($config);
    }
    
    public function getFreeSpace()
    {
        $result = $this->client->getSize();
        $result = $result->getResponseArray();
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
            return;
        }
        $result = $result->getResponseArray();
        //echo print_r($result);
        if ($result) { 
        //{"response":[{"href":"\/Backups\/","propstat":{"status":"HTTP\/1.1 200 OK","prop":{"resourcetype":{"collection":{}},"getlastmodified":"Thu, 19 Oct 2017 12:53:49 GMT","displayname":"Backups","creationdate":"2017-10-19T12:53:49Z"}}},{"href":"\/Backups\/Result.php","propstat":{"status":"HTTP\/1.1 200 OK","prop":{"resourcetype":{},"getlastmodified":"Sat, 21 Oct 2017 10:54:03 GMT","getetag":"e428489fbce726bcf57275b8ac8b2a6b","getcontenttype":"text\/php","getcontentlength":"747","displayname":"Result.php","creationdate":"2017-10-21T10:54:03Z"}}}]}end propfind
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
                $file["CREATED"] = $item["propstat"]["prop"]["creationdate"];
                if (isset($item["propstat"]["prop"]["getcontentlength"]))
                    $file["SIZE"] = $item["propstat"]["prop"]["getcontentlength"];
                //$file["URL"] = $item["href"];
                $files[] = $file;
                }
            }
            return $files;
        }
    }
    
    public function addBackup($file, $backup)
    {
        $result = $this->client->put($this->path."/".$backup,$file);
        if ($result->code != 200)
        {
            $this->error = $result->response;
            return;
        }
        $result = $result->getResponseArray();
        //echo print_r($result);
    }
    
    public function deleteBackup($backup)
    {
        $filename = $this->path ."/". $backup;
        $result = $this->client->delete($filename);
        if ($result->code != 200)
        {
            $this->error = $result->response;
            return;
        }
        $result = $result->getResponseArray();
        //echo print_r($result);
    }
	

}