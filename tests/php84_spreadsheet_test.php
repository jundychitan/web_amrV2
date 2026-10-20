<?php
// Run each bundled tree in a separate process:
// php tests/php84_spreadsheet_test.php
// php tests/php84_spreadsheet_test.php Classes
error_reporting(E_ALL);
set_error_handler(function ($level, $message, $file, $line) {
    throw new ErrorException($message, 0, $level, $file, $line);
});
$tree = isset($argv[1]) && $argv[1] === 'Classes' ? 'Classes' : 'application/third_party/PHPExcel';
$root = dirname(__DIR__);
require $root.'/'.$tree.'/PHPExcel.php';
$assertions = 0;
function spreadsheet_check($value, $label) {
    if (!$value) {
        throw new RuntimeException('FAILED: '.$label);
    }
    $GLOBALS['assertions']++;
}
$path = tempnam(sys_get_temp_dir(), 'amr84_');
try {
    $book = new PHPExcel();
    $sheet = $book->getActiveSheet();
    $sheet->setTitle('Meter report');
    $book->getProperties()->setCreator('Meter Report')->setTitle('Meter Report');
    $sheet->setCellValue('A1', 'Report')->mergeCells('A1:E1');
    $sheet->getStyle('A1:E1')->getFont()->setBold(true);
    $sheet->getStyle('A1:E1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $sheet->getColumnDimension('A')->setAutoSize(true);
    $sheet->setCellValueExplicit('A2', '000123', PHPExcel_Cell_DataType::TYPE_STRING);
    $sheet->setCellValue('B2', 12.5);
    $sheet->setCellValue('B3', 7.5);
    $sheet->setCellValue('B4', '=SUM(B2:B3)');
    $sheet->setCellValue('C2', 'Mètre – 電力量');
    $sum = $sheet->getCell('B4')->getCalculatedValue();
    spreadsheet_check($sum == 20, 'SUM calculation: '.var_export($sum, TRUE));
    $sheet->setCellValue('D2', '=IF(B2>0,"Active","Offline")');
    spreadsheet_check($sheet->getCell('D2')->getCalculatedValue() === 'Active', 'IF calculation');
    $sheet->setCellValue('E2', '=ROUND(B2/3,2)');
    spreadsheet_check($sheet->getCell('E2')->getCalculatedValue() == 4.17, 'ROUND calculation');
    $book->addNamedRange(new PHPExcel_NamedRange('Readings', $sheet, 'B2:B3'));
    $sheet->setCellValue('B5', '=SUM(Readings)');
    spreadsheet_check($sheet->getCell('B5')->getCalculatedValue() == 20, 'named-range calculation');
    $sheet->setCellValue('C3', TRUE);
    $sheet->setCellValue('C4', NULL);
    $sheet->setCellValue('C5', 0);
    $sheet->setCellValueExplicit('D3', '=not-a-formula', PHPExcel_Cell_DataType::TYPE_STRING);
    $rich = new PHPExcel_RichText();
    $rich->createTextRun('Meter note')->getFont()->setBold(TRUE);
    $sheet->setCellValue('D4', $rich);
    $sheet->getStyle('B2:B3')->getNumberFormat()->setFormatCode('0.00');
    $rowCount = 0;
    foreach ($sheet->getRowIterator(1, 2) as $row) {
        foreach ($row->getCellIterator('A', 'B') as $cell) {
            spreadsheet_check($cell instanceof PHPExcel_Cell, 'row/cell iterator value');
        }
        $rowCount++;
    }
    spreadsheet_check($rowCount === 2, 'row iterator bounds');
    $columnCount = 0;
    foreach ($sheet->getColumnIterator('A', 'B') as $column) {
        foreach ($column->getCellIterator(1, 2) as $cell) {
            spreadsheet_check($cell instanceof PHPExcel_Cell, 'column/cell iterator value');
        }
        $columnCount++;
    }
    spreadsheet_check($columnCount === 2, 'column iterator bounds');
    $xmlOptions = PHPExcel_Settings::getLibXmlLoaderOptions();
    spreadsheet_check(($xmlOptions & LIBXML_NONET) !== 0, 'XML network loading disabled');
    spreadsheet_check(($xmlOptions & (LIBXML_DTDLOAD | LIBXML_DTDATTR | LIBXML_NOENT)) === 0,
        'XML external entity expansion disabled');
    $writer = PHPExcel_IOFactory::createWriter($book, 'Excel2007');
    $writer->save($path);
    $zip = new ZipArchive();
    spreadsheet_check($zip->open($path) === TRUE, 'valid XLSX ZIP container');
    spreadsheet_check($zip->locateName('xl/worksheets/sheet1.xml') !== FALSE, 'worksheet XML exists');
    $zip->close();
    $read = PHPExcel_IOFactory::createReader('Excel2007')->load($path);
    $loaded = $read->getActiveSheet();
    spreadsheet_check($loaded->getCell('A2')->getValue() === '000123', 'meter serial retains leading zeros');
    spreadsheet_check($loaded->getCell('C2')->getValue() === 'Mètre – 電力量', 'Unicode round trip');
    spreadsheet_check($loaded->getCell('B4')->getCalculatedValue() == 20, 'formula round trip');
    spreadsheet_check($loaded->getStyle('A1')->getFont()->getBold() === TRUE, 'report styling round trip');
    spreadsheet_check($loaded->getCell('C3')->getValue() === TRUE, 'boolean round trip');
    spreadsheet_check($loaded->getCell('C4')->getValue() === NULL, 'null cell round trip');
    spreadsheet_check((float) $loaded->getCell('C5')->getValue() === 0.0, 'zero reading round trip');
    spreadsheet_check($loaded->getCell('D3')->getValue() === '=not-a-formula', 'literal formula-like text');
    spreadsheet_check($loaded->getCell('D4')->getValue()->getPlainText() === 'Meter note', 'rich text round trip');
    spreadsheet_check($loaded->getCell('B5')->getCalculatedValue() == 20, 'named range round trip');
    spreadsheet_check((float) $loaded->getCell('B2')->getValue() === 12.5, 'fractional reading retains precision');
    $read->disconnectWorksheets();
    $book->disconnectWorksheets();
    print('PASS: '.$assertions.' spreadsheet assertions ('.$tree.", strict PHP 8.4).\n");
} finally {
    if (is_file($path)) {
        unlink($path);
    }
}
