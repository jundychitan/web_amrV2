<?php
error_reporting(E_ALL);
set_error_handler(function ($level, $message, $file, $line) {
    throw new ErrorException($message, 0, $level, $file, $line);
});
define('BASEPATH', dirname(__DIR__).'/system/');
define('APPPATH', dirname(__DIR__).'/application/');
require APPPATH.'third_party/PHPExcel/PHPExcel.php';
require APPPATH.'libraries/Gateway_enrollment_workbook.php';
$assertions = 0;
function gateway_check($value, $label) {
    if (!$value) { throw new RuntimeException('FAILED: '.$label); }
    $GLOBALS['assertions']++;
}
$book = new PHPExcel();
$sheet = $book->getActiveSheet()->setTitle('Asbuilt');
$labels = array('Gateway serial no', 'Gateway mac address', 'Gateway Location',
    'Gateway IP address', 'IDF Number', 'Switch', 'Port number');
$values = array('000123', 'AA-BB-CC-DD-EE-01', 'Floor 1', '192.0.2.10', 'IDF 1', 'Switch 1', '01');
foreach ($labels as $i => $label) {
    $sheet->setCellValue('B'.($i + 1), $label);
    $sheet->setCellValueExplicit('C'.($i + 1), $values[$i], PHPExcel_Cell_DataType::TYPE_STRING);
}
$sheet->fromArray(array('Meter Location', 'Meter Serial No', 'Config File'), NULL, 'B8');
$sheet->fromArray(array('Room', 'METER', 'elite.cfg'), NULL, 'B9');
foreach ($labels as $i => $label) {
    $sheet->setCellValue('B'.($i + 12), $label);
    $sheet->setCellValueExplicit('C'.($i + 12), $i === 0 ? '000124'
        : ($i === 1 ? 'aa:bb:cc:dd:ee:02' : $values[$i]), PHPExcel_Cell_DataType::TYPE_STRING);
}
$parser = new Gateway_enrollment_workbook();
$rows = $parser->parse_sheet($sheet);
gateway_check(count($rows) === 2, 'one gateway per metadata block; meters ignored');
gateway_check($rows[0]['rtu_sn_number'] === '000123' && $rows[0]['idf_port'] === '01', 'leading zeros preserved');
gateway_check($rows[0]['mac_addr'] === $values[1] && !isset($rows[0]['mac_address']), 'verified mac_addr mapping');
gateway_check($rows[1]['row'] === 12 && $rows[0]['switch_name'] === 'Switch 1', 'source row and switch mapping');
$path = tempnam(sys_get_temp_dir(), 'gateway_test_');
try {
    PHPExcel_IOFactory::createWriter($book, 'Excel2007')->save($path);
    gateway_check($parser->read($path) === $rows, 'bounded reader round trip');
} finally { unlink($path); $book->disconnectWorksheets(); }
$flat = new PHPExcel();
$flat->getActiveSheet()->fromArray($labels, NULL, 'A1');
$flat->getActiveSheet()->fromArray($values, NULL, 'A2');
gateway_check(count($parser->parse_sheet($flat->getActiveSheet())) === 1, 'flat table');
$flat->getActiveSheet()->setCellValue('B1', 'MAC Address')->setCellValue('A1', 'Gateway Serial #')
    ->setCellValue('D1', 'IP Address');
gateway_check(count($parser->parse_sheet($flat->getActiveSheet())) === 1, 'Gateway tab header aliases');
$flat->disconnectWorksheets();

