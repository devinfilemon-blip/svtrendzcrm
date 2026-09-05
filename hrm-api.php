<?php
/**
 * HRM module API — Employees, Salary Structures, Payroll.
 */
session_start();
header('Content-Type: application/json');
date_default_timezone_set('Asia/Kolkata');

require_once __DIR__ . '/layouts/config.php';
require_once __DIR__ . '/layouts/crm-access.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$inputData = [];

if ($method === 'POST') {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
    if (stripos($contentType, 'application/json') !== false) {
        $raw = file_get_contents('php://input');
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $inputData = $decoded;
        }
    } else {
        $inputData = $_POST;
    }
}

$action = $inputData['action'] ?? '';

function hrmJson($status, $message, $data = null) {
    $out = ['status' => $status, 'message' => $message];
    if ($data !== null) {
        $out['data'] = $data;
    }
    echo json_encode($out);
    exit;
}

function hrmRequireAdmin() {
    if (empty($_SESSION['user_id'])) {
        hrmJson('error', 'Please login first.');
    }
    if (!crmIsAdmin()) {
        hrmJson('error', 'Access denied. Admin only.');
    }
}

function hrmEmptyToNull($v) {
    $v = trim((string)$v);
    return $v === '' ? null : $v;
}

function hrmNum($v) {
    if ($v === '' || $v === null) {
        return 0.0;
    }
    return (float)$v;
}

hrmRequireAdmin();

// ==================== EMPLOYEES ====================

if ($method === 'POST' && $action === 'listemployees') {
    $rows = $link->query("SELECT * FROM tblhr_employee ORDER BY iEmployeeid DESC")->fetch_all(MYSQLI_ASSOC);
    hrmJson('success', 'Employees fetched', $rows);
}

if ($method === 'POST' && $action === 'listactiveemployees') {
    $rows = $link->query("SELECT iEmployeeid, sFullName, sEmployeeCode FROM tblhr_employee WHERE sStatus = 'Active' ORDER BY sFullName ASC")->fetch_all(MYSQLI_ASSOC);
    hrmJson('success', 'Employees fetched', $rows);
}

if ($method === 'POST' && $action === 'getemployeebyid') {
    $id = (int)($inputData['id'] ?? 0);
    if ($id <= 0) {
        hrmJson('error', 'Invalid employee ID.');
    }
    $stmt = $link->prepare('SELECT * FROM tblhr_employee WHERE iEmployeeid = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) {
        hrmJson('error', 'Employee not found.');
    }
    hrmJson('success', 'Employee fetched', $row);
}

if ($method === 'POST' && in_array($action, ['saveemployee', 'updateemployee'], true)) {
    $fullName = trim($inputData['full_name'] ?? '');
    if ($fullName === '') {
        hrmJson('error', 'Full name is required.');
    }
    $employeeCode = hrmEmptyToNull($inputData['employee_code'] ?? '');
    $fatherName = hrmEmptyToNull($inputData['father_name'] ?? '');
    $dob = hrmEmptyToNull($inputData['dob'] ?? '');
    $age = ($inputData['age'] ?? '') !== '' ? (int)$inputData['age'] : null;
    $birthLocation = hrmEmptyToNull($inputData['birth_location'] ?? '');
    $gender = hrmEmptyToNull($inputData['gender'] ?? '');
    $bloodGroup = hrmEmptyToNull($inputData['blood_group'] ?? '');
    $maritalStatus = hrmEmptyToNull($inputData['marital_status'] ?? '');
    $nomineeName = hrmEmptyToNull($inputData['nominee_name'] ?? '');
    $nomineeRelation = hrmEmptyToNull($inputData['nominee_relation'] ?? '');
    $userId = (int)($_SESSION['user_id'] ?? 0);

    if ($action === 'updateemployee') {
        $id = (int)($inputData['id'] ?? 0);
        if ($id <= 0) {
            hrmJson('error', 'Invalid employee ID.');
        }
        $sql = "UPDATE tblhr_employee SET
            sFullName = ?, sEmployeeCode = ?, sFatherName = ?, dDob = ?, iAge = ?,
            sBirthLocation = ?, sGender = ?, sBloodGroup = ?, sMaritalStatus = ?,
            sNomineeName = ?, sNomineeRelation = ?, sModifiedTimestamp = NOW()
            WHERE iEmployeeid = ?";
        $stmt = $link->prepare($sql);
        $stmt->bind_param(
            'ssssissssssi',
            $fullName, $employeeCode, $fatherName, $dob, $age,
            $birthLocation, $gender, $bloodGroup, $maritalStatus,
            $nomineeName, $nomineeRelation, $id
        );
        if (!$stmt->execute()) {
            $err = $stmt->error;
            $stmt->close();
            hrmJson('error', 'Update failed: ' . $err);
        }
        $stmt->close();
        hrmJson('success', 'Employee updated successfully.', ['id' => $id]);
    }

    $sql = "INSERT INTO tblhr_employee
        (sFullName, sEmployeeCode, sFatherName, dDob, iAge, sBirthLocation, sGender, sBloodGroup, sMaritalStatus, sNomineeName, sNomineeRelation, iCreatedBy)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $link->prepare($sql);
    $stmt->bind_param(
        'ssssissssssi',
        $fullName, $employeeCode, $fatherName, $dob, $age,
        $birthLocation, $gender, $bloodGroup, $maritalStatus,
        $nomineeName, $nomineeRelation, $userId
    );
    if (!$stmt->execute()) {
        $err = $stmt->error;
        $stmt->close();
        hrmJson('error', 'Save failed: ' . $err);
    }
    $newId = (int)$stmt->insert_id;
    $stmt->close();
    hrmJson('success', 'Employee saved successfully.', ['id' => $newId]);
}

