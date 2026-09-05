<?php
/**
 * HR Offer / Joining letter CRUD API
 * Supports JSON body and multipart FormData (signature upload).
 */
session_start();
header('Content-Type: application/json');
date_default_timezone_set('Asia/Kolkata');

require_once __DIR__ . '/layouts/config.php';
require_once __DIR__ . '/layouts/crm-access.php';
require_once __DIR__ . '/layouts/letterhead.php';

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

function hrLetterJson($status, $message, $data = null) {
    $out = ['status' => $status, 'message' => $message];
    if ($data !== null) {
        $out['data'] = $data;
    }
    echo json_encode($out);
    exit;
}

function hrLetterRequireAdmin($link) {
    if (empty($_SESSION['user_id'])) {
        hrLetterJson('error', 'Please login first.');
    }
    if (!crmIsAdmin()) {
        hrLetterJson('error', 'Access denied. Admin only.');
    }
}

function hrLetterEnsureTable($link) {
    static $done = false;
    if ($done) {
        return;
    }
    $sql = "CREATE TABLE IF NOT EXISTS tblhr_letter (
      iLetterid INT AUTO_INCREMENT PRIMARY KEY,
      sLetterType ENUM('offer','joining') NOT NULL,
      sRefNo VARCHAR(50) NOT NULL DEFAULT '',
      sLetterDate DATE NULL,
      sEmployeeTitle VARCHAR(20) NOT NULL DEFAULT 'Ms.',
      sEmployeeName VARCHAR(150) NOT NULL DEFAULT '',
      sDesignation VARCHAR(150) NOT NULL DEFAULT '',
      sEmployeeAddress TEXT NULL,
      sJoiningDate DATE NULL,
      sJoiningTime VARCHAR(50) NULL,
      sOfficeLocation VARCHAR(150) NULL,
      sReportingManager VARCHAR(150) NULL,
      sSalary VARCHAR(120) NULL,
      sProbation VARCHAR(100) NULL,
      iValidityDays INT NULL DEFAULT 7,
      sCompanyPhone VARCHAR(50) NULL,
      sCompanyAddress TEXT NULL,
      sSignaturePath VARCHAR(255) NULL,
      iCreatedBy INT NULL,
      sCreatedTimeStamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      sModifiedTimestamp DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
      INDEX idx_type (sLetterType),
      INDEX idx_name (sEmployeeName),
      INDEX idx_letter_date (sLetterDate)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    mysqli_query($link, $sql);
    $done = true;
}

function hrLetterEmptyToNull($v) {
    $v = trim((string)$v);
    return $v === '' ? null : $v;
}

function hrLetterCollectFields($inputData, $type) {
    $validity = (int)($inputData['validity_days'] ?? 7);
    if ($validity < 1) {
        $validity = 7;
    }

    return [
        'type' => $type,
        'ref_no' => trim($inputData['ref_no'] ?? ''),
        'letter_date' => hrLetterEmptyToNull($inputData['letter_date'] ?? ''),
        'employee_title' => trim($inputData['employee_title'] ?? 'Ms.'),
        'employee_name' => trim($inputData['employee_name'] ?? ''),
        'designation' => trim($inputData['designation'] ?? ''),
        'employee_address' => trim($inputData['employee_address'] ?? ''),
        'joining_date' => hrLetterEmptyToNull($inputData['joining_date'] ?? ''),
        'joining_time' => trim($inputData['joining_time'] ?? ''),
        'office_location' => trim($inputData['office_location'] ?? ''),
        'reporting_manager' => trim($inputData['reporting_manager'] ?? ''),
        'salary' => trim($inputData['salary'] ?? ''),
        'probation' => trim($inputData['probation'] ?? ''),
        'validity_days' => $validity,
        'company_phone' => trim($inputData['company_phone'] ?? ''),
        'company_address' => trim($inputData['company_address'] ?? ''),
    ];
}

hrLetterRequireAdmin($link);
hrLetterEnsureTable($link);

