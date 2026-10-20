<?php
// Real framework classes, synthetic configuration, no application DB settings.
error_reporting(E_ALL);
set_error_handler(function ($level, $message, $file, $line) {
    throw new ErrorException($message, 0, $level, $file, $line);
});
define('BASEPATH', dirname(__DIR__).'/system/');
define('APPPATH', dirname(__DIR__).'/application/');
define('VIEWPATH', APPPATH.'views/');
define('ENVIRONMENT', 'testing');
define('UTF8_ENABLED', TRUE);
function log_message($level, $message) {}
function is_php($version) { return version_compare(PHP_VERSION, $version, '>='); }
function is_cli() { return FALSE; } // Exercise actual web-session initialization.
function config_item($key) { return $GLOBALS['test_config'][$key] ?? NULL; }
$config = array(
    'subclass_prefix' => 'MY_', 'sess_driver' => 'files',
    'sess_cookie_name' => 'amr84test', 'sess_expiration' => 7200,
    'cookie_path' => '/', 'cookie_domain' => '', 'cookie_secure' => FALSE,
    'sess_match_ip' => FALSE, 'sess_time_to_update' => 300,
    'sess_regenerate_destroy' => TRUE
);
$path = sys_get_temp_dir().'/amr84_sessions_'.bin2hex(random_bytes(8));
mkdir($path, 0700);
$config['sess_save_path'] = $path;
$GLOBALS['test_config'] = $config;
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SESSION = array();
$assertions = 0;
function framework_check($value, $label) {
    if (!$value) {
        throw new RuntimeException('FAILED: '.$label);
    }
    $GLOBALS['assertions']++;
}
require BASEPATH.'core/Controller.php';
require BASEPATH.'core/Loader.php';
require BASEPATH.'core/Model.php';
require BASEPATH.'libraries/Session/Session.php';
try {
    // CI intentionally injects services dynamically. Check actual base classes.
    $controller = (new ReflectionClass('CI_Controller'))->newInstanceWithoutConstructor();
    $controller->test_service = new stdClass();
    $loader = (new ReflectionClass('CI_Loader'))->newInstanceWithoutConstructor();
    $loader->test_service = $controller->test_service;
    framework_check($loader->test_service === $controller->test_service, 'dynamic service injection');
    $session = new CI_Session();
    framework_check(session_status() === PHP_SESSION_ACTIVE, 'real file session starts');
    $session->set_userdata('meter_site', 'test-site');
    $session->set_flashdata('notice', 'saved');
    $original_id = session_id();
    $session->sess_regenerate(TRUE);
    framework_check(session_id() !== $original_id, 'session ID regeneration');
    framework_check(!is_file($path.'/amr84test'.$original_id), 'old session file removed on regeneration');
    framework_check($session->userdata('meter_site') === 'test-site', 'session data survives regeneration');
    session_write_close();
    session_start();
    framework_check($_SESSION['meter_site'] === 'test-site', 'session persists to private files');
    $_SESSION['__ci_vars']['fresh'] = 'new';
    $_SESSION['fresh'] = 'one-request';
    $_SESSION['__ci_vars']['expired'] = time() - 10;
    $_SESSION['expired'] = 'expired';
    $_SESSION['__ci_vars']['future'] = time() + 300;
    $_SESSION['future'] = 'valid';
    // PHP 8 compares nonnumeric strings differently; old flashdata must expire.
    $_SESSION['__ci_vars']['notice'] = 'old';
    $init = new ReflectionMethod('CI_Session', '_ci_init_vars');
    $init->invoke($session);
    framework_check(!isset($_SESSION['notice']), 'old flashdata expires');
    framework_check(isset($_SESSION['fresh']), 'new flashdata survives one request');
    framework_check(!isset($_SESSION['expired']), 'expired temporary data removed');
    framework_check(isset($_SESSION['future']), 'future temporary data retained');
    session_destroy();
    $sid = new ReflectionProperty('CI_Session', '_sid_regexp');
    $driverConfig = array(
        'save_path' => $path, '_sid_regexp' => $sid->getValue($session),
        'match_ip' => FALSE, 'cookie_name' => 'amr84test',
        'cookie_path' => '/', 'cookie_domain' => '', 'cookie_secure' => FALSE
    );
    $driver = new CI_Session_files_driver($driverConfig);
    $expiredFile = $path.'/amr84test'.str_repeat('a', strlen($original_id));
    $freshFile = $path.'/amr84test'.str_repeat('b', strlen($original_id));
    file_put_contents($expiredFile, 'expired');
    touch($expiredFile, time() - 10000);
    file_put_contents($freshFile, 'fresh');
    file_put_contents($path.'/keep.txt', 'unrelated');
    framework_check($driver->gc(7200) === 1, 'garbage collector returns deleted-file count');
    framework_check(!file_exists($expiredFile), 'expired session deleted');
    framework_check(file_exists($freshFile) && file_exists($path.'/keep.txt'), 'GC preserves fresh/unrelated files');
    print('PASS: '.$assertions." framework/session assertions (strict PHP 8.4; no database).\n");
} finally {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
    foreach (glob($path.'/*') as $file) {
        unlink($file);
    }
    rmdir($path);
}