if ($method === 'POST' && $action === 'deleteemployee') {
    $id = (int)($inputData['id'] ?? 0);
    if ($id <= 0) {
        hrmJson('error', 'Invalid employee ID.');
    }
    $stmt = $link->prepare('DELETE FROM tblhr_employee WHERE iEmployeeid = ?');
    $stmt->bind_param('i', $id);
    if (!$stmt->execute()) {
        $err = $stmt->error;
        $stmt->close();
        hrmJson('error', 'Delete failed: ' . $err);
    }
    $stmt->close();
    hrmJson('success', 'Employee deleted successfully.');
}

// ==================== SALARY STRUCTURE ====================

function hrmCalcStructureTotals($e) {
    $gross = $e['basic'] + $e['hra'] + $e['da'] + $e['travel'] + $e['medical'] + $e['special'] + $e['other_allow'];
    $tds = 0.0;
    if ($e['tds_type'] === 'percent') {
        $tds = ($e['basic'] + $e['da']) * ($e['tds_value'] / 100);
    } else {
        $tds = $e['tds_value'];
    }
    $deductions = $e['pf'] + $e['esi'] + $e['pt'] + $tds + $e['other_ded'];
    $net = $gross - $deductions;
    return [$gross, $deductions, $net, $tds];
}

if ($method === 'POST' && $action === 'liststructures') {
    $sql = "SELECT s.*, e.sFullName, e.sEmployeeCode
            FROM tblhr_salary_structure s
            JOIN tblhr_employee e ON e.iEmployeeid = s.iEmployeeid
            ORDER BY s.iStructureid DESC";
    $rows = $link->query($sql)->fetch_all(MYSQLI_ASSOC);
    hrmJson('success', 'Salary structures fetched', $rows);
}

if ($method === 'POST' && $action === 'getstructurebyid') {
    $id = (int)($inputData['id'] ?? 0);
    if ($id <= 0) {
        hrmJson('error', 'Invalid structure ID.');
    }
    $stmt = $link->prepare('SELECT * FROM tblhr_salary_structure WHERE iStructureid = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) {
        hrmJson('error', 'Salary structure not found.');
    }
    hrmJson('success', 'Salary structure fetched', $row);
}

