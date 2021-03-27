<?php
/**
* Backup 
* @package project
* @author Wizard <sergejey@gmail.com>
* @copyright http://majordomo.smartliving.ru/ (c)
* @version 0.1 (wizard, 14:10:57 [Oct 12, 2017])
*/
//
//
class backup extends module {
/**
* backup
*
* Module class constructor
*
* @access private
*/
function backup() {
  $this->name="backup";
  $this->title="Backup";
  $this->module_category="<#LANG_SECTION_SYSTEM#>";
  $this->pass="!@#$%^&*12345678";
  $this->app_key = $this->decrypt("Ay+B8Tx2vHPVyby2tXH+Ww==");
  $this->app_secret = $this->decrypt("x2C0p3QQz0i8nyRDrOOIMw==");
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=0) {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 return parent::saveParams($p);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  global $command;
  global $backup;
  $out['COMMAND']=$command;
  $out['BACKUP']=$backup;
  
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}
/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {
    $this->getConfig();
    
    if ($this->mode=='update_settings') {
        global $backup_debug;
        $this->config['BACKUP_DEBUG'] = $backup_debug;
        global $provider;
        $this->config['PROVIDER'] = $provider;
        global $local_path;
        $this->config['LOCAL_PATH'] = $local_path; 
        global $webdav_path;
        $this->config['WEBDAV_PATH'] = $webdav_path; 
        global $webdav_url;
        $this->config['WEBDAV_URL'] = $webdav_url; 
        global $webdav_login;
        $this->config['WEBDAV_LOGIN'] = $webdav_login; 
        global $webdav_password;
        $this->config['WEBDAV_PASSWORD'] = $webdav_password; 
        global $mailru_path;
        $this->config['MAILRU_PATH'] = $mailru_path; 
        global $mailru_login;
        $this->config['MAILRU_LOGIN'] = $mailru_login; 
        global $mailru_password;
        $this->config['MAILRU_PASSWORD'] = $mailru_password; 
        global $ftp_path;
        $this->config['FTP_PATH'] = $ftp_path; 
        global $ftp_url;
        $this->config['FTP_URL'] = $ftp_url; 
        global $ftp_login;
        $this->config['FTP_LOGIN'] = $ftp_login; 
        global $ftp_password;
        $this->config['FTP_PASSWORD'] = $ftp_password; 
        
        if ($provider == 5 && $this->config['DROPBOX_TOKEN']=="")
        {
            $this->saveConfig();
            $dp = $this->getProvider();
            global $dropbox_code;
            $token = $dp->getToken($dropbox_code);
            $this->config['DROPBOX_TOKEN']=$token['t'];
            $this->config['DROPBOX_ACCOUNT']=$token['account_id'];
        }
        
        global $max_count;
        $this->config['MAX_COUNT'] = $max_count; 
        global $temp_backup_folder;
        $this->config['TEMP_BACKUP_FOLDER'] = $temp_backup_folder; 
        global $backup_database;
        $this->config['BACKUP_DATABASE'] = $backup_database; 
        global $backup_dirs;
        $this->config['BACKUP_DIRS'] = $backup_dirs; 
        global $script_create_id;
        $this->config['SCRIPT_CREATE_ID'] = $script_create_id;
        
        $this->saveConfig();
        $this->redirect("?");
    }
    if($this->mode == 'delete_backup') {
        global $name;
        $this->delete_backup($name);
        $this->redirect("?");
    }
    if($this->mode == 'connection_delete') {
        $this->config['DROPBOX_TOKEN']="";
        $this->config['DROPBOX_ACCOUNT']="";
        $this->saveConfig();
        $this->redirect("?");
    }
    
    if($this->view_mode == '') {
        $out['BACKUP_DEBUG'] = $this->config['BACKUP_DEBUG']; 
        $out['PROVIDER'] = $this->config['PROVIDER']; 
        $out['LOCAL_PATH'] = $this->config['LOCAL_PATH'];
        $out['WEBDAV_PATH'] = $this->config['WEBDAV_PATH'];
        $out['WEBDAV_URL'] = $this->config['WEBDAV_URL'];
        $out['WEBDAV_LOGIN'] = $this->config['WEBDAV_LOGIN'];
        $out['WEBDAV_PASSWORD'] = $this->config['WEBDAV_PASSWORD'];
        $out['MAILRU_PATH'] = $this->config['MAILRU_PATH'];
        $out['MAILRU_LOGIN'] = $this->config['MAILRU_LOGIN'];
        $out['MAILRU_PASSWORD'] = $this->config['MAILRU_PASSWORD'];
        $out['FTP_PATH'] = $this->config['FTP_PATH'];
        $out['FTP_URL'] = $this->config['FTP_URL'];
        $out['FTP_LOGIN'] = $this->config['FTP_LOGIN'];
        $out['FTP_PASSWORD'] = $this->config['FTP_PASSWORD'];
        $out['MAX_COUNT'] = $this->config['MAX_COUNT'];
        if ($out['MAX_COUNT'] == "")
            $out['MAX_COUNT'] = 10;
        $out['TEMP_BACKUP_FOLDER'] = $this->config['TEMP_BACKUP_FOLDER'];
        $out['BACKUP_DATABASE'] = $this->config['BACKUP_DATABASE'];
        $out['BACKUP_DIRS'] = $this->config['BACKUP_DIRS'];
        
        if ($this->config['PROVIDER'] == 5 && $this->config['DROPBOX_TOKEN']!="")
        {
            $dp = $this->getProvider();
            $info = $dp->GetInfo();
            $out['DROPBOX_LOGIN'] = $info;
        }
        
        $list_dir = array_diff(scandir(ROOT), array('.', '..'));
        $sel_dirs = explode(',',$this->config['BACKUP_DIRS']);
        foreach($list_dir as $dir) {
            $dir_item = array();
            $dir_item["DIR_NAME"] = $dir;
            if (in_array ($dir,$sel_dirs))
                $dir_item["DIR_CHECK"] = 1;
            $out["LIST_DIR"][] = $dir_item;
        }
        
        $out['SCRIPT_CREATE_ID'] = $this->config['SCRIPT_CREATE_ID'];
        $out['SCRIPTS']=SQLSelect("SELECT ID, TITLE FROM scripts ORDER BY TITLE");
        
        $this->get_backups($out);
    }
}

function format_filesize($bytes, $decimals = 2) {
  $sz = ' KMGTP';
  $factor = floor((strlen($bytes) - 1) / 3);
  return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) ." ". @$sz[$factor]."b";
}

