<?php
include 'layouts/session.php';
require 'vendor/autoload.php';
require 'includes/excel-helper.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;

if (!isset($_SESSION['user_id'])) {
    header('Location: auth-login.php');
    exit;
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->fromArray([
    ['Company Name', 'Email', 'Customer Type', 'Industry Type', 'User ID', 'Source ID', 'Tags', 'Status', 'Notes', 'Contact Name', 'Contact Email', 'Phone', 'Department'],
    ['Sample Company', 'info@sample.com', 'Corporate', 'Manufacturing', '', '', 'VIP', 'Active', 'Sample note', 'Rahul Sharma', 'rahul@sample.com', '9876543210', 'Sales'],
], null, 'A1');

crmDownloadSpreadsheet($spreadsheet, 'customer_import_template.xlsx');
