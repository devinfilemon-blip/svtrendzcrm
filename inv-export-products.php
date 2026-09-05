<?php
include 'layouts/session.php';
include 'layouts/config.php';
include 'layouts/crm-access.php';
crmRequireAdmin($link);

require 'vendor/autoload.php';
require 'includes/excel-helper.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;

$query = "SELECT p.sProductCode, p.sProductName, p.sCategory, p.sHsnCode, p.sUnit,
        p.fPurchaseRate, p.fSaleRate, p.iOpeningStock, p.iReorderLevel, p.sStatus,
        (p.iOpeningStock + COALESCE(inw.qty,0) - COALESCE(outw.qty,0)) AS iCurrentStock
    FROM tblinv_product p
    LEFT JOIN (SELECT iProductid, SUM(iQty) qty FROM tblinv_inward GROUP BY iProductid) inw ON inw.iProductid = p.iProductid
    LEFT JOIN (SELECT iProductid, SUM(iQty) qty FROM tblinv_outward GROUP BY iProductid) outw ON outw.iProductid = p.iProductid
    ORDER BY p.iProductid ASC";

$result = mysqli_query($link, $query);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Products');

$headers = [
    'Product Code', 'Product Name', 'Category', 'HSN Code', 'Unit',
    'Purchase Rate', 'Sale Rate', 'Opening Stock', 'Reorder Level', 'Status', 'Current Stock'
];
$sheet->fromArray($headers, null, 'A1');

$rowNum = 2;
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $sheet->fromArray(array_values($row), null, 'A' . $rowNum);
        $rowNum++;
    }
}

crmDownloadSpreadsheet($spreadsheet, 'inventory_products_' . date('Y-m-d') . '.xlsx');