if ($method === 'POST' && in_array($action, ['savestructure', 'updatestructure'], true)) {
    $employeeId = (int)($inputData['employee_id'] ?? 0);
    $effectiveFrom = hrmEmptyToNull($inputData['effective_from'] ?? '');
    if ($employeeId <= 0) {
        hrmJson('error', 'Please select an employee.');
    }
    if (!$effectiveFrom) {
        hrmJson('error', 'Effective from date is required.');
    }

    $e = [
        'basic' => hrmNum($inputData['basic_salary'] ?? 0),
        'hra' => hrmNum($inputData['hra'] ?? 0),
        'da' => hrmNum($inputData['da'] ?? 0),
        'travel' => hrmNum($inputData['travel_allowance'] ?? 0),
        'medical' => hrmNum($inputData['medical_allowance'] ?? 0),
        'special' => hrmNum($inputData['special_allowance'] ?? 0),
        'other_allow' => hrmNum($inputData['other_allowance'] ?? 0),
        'pf' => hrmNum($inputData['pf'] ?? 0),
        'esi' => hrmNum($inputData['esi'] ?? 0),
        'pt' => hrmNum($inputData['professional_tax'] ?? 0),
        'tds_type' => (($inputData['tds_type'] ?? 'percent') === 'amount') ? 'amount' : 'percent',
        'tds_value' => hrmNum($inputData['tds_value'] ?? 0),
        'other_ded' => hrmNum($inputData['other_deduction'] ?? 0),
    ];
    list($gross, $deductions, $net, $tds) = hrmCalcStructureTotals($e);
    $userId = (int)($_SESSION['user_id'] ?? 0);

    if ($action === 'updatestructure') {
        $id = (int)($inputData['id'] ?? 0);
        if ($id <= 0) {
            hrmJson('error', 'Invalid structure ID.');
        }
        $sql = "UPDATE tblhr_salary_structure SET
            iEmployeeid = ?, dEffectiveFrom = ?, fBasicSalary = ?, fHRA = ?, fDA = ?,
            fTravelAllowance = ?, fMedicalAllowance = ?, fSpecialAllowance = ?, fOtherAllowance = ?,
            fPF = ?, fESI = ?, fProfessionalTax = ?, sTdsType = ?, fTdsValue = ?, fOtherDeduction = ?,
            fGrossSalary = ?, fTotalDeductions = ?, fNetSalary = ?, sModifiedTimestamp = NOW()
            WHERE iStructureid = ?";
        $stmt = $link->prepare($sql);
        $stmt->bind_param(
            'isddddddddddsdddddi',
            $employeeId, $effectiveFrom, $e['basic'], $e['hra'], $e['da'],
            $e['travel'], $e['medical'], $e['special'], $e['other_allow'],
            $e['pf'], $e['esi'], $e['pt'], $e['tds_type'], $e['tds_value'], $e['other_ded'],
            $gross, $deductions, $net, $id
        );
        if (!$stmt->execute()) {
            $err = $stmt->error;
            $stmt->close();
            hrmJson('error', 'Update failed: ' . $err);
        }
        $stmt->close();
        hrmJson('success', 'Salary structure updated successfully.', ['id' => $id, 'gross' => $gross, 'deductions' => $deductions, 'net' => $net]);
    }

    $sql = "INSERT INTO tblhr_salary_structure
        (iEmployeeid, dEffectiveFrom, fBasicSalary, fHRA, fDA, fTravelAllowance, fMedicalAllowance, fSpecialAllowance, fOtherAllowance,
         fPF, fESI, fProfessionalTax, sTdsType, fTdsValue, fOtherDeduction, fGrossSalary, fTotalDeductions, fNetSalary, iCreatedBy)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $link->prepare($sql);
    $stmt->bind_param(
        'isddddddddddsdddddi',
        $employeeId, $effectiveFrom, $e['basic'], $e['hra'], $e['da'],
        $e['travel'], $e['medical'], $e['special'], $e['other_allow'],
        $e['pf'], $e['esi'], $e['pt'], $e['tds_type'], $e['tds_value'], $e['other_ded'],
        $gross, $deductions, $net, $userId
    );
    if (!$stmt->execute()) {
        $err = $stmt->error;
        $stmt->close();
        hrmJson('error', 'Save failed: ' . $err);
    }
    $newId = (int)$stmt->insert_id;
    $stmt->close();
    hrmJson('success', 'Salary structure saved successfully.', ['id' => $newId, 'gross' => $gross, 'deductions' => $deductions, 'net' => $net]);
}

if ($method === 'POST' && $action === 'deletestructure') {
    $id = (int)($inputData['id'] ?? 0);
    if ($id <= 0) {
        hrmJson('error', 'Invalid structure ID.');
    }
    $stmt = $link->prepare('DELETE FROM tblhr_salary_structure WHERE iStructureid = ?');
    $stmt->bind_param('i', $id);
    if (!$stmt->execute()) {
        $err = $stmt->error;
        $stmt->close();
        hrmJson('error', 'Delete failed: ' . $err);
    }
    $stmt->close();
    hrmJson('success', 'Salary structure deleted successfully.');
}

