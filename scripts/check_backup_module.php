<?php
/**
 * Static regression check for the backup module hardening.
 * Run from the module root: php scripts/check_backup_module.php
 */
$root = dirname(__DIR__);
$files = array(
    'modules/backup/backup.class.php',
    'modules/backup/update_iframe.php',
    'modules/backup/provider/local.php',
);

$checks = array();
foreach ($files as $file) {
    $out = array();
    $code = 0;
    exec('php -l ' . escapeshellarg($root . '/' . $file) . ' 2>&1', $out, $code);
    $checks['syntax: ' . $file] = $code === 0;
}

$class = file_get_contents($root . '/modules/backup/backup.class.php');
$iframe = file_get_contents($root . '/modules/backup/update_iframe.php');
$local = file_get_contents($root . '/modules/backup/provider/local.php');

$checks['php8 constructor'] = strpos($class, 'function __construct()') !== false;
$checks['backup name validated'] = strpos($class, 'function safeBackupName(') !== false
    && strpos($class, '$backup = $this->safeBackupName($backup)') !== false;
$checks['no chdir in backup flows'] = strpos($class, 'chdir(') === false;
$checks['tar uses -C and escapeshellarg'] = strpos($class, "-C ' . escapeshellarg") !== false;
$checks['mysqldump args escaped'] = strpos($class, '--password=' . "' . escapeshellarg(DB_PASSWORD)") !== false
    || strpos($class, '--password=' . '" . escapeshellarg(DB_PASSWORD)') !== false;
$checks['missing gdrive provider not required'] = strpos($class, 'require_once(ROOT . "modules/backup/provider/gdrive.php")') === false;
$checks['provider fallback'] = strpos($class, 'if (!$provider) {') !== false;
$checks['iframe authorization'] = strpos($iframe, "empty(\$session->data['AUTHORIZED'])") !== false;
$checks['local delete uses basename'] = strpos($local, 'basename($backup)') !== false;

$failed = 0;
foreach ($checks as $name => $ok) {
    if (!$ok) {
        $failed++;
    }
    echo ($ok ? 'PASS' : 'FAIL') . ' - ' . $name . PHP_EOL;
}
echo $failed === 0 ? 'ALL CHECKS PASSED' . PHP_EOL : 'FAILED: ' . $failed . PHP_EOL;
exit($failed === 0 ? 0 : 1);
