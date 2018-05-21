<?php

chdir(dirname(__FILE__) . '/../../');

include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");

set_time_limit(0);

// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME);

include_once("./load_settings.php");
include_once(DIR_MODULES . "backup/backup.class.php");

$sv=new backup();

echo "<html>";
echo "<body>";


$out=array();

$cmd = $_GET['command'];
$name = $_GET['backup'];

if ($cmd == 'create')
    $res=$sv->create_backup($out, 1);
if ($cmd == 'restore')
    $res=$sv->restore_backup($name, $out, 1);
$sv->echonow($res);
if ($res) {
   $sv->echonow("Redirecting to main page...");
   $sv->echonow('<script language="javascript">window.top.location.href="'.ROOTHTML.'admin.php?md=panel&action=backup";</script>');
}

echo "</body>";
echo "</html>";


$db->Disconnect();
