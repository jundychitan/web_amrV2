<?php
// No network connection: inject a fake transport into the real mysqli adapter.
error_reporting(E_ALL);
set_error_handler(function ($level, $message, $file, $line) {
    throw new ErrorException($message, 0, $level, $file, $line);
});
define('BASEPATH', dirname(__DIR__).'/system/');
function log_message($level, $message) {}
function is_php($version) { return version_compare(PHP_VERSION, $version, '>='); }
require BASEPATH.'database/DB_driver.php';
require BASEPATH.'database/DB_query_builder.php';
class CI_DB extends CI_DB_query_builder {}
require BASEPATH.'database/drivers/mysqli/mysqli_driver.php';
class Php84FakeTransport {
    public $fail = FALSE;
    public $errno = 0;
    public $error = '';
    public $queries = array();
    public function query($sql) {
        $this->queries[] = $sql;
        if ($this->fail) {
            $this->errno = 1064;
            $this->error = 'Synthetic query failure';
            throw new mysqli_sql_exception('Synthetic query failure', 1064);
        }
        return TRUE;
    }
}
$db = new CI_DB_mysqli_driver(array('db_debug' => FALSE, 'failover' => array()));
$transport = new Php84FakeTransport();
$db->conn_id = $transport;
$transport->fail = TRUE;
if ($db->query('UPDATE synthetic_table SET value = 1') !== FALSE) {
    throw new RuntimeException('Failed SQL must return FALSE when db_debug is disabled.');
}
if ($db->error()['code'] !== 1064) {
    throw new RuntimeException('Native error details must remain available.');
}
$transport->fail = FALSE;
$db->reconnect();
if (end($transport->queries) !== 'SELECT 1' || $db->conn_id !== $transport) {
    throw new RuntimeException('Connection health check must use nondeprecated SQL.');
}
$transport->fail = TRUE;
$db->reconnect();
if ($db->conn_id !== FALSE) {
    throw new RuntimeException('Broken connection must be invalidated.');
}
print("PASS: 4 mysqli compatibility assertions (fake transport; no network).\n");