// -------- LIST --------
if ($method === 'POST' && ($action === 'listofferletters' || $action === 'listjoiningletters')) {
    $type = ($action === 'listofferletters') ? 'offer' : 'joining';
    $stmt = $link->prepare(
        "SELECT l.*, u.sName AS created_by_name
         FROM tblhr_letter l
         LEFT JOIN tbluser u ON u.iUserid = l.iCreatedBy
         WHERE l.sLetterType = ?
         ORDER BY l.iLetterid DESC"
    );
    $stmt->bind_param('s', $type);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    hrLetterJson('success', 'Letters fetched', $rows);
}

// -------- GET BY ID --------
if ($method === 'POST' && $action === 'gethrletterbyid') {
    $id = (int)($inputData['id'] ?? 0);
    if ($id <= 0) {
        hrLetterJson('error', 'Invalid letter ID.');
    }
    $stmt = $link->prepare('SELECT * FROM tblhr_letter WHERE iLetterid = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) {
        hrLetterJson('error', 'Letter not found.');
    }
    hrLetterJson('success', 'Letter fetched', $row);
}

// -------- DELETE --------
if ($method === 'POST' && $action === 'deletehrletter') {
    $id = (int)($inputData['id'] ?? 0);
    if ($id <= 0) {
        hrLetterJson('error', 'Invalid letter ID.');
    }
    $stmt = $link->prepare('SELECT sSignaturePath FROM tblhr_letter WHERE iLetterid = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) {
        hrLetterJson('error', 'Letter not found.');
    }

    $del = $link->prepare('DELETE FROM tblhr_letter WHERE iLetterid = ?');
    $del->bind_param('i', $id);
    if (!$del->execute()) {
        $del->close();
        hrLetterJson('error', 'Failed to delete letter.');
    }
    $del->close();

    $sig = $row['sSignaturePath'] ?? '';
    if ($sig && strpos($sig, 'uploads/hr-signatures/') === 0) {
        $full = __DIR__ . '/' . $sig;
        if (is_file($full)) {
            @unlink($full);
        }
    }
    hrLetterJson('success', 'Letter deleted successfully.');
}

