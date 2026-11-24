<?php
/**
 * Static check for cloud providers in the backup module.
 * Run from the module root: php scripts/check_providers.php
 */
$root = dirname(__DIR__);
$files = array(
    'modules/backup/backup.class.php',
    'modules/backup/provider/gdrive.php',
    'modules/backup/provider/s3.php',
);
$checks = array();
foreach ($files as $file) {
    $out = array();
    $code = 0;
    exec('php -l ' . escapeshellarg($root . '/' . $file) . ' 2>&1', $out, $code);
    $checks['syntax: ' . $file] = $code === 0;
}
$class = file_get_contents($root . '/modules/backup/backup.class.php');
$tpl = file_get_contents($root . '/templates/backup/action_admin.html');
$gdrive = file_get_contents($root . '/modules/backup/provider/gdrive.php');
$s3 = file_get_contents($root . '/modules/backup/provider/s3.php');

$checks['gdrive factory'] = strpos($class, 'new GoogleDriveBackup(') !== false
    && strpos($class, "provider/gdrive.php") !== false;
$checks['s3 factory'] = strpos($class, 'new AmazonS3Backup(') !== false
    && strpos($class, "provider/s3.php") !== false;
$checks['config fields'] = strpos($class, "config['GDRIVE_REFRESH_TOKEN']") !== false
    && strpos($class, "config['S3_SECRET']") !== false;
$checks['gdrive oauth'] = strpos($gdrive, 'grant_type') !== false
    && strpos($gdrive, 'uploadType=multipart') !== false;
$checks['s3 signature v4'] = strpos($s3, 'AWS4-HMAC-SHA256') !== false
    && strpos($s3, 'hash_hmac') !== false;
$checks['template options'] = strpos($tpl, 'value="6"') !== false
    && strpos($tpl, 'value="7"') !== false;
$checks['template fields'] = strpos($tpl, 'name="gdrive_refresh_token"') !== false
    && strpos($tpl, 'name="s3_secret"') !== false;

$failed = 0;
foreach ($checks as $name => $ok) {
    if (!$ok) { $failed++; }
    echo ($ok ? 'PASS' : 'FAIL') . ' - ' . $name . PHP_EOL;
}
echo $failed === 0 ? 'ALL CHECKS PASSED' . PHP_EOL : 'FAILED: ' . $failed . PHP_EOL;
exit($failed === 0 ? 0 : 1);