function get_backups(&$out) {
    $provider = $this->getProvider();
    $freespace = $provider->getFreeSpace();
    if ($freespace > -1)
        $out["FREESPACE"] = $this->format_filesize($freespace);
    else
        $out["FREESPACE"] = "---";

    $backups = $provider->getList();
    $this->debug($backups);
    if ($backups)
    {
        usort($backups, function($a1, $a2) {
                    $v1 = strtotime($a1['CREATED']);
                    $v2 = strtotime($a2['CREATED']);
                    return $v1 - $v2; // $v2 - $v1 to reverse direction
                });
        foreach($backups as $backup) {
            $backup['SIZE'] = $this->format_filesize($backup['SIZE']);
            //paging($backup, 20, $out); // search result paging
            $out["RESULT"][] = $backup;
        }
    }
    $out["ERR_MSG"] = $provider->error;
    $out["RESTORE"] = $provider->supportUpload;
}
 
function delete_backup($name) {
  $provider = $this->getProvider();
  $provider->deleteBackup($name);  
 } 

function create_backup(&$out = false, $iframe = 0) {
    $state = "Unknow";
    $description = "";

    $this->log("Working on backup");
    
    set_time_limit(1800);
    $this->log("max_execution_time=".ini_get('max_execution_time'));

    $this->getConfig();
    
    if ($iframe) $this->echonow("<b>Working on backup.</b><br/>");
    
    if ($iframe) $this->echonow("<b>Check settings </b>");
    $this->log("BACKUP_DIRS = ".$this->config['BACKUP_DIRS']);
    if ($this->config['BACKUP_DIRS']=="")
    {
        if ($iframe) $this->echonow("Set backup directory.<br/>", 'red');
        $state = "Error";
        $description = "Set backup directory.";
    }
    else
    {
        if ($iframe) $this->echonow("Ok<br/>", 'green');
        $backup_dir = $this->config['TEMP_BACKUP_FOLDER'];
        if ($backup_dir=="")
            $backup_dir = ROOT;
        $backup_dir_temp = $backup_dir.'backup_temp';
        if (is_dir($backup_dir_temp )) { 
           if ($iframe) $this->echonow("Remove old temp directory $backup_dir_temp ... ");
           $this->removeTree($backup_dir_temp);
           if ($iframe) $this->echonow(" OK<br/>", 'green');
        }
        $backup_dir_temp .= DIRECTORY_SEPARATOR;
        
        $file = $backup_dir."backup";
        $file .= IsWindowsOS() ? '.tar' : '.tgz';
    
        
        if ($iframe) $this->echonow("Create temp directory $backup_dir_temp ... ");
            
        if (mkdir($backup_dir_temp, 0777)) {
            if ($iframe) $this->echonow(" OK<br/>", 'green');
            $sel_dirs = explode(',',$this->config['BACKUP_DIRS']);
            foreach($sel_dirs as $dir) {
                if ($dir == "backup_temp") continue;
                $this->log("Copy dir ".$dir);
            
                if ($iframe) $this->echonow("Backup $dir ...");
                if (!Is_Dir(ROOT . $dir))
                    $this->copyFile(ROOT . $dir, $backup_dir_temp . $dir );
                else
                    $this->copyTree(ROOT . $dir, $backup_dir_temp . $dir );
                if ($iframe) $this->echonow(" OK<br/>", 'green');
            }
            
            if ($this->config['BACKUP_DATABASE'])
            {
                $this->log("Backup datadase");
                if ($iframe) $this->echonow("Backup datadase ...");
                $this->backupdatabase($backup_dir_temp . 'dump.sql');
                if ($iframe) $this->echonow(" OK<br/>", 'green');
            }
            
            if ($iframe) $this->echonow("Packing $file ... ");
            $this->log("Packing $file");
            if (IsWindowsOS()) {
                $cmd = 'tar.exe --strip-components=2 -C '.$backup_dir_temp.' -cvf ' . $file . ' ./';
                $this->log($cmd);
                $result = exec($cmd);
                $new_name = str_replace('.tar', '.tar.gz', $file);
                $result = exec('gzip.exe ' . $file);
                if (file_exists($new_name)) {
                    $file = $new_name;
                }
            } else {
                chdir($backup_dir_temp);
                exec('tar cvzf ' . $file . ' .');
            }
            if (file_exists($file)) {
                if ($iframe) $this->echonow(" OK<br/>", 'green');
            
                if ($iframe) $this->echonow("Remove temp directory $backup_dir_temp ... ");
                $this->removeTree($backup_dir_temp);
                if ($iframe) $this->echonow(" OK<br/>", 'green');
                
                $description = $this->format_filesize(filesize($file));
                
                $this->log("Save to storage");
                if ($iframe) $this->echonow("Save to storage ... ");
                $backupName .= "backup_" . date("YmdHis");
                $backupName .= IsWindowsOS() ? '.tar' : '.tgz';
                $provider = $this->getProvider();
                $provider->uploadBackup($file,$backupName);
                unlink($file);
                if ($provider->error == "")
                {
                    if ($iframe) $this->echonow(" OK<br/>", 'green');
                    
                    $this->log("Delete old backups");
                    if ($iframe) $this->echonow("Delete old backups ... ");
                    $backups = $provider->getList();
                    if ($backups)
                    {
                        if (count($backups) > $this->config['MAX_COUNT'])
                        {
                            usort($backups, function($a1, $a2) {
                                $v1 = strtotime($a1['CREATED']);
                                $v2 = strtotime($a2['CREATED']);
                                return $v1 - $v2; // $v2 - $v1 to reverse direction
                            });
                            $need_delete = count($backups) - $this->config['MAX_COUNT'];
                            for ($i = 0; $i < $need_delete; $i++) {
                                $this->log("Delete old backup - ".$backups[$i]['NAME']);
                                $provider->deleteBackup($backups[$i]['NAME']);
                            }
                        }
                    }
                    if ($iframe) $this->echonow(" OK<br/>", 'green');
                    $this->log("End backup");
                    $state = "Ok";
                    if ($iframe) $this->echonow("<b>Backup end</b><br/>");
                }
                else
                {
                    if ($iframe) $this->echonow(" Error<br/>", 'red');
                    $this->log($provider->error);
                    $state = "Error";
                    $description = $provider->error;
                }
            }
            else
            {
                if ($iframe) $this->echonow(" Error<br/>", 'red');
                $state = "Error";
                $description = "Error packing";
            }
            
        }
        else
        {
            if ($iframe) $this->echonow(" Error<br/>", 'red');
            $state = "Error";
            $description = "Error create temp directory ".$backup_dir;
        }
    }
    
    if ($this->config['SCRIPT_CREATE_ID']) {
        $this->log("Run script ".$this->config['SCRIPT_CREATE_ID']);
        $params=array();
        $params['STATE']=$state;
        $params['DESCRIPTION']=$description;
        callAPI('/api/script/' . $this->config['SCRIPT_CREATE_ID'], 'GET', $params);
    }
    if ($state == "Ok")
        return "Ok";
}