// ==================== PAYROLL ====================

if ($method === 'POST' && $action === 'listpayroll') {
    $month = hrmEmptyToNull($inputData['month'] ?? '');
    $status = hrmEmptyToNull($inputData['status'] ?? '');
    $sql = "SELECT p.*, e.sFullName, e.sEmployeeCode
            FROM tblhr_payroll p
            JOIN tblhr_employee e ON e.iEmployeeid = p.iEmployeeid
            WHERE 1=1";
    $params = [];
    $types = '';
    if ($month) {
        $sql .= " AND p.sPayMonth = ?";
        $params[] = $month;
        $types .= 's';
    }
    if ($status) {
        $sql .= " AND p.sStatus = ?";
        $params[] = $status;
        $types .= 's';
    }
    $sql .= " ORDER BY p.sPayMonth DESC, e.sFullName ASC";
    $stmt = $link->prepare($sql);
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    hrmJson('success', 'Payroll fetched', $rows);
}

if ($method === 'POST' && $action === 'runpayroll') {
    $month = hrmEmptyToNull($inputData['month'] ?? '');
    if (!$month || !preg_match('/^\d{4}-\d{2}$/', $month)) {
        hrmJson('error', 'Please select a valid pay month.');
    }
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $monthEnd = $month . '-31';

    $sql = "SELECT s.* FROM tblhr_salary_structure s
            INNER JOIN (
                SELECT iEmployeeid, MAX(dEffectiveFrom) AS maxDate
                FROM tblhr_salary_structure
                WHERE dEffectiveFrom <= ?
                GROUP BY iEmployeeid
            ) latest ON latest.iEmployeeid = s.iEmployeeid AND latest.maxDate = s.dEffectiveFrom";
    $stmt = $link->prepare($sql);
    $stmt->bind_param('s', $monthEnd);
    $stmt->execute();
    $structures = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (!$structures) {
        hrmJson('error', 'No active salary structures found for this month.');
    }

    $created = 0;
    $skipped = 0;
    $ins = $link->prepare("INSERT IGNORE INTO tblhr_payroll
        (iEmployeeid, iStructureid, sPayMonth, fBasicSalary, fHRA, fDA, fTravelAllowance, fMedicalAllowance, fSpecialAllowance, fOtherAllowance,
         fPF, fESI, fProfessionalTax, fTds, fOtherDeduction, fGrossSalary, fTotalDeductions, fNetSalary, sStatus, iCreatedBy)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?)");

    foreach ($structures as $s) {
        $tds = 0.0;
        if ($s['sTdsType'] === 'percent') {
            $tds = ((float)$s['fBasicSalary'] + (float)$s['fDA']) * ((float)$s['fTdsValue'] / 100);
        } else {
            $tds = (float)$s['fTdsValue'];
        }
        $empId = (int)$s['iEmployeeid'];
        $structId = (int)$s['iStructureid'];
        $basic = (float)$s['fBasicSalary'];
        $hra = (float)$s['fHRA'];
        $da = (float)$s['fDA'];
        $travel = (float)$s['fTravelAllowance'];
        $medical = (float)$s['fMedicalAllowance'];
        $special = (float)$s['fSpecialAllowance'];
        $otherAllow = (float)$s['fOtherAllowance'];
        $pf = (float)$s['fPF'];
        $esi = (float)$s['fESI'];
        $pt = (float)$s['fProfessionalTax'];
        $otherDed = (float)$s['fOtherDeduction'];
        $gross = $basic + $hra + $da + $travel + $medical + $special + $otherAllow;
        $deductions = $pf + $esi + $pt + $tds + $otherDed;
        $net = $gross - $deductions;

        $ins->bind_param(
            'iisdddddddddddddddi',
            $empId, $structId, $month, $basic, $hra, $da, $travel, $medical, $special, $otherAllow,
            $pf, $esi, $pt, $tds, $otherDed, $gross, $deductions, $net, $userId
        );
        $ins->execute();
        if ($ins->affected_rows > 0) {
            $created++;
        } else {
            $skipped++;
        }
    }
    $ins->close();
    hrmJson('success', "Payroll run complete: {$created} payslip(s) created, {$skipped} already existed.", ['created' => $created, 'skipped' => $skipped]);
}

