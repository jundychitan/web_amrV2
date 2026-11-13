<?php
// Synthetic fixtures only; never loads application database credentials.
error_reporting(E_ALL);
set_error_handler(function ($level, $message, $file, $line) {
    throw new ErrorException($message, 0, $level, $file, $line);
});
define('BASEPATH', dirname(__DIR__).'/system/');
define('APPPATH', dirname(__DIR__).'/application/');
require APPPATH.'third_party/PHPExcel/PHPExcel.php';
require APPPATH.'libraries/Meter_enrollment_workbook.php';
$assertions = 0;
function check_import($condition, $label) {
    if (!$condition) { throw new RuntimeException('FAILED: '.$label); }
    $GLOBALS['assertions']++;
}
function rejects_import($callback, $label) {
    try { $callback(); } catch (InvalidArgumentException $e) {
        check_import(TRUE, $label);
        return;
    }
    throw new RuntimeException('FAILED: '.$label);
}
$parser = new Meter_enrollment_workbook();
$book = new PHPExcel();
$sheet = $book->getActiveSheet()->setTitle('Asbuilt');
$headers = array('Meter Location', 'Meter Serial No', 'Meter Designation/Tenant',
    'Meter Type', 'Config File', 'Modbus Address', 'Meter Multiplier');
$sheet->fromArray(array('Gateway serial no', '000987'), NULL, 'A1');
$sheet->fromArray($headers, NULL, 'A3');
$sheet->fromArray(array('Floor 1', '000123', 'Tenant A', '3P', 'elite.cfg', '', ''), NULL, 'A4');
$sheet->setCellValueExplicit('B4', '000123', PHPExcel_Cell_DataType::TYPE_STRING);
$sheet->setCellValueExplicit('B5', 12, PHPExcel_Cell_DataType::TYPE_NUMERIC);
$sheet->getStyle('B5')->getNumberFormat()->setFormatCode('000000');
$sheet->fromArray(array('Floor 2', NULL, 'Tenant B', '3P', 'elite.cfg', 2, 2.5), NULL, 'A5');
$sheet->getStyle('G5')->getNumberFormat()->setFormatCode('#,##0.00');
$sheet->fromArray(array('Gateway serial no', 'not-found'), NULL, 'A7');
$sheet->fromArray($headers, NULL, 'A8');
$sheet->fromArray(array('Floor 3', 'M3', 'Tenant C', '3P', '', 3, ''), NULL, 'A9');
$rows = $parser->parse_sheet($sheet);
check_import(count($rows) === 3, 'gateway metadata and blank rows excluded');
check_import($rows[0]['meter_name'] === '000123', 'text serial leading zeros');
check_import($rows[1]['meter_name'] === '000012', 'formatted numeric serial leading zeros');
check_import($rows[0]['gateway_serial'] === '000987' && $rows[2]['gateway_serial'] === 'not-found',
    'gateway section inheritance');
check_import($rows[0]['meter_default_name'] === '000123'
    && $rows[0]['meter_name_addressable'] === 1, 'blank address fallback');
check_import($rows[1]['meter_default_name'] === '2'
    && $rows[1]['meter_name_addressable'] === 0, 'Modbus mapping');
check_import($rows[0]['meter_multiplier'] === '1' && $rows[1]['meter_multiplier'] === '2.5',
    'multiplier default and explicit value');
$path = tempnam(sys_get_temp_dir(), 'amr_import_');
try {
    PHPExcel_IOFactory::createWriter($book, 'Excel2007')->save($path);
    check_import($parser->read($path) === $rows, 'XLSX round trip');
    file_put_contents($path, 'not an XLSX');
    rejects_import(function () use ($parser, $path) { $parser->read($path); }, 'invalid archive');
    $zip = new ZipArchive();
    $zip->open($path, ZipArchive::OVERWRITE);
    $zip->addFromString('xl/workbook.xml', '<!DOCTYPE workbook><workbook/>');
    $zip->close();
    rejects_import(function () use ($parser, $path) { $parser->read($path); }, 'DTD rejected');
} finally {
    unlink($path);
    $book->disconnectWorksheets();
}
$flat = new PHPExcel();
$sheet = $flat->getActiveSheet();
$sheet->fromArray(array_merge(array('Gateway serial no'), $headers), NULL, 'A1');
$sheet->fromArray(array('000987', 'Floor', 'M4', 'Tenant', '3P', 'elite.cfg', 4, 1), NULL, 'A2');
check_import($parser->parse_sheet($sheet)[0]['gateway_serial'] === '000987', 'flat gateway column');
$sheet->setCellValue('H1', 'Wrong header');
rejects_import(function () use ($parser, $sheet) { $parser->parse_sheet($sheet); }, 'missing header');
$sheet->setCellValue('A5001', 'too far');
rejects_import(function () use ($parser, $sheet) { $parser->parse_sheet($sheet); }, 'row limit');
$flat->disconnectWorksheets();