function restore_backup($backup, &$out = false, $iframe = 0) {
    $state = "Unknow";
    $description = "";
    $this->log("Working on restore");
    if ($iframe) $this->echonow("<b>Working on restore.</b><br/>");

    $provider = $this->getProvider();
    
    $backup_dir = $this->config['TEMP_BACKUP_FOLDER'];
    if ($backup_dir=="")
        $backup_dir = ROOT;
    $backup_dir_temp = $backup_dir.'backup_temp';
    if (is_dir($backup_dir_temp)) { 
        if ($iframe) $this->echonow("Remove old temp directory $backup_dir_temp ... ");
        $this->removeTree($backup_dir_temp);
        if ($iframe) $this->echonow(" OK<br/>", 'green');
    }
    $backup_dir_temp .= DIRECTORY_SEPARATOR;
        
    $file = $backup_dir."backup";
    $file .= IsWindowsOS() ? '.tar.gz' : '.tgz';

    $this->log("Upload backup file $backup to $file");
        
    if ($iframe) $this->echonow("Upload backup file $backup to $file...");
    
    $provider->downloadBackup($backup,$file);
    if (file_exists($file)) {
        if ($iframe) $this->echonow(" OK<br/>", 'green');

        if ($iframe) $this->echonow("Create temp directory $backup_dir_temp ... ");
            
        if (mkdir($backup_dir_temp, 0777)) {
            if ($iframe) $this->echonow(" OK<br/>", 'green');
            
            $this->log("Unpack file $file");
            
            if ($iframe) $this->echonow("Unpack file $file ...");
            
            chdir($backup_dir);
            if (IsWindowsOS()) {
                $cmd = ROOT.'gunzip.exe ' . $file;
                $this->log($cmd);
                $result = exec($cmd);
                $this->log($result);
                $cmd = ROOT.'tar.exe -xf ' . str_replace('.tar.gz', '.tar', $file) . ' -C '.$backup_dir_temp;
                $this->log($cmd);
                $result = exec($cmd);
                $this->log($result);
            } else {
                chdir($backup_dir_temp);
                exec('tar xf ' . $file . ' -C '.$backup_dir_temp);
            }
            if ($iframe) $this->echonow(" OK<br/>", 'green');
            
            unlink($file);
                
            //copy files
            if ($iframe) $this->echonow("Updating files ... ");
            $this->copyTree($backup_dir_temp, ROOT, 1); // restore all files
            if ($iframe) $this->echonow(" OK<br/> ", 'green');

            // restore database
            $db_filename= $backup_dir_temp . 'dump.sql';
            if (file_exists($db_filename)) {
                if ($iframe) $this->echonow("Restoring database from $db_filename ... ");
                $this->restoredatabase($db_filename);
                if ($iframe) $this->echonow(" OK<br/> ", 'green');
                unlink(ROOT. 'dump.sql');
            }
            
            if ($iframe) $this->echonow("Remove temp directory $backup_dir_temp ... ");
            $this->removeTree($backup_dir_temp);
            if ($iframe) $this->echonow(" OK<br/>", 'green');
            
            if ($iframe) $this->echonow("Re-installing modules ... ");
            // code restore
            $source = ROOT . 'modules';
            if ($dir = @opendir($source)) {
                while (($file = readdir($dir)) !== false) {
                    if (Is_Dir($source . "/" . $file) && ($file != '.') && ($file != '..')) { // && !file_exists($source."/".$file."/installed")
                        @unlink(ROOT . "modules/" . $file . "/installed");
                    }
                }
            }
            @unlink(ROOT . "modules/control_modules/installed");
            if ($iframe) $this->echonow(" OK<br/> ", 'green');
            
            if ($iframe) $this->echonow("Rebooting system ... ");
            @SaveFile(ROOT . 'reboot', 'updated');
            if ($iframe) $this->echonow(" OK<br/> ", 'green');
            $state = "Ok";
        }
        else
        {
            if ($iframe) $this->echonow(" Error<br/>", 'red');
            $state = "Error";
            $description = "Error create temp directory ".$backup_dir;
        }
    }
    else
    {
       if ($iframe) $this->echonow(" Error<br/>", 'red');
       $state = "Error";
       $description = "Error upload";
    }
    
    
    if ($state == "Ok")
        return "Ok";
}