if ($method === 'POST' && $action === 'markpayrollpaid') {
    $id = (int)($inputData['id'] ?? 0);
    if ($id <= 0) {
        hrmJson('error', 'Invalid payroll ID.');
    }
    $stmt = $link->prepare("UPDATE tblhr_payroll SET sStatus = 'Paid', dPaidDate = CURDATE(), sModifiedTimestamp = NOW() WHERE iPayrollid = ?");
    $stmt->bind_param('i', $id);
    if (!$stmt->execute()) {
        $err = $stmt->error;
        $stmt->close();
        hrmJson('error', 'Update failed: ' . $err);
    }
    $stmt->close();
    hrmJson('success', 'Payslip marked as paid.');
}

if ($method === 'POST' && $action === 'deletepayroll') {
    $id = (int)($inputData['id'] ?? 0);
    if ($id <= 0) {
        hrmJson('error', 'Invalid payroll ID.');
    }
    $stmt = $link->prepare('DELETE FROM tblhr_payroll WHERE iPayrollid = ?');
    $stmt->bind_param('i', $id);
    if (!$stmt->execute()) {
        $err = $stmt->error;
        $stmt->close();
        hrmJson('error', 'Delete failed: ' . $err);
    }
    $stmt->close();
    hrmJson('success', 'Payslip deleted successfully.');
}

// ==================== ADVANCES ====================

if ($method === 'POST' && $action === 'listadvances') {
    $sql = "SELECT a.*, e.sFullName, e.sEmployeeCode, (a.fAmount - a.fRecovered) AS fOutstanding
            FROM tblhr_advance a
            JOIN tblhr_employee e ON e.iEmployeeid = a.iEmployeeid
            ORDER BY a.iAdvanceid DESC";
    $rows = $link->query($sql)->fetch_all(MYSQLI_ASSOC);
    hrmJson('success', 'Advances fetched', $rows);
}

if ($method === 'POST' && $action === 'getadvancebyid') {
    $id = (int)($inputData['id'] ?? 0);
    if ($id <= 0) {
        hrmJson('error', 'Invalid advance ID.');
    }
    $stmt = $link->prepare('SELECT * FROM tblhr_advance WHERE iAdvanceid = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) {
        hrmJson('error', 'Advance not found.');
    }
    hrmJson('success', 'Advance fetched', $row);
}

if ($method === 'POST' && in_array($action, ['saveadvance', 'updateadvance'], true)) {
    $employeeId = (int)($inputData['employee_id'] ?? 0);
    $type = trim($inputData['type'] ?? 'Salary Advance');
    $amount = hrmNum($inputData['amount'] ?? 0);
    $monthlyDeduction = hrmNum($inputData['monthly_deduction'] ?? 0);
    $date = hrmEmptyToNull($inputData['date'] ?? '');
    $reason = hrmEmptyToNull($inputData['reason'] ?? '');
    $userId = (int)($_SESSION['user_id'] ?? 0);

    if ($employeeId <= 0) {
        hrmJson('error', 'Please select an employee.');
    }
    if ($type === '') {
        hrmJson('error', 'Please select a type.');
    }
    if ($amount <= 0) {
        hrmJson('error', 'Please enter a valid amount.');
    }

    if ($action === 'updateadvance') {
        $id = (int)($inputData['id'] ?? 0);
        if ($id <= 0) {
            hrmJson('error', 'Invalid advance ID.');
        }
        $sql = "UPDATE tblhr_advance SET
            iEmployeeid = ?, sType = ?, fAmount = ?, fMonthlyDeduction = ?, dDate = ?, sReason = ?, sModifiedTimestamp = NOW()
            WHERE iAdvanceid = ?";
        $stmt = $link->prepare($sql);
        $stmt->bind_param('isddssi', $employeeId, $type, $amount, $monthlyDeduction, $date, $reason, $id);
        if (!$stmt->execute()) {
            $err = $stmt->error;
            $stmt->close();
            hrmJson('error', 'Update failed: ' . $err);
        }
        $stmt->close();
        hrmJson('success', 'Advance updated successfully.', ['id' => $id]);
    }

    $sql = "INSERT INTO tblhr_advance (iEmployeeid, sType, fAmount, fMonthlyDeduction, dDate, sReason, iCreatedBy)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $link->prepare($sql);
    $stmt->bind_param('isddssi', $employeeId, $type, $amount, $monthlyDeduction, $date, $reason, $userId);
    if (!$stmt->execute()) {
        $err = $stmt->error;
        $stmt->close();
        hrmJson('error', 'Save failed: ' . $err);
    }
    $newId = (int)$stmt->insert_id;
    $stmt->close();
    hrmJson('success', 'Advance saved successfully.', ['id' => $newId]);
}

