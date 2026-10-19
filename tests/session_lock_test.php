<?php
// Run: C:\xampp\php\php.exe tests\session_lock_test.php
// Uses only disposable SQLite databases, never application database credentials.
define('BASEPATH', dirname(__DIR__).'/system/');

class CI_Model {
    public function __get($key) { return get_instance()->$key; }
}
class CI_Controller {
    public function __construct()
    {
        foreach (get_object_vars(get_instance()) as $key => $value) {
            $this->$key = $value;
        }
    }
}
function &get_instance() { return $GLOBALS['test_ci']; }
function log_message($level, $message) {}
class TestRedirect extends Exception {}
class TestHttpError extends Exception {}
function redirect($uri) { throw new TestRedirect($uri); }
function show_error($message, $status) { throw new TestHttpError($message, $status); }

require dirname(__DIR__).'/application/models/Session_lock_model.php';
require dirname(__DIR__).'/application/hooks/Session_lock.php';
require dirname(__DIR__).'/application/controllers/Login.php';

class TestResult {
    private $rows;
    public function __construct($rows) { $this->rows = $rows; }
    public function num_rows() { return count($this->rows); }
}
class TestDatabase {
    public $pdo;
    public $now = 100000;
    public $db_debug = TRUE;
    public $fail = FALSE;
    public $throw = FALSE;
    public $zero_changed_rows = FALSE;
    private $affected = 0;

