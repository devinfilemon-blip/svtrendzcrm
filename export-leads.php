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

$fromDate   = $_GET['fromDate'] ?? '';
$toDate     = $_GET['toDate'] ?? '';
$assignedTo = $_GET['assignedTo'] ?? '';

$conditions = [];

if (!empty($fromDate)) {
    $fromDate = mysqli_real_escape_string($link, $fromDate);
    $conditions[] = "DATE(l.sCreated_date) >= '$fromDate'";
}
if (!empty($toDate)) {
    $toDate = mysqli_real_escape_string($link, $toDate);
    $conditions[] = "DATE(l.sCreated_date) <= '$toDate'";
}
if (!empty($assignedTo)) {
    $assignedTo = intval($assignedTo);
    $conditions[] = "FIND_IN_SET($assignedTo, REPLACE(l.sAssigned_to, ' ', '')) > 0";
}

$whereClause = count($conditions) > 0 ? 'WHERE ' . implode(' AND ', $conditions) : '';

$query = "
    SELECT 
        l.iLead_id,
        l.sCompany_name,
        l.sContactperson,
        l.sLead_name,
        l.sEmail,
        l.sPhone,
        l.sAlternate_phone,
        l.sLead_type,
        l.sIndustry_type,
        l.sLocation,
        l.sAddress,
        ls.sStatus AS lead_status,
        src.sSources AS lead_source,
        pr.sPrioritylevel AS priority,
        l.sAssigned_to,
        lo.sName AS lead_owner,
        l.sTags,
        l.sCreated_date
    FROM tblleads l
    LEFT JOIN tbluser lo ON l.sLead_owner = lo.iUserid
    LEFT JOIN tblstatus ls ON l.sLead_status = ls.iStatusid
    LEFT JOIN tblsources src ON l.sLead_source = src.iSourceid
    LEFT JOIN tblpriority pr ON l.sLead_priority = pr.id
    $whereClause
    ORDER BY l.iLead_id DESC
";

$result = mysqli_query($link, $query);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Leads');

$headers = [
    'Lead ID', 'Company Name', 'Contact Person', 'Lead Name', 'Email', 'Phone', 'Alternate Phone',
    'Lead Type', 'Industry', 'Location', 'Address', 'Lead Status', 'Lead Source', 'Priority',
    'Assigned To', 'Lead Owner', 'Tags', 'Created Date'
];
$sheet->fromArray($headers, null, 'A1');

$userNameMap = [];
$uRes = mysqli_query($link, "SELECT iUserid, sName FROM tbluser");
if ($uRes) {
    while ($u = mysqli_fetch_assoc($uRes)) {
        $userNameMap[(string)$u['iUserid']] = $u['sName'];
    }
}

$rowNum = 2;
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $ids = array_filter(array_map('trim', explode(',', (string)($row['sAssigned_to'] ?? ''))));
        $names = [];
        foreach ($ids as $id) {
            if (isset($userNameMap[$id])) {
                $names[] = $userNameMap[$id];
            }
        }
        $row['sAssigned_to'] = implode(', ', $names);
        $sheet->fromArray(array_values($row), null, 'A' . $rowNum);
        $rowNum++;
    }
}

crmDownloadSpreadsheet($spreadsheet, 'leads_export_' . date('Y-m-d') . '.xlsx');
