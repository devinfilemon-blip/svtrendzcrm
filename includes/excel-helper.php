<?php

use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

/**
 * Load uploaded Excel file using the correct reader (never HTML auto-detect).
 */
function crmLoadSpreadsheetFromUpload(array $file): Spreadsheet
{
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Invalid upload.');
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('File upload failed. Please try again.');
    }

    $originalName = $file['name'] ?? '';
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    if (!in_array($ext, ['xlsx', 'xls'], true)) {
        throw new RuntimeException('Please upload a valid Excel file (.xlsx or .xls).');
    }

    $tmpPath = $file['tmp_name'];
    $header = file_get_contents($tmpPath, false, null, 0, 4);

    if ($ext === 'xlsx' && $header !== "PK\x03\x04") {
        throw new RuntimeException('Invalid .xlsx file. Please export or save as Excel Workbook (.xlsx), not HTML/web page.');
    }

    if ($ext === 'xlsx') {
        $reader = new Xlsx();
    } else {
        $reader = new Xls();
    }

    $reader->setReadDataOnly(true);

    return $reader->load($tmpPath);
}

/**
 * Send spreadsheet as .xlsx download.
 */
function crmDownloadSpreadsheet(Spreadsheet $spreadsheet, string $filename): void
{
    if (substr($filename, -5) !== '.xlsx') {
        $filename .= '.xlsx';
    }

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer = new XlsxWriter($spreadsheet);
    $writer->save('php://output');
    exit;
}

/**
 * Normalize spreadsheet row cell to string.
 */
function crmExcelCell($value): string
{
    if ($value === null) {
        return '';
    }
    if (is_float($value) && floor($value) == $value) {
        return (string)(int)$value;
    }
    return trim((string)$value);
}
