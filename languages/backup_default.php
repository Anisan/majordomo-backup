<?php
/**
* Default language file for Telegram module
*
*/
$dictionary=array(
//general
'ABOUT' => 'About',
'HELP' => 'Help',
'BAK_BACKUPS' => 'Backups',
'BAK_SETTING_STORAGE' => 'Settings storage',
'BAK_SETTING_BACKUP' => 'Settings backup',
'BAK_SETTING_NOTIFY' => 'Settings notify',
//backups
'BAK_CREATE' => 'Create Backup',
'BAK_FREESPACE' => 'Free space',
'BAK_DOWNLOAD' => 'Download',
//storage
'BAK_PROVIDER' => 'Provider',
'BAK_LOCAL_PATH' => 'Local path',
'BAK_PATH' => 'Path',
'BAK_LOGIN' => 'Login',
'BAK_PASSWORD' => 'Password',
'BAK_URL' => 'Url',
'BAK_ACCESS_CODE' => 'Access code',
'BAK_GET_CODE' => 'Get code',
'BAK_ACCOUNT' => 'Account',
'BAK_DELETE_CONNECTION' => 'Delete connection',
'BAK_MAX_COUNT' => 'Max count backups',
//backup
'BAK_TEMP_FOLDER' => 'Temporary folder for backup',
'BAK_BACKUP_DATABASE' => 'Backup database',
'BAK_BACKUP_FOLDERS' => 'Backup folders',
'BAK_SELECT_DEFAULT' => 'Select Default',
'BAK_SELECT_ALL' => 'Select All',
'BAK_UNSELECT_ALL' => 'Unselect All',
//notify
'BAK_NOTIFY_SCRIPT' => 'Execute on created backup',

/* end module names */
);

foreach ($dictionary as $k=>$v) {
 if (!defined('LANG_'.$k)) {
  define('LANG_'.$k, $v);
 }
}

?>