#[AllowDynamicProperties]
class CI_Model {}
class Import_result {
    private $rows;
    public function __construct($rows) { $this->rows = $rows; }
    public function result_array() { return $this->rows; }
}
class Import_db {
    public $db_debug = TRUE;
    public $tables;
    public $fail = FALSE;
    public $fail_commit = FALSE;
    public $snapshot;
    public function __construct() {
        $this->tables = array(
            'meter_site' => array(array('id' => 1, 'site_code' => 'SITE', 'company_no' => 7,
                'business_entity' => 'ENTITY')),
            'meter_rtu' => array(array('id' => 42, 'rtu_sn_number' => '000987', 'rtu_site_id' => 1)),
            'meter_model_config' => array(array('id' => 8, 'config_file' => 'elite.cfg', 'meter_model' => 'Elite')),
            'meter_details' => array()
        );
    }
    public function trans_begin() { $this->snapshot = $this->tables; return TRUE; }
    public function trans_rollback() { $this->tables = $this->snapshot; return TRUE; }
    public function trans_commit() { return !$this->fail_commit; }
    public function trans_status() { return TRUE; }
    public function query($sql, $args) {
        check_import(strpos($sql, 'FOR UPDATE') !== FALSE, 'site lock acquired');
        return $this->get_where('meter_site', array('id' => $args[0]));
    }
    public function get_where($table, $criteria) {
        return new Import_result(array_values(array_filter($this->tables[$table], function ($row) use ($criteria) {
            foreach ($criteria as $key => $value) {
                if (strcasecmp((string) $row[$key], (string) $value) !== 0) { return FALSE; }
            }
            return TRUE;
        })));
    }
    public function insert($table, $data) {
        if ($this->fail && count($this->tables[$table]) === 1) { return FALSE; }
        $this->tables[$table][] = $data;
        return TRUE;
    }
}
require APPPATH.'models/Meter_enrollment_model.php';
$model = new Meter_enrollment_model();
$actor = array('user_id' => 5, 'user_name' => 'test-admin');
$model->db = new Import_db();
$report = $model->import($rows, 1, $actor, TRUE);
check_import($report['would_insert'] === 3 && $report['inserted'] === 0
    && !$model->db->tables['meter_details'], 'dry run writes nothing');
$report = $model->import($rows, 1, $actor, FALSE);
$inserted = $model->db->tables['meter_details'];
check_import($report['inserted'] === 3 && $report['unmatched_gateways'] === 1, 'insert count and warnings');
check_import($inserted[0]['rtu_sn_number_id'] === 42 && $inserted[0]['rtu_sn_number'] === '000987',
    'gateway serial resolved to actual ID');
check_import($inserted[2]['rtu_sn_number_id'] === 0 && $inserted[2]['rtu_sn_number'] === '',
    'unmatched gateway sentinel');
check_import($inserted[0]['meter_model_id'] === 8 && $inserted[0]['meter_model'] === 'Elite',
    'config model lookup');
check_import($inserted[0]['meter_site_name'] === 'SITE' && $inserted[0]['added_by'] === 5,
    'site and audit context');
