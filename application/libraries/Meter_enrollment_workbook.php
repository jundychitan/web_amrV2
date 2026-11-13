<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Meter_enrollment_workbook {
    protected $sheet_names = array('Asbuilt');
    const MAX_BYTES = 5242880;
    const MAX_ROWS = 5000;
    const MAX_COLUMNS = 64;

    private $headers = array(
        'gateway serial no' => 'gateway_serial',
        'meter location' => 'physical_location',
        'meter serial no' => 'meter_name',
        'meter designation/tenant' => 'customer_name',
        'meter type' => 'meter_type',
        'config file' => 'meter_config_file',
        'modbus address' => 'meter_default_name',
        'meter multiplier' => 'meter_multiplier'
    );

    protected function normalize($value) {
        return strtolower(trim(preg_replace('/\s+/u', ' ', $value)));
    }

    private function check_dimensions($xml) {
        $reader = new XMLReader();
        $reader->XML($xml, NULL, LIBXML_NONET);
        $cells = 0;
        try {
            while ($reader->read()) {
                if ($reader->nodeType !== XMLReader::ELEMENT) {
                    continue;
                }
                if ($reader->localName === 'row' && (int) $reader->getAttribute('r') > self::MAX_ROWS) {
                    throw new InvalidArgumentException('Worksheet exceeds 5000 rows.');
                }
                if ($reader->localName === 'c') {
                    $cells++;
                    $reference = $reader->getAttribute('r');
                    if ($cells > 100000 || !is_string($reference)
                        || !preg_match('/^([A-Z]{1,3})([1-9][0-9]*)$/D', $reference, $match)
                        || (int) $match[2] > self::MAX_ROWS
                        || $this->column_number($match[1]) > self::MAX_COLUMNS) {
                        throw new InvalidArgumentException('Worksheet exceeds cell or coordinate limits.');
                    }
                }
                if (in_array($reader->localName, array('mergeCell', 'dimension'), TRUE)) {
                    $range = $reader->getAttribute('ref');
                    if (!is_string($range)
                        || !preg_match('/^([A-Z]{1,3})([1-9][0-9]*)(?::([A-Z]{1,3})([1-9][0-9]*))?$/D',
                            $range, $match)
                        || (int) $match[2] > self::MAX_ROWS
                        || (isset($match[4]) && (int) $match[4] > self::MAX_ROWS)
                        || $this->column_number($match[1]) > self::MAX_COLUMNS
                        || (isset($match[3]) && $this->column_number($match[3]) > self::MAX_COLUMNS)) {
                        throw new InvalidArgumentException('Worksheet range exceeds import limits.');
                    }
                }
            }
        } finally {
            $reader->close();
        }
    }

    private function column_number($column) {
        $number = 0;
        foreach (str_split($column) as $letter) {
            $number = $number * 26 + ord($letter) - 64;
        }
        return $number;
    }

    public function read($path) {
        // Convert legacy reader warnings into a clean API error, never PHP/HTML output.
        set_error_handler(function ($level, $message, $file, $line) {
            throw new ErrorException($message, 0, $level, $file, $line);
        });
        try {
            return $this->read_workbook($path);
        } finally {
            restore_error_handler();
        }
    }

    private function read_workbook($path) {
        if (!is_file($path) || filesize($path) > self::MAX_BYTES) {
            throw new InvalidArgumentException('Workbook must be no larger than 5 MiB.');
        }
        $zip = new ZipArchive();
        if ($zip->open($path) !== TRUE) {
            throw new InvalidArgumentException('A valid XLSX workbook is required.');
        }
        try {
            if ($zip->numFiles > 2000 || $zip->locateName('xl/workbook.xml') === FALSE) {
                throw new InvalidArgumentException('Unsupported workbook archive.');
            }
            $expanded = 0;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                $expanded += $stat['size'];
                if ($expanded > 52428800) {
                    throw new InvalidArgumentException('Expanded workbook exceeds 50 MiB.');
                }
                if (preg_match('/\.(xml|rels)$/i', $stat['name'])) {
                    $xml = $zip->getFromIndex($i);
                    if ($xml === FALSE || strpos($xml, "\0") !== FALSE
                        || preg_match('/<!DOCTYPE|<!ENTITY/i', $xml)) {
                        throw new InvalidArgumentException('Workbook XML declarations are not allowed.');
                    }
                    if (preg_match('#^xl/worksheets/[^/]+\.xml$#i', $stat['name'])) {
                        $this->check_dimensions($xml);
                    }
                }
            }
        } finally {
            $zip->close();
        }
        require_once APPPATH.'third_party/PHPExcel/PHPExcel.php';
        $reader = PHPExcel_IOFactory::createReader('Excel2007');
        $info = $reader->listWorksheetInfo($path);
        if (count($info) > 20) {
            throw new InvalidArgumentException('Too many worksheets.');
        }
        $selected = NULL;
        foreach ($this->sheet_names as $name) {
            foreach ($info as $sheet) {
                if ($sheet['worksheetName'] === $name) {
                    $selected = $sheet;
                    break 2;
                }
            }
        }
        if ($selected === NULL && count($info) === 1) {
            $selected = $info[0];
        }
        if ($selected === NULL) {
            throw new InvalidArgumentException('Use a '.implode(' or ', $this->sheet_names).' sheet or a single-sheet workbook.');
        }
        if ($selected['totalRows'] > self::MAX_ROWS || $selected['totalColumns'] > self::MAX_COLUMNS) {
            throw new InvalidArgumentException('Worksheet exceeds 5000 rows or 64 columns.');
        }
        $reader->setLoadSheetsOnly($selected['worksheetName']);
        $book = $reader->load($path);
        try {
            return $this->parse_sheet($book->getSheet(0));
        } finally {
            $book->disconnectWorksheets();
        }
    }

    protected function value($cell, $formatted = TRUE) {
        $value = $cell->getDataType() === PHPExcel_Cell_DataType::TYPE_FORMULA
            ? $cell->getOldCalculatedValue() : $cell->getValue();
        if ($value instanceof PHPExcel_RichText) {
            $value = $value->getPlainText();
        }
        if ($formatted && is_numeric($value)) {
            $value = PHPExcel_Style_NumberFormat::toFormattedString(
                $value, $cell->getStyle()->getNumberFormat()->getFormatCode()
            );
        }
        return trim((string) $value);
    }

    public function parse_sheet($sheet) {
        $result = array();
        $columns = array();
        $gateway = '';
        $width = PHPExcel_Cell::columnIndexFromString($sheet->getHighestDataColumn());
        $height = $sheet->getHighestDataRow();
        if ($width > self::MAX_COLUMNS || $height > self::MAX_ROWS) {
            throw new InvalidArgumentException('Worksheet exceeds import limits.');
        }
        for ($row = 1; $row <= $height; $row++) {
            $values = array();
            $found = array();
            for ($col = 0; $col < $width; $col++) {
                $values[$col] = $this->value($sheet->getCellByColumnAndRow($col, $row));
                $label = $this->normalize($values[$col]);
                if (isset($this->headers[$label])) {
                    $key = $this->headers[$label];
                    if (isset($found[$key])) {
                        throw new InvalidArgumentException('Duplicate header on row '.$row.'.');
                    }
                    $found[$key] = $col;
                }
            }
            if (isset($found['meter_name'])) {
                foreach ($this->headers as $label => $key) {
                    if ($key !== 'gateway_serial' && !isset($found[$key])) {
                        throw new InvalidArgumentException('Missing column: '.$label.' on row '.$row.'.');
                    }
                }
                $columns = $found;
                continue;
            }
            if (isset($found['gateway_serial'])) {
                $gateway = $values[$found['gateway_serial'] + 1] ?? '';
                $columns = array();
                continue;
            }
            if (!$columns) {
                continue;
            }
            $record = array('gateway_serial' => $gateway, 'sheet' => $sheet->getTitle(), 'row' => $row);
            $nonempty = FALSE;
            foreach ($columns as $key => $col) {
                $record[$key] = $key === 'meter_multiplier'
                    ? $this->value($sheet->getCellByColumnAndRow($col, $row), FALSE) : $values[$col];
                if ($key !== 'gateway_serial' && $values[$col] !== '') {
                    $nonempty = TRUE;
                }
            }
            if (!$nonempty) {
                continue;
            }
            $record['meter_name_addressable'] = $record['meter_default_name'] === '' ? 1 : 0;
            if ($record['meter_default_name'] === '') {
                $record['meter_default_name'] = $record['meter_name'];
            }
            if ($record['meter_multiplier'] === '') {
                $record['meter_multiplier'] = '1';
            }
            $result[] = $record;
        }
        if (!$result) {
            throw new InvalidArgumentException('No meter rows found.');
        }
        return $result;
    }
}