// -------- SAVE / UPDATE --------
if ($method === 'POST' && in_array($action, ['saveofferletter', 'updateofferletter', 'savejoiningletter', 'updatejoiningletter'], true)) {
    $isOffer = strpos($action, 'offer') !== false;
    $isUpdate = strpos($action, 'update') === 0;
    $type = $isOffer ? 'offer' : 'joining';
    $fields = hrLetterCollectFields($inputData, $type);

    if ($fields['employee_name'] === '') {
        hrLetterJson('error', 'Employee / candidate name is required.');
    }
    if ($fields['designation'] === '') {
        hrLetterJson('error', 'Designation is required.');
    }

    $userId = (int)($_SESSION['user_id'] ?? 0);
    $newSig = letterResolveSignature('signature');
    // letterResolveSignature returns default path when no file — only store uploaded paths
    $uploadedSig = null;
    if ($newSig !== 'assets/images/director-signature.png' && strpos($newSig, 'uploads/hr-signatures/') === 0) {
        $uploadedSig = $newSig;
    }

    if ($isUpdate) {
        $id = (int)($inputData['id'] ?? 0);
        if ($id <= 0) {
            hrLetterJson('error', 'Invalid letter ID.');
        }

        $check = $link->prepare('SELECT iLetterid, sSignaturePath FROM tblhr_letter WHERE iLetterid = ? AND sLetterType = ? LIMIT 1');
        $check->bind_param('is', $id, $type);
        $check->execute();
        $existing = $check->get_result()->fetch_assoc();
        $check->close();
        if (!$existing) {
            hrLetterJson('error', 'Letter not found.');
        }

        $sigPath = $existing['sSignaturePath'];
        if ($uploadedSig) {
            if ($sigPath && strpos($sigPath, 'uploads/hr-signatures/') === 0) {
                $oldFull = __DIR__ . '/' . $sigPath;
                if (is_file($oldFull)) {
                    @unlink($oldFull);
                }
            }
            $sigPath = $uploadedSig;
        }

        $refNo = $fields['ref_no'];
        $letterDate = $fields['letter_date'];
        $empTitle = $fields['employee_title'];
        $empName = $fields['employee_name'];
        $designation = $fields['designation'];
        $empAddress = $fields['employee_address'];
        $joiningDate = $fields['joining_date'];
        $joiningTime = $fields['joining_time'];
        $officeLocation = $fields['office_location'];
        $reportingManager = $fields['reporting_manager'];
        $salary = $fields['salary'];
        $probation = $fields['probation'];
        $validityDays = $fields['validity_days'];
        $companyPhone = $fields['company_phone'];
        $companyAddress = $fields['company_address'];

        $sql = "UPDATE tblhr_letter SET
            sRefNo = ?, sLetterDate = ?, sEmployeeTitle = ?, sEmployeeName = ?, sDesignation = ?,
            sEmployeeAddress = ?, sJoiningDate = ?, sJoiningTime = ?, sOfficeLocation = ?, sReportingManager = ?,
            sSalary = ?, sProbation = ?, iValidityDays = ?, sCompanyPhone = ?, sCompanyAddress = ?,
            sSignaturePath = ?, sModifiedTimestamp = NOW()
            WHERE iLetterid = ? AND sLetterType = ?";
        $stmt = $link->prepare($sql);
        $stmt->bind_param(
            'ssssssssssssisssis',
            $refNo,
            $letterDate,
            $empTitle,
            $empName,
            $designation,
            $empAddress,
            $joiningDate,
            $joiningTime,
            $officeLocation,
            $reportingManager,
            $salary,
            $probation,
            $validityDays,
            $companyPhone,
            $companyAddress,
            $sigPath,
            $id,
            $type
        );
        if (!$stmt->execute()) {
            $err = $stmt->error;
            $stmt->close();
            hrLetterJson('error', 'Update failed: ' . $err);
        }
        $stmt->close();
        hrLetterJson('success', 'Letter updated successfully.', ['id' => $id]);
    }

    // INSERT
    $sigPath = $uploadedSig;
    $refNo = $fields['ref_no'];
    $letterDate = $fields['letter_date'];
    $empTitle = $fields['employee_title'];
    $empName = $fields['employee_name'];
    $designation = $fields['designation'];
    $empAddress = $fields['employee_address'];
    $joiningDate = $fields['joining_date'];
    $joiningTime = $fields['joining_time'];
    $officeLocation = $fields['office_location'];
    $reportingManager = $fields['reporting_manager'];
    $salary = $fields['salary'];
    $probation = $fields['probation'];
    $validityDays = $fields['validity_days'];
    $companyPhone = $fields['company_phone'];
    $companyAddress = $fields['company_address'];

    $sql = "INSERT INTO tblhr_letter
        (sLetterType, sRefNo, sLetterDate, sEmployeeTitle, sEmployeeName, sDesignation,
         sEmployeeAddress, sJoiningDate, sJoiningTime, sOfficeLocation, sReportingManager,
         sSalary, sProbation, iValidityDays, sCompanyPhone, sCompanyAddress, sSignaturePath, iCreatedBy)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $link->prepare($sql);
    $stmt->bind_param(
        'sssssssssssssisssi',
        $type,
        $refNo,
        $letterDate,
        $empTitle,
        $empName,
        $designation,
        $empAddress,
        $joiningDate,
        $joiningTime,
        $officeLocation,
        $reportingManager,
        $salary,
        $probation,
        $validityDays,
        $companyPhone,
        $companyAddress,
        $sigPath,
        $userId
    );
    if (!$stmt->execute()) {
        $err = $stmt->error;
        $stmt->close();
        hrLetterJson('error', 'Save failed: ' . $err);
    }
    $newId = (int)$stmt->insert_id;
    $stmt->close();
    hrLetterJson('success', 'Letter saved successfully.', ['id' => $newId]);
}

hrLetterJson('error', 'Invalid action.');
