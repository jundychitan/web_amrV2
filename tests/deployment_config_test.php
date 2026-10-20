<?php
// Run: php tests/deployment_config_test.php
// No application bootstrap or database connection; only synthetic environment.
define('BASEPATH', dirname(__DIR__).'/system/');
$root = dirname(__DIR__);
$assertions = 0;
function check_deployment($condition, $message) {
    if (!$condition) {
        throw new RuntimeException('FAILED: '.$message);
    }
    $GLOBALS['assertions']++;
}
function production_config($root) {
    $config = array();
    include $root.'/application/config/production/config.php';
    return $config;
}
function production_database($root) {
    $db = array();
    include $root.'/application/config/production/database.php';
    return $db;
}
function requires_configuration($callback) {
    try {
        $callback();
    } catch (RuntimeException $error) {
        $GLOBALS['assertions']++;
        return;
    }
    throw new RuntimeException('Missing/unsafe configuration was accepted.');
}
$keys = array('AMR_BASE_URL', 'AMR_SESSION_PATH', 'AMR_DB_HOST', 'AMR_DB_NAME',
    'AMR_DB_USER', 'AMR_DB_PASSWORD', 'AMR_DB_PORT', 'AMR_DB_CHARSET', 'AMR_DB_COLLATION');
$saved = array();
foreach ($keys as $key) {
    $saved[$key] = getenv($key);
    putenv($key);
}
try {
    requires_configuration(function () use ($root) { production_config($root); });
    requires_configuration(function () use ($root) { production_database($root); });
    putenv('AMR_SESSION_PATH='.sys_get_temp_dir());
    foreach (array('http://example.invalid/amr/', 'https://example.invalid/amr/?x=y',
        'https://user@example.invalid/amr/', 'https://example.invalid/#fragment') as $bad_url) {
        putenv('AMR_BASE_URL='.$bad_url);
        requires_configuration(function () use ($root) { production_config($root); });
    }
    putenv('AMR_BASE_URL=https://example.invalid/amr');
    $config = production_config($root);
    check_deployment($config['base_url'] === 'https://example.invalid/amr/', 'canonical URL');
    check_deployment($config['cookie_secure'] === TRUE, 'HTTPS cookies');
    check_deployment($config['cookie_httponly'] === TRUE, 'HTTP-only cookies');
    check_deployment($config['composer_autoload'] === FALSE, 'no nonexistent vendor autoload');
    putenv('AMR_DB_HOST=localhost');
    putenv('AMR_DB_NAME=disposable_test');
    putenv('AMR_DB_USER=disposable_user');
    putenv('AMR_DB_PASSWORD='.bin2hex(random_bytes(32)));
    $db = production_database($root);
    check_deployment($db['default']['database'] === 'disposable_test', 'explicit database');
    check_deployment($db['default']['db_debug'] === FALSE, 'no public SQL debug');
    check_deployment($db['default']['pconnect'] === FALSE, 'no persistent connection');
    check_deployment($db['default']['save_queries'] === FALSE, 'no retained SQL queries');
    putenv('AMR_DB_CHARSET=utf8mb4');
    putenv('AMR_DB_COLLATION=utf8mb4_unicode_ci');
    $db = production_database($root);
    check_deployment($db['default']['char_set'] === 'utf8mb4', 'configurable source charset');
    check_deployment($db['default']['dbcollat'] === 'utf8mb4_unicode_ci', 'configurable source collation');
    print('PASS: '.$assertions." deployment configuration assertions (no database used).\n");
} finally {
    foreach ($saved as $key => $value) {
        putenv($value === FALSE ? $key : $key.'='.$value);
    }
}
