<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
// Opt-in only. Never include application/config/database.php or bootstrap index.php.
require __DIR__.'/mariadb118_support.php';

function amr118_main(array $arguments): int {
    $db = NULL;
    $temporary = FALSE;
    try {
        $options = amr118_options($arguments);
        if ($options['help']) {
            print("MariaDB 11.8 real-model query validator (PHP 8.4).\n"
                ."Usage: php tests/mariadb118_query_test.php --confirm-staging=amr_test_NAME [--temporary-writes]\n"
                ."       php tests/mariadb118_query_test.php --list\n"
                ."Requires protected AMR_TEST_DB_HOST, PORT, NAME, USER, PASSWORD environment variables.\n"
                ."Optional AMR_TEST_DB_CHARSET/COLLATION default to the app's utf8/utf8_general_ci;\n"
                ."AMR_TEST_DB_SSL_CA is required for remote connections without a localhost tunnel.\n"
                ."Use only a restored staging clone and a SELECT-only account; add CREATE TEMPORARY TABLES\n"
                ."for temporary writes. No permanent INSERT/UPDATE/DELETE/DDL/EXECUTE privileges.\n"
                ."Exit: 0 = selected checks passed; 1 = validation failure; 2 = setup/incomplete coverage.\n"
                ."No credentials, rows, raw SQL, or database exception messages are printed.\n");
            return 0;
        }
        if ($options['list']) {
            foreach (amr118_catalog(amr118_empty_context()) as [$label, $class, $method]) {
                print($label.' => '.$class.'::'.$method."\n");
            }
            return 0;
        }
        if (PHP_VERSION_ID < 80400 || PHP_VERSION_ID >= 80500 || !extension_loaded('mysqli')) {
            throw new Amr118Failure('PHP 8.4 with mysqli is required.');
        }
        $environment = array();
        foreach (array('HOST', 'PORT', 'NAME', 'USER', 'PASSWORD', 'CHARSET', 'COLLATION', 'SSL_CA') as $key) {
            $value = getenv('AMR_TEST_DB_'.$key);
            if ($value !== FALSE) {
                $environment[$key] = $value;
            }
        }
        $settings = amr118_config($environment, $options);
        error_reporting(E_ALL);
        date_default_timezone_set('Asia/Manila');
        ini_set('display_errors', '0');
        ini_set('log_errors', '0'); // SQL/connection errors may contain secrets.
        set_error_handler(function ($level, $message, $file, $line) {
            throw new ErrorException('PHP diagnostic during integration test.', 0, $level, $file, $line);
        });
        amr118_load_framework();
        $db = new Amr118Database($settings);
        if (!$db->initialize()) {
            throw new Amr118Failure('Connection failed. Check protected credentials, privileges and TLS privately.');
        }
        $identity = $db->query("SELECT VERSION() AS version, DATABASE() AS db_name,
            @@SESSION.sql_mode AS sql_mode, @@character_set_connection AS charset,
            @@collation_connection AS collation, @@SESSION.time_zone AS time_zone,
            @@GLOBAL.sql_mode AS global_mode")->row_array();
        if (!amr118_version($identity['version']) || $identity['db_name'] !== $settings['database']) {
            throw new Amr118Failure('Refusing a non-MariaDB-11.8 server or unexpected database.');
        }
        // Fixed control SQL only; no external SQL or application DDL is accepted.
        amr118_control($db->conn_id, 'SET SESSION max_statement_time = 5');
        amr118_control($db->conn_id, 'START TRANSACTION READ ONLY');
        print('MariaDB '.$identity['version']."; mode=application (stricton=FALSE).\n");
        print('Session charset='.$identity['charset'].'; collation='.$identity['collation'].".\n");
        print('Session sql_mode='.$identity['sql_mode'].".\n");
        print('Server global sql_mode='.$identity['global_mode'].'; session timezone='.$identity['time_zone'].".\n");
        $results = new Amr118Results();
        $results->check('connection.charset', fn() => amr118_assert(
            amr118_normalize($identity['charset']) === amr118_normalize($settings['char_set'])));
        $results->check('connection.collation', fn() => amr118_assert(
            amr118_normalize($identity['collation']) === amr118_normalize($settings['dbcollat'])));
        print("NOTE: collation mismatches are failures, not automatically corrected by this test.\n");

        $ci = new stdClass();
        $ci->db = $db;
        $ci->input = new Amr118Input();
        $ci->load = new Amr118Loader();
        $GLOBALS['amr118_ci'] = $ci;
        $models = array();
        foreach (array('Login_model', 'Admin_model', 'Client_model', 'Report_model', 'Session_lock_model') as $class) {
            require dirname(__DIR__).'/application/models/'.$class.'.php';
            $models[$class] = (new ReflectionClass($class))->newInstanceWithoutConstructor();
        }
        $contexts = array('missing' => amr118_empty_context());
        $results->check('fixture.sample-context', function () use ($db, &$contexts, $results) {
            $user = $db->query('SELECT id FROM user_tb ORDER BY id LIMIT 1')->row_array();
            $site = $db->query('SELECT id, site_code FROM meter_site ORDER BY id LIMIT 1')->row_array();
            $meter = $db->query('SELECT id, meter_name, meter_site_name, meter_site_id,
                rtu_sn_number_id FROM meter_details ORDER BY id LIMIT 1')->row_array();
            if (!$user || !$site || !$meter) {
                $results->skip('representative-data', 'Missing setup rows; only empty/sentinel query paths are available.');
                return;
            }
            $c = amr118_empty_context();
            $c['user_id'] = $user['id'];
            $c['site_id'] = $meter['meter_site_id'];
            $c['site_code'] = $meter['meter_site_name'];
            $c['meter_id'] = $meter['id'];
            $c['meter'] = $meter['meter_name'];
            $c['rtu_id'] = $meter['rtu_sn_number_id'];
            $reading = $db->query('SELECT datetime FROM meter_data WHERE meter_id = ?
                AND location = ? ORDER BY datetime LIMIT 1', array($c['meter'], $c['site_code']))->row_array();
            if ($reading) {
                $c['time'] = $reading['datetime'];
            } else {
                $results->skip('populated-report-data', 'No readings for the sampled meter; report-result correctness remains untested.');
            }
            $contexts['sample'] = $c;
        });
        foreach ($contexts as $contextName => $c) {
            $_SESSION = array('user_id' => $c['user_id'], 'user_name' => 'amr-test');
            $ci->input->post = array(
                'user_name' => '__amr_missing__', 'user_password' => bin2hex(random_bytes(16)),
                'meter_name' => $c['meter'],
                'date_log' => substr($c['time'], 0, 10).' 00:00:00 - '.substr($c['time'], 0, 10).' 23:59:59'
            );
            foreach (amr118_catalog($c) as [$label, $class, $method, $parameters]) {
                $results->check($contextName.'.'.$label, function () use ($models, $class, $method, $parameters, $db) {
                    $before = $db->checkedQueries;
                    $models[$class]->$method(...$parameters);
                    amr118_assert($db->checkedQueries > $before);
                });
            }
        }
        if ($options['temporary_writes']) {
            $results->check('temporary.prepare', function () use ($db, &$temporary, $ci) {
                // Capture before shadowing: schema-qualified names also resolve
                // to a same-named temporary table after CREATE TEMPORARY TABLE.
                $row = $db->query('SELECT * FROM user_tb ORDER BY id LIMIT 1')->row_array();
                if (!$row) {
                    throw new Amr118Failure('Temporary fixture needs an existing staging user row.');
                }
                $columns = $db->query("SELECT COLUMN_NAME, EXTRA FROM information_schema.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user_tb'")->result_array();
                foreach ($columns as $column) {
                    if (stripos($column['EXTRA'], 'GENERATED') !== FALSE) {
                        unset($row[$column['COLUMN_NAME']]);
                    }
                }
                foreach (array('id', 'user_name', 'user_password', 'active_session_hash', 'active_session_activity') as $key) {
                    if (!array_key_exists($key, $row)) {
                        throw new Amr118Failure('Staging user_tb does not match the required application schema.');
                    }
                }
                // MariaDB forbids temporary-table DDL inside READ ONLY.
                // Permanent DML/DDL privileges must not be granted to this account.
                amr118_control($db->conn_id, 'ROLLBACK');
                amr118_control($db->conn_id, 'CREATE TEMPORARY TABLE `user_tb` LIKE `user_tb`');
                $temporary = $db->temporaryUser = TRUE;
                amr118_control($db->conn_id, 'START TRANSACTION READ ONLY');
                $password = bin2hex(random_bytes(24));
                $row['user_name'] = 'amr_'.bin2hex(random_bytes(3));
                $row['user_password'] = md5($password);
                $row['active_session_hash'] = $row['active_session_activity'] = NULL;
                $db->insert('user_tb', $row);
                $ci->input->post = array('user_name' => $row['user_name'], 'user_password' => $password);
                $ci->test_user_id = $row['id'];
            });
            if ($temporary && isset($ci->test_user_id)) {
                $lock = $models['Session_lock_model'];
                $id = $ci->test_user_id;
                $results->check('temporary.login-valid', fn() => amr118_assert(
                    count($models['Login_model']->log_in_correctly()) === 1));
                $ci->input->post['user_password'] .= 'wrong';
                $results->check('temporary.login-invalid', fn() => amr118_assert(
                    $models['Login_model']->log_in_correctly() === array()));
                $token = NULL;
                $results->check('temporary.lock-acquire', function () use ($lock, $id, &$token) {
                    $token = $lock->acquire($id);
                    amr118_assert(is_string($token) && strlen($token) === 64);
                });
                if (is_string($token)) {
                    $results->check('temporary.lock-competing-login', fn() => amr118_assert($lock->acquire($id) === FALSE));
                    $results->check('temporary.lock-refresh', fn() => amr118_assert($lock->refresh($id, $token) === TRUE));
                    $results->check('temporary.lock-wrong-token', fn() => amr118_assert($lock->refresh($id, str_repeat('a', 64)) === FALSE));
                    $results->check('temporary.lock-release', fn() => amr118_assert($lock->release($id, $token) === TRUE));
                    $results->check('temporary.lock-reacquire', fn() => amr118_assert(is_string($lock->acquire($id))));
                } else {
                    $results->skip('temporary.lock-followups', 'No ownership token was acquired.');
                }
                $results->check('temporary.query-builder-update', function () use ($db, $id) {
                    $db->where('id', $id)->update('user_tb', array('active_session_hash' => NULL, 'active_session_activity' => NULL));
                    amr118_assert($db->get_where('user_tb', array('id' => $id))->num_rows() === 1);
                });
                $results->check('temporary.query-builder-delete', function () use ($db, $id) {
                    $db->where('id', $id)->delete('user_tb');
                    amr118_assert($db->get_where('user_tb', array('id' => $id))->num_rows() === 0);
                });
            } else {
                $results->skip('temporary.write-cases', 'Temporary fixture could not be prepared.');
            }
        } else {
            print("NOT RUN: optional temporary login/session-lock/CRUD checks (use --temporary-writes).\n");
        }
        print("Coverage: curated model execution, not every query/branch or report-result equivalence.\n");
        print('Queries checked: '.$db->checkedQueries.'; passed='.$results->passed
            .'; failed='.$results->failed.'; skipped='.$results->skipped.".\n");
        return $results->failed ? 1 : ($results->skipped ? 2 : 0);
    } catch (Amr118Failure $error) {
        print('STOP: '.$error->getMessage()."\n");
        return 2;
    } catch (Throwable $error) {
        print("STOP: connection/schema/runtime setup failed; inspect privately. No exception details emitted.\n");
        return 2;
    } finally {
        if ($db !== NULL && $db->conn_id) {
            try {
                amr118_control($db->conn_id, 'ROLLBACK');
                if ($temporary) {
                    amr118_control($db->conn_id, 'DROP TEMPORARY TABLE IF EXISTS `user_tb`');
                }
            } catch (Throwable $error) {
                // Connection shutdown still removes temporary tables; never
                // fall back to DROP TABLE or other permanent cleanup.
                print("NOTE: cleanup connection failed; no permanent cleanup was attempted.\n");
            } finally {
                try {
                    $db->close();
                } catch (Throwable $error) {
                    // Process exit closes any remaining native connection.
                }
            }
        }
    }
}

function amr118_empty_context(): array {
    return array('user_id' => 0, 'site_id' => 0, 'site_code' => '__amr_missing__',
        'meter_id' => 0, 'meter' => '__amr_missing__', 'rtu_id' => 0,
        'time' => '2026-01-15 00:00:00');
}

function amr118_assert(bool $condition): void {
    if (!$condition) {
        throw new Amr118Failure('Expected test condition was not met.');
    }
}

class Amr118Input {
    public array $post = array();
    public function post($key) { return $this->post[$key] ?? NULL; }
}
class Amr118Loader {
    public function helper($name) {}
    public function library($name) {}
}
class Amr118Results {
    public int $passed = 0;
    public int $failed = 0;
    public int $skipped = 0;
    public function check(string $label, callable $test): void {
        try {
            if (isset($GLOBALS['amr118_ci']->db)) {
                $GLOBALS['amr118_ci']->db->reset_query();
            }
            $test();
            $this->passed++;
            print('PASS: '.$label."\n");
        } catch (Throwable $error) {
            $this->failed++;
            $detail = $error instanceof Amr118Failure ? $error->getMessage() : 'PHP runtime diagnostic (details withheld).';
            print('FAIL: '.$label.'; '.$detail."\n");
        }
    }
    public function skip(string $label, string $reason): void {
        $this->skipped++;
        print('SKIP: '.$label.'; '.$reason."\n");
    }
}

function amr118_load_framework(): void {
    define('BASEPATH', dirname(__DIR__).'/system/');
    define('ENVIRONMENT', 'testing');
    // No real controller/application bootstrap or config files are loaded.
    require BASEPATH.'core/Model.php';
    require BASEPATH.'database/DB_driver.php';
    require BASEPATH.'database/DB_query_builder.php';
    class CI_DB extends CI_DB_query_builder {}
    require BASEPATH.'database/drivers/mysqli/mysqli_driver.php';
    class Amr118Database extends CI_DB_mysqli_driver {
        public int $checkedQueries = 0;
        public bool $temporaryUser = FALSE;
        public function query($sql, $binds = FALSE, $return_object = NULL) {
            if (!amr118_allowed($sql, $this->temporaryUser)) {
                throw new Amr118Failure('Query blocked by the read/temporary-write policy.');
            }
            $result = parent::query($sql, $binds, $return_object);
            $this->checkedQueries++;
            if ($result === FALSE) {
                throw new Amr118Failure('SQL error code '.(int) $this->error()['code'].'.');
            }
            if ($this->conn_id->warning_count > 0) {
                throw new Amr118Failure('Server returned SQL warnings; review this case privately.');
            }
            return $result;
        }
    }
}
function &get_instance() { return $GLOBALS['amr118_ci']; }
function log_message($level, $message) {} // Do not log SQL/credentials through framework error paths.
function is_php($version) { return version_compare(PHP_VERSION, $version, '>='); }
function config_item($key) { return $key === 'charset' ? 'UTF-8' : NULL; }

exit(amr118_main(array_slice($argv, 1)));