#[AllowDynamicProperties]
class CI_Model {}
class Gateway_result {
    private $data;
    public function __construct($data) { $this->data = $data; }
    public function result_array() { return $this->data; }
}
class Gateway_db {
    public $db_debug = TRUE;
    public $data = array();
    public $snapshot;
    public $fail = FALSE;
    public $fail_commit = FALSE;
    public function trans_begin() { $this->snapshot = $this->data; return TRUE; }
    public function trans_commit() { return !$this->fail_commit; }
    public function trans_rollback() { $this->data = $this->snapshot; return TRUE; }
    public function trans_status() { return TRUE; }
    public function query($sql, $args = array()) {
        if (strpos($sql, 'FOR UPDATE') !== FALSE) {
            return new Gateway_result(array(array('id' => 1, 'site_code' => 'SITE')));
        }
        return new Gateway_result(array_values(array_filter($this->data, function ($r) use ($args) {
            return strtolower(str_replace('-', ':', $r['mac_addr'])) === $args[0];
        })));
    }
    public function get_where($table, $criteria) {
        return new Gateway_result(array_values(array_filter($this->data, function ($r) use ($criteria) {
            return strcasecmp($r['rtu_sn_number'], $criteria['rtu_sn_number']) === 0;
        })));
    }
    public function insert($table, $data) {
        if ($this->fail && $this->data) { return FALSE; }
        $data['id'] = count($this->data) + 1;
        $this->data[] = $data;
        return TRUE;
    }
}
require APPPATH.'models/Gateway_enrollment_model.php';
$model = new Gateway_enrollment_model();
$model->db = new Gateway_db();
$actor = array('user_id' => 7, 'user_name' => 'fixture');
$preview = $model->import($rows, 1, $actor, TRUE);
gateway_check($preview['would_insert'] === 2 && !$model->db->data, 'dry run does not insert');
$report = $model->import($rows, 1, $actor, FALSE);
gateway_check($report['inserted'] === 2, 'gateways inserted');
$data = $model->db->data[0];
foreach (array('connection_type' => 'LAN', 'ip_netmask' => 'n/a', 'ip_gateway' => 'n/a',
    'rtu_server_ip' => 'n/a', 'gateway_description' => 'Floor 1', 'rtu_site_name' => 'SITE',
    'rtu_site_id' => 1, 'mac_addr' => 'aa:bb:cc:dd:ee:01', 'idf_number' => 'IDF 1',
    'switch_name' => 'Switch 1', 'idf_port' => '01', 'added_by' => 7,
    'update_rtu' => 0, 'update_rtu_location' => 0, 'update_rtu_ssh' => 0, 'update_rtu_force_lp' => 0) as $key => $value) {
    gateway_check($data[$key] === $value, 'mapping/default '.$key);
}
$schema = file_get_contents(dirname(__DIR__).'/database/meter_reading_db_schema.sql');
preg_match('/CREATE TABLE IF NOT EXISTS `meter_rtu` \((.*?)\n\)/s', $schema, $match);
preg_match_all('/^\s*`([^`]+)` (.+)$/m', $match[1], $definitions, PREG_SET_ORDER);
$columns = array();
foreach ($definitions as $definition) {
    $columns[] = $definition[1];
    if (strpos($definition[2], 'NOT NULL') !== FALSE && strpos($definition[2], 'DEFAULT') === FALSE
        && strpos($definition[2], 'AUTO_INCREMENT') === FALSE) {
        gateway_check(array_key_exists($definition[1], $data), 'required schema field '.$definition[1]);
    }
}
gateway_check(!array_diff(array_keys($data), $columns), 'all fields exist in schema');
gateway_check($model->import($rows, 1, $actor, FALSE)['skipped'] === 2, 'retry skips existing');
$model->db = new Gateway_db();
gateway_check($model->import(array($rows[0], $rows[0]), 1, $actor, FALSE)['skipped'] === 1, 'duplicate workbook rows');
$model->db = new Gateway_db();
foreach (array('mac_addr' => 'bad', 'phone_no_or_ip_address' => 'not-ip',
    'rtu_sn_number' => '', 'rtu_physical_location' => '<script>') as $field => $bad) {
    $invalid = $rows;
    $invalid[1][$field] = $bad;
    gateway_check($model->import($invalid, 1, $actor, FALSE)['invalid'] === 1 && !$model->db->data,
        'atomic validation '.$field);
}
$conflict = $rows;
$conflict[1]['mac_addr'] = $rows[0]['mac_addr'];
gateway_check($model->import($conflict, 1, $actor, FALSE)['invalid'] === 1, 'workbook MAC conflict');
$conflict = $rows;
$conflict[1]['rtu_sn_number'] = $rows[0]['rtu_sn_number'];
gateway_check($model->import($conflict, 1, $actor, FALSE)['invalid'] === 1, 'workbook serial conflict');
$model->import($rows, 1, $actor, FALSE);
$model->db->data[0]['rtu_site_id'] = 2;
gateway_check($model->import($rows, 1, $actor, FALSE)['invalid'] === 1, 'existing cross-site conflict');
$model->db->data[0]['rtu_site_id'] = 1;
$model->db->data[0]['rtu_sn_number'] = 'other';
gateway_check($model->import($rows, 1, $actor, FALSE)['invalid'] === 1, 'existing MAC conflict');
foreach (array('fail', 'fail_commit') as $failure) {
    $model->db = new Gateway_db();
    $model->db->$failure = TRUE;
    try { $model->import($rows, 1, $actor, FALSE); throw new LogicException('Expected storage failure'); }
    catch (RuntimeException $e) {
        gateway_check(!$model->db->data && $model->db->db_debug === TRUE, 'storage rollback '.$failure);
    }
}
try { $model->import($rows, 99, $actor, TRUE); throw new LogicException('Expected missing site'); }
catch (InvalidArgumentException $e) { gateway_check(TRUE, 'unknown site rejected'); }
if (isset($argv[1])) {
    $actual = $parser->read($argv[1]);
    gateway_check(count($actual) === 6, 'sample has six gateway blocks');
    $model->db = new Gateway_db();
    gateway_check($model->import($actual, 1, $actor, TRUE)['invalid'] === 0, 'sample gateway data validates');
}
print('PASS: '.$assertions." gateway enrollment assertions (no application database).\n");
