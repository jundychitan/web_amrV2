<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once __DIR__.'/Meter_enrollment_workbook.php';

class Gateway_enrollment_workbook extends Meter_enrollment_workbook {
    protected $sheet_names = array('Asbuilt', 'Gateway');
    private $gateway_headers = array(
        'gateway serial no' => 'rtu_sn_number', 'gateway serial #' => 'rtu_sn_number',
        'gateway mac address' => 'mac_addr', 'mac address' => 'mac_addr',
        'gateway location' => 'rtu_physical_location',
        'gateway ip address' => 'phone_no_or_ip_address', 'ip address' => 'phone_no_or_ip_address',
        'idf number' => 'idf_number', 'switch' => 'switch_name', 'port number' => 'idf_port'
    );

    private function empty_record($sheet, $row) {
        return array_merge(array_fill_keys(array_unique(array_values($this->gateway_headers)), ''),
            array('sheet' => $sheet->getTitle(), 'row' => $row));
    }

    public function parse_sheet($sheet) {
        $width = PHPExcel_Cell::columnIndexFromString($sheet->getHighestDataColumn());
        $height = $sheet->getHighestDataRow();
        if ($width > self::MAX_COLUMNS || $height > self::MAX_ROWS) {
            throw new InvalidArgumentException('Worksheet exceeds import limits.');
        }
        $records = array();
        $block = NULL;
        $columns = array();
        for ($row = 1; $row <= $height; $row++) {
            $values = $found = array();
            for ($col = 0; $col < $width; $col++) {
                $values[$col] = $this->value($sheet->getCellByColumnAndRow($col, $row));
                $label = $this->normalize($values[$col]);
                if (isset($this->gateway_headers[$label])) {
                    $key = $this->gateway_headers[$label];
                    if (isset($found[$key])) {
                        throw new InvalidArgumentException('Duplicate gateway header on row '.$row.'.');
                    }
                    $found[$key] = $col;
                }
            }
            if (isset($found['rtu_sn_number']) && count($found) >= 2) {
                foreach (array('rtu_sn_number', 'mac_addr', 'rtu_physical_location', 'phone_no_or_ip_address') as $field) {
                    if (!isset($found[$field])) {
                        throw new InvalidArgumentException('Missing gateway column: '.$field.'.');
                    }
                }
                if ($block !== NULL) {
                    $records[] = $block;
                    $block = NULL;
                }
                $columns = $found;
                continue;
            }
            if ($columns) {
                $record = $this->empty_record($sheet, $row);
                $nonempty = FALSE;
                foreach ($columns as $key => $col) {
                    $record[$key] = $values[$col];
                    $nonempty = $nonempty || $values[$col] !== '';
                }
                if ($nonempty) { $records[] = $record; }
                continue;
            }
            if (isset($found['rtu_sn_number'])) {
                if ($block !== NULL) { $records[] = $block; }
                $block = $this->empty_record($sheet, $row);
            }
            // Metadata labels occupy a single cell; their values are immediately to the right.
            if ($block !== NULL && count($found) === 1) {
                foreach ($found as $key => $col) {
                    if (isset($block['_seen'][$key])) {
                        throw new InvalidArgumentException('Repeated gateway metadata on row '.$row.'.');
                    }
                    $block[$key] = $values[$col + 1] ?? '';
                    $block['_seen'][$key] = TRUE;
                }
            }
        }
        if ($block !== NULL) { $records[] = $block; }
        if (!$records) { throw new InvalidArgumentException('No gateway rows found.'); }
        foreach ($records as &$record) { unset($record['_seen']); }
        unset($record);
        return $records;
    }
}