function echonow($msg, $color = '')
    {
        if ($color) {
            echo '<font color="' . $color . '">';
        }
        echo $msg;
        if ($color) {
            echo '</font>';
        }
        echo str_repeat(' ', 16 * 1024);
        flush();
        ob_flush();
    }

function backupdatabase($filename)
    {
        if (defined('PATH_TO_MYSQLDUMP'))
            $pathToMysqlDump = PATH_TO_MYSQLDUMP;
        else
            $pathToMysqlDump = IsWindowsOS() ? SERVER_ROOT . "/server/mysql/bin/mysqldump" : "/usr/bin/mysqldump";

        exec($pathToMysqlDump . " --host=\"" . DB_HOST . "\" --user=\"" . DB_USER . "\" --password=\"" . DB_PASSWORD . "\" --lock-tables=false --no-create-db --add-drop-table --ignore-table=".DB_NAME.".cached_values --databases " . DB_NAME . ">" . $filename);
    }
 function restoredatabase($filename)
    {
        $mysql_path = (substr(php_uname(), 0, 7) == "Windows") ? SERVER_ROOT . "/server/mysql/bin/mysql" : 'mysql';
        $mysqlParam = " -h " . DB_HOST;
        $mysqlParam .= " -u " . DB_USER;
        if (DB_PASSWORD != '') $mysqlParam .= " -p" . DB_PASSWORD;
        $mysqlParam .= " " . DB_NAME . " <" . $filename;
        exec($mysql_path . $mysqlParam);
        SQLExec("DELETE FROM cached_values");
        setGlobal('cycle_mainRun',time());
    }

