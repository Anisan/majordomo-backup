<?php
/**
 * Regression check for credentials and language files.
 * Run from the module root: php scripts/check_credentials_languages.php
 */
$root = dirname(__DIR__);
$files = array(
    'modules/backup/backup.class.php',
    'templates/backup/action_admin.html',
    'languages/backup_default.php',
    'languages/backup_en.php',
    'languages/backup_ru.php',
    'languages/backup_uk.php',
);
$checks = array();
foreach ($files as $file) {
    $path = $root . '/' . $file;
    if (substr($file, -4) === '.php') {
        $out = array();
        $code = 0;
        exec('php -l ' . escapeshellarg($path) . ' 2>&1', $out, $code);
        $checks['syntax: ' . $file] = $code === 0;
    } else {
        $checks['exists: ' . $file] = is_file($path);
    }
}

$class = file_get_contents($root . '/modules/backup/backup.class.php');
$tpl = file_get_contents($root . '/templates/backup/action_admin.html');
$checks['no hardcoded passphrase'] = strpos($class, '!@#$%^&*12345678') === false;
$checks['no legacy encrypted keys'] = strpos($class, 'Ay+B8Tx2vHPVyby2tXH+Ww==') === false
    && strpos($class, 'x2C0p3QQz0i8nyRDrOOIMw==') === false;
$checks['no weak crypto helpers'] = strpos($class, 'function encrypt(') === false
    && strpos($class, 'function decrypt(') === false;
$checks['oauth client from config'] = strpos($tpl, 'client_id=<#DROPBOX_APP_KEY#>') !== false
    && strpos($tpl, 'name="dropbox_app_secret"') !== false;
$checks['config fields saved'] = strpos($class, "config['DROPBOX_APP_KEY']") !== false
    && strpos($class, "config['DROPBOX_APP_SECRET']") !== false;

$keyPattern = "/'(BAK_[A-Z_]+|ABOUT|HELP)'\s*=>/";
$keys = array();
foreach (array('default', 'en', 'ru', 'uk') as $lang) {
    $src = file_get_contents($root . '/languages/backup_' . $lang . '.php');
    preg_match_all($keyPattern, $src, $m);
    $keys[$lang] = $m[1];
}
foreach (array('en', 'ru', 'uk') as $lang) {
    $checks['language ' . $lang . ' parity'] = $keys[$lang] == $keys['default'] && count($keys[$lang]) > 0;
}

$failed = 0;
foreach ($checks as $name => $ok) {
    if (!$ok) { $failed++; }
    echo ($ok ? 'PASS' : 'FAIL') . ' - ' . $name . PHP_EOL;
}
echo $failed === 0 ? 'ALL CHECKS PASSED' . PHP_EOL : 'FAILED: ' . $failed . PHP_EOL;
exit($failed === 0 ? 0 : 1);
