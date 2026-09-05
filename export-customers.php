<?php
include 'layouts/session.php';
include 'layouts/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth-login.php');
    exit;
}

require 'vendor/autoload.php';
require 'includes/excel-helper.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;

$query = "
    SELECT 
        c.iCustomerid,
        c.sCompanyname,
        c.sEmail,
        c.sGstin,
        c.sBillingaddress,
        c.sShippingaddress,
        c.sCustomertype,
        c.sIndustrytype,
        c.sTags,
        c.sStatus,
        c.sNotes,
        u.sName AS manager_name,
        ct.sContactname,
        ct.sEmail AS contact_email,
        ct.sPhone AS contact_phone,
        ct.sDepartment AS contact_department,
        c.sCreateddate
    FROM tblcustomer c
    LEFT JOIN tbluser u ON c.iUserid = u.iUserid
    LEFT JOIN tblcontact ct ON ct.iCustomerid = c.iCustomerid
    ORDER BY c.iCustomerid DESC
";

$result = mysqli_query($link, $query);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Customers');

$headers = [
    'Customer ID', 'Company Name', 'Company Email', 'GSTIN', 'Billing Address', 'Shipping Address',
    'Customer Type', 'Industry Type', 'Tags', 'Status', 'Notes', 'Manager',
    'Contact Name', 'Contact Email', 'Contact Phone', 'Contact Department', 'Created Date'
];
$sheet->fromArray($headers, null, 'A1');

$rowNum = 2;
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $sheet->fromArray(array_values($row), null, 'A' . $rowNum);
        $rowNum++;
    }
}

crmDownloadSpreadsheet($spreadsheet, 'customers_export_' . date('Y-m-d') . '.xlsx');