function copyTree($source, $destination, $over = 0, $patterns = 0)
    {
        $res = 1;
        //Remove last slash '/' in source and destination - slash was added when copy
        $source = preg_replace("#/$#", "", $source);
        $destination = preg_replace("#/$#", "", $destination);

        if (!Is_Dir($source)) {
            return 0; // cannot create destination path
        }

        if (!Is_Dir($destination)) {
            if (!mkdir($destination)) {
                return 0; // cannot create destination path
            }
        }

        if ($dir = @opendir($source)) {
            while (($file = readdir($dir)) !== false) {
                if (Is_Dir($source . "/" . $file) && ($file != '.') && ($file != '..')) {
                    $res = $this->copyTree($source . "/" . $file, $destination . "/" . $file, $over, $patterns);
                } elseif (Is_File($source . "/" . $file) && (!file_exists($destination . "/" . $file) || $over)) {
                    if (!is_array($patterns)) {
                        $ok_to_copy = 1;
                    } else {
                        $ok_to_copy = 0;
                        $total = count($patterns);
                        for ($i = 0; $i < $total; $i++) {
                            if (preg_match('/' . $patterns[$i] . '/is', $file)) {
                                $ok_to_copy = 1;
                            }
                        }
                    }
                    if ($ok_to_copy) {
                        $res = copy($source . "/" . $file, $destination . "/" . $file);
                    }
                }
            }
            closedir($dir);
        }
        return $res;
    }

    function copyFile($source, $destination)
    {
        $tmp = explode('/', $destination);
        $total = count($tmp);
        if ($total > 0) {
            $d = $tmp[0];
            for ($i = 1; $i < ($total - 1); $i++) {
                $d .= '/' . $tmp[$i];
                if (!is_dir($d)) {
                    mkdir($d);
                }
            }
        }
        return copy($source, $destination);
    }

    function copyFiles($source, $destination, $over = 0, $patterns = 0)
    {
        $res = 1;
        if (!Is_Dir($source)) {
            return 0; // cannot create destination path
        }

        if (!Is_Dir($destination)) {
            if (!mkdir($destination)) {
                return 0; // cannot create destination path
            }
        }

        if ($dir = @opendir($source)) {
            while (($file = readdir($dir)) !== false) {
                if (Is_Dir($source . "/" . $file) && ($file != '.') && ($file != '..')) {
                    //$res=$this->copyTree($source."/".$file, $destination."/".$file, $over, $patterns);
                } elseif (Is_File($source . "/" . $file) && (!file_exists($destination . "/" . $file) || $over)) {
                    if (!is_array($patterns)) {
                        $ok_to_copy = 1;
                    } else {
                        $ok_to_copy = 0;
                        $total = count($patterns);
                        for ($i = 0; $i < $total; $i++) {
                            if (preg_match('/' . $patterns[$i] . '/is', $file)) {
                                $ok_to_copy = 1;
                            }
                        }
                    }
                    if ($ok_to_copy) {
                        $res = copy($source . "/" . $file, $destination . "/" . $file);
                    }
                }
            }
            closedir($dir);
        }
        return $res;
    }
    
    function removeTree($destination, $iframe = 0)
    {
        $res = 1;

        if (!Is_Dir($destination)) {
            return 0; // cannot create destination path
        }
        if ($dir = @opendir($destination)) {

            if ($iframe) {
                $this->echonow("Removing dir $destination ... ");
            }

            while (($file = readdir($dir)) !== false) {
                if (Is_Dir($destination . "/" . $file) && ($file != '.') && ($file != '..')) {
                    $res = $this->removeTree($destination . "/" . $file);
                } elseif (Is_File($destination . "/" . $file)) {
                    $res = @unlink($destination . "/" . $file);
                }
            }
            closedir($dir);
            $res = @rmdir($destination);

            if ($iframe) {
                $this->echonow("OK<br/>", "green");
            }


        }
        return $res;
    }