    public function __construct($path = ':memory:')
    {
        $this->pdo = new PDO('sqlite:'.$path);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec('PRAGMA busy_timeout = 5000');
        $this->pdo->sqliteCreateFunction('UNIX_TIMESTAMP', function () { return $this->now; }, 0);
    }
    public function dbprefix($table) { return $table; }
    public function affected_rows() { return $this->affected; }
    public function query($sql, $bindings)
    {
        if ($this->throw) { throw new RuntimeException('Simulated database exception'); }
        if ($this->fail) { return FALSE; }
        $statement = $this->pdo->prepare($sql);
        foreach ($bindings as $index => $value) {
            $statement->bindValue($index + 1, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $statement->execute();
        $this->affected = $this->zero_changed_rows ? 0 : $statement->rowCount();
        return stripos(ltrim($sql), 'SELECT') === 0
            ? new TestResult($statement->fetchAll(PDO::FETCH_ASSOC)) : TRUE;
    }
}
class TestSession {
    public $data = array();
    public $destroyed = FALSE;
    public $regenerated = FALSE;
    public function userdata($key = NULL)
    {
        return $key === NULL ? $this->data : (isset($this->data[$key]) ? $this->data[$key] : NULL);
    }
    public function set_userdata($data) { $this->data = array_merge($this->data, $data); }
    public function unset_userdata($keys)
    {
        foreach ($keys as $key) { unset($this->data[$key]); }
    }
    public function sess_destroy() { $this->destroyed = TRUE; }
    public function sess_regenerate($destroy) { $this->regenerated = $destroy; }
}
class TestLoader {
    public $views = array();
    public function model($name) {}
    public function helper($name) {}
    public function library($name) {}
    public function view($name, $data = array()) { $this->views[$name] = $data; }
}
class TestValidation {
    public $valid = TRUE;
    public function set_rules($field, $label, $rules) {}
    public function run() { return $this->valid; }
    public function set_message($rule, $message) {}
}
class TestCredentials {
    public $rows = array();
    public function log_in_correctly() { return $this->rows; }
}
function setup($db = NULL)
{
    $ci = new stdClass();
    $ci->db = $db === NULL ? new TestDatabase() : $db;
    $ci->session = new TestSession();
    $ci->load = new TestLoader();
    $ci->form_validation = new TestValidation();
    $ci->login_model = new TestCredentials();
    $ci->session_lock_model = new Session_lock_model();
    $GLOBALS['test_ci'] = $ci;
    return $ci;
}
function seed($db)
{
    $db->pdo->exec('CREATE TABLE user_tb (
        id INTEGER PRIMARY KEY, active_session_hash TEXT NULL, active_session_activity INTEGER NULL)');
    $db->pdo->exec('INSERT INTO user_tb (id) VALUES (1), (2)');
}
function authenticate($ci, $token)
{
    $ci->session->data = array(
        'user_id' => 1, 'currently_logged_in_as' => 'Admin', 'active_session_token' => $token
    );
}
$assertions = 0;
function check($condition, $label)
{
    $GLOBALS['assertions']++;
    if ( ! $condition) { throw new RuntimeException('FAILED: '.$label); }
}
function expect_redirect($callback, $destination)
{
    try { $callback(); } catch (TestRedirect $redirect) {
        check($redirect->getMessage() === $destination, 'redirect to '.$destination);
        return;
    }
    throw new RuntimeException('Expected redirect: '.$destination);
}
function expect_unavailable($callback)
{
    try { $callback(); } catch (TestHttpError $error) {
        check($error->getCode() === 503, 'database outage blocks request with HTTP 503');
        return;
    }
    throw new RuntimeException('Expected HTTP 503');
}

// Worker processes execute the production atomic claim against one shared file.
if (isset($argv[1]) && $argv[1] === '--claim') {
    $ci = setup(new TestDatabase($argv[2]));
    while (microtime(TRUE) < (float) $argv[3]) { usleep(1000); }
    $claim = $ci->session_lock_model->acquire(1);
    print(is_string($claim) ? 'claimed' : ($claim === FALSE ? 'occupied' : 'error'));
    exit;
}

$ci = setup();
seed($ci->db);
$model = $ci->session_lock_model;
$token = $model->acquire(1);
check(is_string($token) && strlen($token) === 64, 'first login receives a random token');
check($ci->db->pdo->query('SELECT active_session_hash FROM user_tb WHERE id = 1')->fetchColumn()
    === hash('sha256', $token), 'only the token hash is stored in the database');
check($model->acquire(1) === FALSE, 'second browser is blocked');
check(is_string($model->acquire(2)), 'another account can still log in');
check($model->refresh(1, $token) === TRUE, 'owning session remains valid');
$ci->db->zero_changed_rows = TRUE;
check($model->refresh(1, $token) === TRUE, 'MySQL same-second no-op refresh remains valid');
$ci->db->zero_changed_rows = FALSE;
check($model->refresh(1, str_repeat('a', 64)) === FALSE, 'wrong token cannot refresh');
check($model->refresh(1, NULL) === FALSE, 'legacy session without token is rejected');
check($model->refresh(1, array()) === FALSE, 'malformed token is rejected');
check($model->release(1, str_repeat('a', 64)), 'stale logout is harmless');
check($model->acquire(1) === FALSE, 'stale logout did not free the active account');
$ci->db->now += 7199;
check($model->acquire(1) === FALSE, 'lock remains just before two-hour expiry');
check($model->refresh(1, $token) === TRUE, 'activity extends the expiry');
$ci->db->now += 7199;
check($model->acquire(1) === FALSE, 'expiry is based on the latest request');
$ci->db->now++;
check($model->refresh(1, $token) === FALSE, 'expired owner cannot revive its lock');
$replacement = $model->acquire(1);
check(is_string($replacement) && $replacement !== $token, 'new login claims exactly at expiry');
check($model->refresh(1, $token) === FALSE, 'old token rejected after replacement');
check($model->release(1, $token), 'old logout after replacement is harmless');
check($model->refresh(1, $replacement) === TRUE, 'old logout cannot clear replacement');
check($model->release(1, $replacement), 'current logout releases lock');
check(is_string($model->acquire(1)), 'immediate login after logout succeeds');
check($model->acquire(999) === FALSE, 'deleted account cannot acquire a lock');

$ci->db->fail = TRUE;
check($model->acquire(1) === NULL, 'failed claim denies login');
check($model->refresh(1, $replacement) === NULL, 'failed refresh denies access');
check($model->release(1, $replacement) === FALSE, 'failed logout is reported');
check($ci->db->db_debug === TRUE, 'database debug setting is restored');
$ci->db->fail = FALSE;
$ci->db->throw = TRUE;
check($model->acquire(1) === NULL, 'database exceptions deny login');
check($ci->db->db_debug === TRUE, 'debug setting restored after exception');
$ci->db->throw = FALSE;
$ci->db->db_debug = FALSE;
$model->refresh(1, $replacement);
check($ci->db->db_debug === FALSE, 'disabled database debugging stays disabled');

// Central hook uses the same production lock model as login/logout.
$hook = new Session_lock();
$hook->enforce();
check( ! $ci->session->destroyed, 'anonymous login page remains available');
authenticate($ci, NULL);
expect_redirect(function () use ($hook) { $hook->enforce(); }, 'Login/invalid');
check($ci->session->destroyed && $ci->session->data === array(), 'legacy auth cleared before redirect');
$ci->session = new TestSession();
$ci->db->now += 7200;
$token = $model->acquire(1);
authenticate($ci, $token);
$hook->enforce();
check( ! $ci->session->destroyed, 'owning browser passes central guard');
$ci->session->sess_regenerate(TRUE);
$hook->enforce();
check($ci->session->userdata('active_session_token') === $token, 'session ID rotation preserves lock ownership');
$ci->db->fail = TRUE;
expect_unavailable(function () use ($hook) { $hook->enforce(); });
check( ! $ci->session->destroyed, 'transient outage does not discard owning session');
$ci->db->fail = FALSE;
$ci->db->now += 7200;
expect_redirect(function () use ($hook) { $hook->enforce(); }, 'Login/invalid');
check($ci->session->data === array(), 'expired authentication is cleared');

// Exercise production login and logout handlers with isolated framework doubles.
$ci = setup();
seed($ci->db);
$ci->login_model->rows = array(array(
    'id' => 1, 'user_name' => 'test', 'user_real_name' => 'Test User',
    'user_type' => 'Admin', 'user_list_src' => 'AMR', 'user_access' => ''
));
$login = new Login();
expect_redirect(function () use ($login) { $login->login_action(); }, 'admin/welcome');
check($ci->session->regenerated, 'login regenerates the browser session ID');
$original = $ci->session->userdata('active_session_token');
check(is_string($original), 'login stores token with authentication');
$ci->session = new TestSession();
$ci->load = new TestLoader();
$second = new Login();
$second->login_action();
check(strpos($ci->load->views['login_view']['login_error'], 'already logged in') !== FALSE,
    'blocked browser sees account-in-use message');
check($ci->session->data === array(), 'blocked browser never receives authentication');
authenticate($ci, $original);
$owner = new Login();
$owner->login_action();
check($ci->session->userdata('active_session_token') === $original, 'repeat login keeps existing ownership');
$ci->db->fail = TRUE;
expect_unavailable(function () use ($owner) { $owner->logout(); });
check( ! $ci->session->destroyed, 'failed logout retains session so it can be retried');
$ci->db->fail = FALSE;
$owner->logout();
check($ci->session->destroyed && $ci->session->data === array(), 'logout clears local auth');
check(is_string($ci->session_lock_model->acquire(1)), 'logout handler releases database lock');
$ci->session = new TestSession();
$ci->load = new TestLoader();
$ci->db->fail = TRUE;
$failed = new Login();
$failed->login_action();
check($ci->session->data === array(), 'database failure never authenticates browser');
check(strpos($ci->load->views['login_view']['login_error'], 'Unable to start') !== FALSE,
    'database failure has a distinct login message');
$ci->db->fail = FALSE;
$ci->form_validation->valid = FALSE;
$invalid = new Login();
$invalid->login_action();
check($ci->session->data === array(), 'invalid credentials never authenticate browser');
foreach (array('BA' => 'ba/welcome', 'OM' => 'om/welcome', 'RE' => 're/welcome') as $role => $uri) {
    $ci = setup();
    seed($ci->db);
    $ci->login_model->rows = array(array(
        'id' => 1, 'user_name' => 'test', 'user_real_name' => 'Test User',
        'user_type' => $role, 'user_list_src' => 'SAP', 'user_id_sap' => 123, 'user_access' => ''
    ));
    $login = new Login();
    expect_redirect(function () use ($login) { $login->login_action(); }, $uri);
    check($ci->session->userdata('user_id_sap') === 123, 'SAP user mapping preserved for '.$role);
}
$ci = setup();
$ci->db->pdo->exec('CREATE TABLE user_tb (id INTEGER PRIMARY KEY)');
check($ci->session_lock_model->acquire(1) === NULL, 'unapplied schema fails closed');

// Start two independent PHP processes at the same deadline against an unlocked account.
$path = tempnam(sys_get_temp_dir(), 'amr_lock_test_');
try {
    $db = new TestDatabase($path);
    seed($db);
    $deadline = sprintf('%.6f', microtime(TRUE) + 1);
    $workers = array();
    for ($i = 0; $i < 2; $i++) {
        $command = escapeshellarg(PHP_BINARY).' '.escapeshellarg(__FILE__).
            ' --claim '.escapeshellarg($path).' '.$deadline;
        $process = proc_open($command, array(0 => array('pipe', 'r'),
            1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes, NULL, NULL,
            array('bypass_shell' => TRUE));
        if ( ! is_resource($process)) { throw new RuntimeException('Cannot start claim worker'); }
        fclose($pipes[0]);
        $workers[] = array($process, $pipes);
    }
    $results = array();
    $worker_errors = array();
    foreach ($workers as $worker) {
        $results[] = stream_get_contents($worker[1][1]);
        $errors = stream_get_contents($worker[1][2]);
        fclose($worker[1][1]);
        fclose($worker[1][2]);
        $status = proc_close($worker[0]);
        $worker_errors[] = $status === 0 && $errors === '' ? '' : 'Exit '.$status.': '.$errors;
    }
    sort($results);
    foreach ($worker_errors as $error) {
        check($error === '', 'concurrent worker succeeds '.$error);
    }
    check($results === array('claimed', 'occupied'),
        'exactly one simultaneous login wins: '.json_encode($results));
} finally {
    // Remove only this test's temporary database, never application data.
    $db->pdo = NULL;
    unlink($path);
}
print('PASS: '.$assertions." session-lock assertions (isolated SQLite; no live database used).\n");