if ($method === 'POST' && $action === 'recordadvancerepayment') {
    $id = (int)($inputData['id'] ?? 0);
    $amount = hrmNum($inputData['amount'] ?? 0);
    if ($id <= 0) {
        hrmJson('error', 'Invalid advance ID.');
    }
    if ($amount <= 0) {
        hrmJson('error', 'Please enter a valid repayment amount.');
    }
    $stmt = $link->prepare('SELECT fAmount, fRecovered FROM tblhr_advance WHERE iAdvanceid = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) {
        hrmJson('error', 'Advance not found.');
    }
    $newRecovered = min((float)$row['fAmount'], (float)$row['fRecovered'] + $amount);
    $status = ($newRecovered >= (float)$row['fAmount']) ? 'Closed' : 'Active';

    $upd = $link->prepare('UPDATE tblhr_advance SET fRecovered = ?, sStatus = ?, sModifiedTimestamp = NOW() WHERE iAdvanceid = ?');
    $upd->bind_param('dsi', $newRecovered, $status, $id);
    if (!$upd->execute()) {
        $err = $upd->error;
        $upd->close();
        hrmJson('error', 'Repayment update failed: ' . $err);
    }
    $upd->close();
    hrmJson('success', 'Repayment recorded successfully.', ['recovered' => $newRecovered, 'status' => $status]);
}

if ($method === 'POST' && $action === 'deleteadvance') {
    $id = (int)($inputData['id'] ?? 0);
    if ($id <= 0) {
        hrmJson('error', 'Invalid advance ID.');
    }
    $stmt = $link->prepare('DELETE FROM tblhr_advance WHERE iAdvanceid = ?');
    $stmt->bind_param('i', $id);
    if (!$stmt->execute()) {
        $err = $stmt->error;
        $stmt->close();
        hrmJson('error', 'Delete failed: ' . $err);
    }
    $stmt->close();
    hrmJson('success', 'Advance deleted successfully.');
}

// ==================== DASHBOARD ====================

if ($method === 'POST' && $action === 'hrdashboardstats') {
    $totalEmployees = (int)$link->query("SELECT COUNT(*) c FROM tblhr_employee")->fetch_assoc()['c'];
    $activeEmployees = (int)$link->query("SELECT COUNT(*) c FROM tblhr_employee WHERE sStatus = 'Active'")->fetch_assoc()['c'];
    $structureCount = (int)$link->query("SELECT COUNT(*) c FROM tblhr_salary_structure")->fetch_assoc()['c'];

    $curMonth = date('Y-m');
    $stmt = $link->prepare("SELECT COUNT(*) c, COALESCE(SUM(fNetSalary),0) net, COALESCE(SUM(CASE WHEN sStatus='Pending' THEN 1 ELSE 0 END),0) pending FROM tblhr_payroll WHERE sPayMonth = ?");
    $stmt->bind_param('s', $curMonth);
    $stmt->execute();
    $payrollRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $genderRows = $link->query("SELECT COALESCE(NULLIF(sGender,''),'Unspecified') g, COUNT(*) c FROM tblhr_employee GROUP BY g")->fetch_all(MYSQLI_ASSOC);

    $recent = $link->query("SELECT iEmployeeid, sFullName, sEmployeeCode, sGender, sCreatedTimeStamp FROM tblhr_employee ORDER BY iEmployeeid DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

    hrmJson('success', 'Dashboard stats fetched', [
        'total_employees' => $totalEmployees,
        'active_employees' => $activeEmployees,
        'structure_count' => $structureCount,
        'current_month' => $curMonth,
        'current_month_payslips' => (int)$payrollRow['c'],
        'current_month_net' => (float)$payrollRow['net'],
        'current_month_pending' => (int)$payrollRow['pending'],
        'gender_breakdown' => $genderRows,
        'recent_employees' => $recent,
    ]);
}

hrmJson('error', 'Invalid action.');