/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
 $this->admin($out);
}

function getProvider() {
    $this->getConfig();
    switch ($this->config['PROVIDER']) {
            case 0: // local
                $this->log("Provider - LocalBackup");
                require_once(ROOT . "modules/backup/provider/local.php");
                $provider = new LocalBackup($this->config['LOCAL_PATH'],$this);
                break;
            case 1: // WebDav
                $this->log("Provider - WebDavBackup");
                require_once(ROOT . "modules/backup/provider/webdav.php");
                $provider = new WebDavBackup($this->config['WEBDAV_URL'],$this->config['WEBDAV_LOGIN'],$this->config['WEBDAV_PASSWORD'],$this->config['WEBDAV_PATH'],$this);
                break;
            case 2: // GDrive
                $this->log("Provider - GdriveBackup");
                require_once(ROOT . "modules/backup/provider/gdrive.php");
                $provider = new GdriveBackup();
                break;
            case 3: // Cloud Mail.ru
                $this->log("Provider - MailRuBackup");
                require_once(ROOT . "modules/backup/provider/mailru.php");
                $provider = new MailRuBackup($this->config['MAILRU_LOGIN'],$this->config['MAILRU_PASSWORD'],$this->config['MAILRU_PATH'],$this);
                break;
            case 4: // FTP
                $this->log("Provider - FTP");
                require_once(ROOT . "modules/backup/provider/ftp.php");
                $provider = new FtpBackup($this->config['FTP_URL'],$this->config['FTP_LOGIN'],$this->config['FTP_PASSWORD'],$this->config['FTP_PATH'],$this);
                break;
            case 5: // Dropbox
                $this->log("Provider - Dropbox");
                require_once(ROOT . "modules/backup/provider/Dropbox.php");
                $provider = new DropboxBackup($this->app_key, $this->app_secret, $this->config['DROPBOX_ACCOUNT'],$this->config['DROPBOX_TOKEN'],$this);
                break;
    }
    return $provider;
}

function encrypt($text) 
{ 
    return base64_encode(openssl_encrypt($text, 'aes-128-cbc', $this->pass, OPENSSL_RAW_DATA, $this->pass));
    
} 

function decrypt($text) 
{ 
    return openssl_decrypt(base64_decode($text), 'aes-128-cbc', $this->pass, OPENSSL_RAW_DATA, $this->pass);
} 



function debug($content) {
    if($this->config['BACKUP_DEBUG'])
        $this->log(print_r($content,true));
}
function log($message) {
        if (is_array($message))
            $message = json_encode($message, JSON_UNESCAPED_UNICODE);
        DebMes($message,"backup");
}

/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($data='') {
  parent::install();
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgT2N0IDEyLCAyMDE3IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