// Mechanically check insert fields against the exact checked-in schema.
$schema = file_get_contents(dirname(__DIR__).'/database/meter_reading_db_schema.sql');
preg_match('/CREATE TABLE IF NOT EXISTS `meter_details` \((.*?)\n\)/s', $schema, $match);
preg_match_all('/^\s*`([^`]+)` (.+),?$/m', $match[1], $definitions, PREG_SET_ORDER);
$columns = array();
foreach ($definitions as $definition) {
    $columns[] = $definition[1];
    if (strpos($definition[2], 'NOT NULL') !== FALSE
        && strpos($definition[2], 'DEFAULT') === FALSE
        && strpos($definition[2], 'AUTO_INCREMENT') === FALSE) {
        check_import(array_key_exists($definition[1], $inserted[0]), 'required schema field '.$definition[1]);
    }
}
check_import(!array_diff(array_keys($inserted[0]), $columns), 'all insert columns exist in schema');
$report = $model->import($rows, 1, $actor, FALSE);
check_import($report['skipped'] === 3 && count($model->db->tables['meter_details']) === 3, 'retry skips existing');
$model->db = new Import_db();
$report = $model->import(array($rows[0], $rows[0]), 1, $actor, FALSE);
check_import($report['inserted'] === 1 && $report['skipped'] === 1, 'workbook duplicates skipped');
$model->db = new Import_db();
$invalid = $rows;
$invalid[1]['meter_multiplier'] = 'oops';
$report = $model->import($invalid, 1, $actor, FALSE);
check_import($report['invalid'] === 1 && !$model->db->tables['meter_details'], 'validation atomicity');
$invalid[1]['meter_multiplier'] = '-1';
check_import($model->import($invalid, 1, $actor, TRUE)['invalid'] === 1, 'negative multiplier rejected');
$invalid = $rows;
$invalid[0]['customer_name'] = '<script>';
check_import($model->import($invalid, 1, $actor, TRUE)['invalid'] === 1, 'legacy UI markup rejected');
$model->db->tables['meter_rtu'][0]['rtu_site_id'] = 2;
check_import($model->import($rows, 1, $actor, TRUE)['invalid'] === 2, 'cross-site gateways rejected');
$model->db = new Import_db();
$model->db->tables['meter_rtu'][] = $model->db->tables['meter_rtu'][0];
check_import($model->import($rows, 1, $actor, TRUE)['invalid'] === 2, 'ambiguous gateways rejected');
$model->db = new Import_db();
$model->db->tables['meter_model_config'][] = $model->db->tables['meter_model_config'][0];
check_import($model->import($rows, 1, $actor, TRUE)['invalid'] === 2, 'ambiguous configs rejected');
$model->db = new Import_db();
rejects_import(function () use ($model, $rows, $actor) { $model->import($rows, 99, $actor, FALSE); },
    'unknown site rejected');
foreach (array('fail', 'fail_commit') as $failure) {
    $model->db = new Import_db();
    $model->db->$failure = TRUE;
    try {
        $model->import($rows, 1, $actor, FALSE);
        throw new LogicException('Storage failure was ignored.');
    } catch (RuntimeException $e) {
        check_import(!$model->db->tables['meter_details'] && $model->db->db_debug === TRUE,
            'storage failure rollback and debug restoration '.$failure);
    }
}

#[AllowDynamicProperties]
class CI_Controller {}
class Import_session {
    public $data = array();
    public function userdata($key) { return $this->data[$key] ?? NULL; }
    public function set_userdata($key, $value) { $this->data[$key] = $value; }
}
class Import_input {
    public $verb = 'POST';
    public $fields = array();
    public $token = NULL;
    public function method($upper) { return $this->verb; }
    public function post($key, $filter) { return $this->fields[$key] ?? NULL; }
    public function get_request_header($key, $filter) { return $this->token; }
}
class Import_output {
    public $status;
    public $body;
    public function set_content_type($type, $charset) { return $this; }
    public function set_header($header) { return $this; }
    public function set_status_header($status) { $this->status = $status; return $this; }
    public function set_output($body) { $this->body = json_decode($body, TRUE, 512, JSON_THROW_ON_ERROR); return $this; }
}
require APPPATH.'controllers/Meter_import.php';
$controller = new Meter_import();
$controller->session = new Import_session();
$controller->input = new Import_input();
$controller->output = new Import_output();
$controller->index();
check_import($controller->output->status === 401, 'anonymous JSON denial');
$controller->session->data['currently_logged_in_as'] = 'BA';
$controller->index();
check_import($controller->output->status === 403, 'non-admin denial');
$controller->session->data['currently_logged_in_as'] = 'Admin';
$controller->index();
check_import($controller->output->status === 403, 'CSRF required');
$controller->input->verb = 'GET';
$controller->token();
$token = $controller->output->body['csrf_token'];
check_import(strlen($token) === 64, 'session CSRF token issued');
$controller->index();
check_import($controller->output->status === 405, 'method restriction');
$controller->input->verb = 'POST';
$controller->input->token = $token;
$controller->input->fields['site_id'] = array('1');
$controller->index();
check_import($controller->output->status === 400, 'malformed site rejected');
$controller->input->fields['site_id'] = '1';
$_FILES = array();
$controller->index();
check_import($controller->output->status === 400, 'missing upload rejected');
$_FILES['file'] = array('name' => array('a.xlsx'), 'error' => array(0));
$controller->index();
check_import($controller->output->status === 400, 'malformed upload rejected');
if (isset($argv[1])) {
    $actual = $parser->read($argv[1]);
    check_import(count($actual) === 61, 'sample has 61 meter rows');
    check_import(count(array_unique(array_column($actual, 'gateway_serial'))) === 6, 'sample has six gateways');
    check_import(count(array_filter($actual, function ($r) { return $r['meter_multiplier'] === '1'; })) === 61,
        'sample blank multipliers default to one');
}
print('PASS: '.$assertions." meter enrollment assertions (offline; no application database).\n");
