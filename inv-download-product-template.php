<?php
include 'layouts/session.php';
include 'layouts/config.php';
include 'layouts/crm-access.php';
crmRequireAdmin($link);

require 'vendor/autoload.php';
require 'includes/excel-helper.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->fromArray([
    ['Product Code', 'Product Name', 'Category', 'HSN Code', 'Unit', 'Purchase Rate', 'Sale Rate', 'Opening Stock', 'Reorder Level', 'Status'],
    ['KPT-PPR-25', 'KPT Pneumato PPR Pipe 25mm PN16', 'KPT Pneumato', '39172200', 'Mtrs', 240, 284, 100, 20, 'Active'],
], null, 'A1');

crmDownloadSpreadsheet($spreadsheet, 'inventory_product_import_template.xlsx');
