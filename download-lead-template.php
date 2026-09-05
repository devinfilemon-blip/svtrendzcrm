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
    ['Company Name', 'Contact Person', 'Email', 'Phone', 'Alternate Phone', 'Lead Type', 'Industry', 'Location', 'Address', 'Lead Source', 'Lead Status', 'Priority', 'Assigned To', 'Lead Owner', 'Tags'],
    ['ABC Pvt Ltd', 'John Doe', 'john@abc.com', '9876543210', '', 'New Business', 'IT', 'Mumbai', 'Andheri East', 'Website', 'New Lead', 'High', '', '', 'Hot'],
], null, 'A1');

crmDownloadSpreadsheet($spreadsheet, 'lead_import_template.xlsx');
