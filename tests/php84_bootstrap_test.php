<?php
// Real CodeIgniter bootstrap using a disposable synthetic application.
// Never includes application/config/database.php or opens a network connection.
error_reporting(E_ALL);
define('ENVIRONMENT', 'testing');
$root = dirname(__DIR__);
$sandbox = sys_get_temp_dir().'/amr84_boot_'.bin2hex(random_bytes(8));
foreach (array('', '/config', '/controllers', '/models', '/views', '/logs', '/cache') as $directory) {
    mkdir($sandbox.$directory, 0700);
}
define('BASEPATH', $root.'/system/');
define('APPPATH', $sandbox.'/');
define('VIEWPATH', $sandbox.'/views/');
define('FCPATH', $sandbox.'/');
define('SELF', 'index.php');
define('SYSDIR', 'system');
// Common.php respects pre-existing handlers, so all diagnostics stay fatal.
function _error_handler($level, $message, $file, $line) {
    throw new ErrorException($message, 0, $level, $file, $line);
}
$config = array(
    'base_url' => 'https://example.invalid/amr/', 'index_page' => 'index.php',
    'uri_protocol' => 'REQUEST_URI', 'url_suffix' => '', 'language' => 'english',
    'charset' => 'UTF-8', 'enable_hooks' => FALSE, 'subclass_prefix' => 'MY_',
    'composer_autoload' => FALSE, 'permitted_uri_chars' => 'a-z 0-9~%.:_\-',
    'allow_get_array' => TRUE, 'enable_query_strings' => FALSE,
    'controller_trigger' => 'c', 'function_trigger' => 'm', 'directory_trigger' => 'd',
    'standardize_newlines' => FALSE, 'global_xss_filtering' => FALSE,
    'csrf_protection' => FALSE, 'compress_output' => FALSE, 'log_threshold' => 0,
    'log_path' => $sandbox.'/logs/', 'log_file_extension' => '',
    'log_file_permissions' => 0644, 'log_date_format' => 'Y-m-d H:i:s',
    'error_views_path' => $root.'/application/views/errors/',
    'cache_path' => $sandbox.'/cache/', 'cache_query_string' => FALSE,
    'time_reference' => 'local', 'proxy_ips' => '', 'encryption_key' => ''
);
file_put_contents($sandbox.'/config/config.php', '<?php $config = '.var_export($config, TRUE).';');
file_put_contents($sandbox.'/config/routes.php', '<?php $route = array("default_controller" => "probe");');
$autoload = array(
    'packages' => array(), 'libraries' => array('session', 'form_validation', 'user_agent'),
    'drivers' => array(), 'helper' => array('url', 'form', 'file'),
    'config' => array(), 'language' => array(), 'model' => array()
);
file_put_contents($sandbox.'/config/autoload.php', '<?php $autoload = '.var_export($autoload, TRUE).';');
file_put_contents($sandbox.'/models/Probe_model.php',
    '<?php class Probe_model extends CI_Model { public function label() { return "synthetic site"; } }');
file_put_contents($sandbox.'/views/probe.php', '<?php print $message;');
file_put_contents($sandbox.'/controllers/Probe.php', <<<'PHP'
<?php
class Probe extends CI_Controller {
    public function index() {
        $this->load->model('probe_model');
        if ($this->probe_model->label() !== 'synthetic site' || $this->input->get('site') !== 'test') {
            throw new RuntimeException('Framework injection/input failed.');
        }
        $this->form_validation->set_rules('meter', 'Meter', 'required|numeric');
        if ($this->form_validation->run() !== FALSE) {
            throw new RuntimeException('Empty form must fail validation.');
        }
        $db = $this->load->database(array(
            'dbdriver' => 'sqlite3', 'database' => ':memory:', 'db_debug' => FALSE
        ), TRUE);
        $db->query('CREATE TABLE test_meter (id INTEGER PRIMARY KEY, value REAL)');
        $db->insert('test_meter', array('id' => 1, 'value' => 12.5));
        $row = $db->get_where('test_meter', array('id' => 1))->row_array();
        if ((float) $row['value'] !== 12.5) {
            throw new RuntimeException('Native query builder round trip failed.');
        }
        $db->close();
        $output = $this->load->view('probe', array('message' => 'PASS: isolated PHP 8.4 framework bootstrap, loader, model, form validation and SQLite query builder.'), TRUE);
        $this->output->set_output($output."\n");
    }
}
PHP
);
$_GET = array('site' => 'test');
$_POST = $_COOKIE = array();
$_SERVER['HTTP_HOST'] = 'example.invalid';
$_SERVER['PHP_SELF'] = '/index.php';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['REQUEST_URI'] = '/probe';
$_SERVER['REQUEST_METHOD'] = 'GET';
register_shutdown_function(function () use ($sandbox) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($sandbox, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($iterator as $entry) {
        $entry->isDir() ? rmdir($entry->getPathname()) : unlink($entry->getPathname());
    }
    rmdir($sandbox);
});
require BASEPATH.'core/CodeIgniter.php';
