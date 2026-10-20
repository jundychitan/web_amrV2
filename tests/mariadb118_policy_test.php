<?php
// Offline-only: no environment reads, database connection, or model constructor.
error_reporting(E_ALL);
set_error_handler(function ($level, $message, $file, $line) {
    throw new ErrorException($message, 0, $level, $file, $line);
});
require __DIR__.'/mariadb118_support.php';
$assertions = 0;
function policy_check(bool $condition): void {
    if (!$condition) {
        throw new RuntimeException('Integration policy assertion failed.');
    }
    $GLOBALS['assertions']++;
}
function policy_reject(callable $test): void {
    try {
        $test();
    } catch (Amr118Failure $error) {
        $GLOBALS['assertions']++;
        return;
    }
    throw new RuntimeException('Unsafe configuration/query option was accepted.');
}
$environment = array('HOST' => '127.0.0.1', 'PORT' => '3306', 'NAME' => 'amr_test_offline',
    'USER' => 'test_only', 'PASSWORD' => bin2hex(random_bytes(24)));
$options = amr118_options(array('--confirm-staging=amr_test_offline'));
$settings = amr118_config($environment, $options);
policy_check($settings['db_debug'] === FALSE && $settings['save_queries'] === FALSE);
policy_check($settings['pconnect'] === FALSE);
policy_reject(fn() => amr118_config(array(), $options));
policy_reject(fn() => amr118_config($environment, amr118_options(array())));
policy_reject(fn() => amr118_config(array_replace($environment, array('NAME' => 'meter_reading')), $options));
policy_reject(fn() => amr118_config(array_replace($environment, array('PORT' => '0')), $options));
policy_reject(fn() => amr118_config(array_replace($environment, array('PORT' => '65536')), $options));
policy_reject(fn() => amr118_config(array_replace($environment, array('HOST' => 'db.example.invalid')), $options));
policy_reject(fn() => amr118_config(array_replace($environment, array('COLLATION' => "bad';SQL")), $options));
policy_reject(fn() => amr118_options(array('--password=not-accepted')));
policy_reject(fn() => amr118_options(array('--confirm-staging=x', '--confirm-staging=x')));
policy_check(amr118_version('11.8.6-MariaDB'));
policy_check(!amr118_version('11.4.9-MariaDB'));
policy_check(!amr118_version('8.4.0-MySQL'));
policy_check(amr118_normalize('utf8_general_ci') === amr118_normalize('utf8mb3_general_ci'));
policy_check(amr118_normalize('utf8mb4_general_ci') !== amr118_normalize('utf8_general_ci'));
$transport = new class {
    public bool $success = TRUE;
    public int $queries = 0;
    public function query($sql) { $this->queries++; return $this->success; }
};
amr118_control($transport, 'START TRANSACTION READ ONLY');
policy_check($transport->queries === 1);
$transport->success = FALSE;
policy_reject(fn() => amr118_control($transport, 'CREATE TEMPORARY TABLE `user_tb` LIKE `user_tb`'));
policy_reject(fn() => amr118_control($transport, 'DROP TABLE user_tb'));
policy_check($transport->queries === 2);
policy_check(amr118_allowed('SELECT * FROM user_tb', FALSE));
foreach (array('UPDATE user_tb SET id = 1', 'DELETE FROM user_tb WHERE id = 1',
    'INSERT INTO user_tb (id) VALUES (1)', 'DROP TABLE user_tb', 'CALL operation()',
    'SELECT * FROM user_tb FOR UPDATE', "SELECT 1 INTO OUTFILE '/tmp/file'") as $sql) {
    policy_check(!amr118_allowed($sql, FALSE));
}
foreach (array('UPDATE `user_tb` SET id = 1', 'DELETE FROM user_tb WHERE id = 1',
    'INSERT INTO `user_tb` (id) VALUES (1)') as $sql) {
    policy_check(amr118_allowed($sql, TRUE));
}
foreach (array('UPDATE meter_site SET id = 1', 'DELETE FROM meter_data WHERE id = 1',
    'DROP TABLE user_tb', 'UPDATE amr_test_offline.user_tb SET id = 1') as $sql) {
    policy_check(!amr118_allowed($sql, TRUE));
}
define('BASEPATH', dirname(__DIR__).'/system/');
require BASEPATH.'core/Model.php';
foreach (array('Login_model', 'Admin_model', 'Client_model', 'Report_model') as $class) {
    require dirname(__DIR__).'/application/models/'.$class.'.php';
}
$context = array('user_id' => 0, 'site_id' => 0, 'site_code' => 'sample',
    'meter_id' => 0, 'meter' => 'sample', 'rtu_id' => 0, 'time' => '2026-01-15 00:00:00');
$labels = array();
foreach (amr118_catalog($context) as [$label, $class, $method, $parameters]) {
    $reflection = new ReflectionMethod($class, $method);
    policy_check($reflection->isPublic() && count($parameters) >= $reflection->getNumberOfRequiredParameters());
    policy_check(!isset($labels[$label]));
    $labels[$label] = TRUE;
}
$GLOBALS['amr_policy_ci'] = new stdClass();
function &get_instance() { return $GLOBALS['amr_policy_ci']; }
$ci = $GLOBALS['amr_policy_ci'];
$ci->load = new class { public function helper($name) {} };
$ci->input = new class { public function post($name) { return 'synthetic-meter'; } };
$ci->db = new class {
    public array $queries = array();
    public function escape($value) { return "'".str_replace("'", "''", (string) $value)."'"; }
    public function query($sql) {
        $this->queries[] = $sql;
        return new class { public function row_array() { return array(); } };
    }
};
$report = (new ReflectionClass('Report_model'))->newInstanceWithoutConstructor();
foreach (amr118_catalog($context) as [$label, $class, $method, $parameters]) {
    if (in_array($label, array('report.fifteen-minute', 'report.hourly', 'report.daily'), TRUE)) {
        policy_check($report->$method(...$parameters) === array());
    }
}
policy_check(count($ci->db->queries) === 3);
policy_check(str_contains($ci->db->queries[1], '2026-01-15 00:54:59'));
print('PASS: '.$assertions." MariaDB validator policy/catalog assertions (offline; no connection).\n");
