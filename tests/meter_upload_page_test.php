<?php
error_reporting(E_ALL);
set_error_handler(function ($level, $message, $file, $line) {
    throw new ErrorException($message, 0, $level, $file, $line);
});
define('BASEPATH', dirname(__DIR__).'/system/');
define('APPPATH', dirname(__DIR__).'/application/');
#[AllowDynamicProperties]
class CI_Controller {}
function redirect($location) { $GLOBALS['redirect_location'] = $location; }
function base_url($path = '') { return '/amr/'.$path; }
function site_url($path) { return '/amr/index.php/'.$path; }
class Upload_page_session {
    public $role;
    public function userdata($key) { return $this->role; }
}
class Upload_page_input {
    public $verb = 'GET';
    public function method($upper) { return $this->verb; }
}
class Upload_page_output {
    public $status = 200;
    public $headers = array();
    public function set_header($header) { $this->headers[] = $header; return $this; }
    public function set_status_header($status) { $this->status = $status; return $this; }
}
class Upload_page_model {
    public $calls = 0;
    public function site_list() {
        $this->calls++;
        return array(array('id' => 7, 'site_name' => '<script>bad</script>', 'site_code' => '"SITE"'));
    }
}
class Upload_page_loader {
    public $views = array();
    public function view($name, $data = array()) { $this->views[$name] = $data; }
}
require APPPATH.'controllers/Admin.php';
$controller = (new ReflectionClass('Admin'))->newInstanceWithoutConstructor();
$controller->session = new Upload_page_session();
$controller->input = new Upload_page_input();
$controller->output = new Upload_page_output();
$controller->admin_model = new Upload_page_model();
$controller->load = new Upload_page_loader();
$assertions = 0;
function page_check($value, $label) {
    if (!$value) { throw new RuntimeException('FAILED: '.$label); }
    $GLOBALS['assertions']++;
}
foreach (array(NULL, 'BA', 'OM', 'RE') as $role) {
    $controller->session->role = $role;
    $controller->meter_upload();
    page_check($GLOBALS['redirect_location'] === 'Login/invalid'
        && !$controller->admin_model->calls && !$controller->load->views, 'non-admin cannot read sites');
}
$controller->session->role = 'Admin';
$controller->input->verb = 'POST';
$controller->meter_upload();
page_check($controller->output->status === 405 && !$controller->load->views, 'page is GET-only');
$controller->input->verb = 'GET';
$controller->meter_upload();
page_check(array_keys($controller->load->views) === array(
    'templates/header', 'admin/navigation-admin', 'admin/meter_upload', 'templates/footer'
), 'page follows Admin layout');
page_check(in_array('Cache-Control: no-store', $controller->output->headers, TRUE), 'page is not cached');
$sites = $controller->load->views['admin/meter_upload']['sites'];
ob_start();
require APPPATH.'views/admin/meter_upload.php';
$html = ob_get_clean();
page_check(strpos($html, '<script>bad</script>') === FALSE
    && strpos($html, '&lt;script&gt;bad&lt;/script&gt;') !== FALSE, 'site names HTML escaped');
page_check(strpos($html, '&quot;SITE&quot;') !== FALSE && strpos($html, 'value="7"') !== FALSE,
    'site code escaped and database ID used');
page_check(strpos($html, '/amr/index.php/api/meters/import/token') !== FALSE
    && strpos($html, '/amr/assets/meter_upload.js') !== FALSE, 'deployment-aware URLs');
page_check(strpos($html, 'id="meter-upload-import" type="button" class="btn btn-success" disabled') !== FALSE,
    'import disabled before preview');
$sites = array();
ob_start();
require APPPATH.'views/admin/meter_upload.php';
$empty = ob_get_clean();
page_check(strpos($empty, 'No sites are available.') !== FALSE, 'empty-site guidance');
$_SESSION['user_name'] = 'fixture-admin';
ob_start();
require APPPATH.'views/admin/navigation-admin.php';
$navigation = ob_get_clean();
page_check(strpos($navigation, '/amr/index.php/admin/meter_upload">Meter Upload</a>') !== FALSE,
    'Maintenance entry points to new page');
print('PASS: '.$assertions." Meter Upload page assertions (no application database).\n");
