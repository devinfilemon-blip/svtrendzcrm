<?php
include 'layouts/session.php';
include 'layouts/config.php';

header('Content-Type: application/json');
date_default_timezone_set('Asia/Kolkata');
error_reporting(0);
ini_set('display_errors', '0');

// A fatal error otherwise leaves the response body completely empty (blank
// 500), which the frontend can't show to the user. Surface it as JSON instead.
register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_COMPILE_ERROR, E_CORE_ERROR], true) && !headers_sent()) {
        if (ob_get_level() > 0) {
            ob_clean();
        }
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $err['message'] . ' in ' . $err['file'] . ':' . $err['line']]);
    }
});

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'Client') {
    echo json_encode(['status' => 'error', 'message' => 'Access denied.']);
    exit;
}

function jsonOut($payload)
{
    echo json_encode($payload);
    exit;
}

function ensureColumn($link, $table, $column, $definition)
{
    $table = mysqli_real_escape_string($link, $table);
    $column = mysqli_real_escape_string($link, $column);
    $result = mysqli_query($link, "SHOW COLUMNS FROM `$table` LIKE '$column'");
    if ($result && mysqli_num_rows($result) > 0) {
        return;
    }
    if (!mysqli_query($link, "ALTER TABLE `$table` ADD COLUMN `$column` $definition")) {
        jsonOut(['status' => 'error', 'message' => "Could not add column $column to $table: " . mysqli_error($link)]);
    }
}

function ensureProjectTasksTable($link)
{
    $sql = "CREATE TABLE IF NOT EXISTS tblproject_tasks (
      id INT AUTO_INCREMENT PRIMARY KEY,
      lead_id INT NOT NULL,
      sTitle VARCHAR(255) NOT NULL,
      sDescription TEXT NULL,
      sAssigned_to INT NOT NULL,
      sCreated_by INT NOT NULL,
      sStatus VARCHAR(50) NOT NULL DEFAULT 'Pending',
      sDue_date DATE NULL,
      sCreated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      sUpdated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
      INDEX idx_lead (lead_id),
      INDEX idx_assigned (sAssigned_to),
      INDEX idx_status (sStatus)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    if (!mysqli_query($link, $sql)) {
        jsonOut(['status' => 'error', 'message' => 'Could not prepare project tasks table: ' . mysqli_error($link)]);
    }
    // Table may pre-date these columns on older deployments; add them if missing.
    ensureColumn($link, 'tblproject_tasks', 'sDue_date', 'DATE NULL');
    ensureColumn($link, 'tblproject_tasks', 'sStartLatitude', 'DECIMAL(10,7) NULL');
    ensureColumn($link, 'tblproject_tasks', 'sStartLongitude', 'DECIMAL(10,7) NULL');
    ensureColumn($link, 'tblproject_tasks', 'sStartTime', 'DATETIME NULL');
    ensureColumn($link, 'tblproject_tasks', 'sEndLatitude', 'DECIMAL(10,7) NULL');
    ensureColumn($link, 'tblproject_tasks', 'sEndLongitude', 'DECIMAL(10,7) NULL');
    ensureColumn($link, 'tblproject_tasks', 'sEndTime', 'DATETIME NULL');
    ensureColumn($link, 'tblproject_tasks', 'sReport', 'TEXT NULL');
}

if (!defined('PROJECT_TASK_PETROL_RATE_PER_KM')) {
    define('PROJECT_TASK_PETROL_RATE_PER_KM', 3);
}

// Great-circle distance between two lat/lng points, in kilometers. Returns
// null when either point is missing (task not started and/or not completed).
function haversineDistanceKm($lat1, $lng1, $lat2, $lng2)
{
    if ($lat1 === null || $lng1 === null || $lat2 === null || $lng2 === null) {
        return null;
    }
    $lat1 = (float)$lat1;
    $lng1 = (float)$lng1;
    $lat2 = (float)$lat2;
    $lng2 = (float)$lng2;
    $earthRadiusKm = 6371;
    $dLat = deg2rad($lat2 - $lat1);
    $dLng = deg2rad($lng2 - $lng1);
    $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $earthRadiusKm * $c;
}

function ensureProjectNotificationsTable($link)
{
    $sql = "CREATE TABLE IF NOT EXISTS tblproject_notifications (
      id INT AUTO_INCREMENT PRIMARY KEY,
      iUserid INT NOT NULL,
      lead_id INT NOT NULL,
      sType VARCHAR(50) NOT NULL,
      sMessage TEXT NOT NULL,
      iIsRead TINYINT(1) NOT NULL DEFAULT 0,
      dCreatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      iCreatedBy INT NULL,
      INDEX idx_user (iUserid),
      INDEX idx_lead (lead_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    if (!mysqli_query($link, $sql)) {
        jsonOut(['status' => 'error', 'message' => 'Could not prepare notifications table: ' . mysqli_error($link)]);
    }
}

function ensureProjectExpensesTable($link)
{
    $sql = "CREATE TABLE IF NOT EXISTS tblproject_expenses (
      id INT AUTO_INCREMENT PRIMARY KEY,
      lead_id INT NOT NULL,
      iUserid INT NOT NULL,
      sCategory VARCHAR(30) NOT NULL,
      dAmount DECIMAL(10,2) NOT NULL,
      sExpenseDate DATE NOT NULL,
      sDescription TEXT NULL,
      sReceipt VARCHAR(255) NULL,
      sReceiptName VARCHAR(255) NULL,
      sStatus VARCHAR(20) NOT NULL DEFAULT 'Pending',
      iReviewedBy INT NULL,
      sReviewNote TEXT NULL,
      dCreatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      dUpdatedAt DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
      INDEX idx_lead (lead_id),
      INDEX idx_user (iUserid),
      INDEX idx_status (sStatus)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    if (!mysqli_query($link, $sql)) {
        jsonOut(['status' => 'error', 'message' => 'Could not prepare expenses table: ' . mysqli_error($link)]);
    }
}

function ensureProjectVisitsTable($link)
{
    $sql1 = "CREATE TABLE IF NOT EXISTS tblproject_visits (
      id INT AUTO_INCREMENT PRIMARY KEY,
      lead_id INT NOT NULL,
      iUserid INT NOT NULL,
      sVisitType VARCHAR(20) NOT NULL,
      sVisitDate DATE NOT NULL,
      sLocation VARCHAR(255) NULL,
      sNotes TEXT NULL,
      dCreatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      INDEX idx_lead (lead_id),
      INDEX idx_user (iUserid)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $sql2 = "CREATE TABLE IF NOT EXISTS tblproject_visit_photos (
      id INT AUTO_INCREMENT PRIMARY KEY,
      visit_id INT NOT NULL,
      sPhoto VARCHAR(255) NOT NULL,
      sPhotoName VARCHAR(255) NULL,
      dCreatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      INDEX idx_visit (visit_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    foreach ([$sql1, $sql2] as $sql) {
        if (!mysqli_query($link, $sql)) {
            jsonOut(['status' => 'error', 'message' => 'Could not prepare visits table: ' . mysqli_error($link)]);
        }
    }
}

function ensureLocationTable($link)
{
    $sql = "CREATE TABLE IF NOT EXISTS tbllocation (
      iLocationid INT AUTO_INCREMENT PRIMARY KEY,
      iUserid INT NOT NULL,
      dLatitude DECIMAL(10,7) NOT NULL,
      dLongitude DECIMAL(10,7) NOT NULL,
      dEndLatitude DECIMAL(10,7) NULL,
      dEndLongitude DECIMAL(10,7) NULL,
      sDateTime DATETIME NOT NULL,
      sEndDateTime DATETIME NULL,
      sCreatedTimeStamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      INDEX idx_user (iUserid),
      INDEX idx_datetime (sDateTime)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    if (!mysqli_query($link, $sql)) {
        jsonOut(['status' => 'error', 'message' => 'Could not prepare location table: ' . mysqli_error($link)]);
    }
}

try {
    ensureProjectTasksTable($link);
    ensureProjectNotificationsTable($link);
    ensureProjectExpensesTable($link);
    ensureProjectVisitsTable($link);
    ensureLocationTable($link);
} catch (Throwable $e) {
    jsonOut(['status' => 'error', 'message' => 'Database setup failed: ' . $e->getMessage()]);
}

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!is_array($input)) {
    $input = $_POST;
}

$action = isset($input['action']) ? $input['action'] : '';
$userId = (int)$_SESSION['user_id'];
$isAdmin = (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'Admin');
$wonStatusId = '6'; // Won

function normalizeAssignedIds($value)
{
    $parts = preg_split('/\s*,\s*/', trim((string)$value), -1, PREG_SPLIT_NO_EMPTY);
    $ids = [];
    foreach ($parts as $part) {
        $id = (int)$part;
        if ($id > 0) {
            $ids[$id] = $id;
        }
    }
    return array_values($ids);
}

function resolveUserNames($link, $ids)
{
    if (!is_array($ids)) {
        $ids = [];
    }
    if (!$ids) {
        return '';
    }
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));
    $stmt = $link->prepare("SELECT iUserid, sName FROM tbluser WHERE iUserid IN ($placeholders)");
    if (!$stmt) {
        return '';
    }
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $result = $stmt->get_result();
    $map = [];
    while ($row = $result->fetch_assoc()) {
        $map[(int)$row['iUserid']] = $row['sName'];
    }
    $stmt->close();
    $names = [];
    foreach ($ids as $id) {
        if (isset($map[$id])) {
            $names[] = $map[$id];
        }
    }
    return implode(', ', $names);
}

function getLeadRow($link, $leadId)
{
    $leadId = (int)$leadId;
    $stmt = $link->prepare("SELECT iLead_id, sCompany_name, sLead_name, sAssigned_to, sLead_owner, sLead_status, sPhone, sEmail
        FROM tblleads WHERE iLead_id = ? LIMIT 1");
    $stmt->bind_param('i', $leadId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function getEffectiveStatusId($link, $leadId, $fallbackStatus)
{
    $leadId = (int)$leadId;
    $stmt = $link->prepare("SELECT sStatus FROM tblreplayleads WHERE lead_id = ? ORDER BY sCreatedTimestamp DESC LIMIT 1");
    $stmt->bind_param('i', $leadId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($row && $row['sStatus'] !== null && $row['sStatus'] !== '') {
        return (string)$row['sStatus'];
    }
    return (string)$fallbackStatus;
}

function userCanAccessProject($lead, $userId, $isAdmin)
{
    if ($isAdmin) {
        return true;
    }
    $assigned = normalizeAssignedIds($lead['sAssigned_to'] ?? '');
    $owner = (int)($lead['sLead_owner'] ?? 0);
    return in_array($userId, $assigned, true) || $owner === $userId;
}

function projectAssignableUsers($link, $lead)
{
    $ids = normalizeAssignedIds($lead['sAssigned_to'] ?? '');
    $owner = (int)($lead['sLead_owner'] ?? 0);
    if ($owner > 0) {
        $ids[] = $owner;
    }
    $ids = array_values(array_unique(array_filter($ids)));
    if (!$ids) {
        return [];
    }
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));
    $stmt = $link->prepare("SELECT iUserid, sName, sRole FROM tbluser WHERE iUserid IN ($placeholders) ORDER BY sName");
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $result = $stmt->get_result();
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = [
            'id' => (int)$row['iUserid'],
            'name' => $row['sName'],
            'role' => $row['sRole'],
        ];
    }
    $stmt->close();
    return $users;
}

function userExists($link, $userId)
{
    $userId = (int)$userId;
    if ($userId <= 0) {
        return false;
    }
    $stmt = $link->prepare("SELECT 1 FROM tbluser WHERE iUserid = ? LIMIT 1");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $exists = (bool)$stmt->get_result()->fetch_row();
    $stmt->close();
    return $exists;
}

function canManageProjectTeam($link, $lead, $userId, $isAdmin)
{
    $ownerId = (int)($lead['sLead_owner'] ?? 0);
    if ($isAdmin || $ownerId === $userId) {
        return true;
    }
    // If the recorded owner account has been removed, every team member would
    // otherwise be permanently locked out of fixing the team assignment.
    if (!userExists($link, $ownerId)) {
        return in_array($userId, normalizeAssignedIds($lead['sAssigned_to'] ?? ''), true);
    }
    return false;
}

function notifyUser($link, $userId, $leadId, $type, $message, $createdBy)
{
    $userId = (int)$userId;
    $leadId = (int)$leadId;
    $createdBy = (int)$createdBy;
    $stmt = $link->prepare("INSERT INTO tblproject_notifications (iUserid, lead_id, sType, sMessage, iCreatedBy) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        return;
    }
    $stmt->bind_param('iissi', $userId, $leadId, $type, $message, $createdBy);
    $stmt->execute();
    $stmt->close();
}

function saveUploadedFile($file, $uploadDir, $allowedExt, $maxBytes)
{
    if (!$file || !is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return ['error' => 'File upload failed. Please try again.'];
    }

    $tmp = $file['tmp_name'] ?? '';
    $origName = (string)($file['name'] ?? 'file');
    $size = (int)($file['size'] ?? 0);

    if ($tmp === '' || !is_uploaded_file($tmp)) {
        return ['error' => 'Invalid upload.'];
    }
    if ($size <= 0 || $size > $maxBytes) {
        return ['error' => 'File must be between 1 byte and ' . round($maxBytes / (1024 * 1024)) . ' MB.'];
    }

    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    if ($ext === '' || !in_array($ext, $allowedExt, true)) {
        return ['error' => 'That file type is not allowed. Allowed: ' . implode(', ', $allowedExt) . '.'];
    }

    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0777, true);
    }

    $destName = bin2hex(random_bytes(16)) . '.' . $ext;
    $dest = $uploadDir . DIRECTORY_SEPARATOR . $destName;
    if (!@move_uploaded_file($tmp, $dest)) {
        return ['error' => 'Could not save the uploaded file.'];
    }

    return ['stored' => $destName, 'original' => $origName];
}

function saveProjectReceipt($file)
{
    return saveUploadedFile(
        $file,
        __DIR__ . '/uploads/project-expenses',
        ['pdf', 'png', 'jpg', 'jpeg'],
        10 * 1024 * 1024
    );
}

function saveProjectVisitPhoto($file)
{
    return saveUploadedFile(
        $file,
        __DIR__ . '/uploads/project-visit-photos',
        ['png', 'jpg', 'jpeg', 'gif', 'webp'],
        8 * 1024 * 1024
    );
}

// Records the device location captured at submit time as the "end location"
// of the field entry (expense/visit/meeting). There is no separate start
// point for these one-shot submissions, so the same coordinates fill both
// the required start columns and the end columns.
function saveEndLocation($link, $userId, $lat, $lng)
{
    if ($lat === null || $lng === null) {
        return;
    }
    $lat = (float)$lat;
    $lng = (float)$lng;
    if ($lat === 0.0 && $lng === 0.0) {
        return;
    }
    $userId = (int)$userId;
    $now = date('Y-m-d H:i:s');
    $stmt = $link->prepare("INSERT INTO tbllocation (iUserid, dLatitude, dLongitude, dEndLatitude, dEndLongitude, sDateTime, sEndDateTime) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        return;
    }
    $stmt->bind_param('iddddss', $userId, $lat, $lng, $lat, $lng, $now, $now);
    $stmt->execute();
    $stmt->close();
}

if ($action === 'list_won_projects') {
    try {
        $sql = "
            SELECT
                l.iLead_id,
                l.sCompany_name,
                l.sLead_name,
                l.sPhone,
                l.sEmail,
                l.sAssigned_to,
                l.sLead_owner,
                l.sCreated_date,
                COALESCE(NULLIF(r.sStatus, ''), l.sLead_status) AS status_id,
                owner.sName AS lead_owner_name,
                (SELECT COUNT(*) FROM tblproject_tasks t WHERE t.lead_id = l.iLead_id) AS task_total,
                (SELECT COUNT(*) FROM tblproject_tasks t WHERE t.lead_id = l.iLead_id AND t.sStatus = 'Done') AS task_done,
                (SELECT COUNT(*) FROM tblproject_tasks t WHERE t.lead_id = l.iLead_id AND t.sAssigned_to = ?) AS my_tasks
            FROM tblleads l
            LEFT JOIN (
                SELECT r1.lead_id, r1.sStatus
                FROM tblreplayleads r1
                INNER JOIN (
                    SELECT lead_id, MAX(sCreatedTimestamp) AS latest_ts
                    FROM tblreplayleads
                    GROUP BY lead_id
                ) r2 ON r1.lead_id = r2.lead_id AND r1.sCreatedTimestamp = r2.latest_ts
            ) r ON r.lead_id = l.iLead_id
            LEFT JOIN tbluser owner ON owner.iUserid = l.sLead_owner
            WHERE COALESCE(NULLIF(r.sStatus, ''), l.sLead_status) = ?
        ";

        if (!$isAdmin) {
            $sql .= " AND (FIND_IN_SET(?, REPLACE(l.sAssigned_to, ' ', '')) > 0 OR l.sLead_owner = ?)";
        }
        $sql .= " ORDER BY l.sCreated_date DESC, l.iLead_id DESC";

        $stmt = $link->prepare($sql);
        if (!$stmt) {
            jsonOut(['status' => 'error', 'message' => 'Query prepare failed: ' . $link->error]);
        }

        if ($isAdmin) {
            $stmt->bind_param('is', $userId, $wonStatusId);
        } else {
            $stmt->bind_param('isii', $userId, $wonStatusId, $userId, $userId);
        }
        if (!$stmt->execute()) {
            jsonOut(['status' => 'error', 'message' => 'Query execute failed: ' . $stmt->error]);
        }
        $result = $stmt->get_result();
        $projects = [];
        while ($row = $result->fetch_assoc()) {
            $assignedIds = normalizeAssignedIds(isset($row['sAssigned_to']) ? $row['sAssigned_to'] : '');
            $projects[] = [
                'iLead_id' => (int)$row['iLead_id'],
                'sCompany_name' => $row['sCompany_name'],
                'sLead_name' => $row['sLead_name'],
                'sPhone' => $row['sPhone'],
                'sEmail' => $row['sEmail'],
                'assigned_to_name' => resolveUserNames($link, $assignedIds),
                'assigned_ids' => $assignedIds,
                'lead_owner_id' => (int)$row['sLead_owner'],
                'lead_owner_name' => $row['lead_owner_name'] ? $row['lead_owner_name'] : '',
                'sCreated_date' => $row['sCreated_date'],
                'task_total' => (int)$row['task_total'],
                'task_done' => (int)$row['task_done'],
                'my_tasks' => (int)$row['my_tasks'],
            ];
        }
        $stmt->close();
        jsonOut(['status' => 'success', 'data' => $projects]);
    } catch (Throwable $e) {
        jsonOut(['status' => 'error', 'message' => 'Failed to load projects: ' . $e->getMessage()]);
    }
}

// Same as list_won_projects, but every lead regardless of status — used by the
// "All Projects" view so any project can get the identical task/team/expense/visit flow.
if ($action === 'list_all_projects') {
    try {
        $sql = "
            SELECT
                l.iLead_id,
                l.sCompany_name,
                l.sLead_name,
                l.sPhone,
                l.sEmail,
                l.sAssigned_to,
                l.sLead_owner,
                l.sCreated_date,
                COALESCE(NULLIF(r.sStatus, ''), l.sLead_status) AS status_id,
                st.sStatus AS status_name,
                owner.sName AS lead_owner_name,
                (SELECT COUNT(*) FROM tblproject_tasks t WHERE t.lead_id = l.iLead_id) AS task_total,
                (SELECT COUNT(*) FROM tblproject_tasks t WHERE t.lead_id = l.iLead_id AND t.sStatus = 'Done') AS task_done,
                (SELECT COUNT(*) FROM tblproject_tasks t WHERE t.lead_id = l.iLead_id AND t.sAssigned_to = ?) AS my_tasks
            FROM tblleads l
            LEFT JOIN (
                SELECT r1.lead_id, r1.sStatus
                FROM tblreplayleads r1
                INNER JOIN (
                    SELECT lead_id, MAX(sCreatedTimestamp) AS latest_ts
                    FROM tblreplayleads
                    GROUP BY lead_id
                ) r2 ON r1.lead_id = r2.lead_id AND r1.sCreatedTimestamp = r2.latest_ts
            ) r ON r.lead_id = l.iLead_id
            LEFT JOIN tbluser owner ON owner.iUserid = l.sLead_owner
            LEFT JOIN tblstatus st ON st.iStatusid = COALESCE(NULLIF(r.sStatus, ''), l.sLead_status)
            WHERE 1=1
        ";

        if (!$isAdmin) {
            $sql .= " AND (FIND_IN_SET(?, REPLACE(l.sAssigned_to, ' ', '')) > 0 OR l.sLead_owner = ?)";
        }
        $sql .= " ORDER BY l.sCreated_date DESC, l.iLead_id DESC";

        $stmt = $link->prepare($sql);
        if (!$stmt) {
            jsonOut(['status' => 'error', 'message' => 'Query prepare failed: ' . $link->error]);
        }

        if ($isAdmin) {
            $stmt->bind_param('i', $userId);
        } else {
            $stmt->bind_param('iii', $userId, $userId, $userId);
        }
        if (!$stmt->execute()) {
            jsonOut(['status' => 'error', 'message' => 'Query execute failed: ' . $stmt->error]);
        }
        $result = $stmt->get_result();
        $projects = [];
        while ($row = $result->fetch_assoc()) {
            $assignedIds = normalizeAssignedIds(isset($row['sAssigned_to']) ? $row['sAssigned_to'] : '');
            $projects[] = [
                'iLead_id' => (int)$row['iLead_id'],
                'sCompany_name' => $row['sCompany_name'],
                'sLead_name' => $row['sLead_name'],
                'sPhone' => $row['sPhone'],
                'sEmail' => $row['sEmail'],
                'assigned_to_name' => resolveUserNames($link, $assignedIds),
                'assigned_ids' => $assignedIds,
                'lead_owner_id' => (int)$row['sLead_owner'],
                'lead_owner_name' => $row['lead_owner_name'] ? $row['lead_owner_name'] : '',
                'sCreated_date' => $row['sCreated_date'],
                'status_id' => $row['status_id'],
                'status_name' => $row['status_name'] ? $row['status_name'] : '',
                'task_total' => (int)$row['task_total'],
                'task_done' => (int)$row['task_done'],
                'my_tasks' => (int)$row['my_tasks'],
            ];
        }
        $stmt->close();
        jsonOut(['status' => 'success', 'data' => $projects]);
    } catch (Throwable $e) {
        jsonOut(['status' => 'error', 'message' => 'Failed to load projects: ' . $e->getMessage()]);
    }
}

if ($action === 'get_project') {
    $leadId = (int)($input['lead_id'] ?? 0);
    if ($leadId <= 0) {
        jsonOut(['status' => 'error', 'message' => 'Invalid project']);
    }
    $lead = getLeadRow($link, $leadId);
    if (!$lead) {
        jsonOut(['status' => 'error', 'message' => 'Project not found']);
    }
    if (!userCanAccessProject($lead, $userId, $isAdmin)) {
        jsonOut(['status' => 'error', 'message' => 'You are not assigned to this project']);
    }

    $assignedIds = normalizeAssignedIds($lead['sAssigned_to'] ?? '');
    jsonOut([
        'status' => 'success',
        'data' => [
            'iLead_id' => (int)$lead['iLead_id'],
            'sCompany_name' => $lead['sCompany_name'],
            'sLead_name' => $lead['sLead_name'],
            'sPhone' => $lead['sPhone'],
            'sEmail' => $lead['sEmail'],
            'assigned_to_name' => resolveUserNames($link, $assignedIds),
            'lead_owner_name' => resolveUserNames($link, [(int)$lead['sLead_owner']]),
            'assignable_users' => projectAssignableUsers($link, $lead),
            'assigned_ids' => $assignedIds,
            'can_manage_all' => $isAdmin || (int)$lead['sLead_owner'] === $userId || in_array($userId, $assignedIds, true),
            'can_manage_team' => canManageProjectTeam($link, $lead, $userId, $isAdmin),
        ],
    ]);
}

if ($action === 'list_project_tasks') {
    $leadId = (int)($input['lead_id'] ?? 0);
    $mineOnly = !empty($input['mine_only']);
    if ($leadId <= 0) {
        jsonOut(['status' => 'error', 'message' => 'Invalid project']);
    }
    $lead = getLeadRow($link, $leadId);
    if (!$lead || !userCanAccessProject($lead, $userId, $isAdmin)) {
        jsonOut(['status' => 'error', 'message' => 'Access denied']);
    }

    $sql = "SELECT t.*, a.sName AS assigned_name, c.sName AS created_by_name
            FROM tblproject_tasks t
            LEFT JOIN tbluser a ON a.iUserid = t.sAssigned_to
            LEFT JOIN tbluser c ON c.iUserid = t.sCreated_by
            WHERE t.lead_id = ?";
    if ($mineOnly && !$isAdmin) {
        $sql .= " AND t.sAssigned_to = ?";
    }
    $sql .= " ORDER BY FIELD(t.sStatus, 'Pending', 'In Progress', 'Done'), t.sDue_date IS NULL, t.sDue_date ASC, t.id DESC";

    $stmt = $link->prepare($sql);
    if ($mineOnly && !$isAdmin) {
        $stmt->bind_param('ii', $leadId, $userId);
    } else {
        $stmt->bind_param('i', $leadId);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $tasks = [];
    while ($row = $result->fetch_assoc()) {
        $tasks[] = [
            'id' => (int)$row['id'],
            'lead_id' => (int)$row['lead_id'],
            'sTitle' => $row['sTitle'],
            'sDescription' => $row['sDescription'],
            'sAssigned_to' => (int)$row['sAssigned_to'],
            'assigned_name' => $row['assigned_name'],
            'sCreated_by' => (int)$row['sCreated_by'],
            'created_by_name' => $row['created_by_name'],
            'sStatus' => $row['sStatus'],
            'sDue_date' => $row['sDue_date'],
            'sCreated_at' => $row['sCreated_at'],
            'has_started' => !empty($row['sStartTime']),
            'has_report' => !empty($row['sEndTime']),
        ];
    }
    $stmt->close();
    jsonOut(['status' => 'success', 'data' => $tasks]);
}

if ($action === 'get_project_task') {
    $taskId = (int)($input['id'] ?? 0);
    if ($taskId <= 0) {
        jsonOut(['status' => 'error', 'message' => 'Invalid task']);
    }
    $stmt = $link->prepare("SELECT t.*, a.sName AS assigned_name, c.sName AS created_by_name
        FROM tblproject_tasks t
        LEFT JOIN tbluser a ON a.iUserid = t.sAssigned_to
        LEFT JOIN tbluser c ON c.iUserid = t.sCreated_by
        WHERE t.id = ? LIMIT 1");
    $stmt->bind_param('i', $taskId);
    $stmt->execute();
    $task = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$task) {
        jsonOut(['status' => 'error', 'message' => 'Task not found']);
    }
    $lead = getLeadRow($link, (int)$task['lead_id']);
    if (!$lead || !userCanAccessProject($lead, $userId, $isAdmin)) {
        jsonOut(['status' => 'error', 'message' => 'Access denied']);
    }

    $distanceKm = haversineDistanceKm(
        $task['sStartLatitude'],
        $task['sStartLongitude'],
        $task['sEndLatitude'],
        $task['sEndLongitude']
    );
    $petrolCost = $distanceKm !== null ? round($distanceKm * PROJECT_TASK_PETROL_RATE_PER_KM, 2) : null;
    $canEditAll = $isAdmin || (int)$lead['sLead_owner'] === $userId || in_array($userId, normalizeAssignedIds($lead['sAssigned_to'] ?? ''), true);
    $isAssignee = (int)$task['sAssigned_to'] === $userId;

    jsonOut([
        'status' => 'success',
        'data' => [
            'id' => (int)$task['id'],
            'lead_id' => (int)$task['lead_id'],
            'company_name' => $lead['sCompany_name'],
            'lead_name' => $lead['sLead_name'],
            'sTitle' => $task['sTitle'],
            'sDescription' => $task['sDescription'],
            'sAssigned_to' => (int)$task['sAssigned_to'],
            'assigned_name' => $task['assigned_name'],
            'sCreated_by' => (int)$task['sCreated_by'],
            'created_by_name' => $task['created_by_name'],
            'sStatus' => $task['sStatus'],
            'sDue_date' => $task['sDue_date'],
            'sCreated_at' => $task['sCreated_at'],
            'start_latitude' => $task['sStartLatitude'] !== null ? (float)$task['sStartLatitude'] : null,
            'start_longitude' => $task['sStartLongitude'] !== null ? (float)$task['sStartLongitude'] : null,
            'start_time' => $task['sStartTime'],
            'end_latitude' => $task['sEndLatitude'] !== null ? (float)$task['sEndLatitude'] : null,
            'end_longitude' => $task['sEndLongitude'] !== null ? (float)$task['sEndLongitude'] : null,
            'end_time' => $task['sEndTime'],
            'report' => $task['sReport'],
            'distance_km' => $distanceKm !== null ? round($distanceKm, 2) : null,
            'petrol_cost' => $petrolCost,
            'petrol_rate_per_km' => PROJECT_TASK_PETROL_RATE_PER_KM,
            'can_start' => ($isAssignee || $canEditAll) && empty($task['sEndTime']),
            'can_complete' => ($isAssignee || $canEditAll) && !empty($task['sStartTime']) && empty($task['sEndTime']),
        ],
    ]);
}

if ($action === 'start_project_task') {
    $taskId = (int)($input['id'] ?? 0);
    $lat = (isset($input['latitude']) && $input['latitude'] !== '') ? (float)$input['latitude'] : null;
    $lng = (isset($input['longitude']) && $input['longitude'] !== '') ? (float)$input['longitude'] : null;
    if ($taskId <= 0 || $lat === null || $lng === null) {
        jsonOut(['status' => 'error', 'message' => 'Task and current location are required']);
    }

    $stmt = $link->prepare("SELECT * FROM tblproject_tasks WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $taskId);
    $stmt->execute();
    $task = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$task) {
        jsonOut(['status' => 'error', 'message' => 'Task not found']);
    }
    $lead = getLeadRow($link, (int)$task['lead_id']);
    if (!$lead || !userCanAccessProject($lead, $userId, $isAdmin)) {
        jsonOut(['status' => 'error', 'message' => 'Access denied']);
    }
    $canEditAll = $isAdmin || (int)$lead['sLead_owner'] === $userId || in_array($userId, normalizeAssignedIds($lead['sAssigned_to'] ?? ''), true);
    $isAssignee = (int)$task['sAssigned_to'] === $userId;
    if (!$isAssignee && !$canEditAll) {
        jsonOut(['status' => 'error', 'message' => 'Only the assignee can start this task']);
    }
    if (!empty($task['sEndTime'])) {
        jsonOut(['status' => 'error', 'message' => 'This task has already been completed']);
    }

    $now = date('Y-m-d H:i:s');
    $newStatus = $task['sStatus'] === 'Pending' ? 'In Progress' : $task['sStatus'];
    $upd = $link->prepare("UPDATE tblproject_tasks SET sStartLatitude = ?, sStartLongitude = ?, sStartTime = ?, sStatus = ? WHERE id = ?");
    $upd->bind_param('ddssi', $lat, $lng, $now, $newStatus, $taskId);
    $ok = $upd->execute();
    $upd->close();
    jsonOut($ok
        ? ['status' => 'success', 'message' => 'Task started']
        : ['status' => 'error', 'message' => 'Could not start task']);
}

if ($action === 'complete_project_task') {
    $taskId = (int)($input['id'] ?? 0);
    $lat = (isset($input['latitude']) && $input['latitude'] !== '') ? (float)$input['latitude'] : null;
    $lng = (isset($input['longitude']) && $input['longitude'] !== '') ? (float)$input['longitude'] : null;
    $report = trim((string)($input['report'] ?? ''));
    if ($taskId <= 0 || $lat === null || $lng === null) {
        jsonOut(['status' => 'error', 'message' => 'Task and current location are required']);
    }
    if ($report === '') {
        jsonOut(['status' => 'error', 'message' => 'Please add a visit/meeting report before completing']);
    }

    $stmt = $link->prepare("SELECT * FROM tblproject_tasks WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $taskId);
    $stmt->execute();
    $task = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$task) {
        jsonOut(['status' => 'error', 'message' => 'Task not found']);
    }
    $lead = getLeadRow($link, (int)$task['lead_id']);
    if (!$lead || !userCanAccessProject($lead, $userId, $isAdmin)) {
        jsonOut(['status' => 'error', 'message' => 'Access denied']);
    }
    $canEditAll = $isAdmin || (int)$lead['sLead_owner'] === $userId || in_array($userId, normalizeAssignedIds($lead['sAssigned_to'] ?? ''), true);
    $isAssignee = (int)$task['sAssigned_to'] === $userId;
    if (!$isAssignee && !$canEditAll) {
        jsonOut(['status' => 'error', 'message' => 'Only the assignee can complete this task']);
    }
    if (empty($task['sStartTime'])) {
        jsonOut(['status' => 'error', 'message' => 'Start the task before submitting a report']);
    }
    if (!empty($task['sEndTime'])) {
        jsonOut(['status' => 'error', 'message' => 'This task has already been completed']);
    }

    $now = date('Y-m-d H:i:s');
    $upd = $link->prepare("UPDATE tblproject_tasks SET sEndLatitude = ?, sEndLongitude = ?, sEndTime = ?, sReport = ?, sStatus = 'Done' WHERE id = ?");
    $upd->bind_param('ddssi', $lat, $lng, $now, $report, $taskId);
    $ok = $upd->execute();
    $upd->close();
    jsonOut($ok
        ? ['status' => 'success', 'message' => 'Task completed and report submitted']
        : ['status' => 'error', 'message' => 'Could not complete task']);
}

if ($action === 'add_project_task') {
    $leadId = (int)($input['lead_id'] ?? 0);
    $title = trim((string)($input['sTitle'] ?? ''));
    $description = trim((string)($input['sDescription'] ?? ''));
    $assignTo = (int)($input['sAssigned_to'] ?? 0);
    $dueDate = trim((string)($input['sDue_date'] ?? ''));
    $status = trim((string)($input['sStatus'] ?? 'Pending'));

    if ($leadId <= 0 || $title === '' || $assignTo <= 0) {
        jsonOut(['status' => 'error', 'message' => 'Title and Assigned To are required']);
    }
    if ($dueDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
        jsonOut(['status' => 'error', 'message' => 'Invalid due date']);
    }
    if (!in_array($status, ['Pending', 'In Progress', 'Done'], true)) {
        $status = 'Pending';
    }

    $lead = getLeadRow($link, $leadId);
    if (!$lead || !userCanAccessProject($lead, $userId, $isAdmin)) {
        jsonOut(['status' => 'error', 'message' => 'Access denied']);
    }
    $allowedIds = array_map(static function ($u) {
        return (int)$u['id'];
    }, projectAssignableUsers($link, $lead));
    if (!in_array($assignTo, $allowedIds, true)) {
        jsonOut(['status' => 'error', 'message' => 'Task can only be assigned to project assigned users or lead owner']);
    }

    $dueParam = $dueDate !== '' ? $dueDate : null;
    $stmt = $link->prepare("INSERT INTO tblproject_tasks (lead_id, sTitle, sDescription, sAssigned_to, sCreated_by, sStatus, sDue_date)
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('issiiss', $leadId, $title, $description, $assignTo, $userId, $status, $dueParam);
    if (!$stmt->execute()) {
        jsonOut(['status' => 'error', 'message' => 'Could not save task: ' . $stmt->error]);
    }
    $newId = (int)$stmt->insert_id;
    $stmt->close();
    jsonOut(['status' => 'success', 'message' => 'Task added', 'id' => $newId]);
}

if ($action === 'update_project_task') {
    $taskId = (int)($input['id'] ?? 0);
    $status = trim((string)($input['sStatus'] ?? ''));
    $title = trim((string)($input['sTitle'] ?? ''));
    $description = trim((string)($input['sDescription'] ?? ''));
    $assignTo = (int)($input['sAssigned_to'] ?? 0);
    $dueDate = trim((string)($input['sDue_date'] ?? ''));

    if ($taskId <= 0) {
        jsonOut(['status' => 'error', 'message' => 'Invalid task']);
    }

    $stmt = $link->prepare("SELECT * FROM tblproject_tasks WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $taskId);
    $stmt->execute();
    $task = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$task) {
        jsonOut(['status' => 'error', 'message' => 'Task not found']);
    }

    $lead = getLeadRow($link, (int)$task['lead_id']);
    if (!$lead || !userCanAccessProject($lead, $userId, $isAdmin)) {
        jsonOut(['status' => 'error', 'message' => 'Access denied']);
    }

    $canEditAll = $isAdmin || (int)$lead['sLead_owner'] === $userId || in_array($userId, normalizeAssignedIds($lead['sAssigned_to'] ?? ''), true);
    $isAssignee = (int)$task['sAssigned_to'] === $userId;
    if (!$canEditAll && !$isAssignee) {
        jsonOut(['status' => 'error', 'message' => 'You can only update your own tasks']);
    }

    if ($status === '') {
        $status = $task['sStatus'];
    }
    if (!in_array($status, ['Pending', 'In Progress', 'Done'], true)) {
        jsonOut(['status' => 'error', 'message' => 'Invalid status']);
    }

    // Assignees can only change status on their task
    if (!$canEditAll && $isAssignee) {
        $upd = $link->prepare("UPDATE tblproject_tasks SET sStatus = ? WHERE id = ?");
        $upd->bind_param('si', $status, $taskId);
        $ok = $upd->execute();
        $upd->close();
        jsonOut($ok
            ? ['status' => 'success', 'message' => 'Task status updated']
            : ['status' => 'error', 'message' => 'Update failed']);
    }

    if ($title === '') {
        $title = $task['sTitle'];
    }
    if ($assignTo <= 0) {
        $assignTo = (int)$task['sAssigned_to'];
    }
    $allowedIds = array_map(static function ($u) {
        return (int)$u['id'];
    }, projectAssignableUsers($link, $lead));
    if (!in_array($assignTo, $allowedIds, true)) {
        jsonOut(['status' => 'error', 'message' => 'Invalid assignee for this project']);
    }
    if ($dueDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
        jsonOut(['status' => 'error', 'message' => 'Invalid due date']);
    }
    $dueParam = $dueDate !== '' ? $dueDate : null;

    $upd = $link->prepare("UPDATE tblproject_tasks
        SET sTitle = ?, sDescription = ?, sAssigned_to = ?, sStatus = ?, sDue_date = ?
        WHERE id = ?");
    $upd->bind_param('ssissi', $title, $description, $assignTo, $status, $dueParam, $taskId);
    $ok = $upd->execute();
    $upd->close();
    jsonOut($ok
        ? ['status' => 'success', 'message' => 'Task updated']
        : ['status' => 'error', 'message' => 'Update failed']);
}

if ($action === 'delete_project_task') {
    $taskId = (int)($input['id'] ?? 0);
    if ($taskId <= 0) {
        jsonOut(['status' => 'error', 'message' => 'Invalid task']);
    }
    $stmt = $link->prepare("SELECT * FROM tblproject_tasks WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $taskId);
    $stmt->execute();
    $task = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$task) {
        jsonOut(['status' => 'error', 'message' => 'Task not found']);
    }
    $lead = getLeadRow($link, (int)$task['lead_id']);
    if (!$lead || !userCanAccessProject($lead, $userId, $isAdmin)) {
        jsonOut(['status' => 'error', 'message' => 'Access denied']);
    }
    $canDelete = $isAdmin || (int)$lead['sLead_owner'] === $userId || (int)$task['sCreated_by'] === $userId;
    if (!$canDelete) {
        jsonOut(['status' => 'error', 'message' => 'You cannot delete this task']);
    }
    $del = $link->prepare("DELETE FROM tblproject_tasks WHERE id = ?");
    $del->bind_param('i', $taskId);
    $ok = $del->execute();
    $del->close();
    jsonOut($ok
        ? ['status' => 'success', 'message' => 'Task deleted']
        : ['status' => 'error', 'message' => 'Delete failed']);
}

if ($action === 'list_assignable_employees') {
    $stmt = $link->prepare("SELECT iUserid, sName, sRole FROM tbluser WHERE sRole <> 'Client' ORDER BY sName");
    $stmt->execute();
    $result = $stmt->get_result();
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = ['id' => (int)$row['iUserid'], 'name' => $row['sName'], 'role' => $row['sRole']];
    }
    $stmt->close();
    jsonOut(['status' => 'success', 'data' => $users]);
}

if ($action === 'assign_project_employees') {
    $leadId = (int)($input['lead_id'] ?? 0);
    $idsRaw = $input['user_ids'] ?? [];
    if (is_string($idsRaw)) {
        $idsRaw = preg_split('/\s*,\s*/', trim($idsRaw), -1, PREG_SPLIT_NO_EMPTY);
    }
    if (!is_array($idsRaw)) {
        $idsRaw = [];
    }
    $newIds = [];
    foreach ($idsRaw as $v) {
        $id = (int)$v;
        if ($id > 0) {
            $newIds[$id] = $id;
        }
    }
    $newIds = array_values($newIds);

    if ($leadId <= 0) {
        jsonOut(['status' => 'error', 'message' => 'Invalid project']);
    }
    $lead = getLeadRow($link, $leadId);
    if (!$lead) {
        jsonOut(['status' => 'error', 'message' => 'Project not found']);
    }
    if (!canManageProjectTeam($link, $lead, $userId, $isAdmin)) {
        jsonOut(['status' => 'error', 'message' => 'Only admin or the lead owner can assign team members']);
    }
    if (!$newIds) {
        jsonOut(['status' => 'error', 'message' => 'Select at least one employee']);
    }

    $placeholders = implode(',', array_fill(0, count($newIds), '?'));
    $types = str_repeat('i', count($newIds));
    $chk = $link->prepare("SELECT iUserid FROM tbluser WHERE iUserid IN ($placeholders) AND sRole <> 'Client'");
    $chk->bind_param($types, ...$newIds);
    $chk->execute();
    $validIds = [];
    $chkResult = $chk->get_result();
    while ($row = $chkResult->fetch_assoc()) {
        $validIds[] = (int)$row['iUserid'];
    }
    $chk->close();
    if (count($validIds) !== count($newIds)) {
        jsonOut(['status' => 'error', 'message' => 'One or more selected users are invalid']);
    }

    $oldIds = normalizeAssignedIds($lead['sAssigned_to'] ?? '');
    $addedIds = array_diff($newIds, $oldIds);

    $assignedStr = implode(',', $newIds);
    $upd = $link->prepare("UPDATE tblleads SET sAssigned_to = ? WHERE iLead_id = ?");
    $upd->bind_param('si', $assignedStr, $leadId);
    if (!$upd->execute()) {
        $upd->close();
        jsonOut(['status' => 'error', 'message' => 'Could not update assignment']);
    }
    $upd->close();

    $company = $lead['sCompany_name'] ? $lead['sCompany_name'] : $lead['sLead_name'];
    foreach ($addedIds as $uid) {
        notifyUser($link, $uid, $leadId, 'assigned', 'You were assigned to project: ' . $company, $userId);
    }

    jsonOut([
        'status' => 'success',
        'message' => 'Team updated',
        'assigned_to_name' => resolveUserNames($link, $newIds),
    ]);
}

if ($action === 'list_notifications') {
    $stmt = $link->prepare("SELECT n.*, l.sCompany_name, l.sLead_name FROM tblproject_notifications n
        LEFT JOIN tblleads l ON l.iLead_id = n.lead_id
        WHERE n.iUserid = ? ORDER BY n.dCreatedAt DESC LIMIT 30");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = [];
    $unread = 0;
    while ($row = $result->fetch_assoc()) {
        if (!(int)$row['iIsRead']) {
            $unread++;
        }
        $items[] = [
            'id' => (int)$row['id'],
            'lead_id' => (int)$row['lead_id'],
            'sType' => $row['sType'],
            'sMessage' => $row['sMessage'],
            'iIsRead' => (int)$row['iIsRead'],
            'dCreatedAt' => $row['dCreatedAt'],
            'project_name' => $row['sCompany_name'] ? $row['sCompany_name'] : $row['sLead_name'],
        ];
    }
    $stmt->close();
    jsonOut(['status' => 'success', 'data' => $items, 'unread' => $unread]);
}

if ($action === 'mark_notifications_read') {
    $ids = $input['ids'] ?? null;
    if (is_array($ids) && $ids) {
        $ids = array_map('intval', $ids);
        $ids = array_values(array_filter($ids, static function ($v) {
            return $v > 0;
        }));
        if ($ids) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $types = str_repeat('i', count($ids)) . 'i';
            $params = $ids;
            $params[] = $userId;
            $stmt = $link->prepare("UPDATE tblproject_notifications SET iIsRead = 1 WHERE id IN ($placeholders) AND iUserid = ?");
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $stmt->close();
        }
    } else {
        $stmt = $link->prepare("UPDATE tblproject_notifications SET iIsRead = 1 WHERE iUserid = ?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stmt->close();
    }
    jsonOut(['status' => 'success', 'message' => 'Notifications marked read']);
}

if ($action === 'list_project_expenses') {
    $leadId = (int)($input['lead_id'] ?? 0);
    if ($leadId <= 0) {
        jsonOut(['status' => 'error', 'message' => 'Invalid project']);
    }
    $lead = getLeadRow($link, $leadId);
    if (!$lead || !userCanAccessProject($lead, $userId, $isAdmin)) {
        jsonOut(['status' => 'error', 'message' => 'Access denied']);
    }
    $stmt = $link->prepare("SELECT e.*, u.sName AS submitted_by_name, r.sName AS reviewed_by_name
        FROM tblproject_expenses e
        LEFT JOIN tbluser u ON u.iUserid = e.iUserid
        LEFT JOIN tbluser r ON r.iUserid = e.iReviewedBy
        WHERE e.lead_id = ?
        ORDER BY e.sExpenseDate DESC, e.id DESC");
    $stmt->bind_param('i', $leadId);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = [
            'id' => (int)$row['id'],
            'lead_id' => (int)$row['lead_id'],
            'sCategory' => $row['sCategory'],
            'dAmount' => (float)$row['dAmount'],
            'sExpenseDate' => $row['sExpenseDate'],
            'sDescription' => $row['sDescription'],
            'has_receipt' => !empty($row['sReceipt']),
            'sStatus' => $row['sStatus'],
            'iUserid' => (int)$row['iUserid'],
            'submitted_by_name' => $row['submitted_by_name'],
            'reviewed_by_name' => $row['reviewed_by_name'],
            'sReviewNote' => $row['sReviewNote'],
            'dCreatedAt' => $row['dCreatedAt'],
        ];
    }
    $stmt->close();
    jsonOut(['status' => 'success', 'data' => $rows]);
}

if ($action === 'add_project_entry') {
    $leadId = (int)($input['lead_id'] ?? 0);
    $entryType = trim((string)($input['entryType'] ?? ''));
    $date = trim((string)($input['sDate'] ?? ''));
    $endLat = (isset($input['endLatitude']) && $input['endLatitude'] !== '') ? (float)$input['endLatitude'] : null;
    $endLng = (isset($input['endLongitude']) && $input['endLongitude'] !== '') ? (float)$input['endLongitude'] : null;

    if (!in_array($entryType, ['Expense', 'Visit', 'Meeting'], true)) {
        jsonOut(['status' => 'error', 'message' => 'Invalid entry type']);
    }
    if ($leadId <= 0) {
        jsonOut(['status' => 'error', 'message' => 'Project is required']);
    }
    if ($date === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        jsonOut(['status' => 'error', 'message' => 'Invalid date']);
    }

    $lead = getLeadRow($link, $leadId);
    if (!$lead || !userCanAccessProject($lead, $userId, $isAdmin)) {
        jsonOut(['status' => 'error', 'message' => 'Access denied']);
    }
    if ($entryType === 'Expense') {
        $category = trim((string)($input['sCategory'] ?? ''));
        $amount = (float)($input['dAmount'] ?? 0);
        $description = trim((string)($input['sDescription'] ?? ''));

        $allowedCategories = ['Travel', 'Food', 'Hotel', 'Other'];
        if (!in_array($category, $allowedCategories, true) || $amount <= 0) {
            jsonOut(['status' => 'error', 'message' => 'Category and a valid amount are required']);
        }

        $receiptResult = saveProjectReceipt($_FILES['receipt'] ?? null);
        if ($receiptResult && isset($receiptResult['error'])) {
            jsonOut(['status' => 'error', 'message' => $receiptResult['error']]);
        }
        $receiptStored = $receiptResult ? $receiptResult['stored'] : null;
        $receiptOriginal = $receiptResult ? $receiptResult['original'] : null;

        $stmt = $link->prepare("INSERT INTO tblproject_expenses (lead_id, iUserid, sCategory, dAmount, sExpenseDate, sDescription, sReceipt, sReceiptName, sStatus)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
        $stmt->bind_param('iisdssss', $leadId, $userId, $category, $amount, $date, $description, $receiptStored, $receiptOriginal);
        if (!$stmt->execute()) {
            jsonOut(['status' => 'error', 'message' => 'Could not save expense: ' . $stmt->error]);
        }
        $newId = (int)$stmt->insert_id;
        $stmt->close();

        saveEndLocation($link, $userId, $endLat, $endLng);
        jsonOut(['status' => 'success', 'message' => 'Expense submitted', 'id' => $newId, 'entryType' => $entryType]);
    }

    // entryType is 'Visit' or 'Meeting'
    $location = trim((string)($input['sLocation'] ?? ''));
    $notes = trim((string)($input['sNotes'] ?? ''));

    $files = $_FILES['photos'] ?? null;
    $photoList = [];
    if ($files && is_array($files['name'] ?? null)) {
        $count = count($files['name']);
        if ($count > 6) {
            jsonOut(['status' => 'error', 'message' => 'You can upload up to 6 photos per entry']);
        }
        for ($i = 0; $i < $count; $i++) {
            if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $single = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i],
            ];
            $saved = saveProjectVisitPhoto($single);
            if ($saved && isset($saved['error'])) {
                foreach ($photoList as $p) {
                    $path = __DIR__ . '/uploads/project-visit-photos/' . $p['stored'];
                    if (is_file($path)) {
                        @unlink($path);
                    }
                }
                jsonOut(['status' => 'error', 'message' => $saved['error']]);
            }
            if ($saved) {
                $photoList[] = $saved;
            }
        }
    }

    $stmt = $link->prepare("INSERT INTO tblproject_visits (lead_id, iUserid, sVisitType, sVisitDate, sLocation, sNotes)
        VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('iissss', $leadId, $userId, $entryType, $date, $location, $notes);
    if (!$stmt->execute()) {
        jsonOut(['status' => 'error', 'message' => 'Could not save visit: ' . $stmt->error]);
    }
    $visitId = (int)$stmt->insert_id;
    $stmt->close();

    foreach ($photoList as $p) {
        $ins = $link->prepare("INSERT INTO tblproject_visit_photos (visit_id, sPhoto, sPhotoName) VALUES (?, ?, ?)");
        $ins->bind_param('iss', $visitId, $p['stored'], $p['original']);
        $ins->execute();
        $ins->close();
    }

    saveEndLocation($link, $userId, $endLat, $endLng);
    jsonOut([
        'status' => 'success',
        'message' => ($entryType === 'Meeting' ? 'Meeting' : 'Visit') . ' logged',
        'id' => $visitId,
        'entryType' => $entryType,
    ]);
}

if ($action === 'add_project_expense') {
    $leadId = (int)($input['lead_id'] ?? 0);
    $category = trim((string)($input['sCategory'] ?? ''));
    $amount = (float)($input['dAmount'] ?? 0);
    $expenseDate = trim((string)($input['sExpenseDate'] ?? ''));
    $description = trim((string)($input['sDescription'] ?? ''));

    $allowedCategories = ['Travel', 'Food', 'Hotel', 'Other'];
    if ($leadId <= 0 || !in_array($category, $allowedCategories, true) || $amount <= 0) {
        jsonOut(['status' => 'error', 'message' => 'Category and a valid amount are required']);
    }
    if ($expenseDate === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $expenseDate)) {
        jsonOut(['status' => 'error', 'message' => 'Invalid expense date']);
    }

    $lead = getLeadRow($link, $leadId);
    if (!$lead || !userCanAccessProject($lead, $userId, $isAdmin)) {
        jsonOut(['status' => 'error', 'message' => 'Access denied']);
    }
    $receiptResult = saveProjectReceipt($_FILES['receipt'] ?? null);
    if ($receiptResult && isset($receiptResult['error'])) {
        jsonOut(['status' => 'error', 'message' => $receiptResult['error']]);
    }
    $receiptStored = $receiptResult ? $receiptResult['stored'] : null;
    $receiptOriginal = $receiptResult ? $receiptResult['original'] : null;

    $stmt = $link->prepare("INSERT INTO tblproject_expenses (lead_id, iUserid, sCategory, dAmount, sExpenseDate, sDescription, sReceipt, sReceiptName, sStatus)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
    $stmt->bind_param('iisdssss', $leadId, $userId, $category, $amount, $expenseDate, $description, $receiptStored, $receiptOriginal);
    if (!$stmt->execute()) {
        jsonOut(['status' => 'error', 'message' => 'Could not save expense: ' . $stmt->error]);
    }
    $newId = (int)$stmt->insert_id;
    $stmt->close();
    jsonOut(['status' => 'success', 'message' => 'Expense submitted', 'id' => $newId]);
}

if ($action === 'update_project_expense_status') {
    $id = (int)($input['id'] ?? 0);
    $status = trim((string)($input['sStatus'] ?? ''));
    $note = trim((string)($input['sReviewNote'] ?? ''));
    if ($id <= 0 || !in_array($status, ['Approved', 'Rejected', 'Pending'], true)) {
        jsonOut(['status' => 'error', 'message' => 'Invalid request']);
    }
    $stmt = $link->prepare("SELECT * FROM tblproject_expenses WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $expense = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$expense) {
        jsonOut(['status' => 'error', 'message' => 'Expense not found']);
    }
    $lead = getLeadRow($link, (int)$expense['lead_id']);
    if (!$lead || !canManageProjectTeam($link, $lead, $userId, $isAdmin)) {
        jsonOut(['status' => 'error', 'message' => 'Only admin or the lead owner can review expenses']);
    }
    $upd = $link->prepare("UPDATE tblproject_expenses SET sStatus = ?, iReviewedBy = ?, sReviewNote = ? WHERE id = ?");
    $upd->bind_param('sisi', $status, $userId, $note, $id);
    $ok = $upd->execute();
    $upd->close();
    if ($ok && $status !== 'Pending') {
        $company = $lead['sCompany_name'] ? $lead['sCompany_name'] : $lead['sLead_name'];
        notifyUser(
            $link,
            (int)$expense['iUserid'],
            (int)$expense['lead_id'],
            'expense_' . strtolower($status),
            'Your ' . $expense['sCategory'] . ' expense of ' . number_format((float)$expense['dAmount'], 2) . ' for ' . $company . ' was ' . strtolower($status),
            $userId
        );
    }
    jsonOut($ok
        ? ['status' => 'success', 'message' => 'Expense ' . strtolower($status)]
        : ['status' => 'error', 'message' => 'Update failed']);
}

if ($action === 'delete_project_expense') {
    $id = (int)($input['id'] ?? 0);
    if ($id <= 0) {
        jsonOut(['status' => 'error', 'message' => 'Invalid expense']);
    }
    $stmt = $link->prepare("SELECT * FROM tblproject_expenses WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $expense = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$expense) {
        jsonOut(['status' => 'error', 'message' => 'Expense not found']);
    }
    $lead = getLeadRow($link, (int)$expense['lead_id']);
    if (!$lead || !userCanAccessProject($lead, $userId, $isAdmin)) {
        jsonOut(['status' => 'error', 'message' => 'Access denied']);
    }
    $isOwnPending = (int)$expense['iUserid'] === $userId && $expense['sStatus'] === 'Pending';
    if (!canManageProjectTeam($link, $lead, $userId, $isAdmin) && !$isOwnPending) {
        jsonOut(['status' => 'error', 'message' => 'You cannot delete this expense']);
    }
    $del = $link->prepare("DELETE FROM tblproject_expenses WHERE id = ?");
    $del->bind_param('i', $id);
    $ok = $del->execute();
    $del->close();
    if ($ok && !empty($expense['sReceipt'])) {
        $path = __DIR__ . '/uploads/project-expenses/' . basename($expense['sReceipt']);
        if (is_file($path)) {
            @unlink($path);
        }
    }
    jsonOut($ok
        ? ['status' => 'success', 'message' => 'Expense deleted']
        : ['status' => 'error', 'message' => 'Delete failed']);
}

if ($action === 'list_project_visits') {
    $leadId = (int)($input['lead_id'] ?? 0);
    if ($leadId <= 0) {
        jsonOut(['status' => 'error', 'message' => 'Invalid project']);
    }
    $lead = getLeadRow($link, $leadId);
    if (!$lead || !userCanAccessProject($lead, $userId, $isAdmin)) {
        jsonOut(['status' => 'error', 'message' => 'Access denied']);
    }
    $stmt = $link->prepare("SELECT v.*, u.sName AS created_by_name
        FROM tblproject_visits v
        LEFT JOIN tbluser u ON u.iUserid = v.iUserid
        WHERE v.lead_id = ?
        ORDER BY v.sVisitDate DESC, v.id DESC");
    $stmt->bind_param('i', $leadId);
    $stmt->execute();
    $result = $stmt->get_result();
    $visits = [];
    $visitIds = [];
    while ($row = $result->fetch_assoc()) {
        $vid = (int)$row['id'];
        $visitIds[] = $vid;
        $visits[$vid] = [
            'id' => $vid,
            'lead_id' => (int)$row['lead_id'],
            'sVisitType' => $row['sVisitType'],
            'sVisitDate' => $row['sVisitDate'],
            'sLocation' => $row['sLocation'],
            'sNotes' => $row['sNotes'],
            'iUserid' => (int)$row['iUserid'],
            'created_by_name' => $row['created_by_name'],
            'dCreatedAt' => $row['dCreatedAt'],
            'photos' => [],
        ];
    }
    $stmt->close();

    if ($visitIds) {
        $placeholders = implode(',', array_fill(0, count($visitIds), '?'));
        $types = str_repeat('i', count($visitIds));
        $pstmt = $link->prepare("SELECT id, visit_id FROM tblproject_visit_photos WHERE visit_id IN ($placeholders) ORDER BY id");
        $pstmt->bind_param($types, ...$visitIds);
        $pstmt->execute();
        $pres = $pstmt->get_result();
        while ($prow = $pres->fetch_assoc()) {
            $vid = (int)$prow['visit_id'];
            if (isset($visits[$vid])) {
                $visits[$vid]['photos'][] = (int)$prow['id'];
            }
        }
        $pstmt->close();
    }

    jsonOut(['status' => 'success', 'data' => array_values($visits)]);
}

if ($action === 'add_project_visit') {
    $leadId = (int)($input['lead_id'] ?? 0);
    $visitType = trim((string)($input['sVisitType'] ?? ''));
    $visitDate = trim((string)($input['sVisitDate'] ?? ''));
    $location = trim((string)($input['sLocation'] ?? ''));
    $notes = trim((string)($input['sNotes'] ?? ''));

    if ($leadId <= 0 || !in_array($visitType, ['Visit', 'Meeting'], true)) {
        jsonOut(['status' => 'error', 'message' => 'Type and project are required']);
    }
    if ($visitDate === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $visitDate)) {
        jsonOut(['status' => 'error', 'message' => 'Invalid visit date']);
    }

    $lead = getLeadRow($link, $leadId);
    if (!$lead || !userCanAccessProject($lead, $userId, $isAdmin)) {
        jsonOut(['status' => 'error', 'message' => 'Access denied']);
    }
    $files = $_FILES['photos'] ?? null;
    $photoList = [];
    if ($files && is_array($files['name'] ?? null)) {
        $count = count($files['name']);
        if ($count > 6) {
            jsonOut(['status' => 'error', 'message' => 'You can upload up to 6 photos per entry']);
        }
        for ($i = 0; $i < $count; $i++) {
            if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $single = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i],
            ];
            $saved = saveProjectVisitPhoto($single);
            if ($saved && isset($saved['error'])) {
                foreach ($photoList as $p) {
                    $path = __DIR__ . '/uploads/project-visit-photos/' . $p['stored'];
                    if (is_file($path)) {
                        @unlink($path);
                    }
                }
                jsonOut(['status' => 'error', 'message' => $saved['error']]);
            }
            if ($saved) {
                $photoList[] = $saved;
            }
        }
    }

    $stmt = $link->prepare("INSERT INTO tblproject_visits (lead_id, iUserid, sVisitType, sVisitDate, sLocation, sNotes)
        VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('iissss', $leadId, $userId, $visitType, $visitDate, $location, $notes);
    if (!$stmt->execute()) {
        jsonOut(['status' => 'error', 'message' => 'Could not save visit: ' . $stmt->error]);
    }
    $visitId = (int)$stmt->insert_id;
    $stmt->close();

    foreach ($photoList as $p) {
        $ins = $link->prepare("INSERT INTO tblproject_visit_photos (visit_id, sPhoto, sPhotoName) VALUES (?, ?, ?)");
        $ins->bind_param('iss', $visitId, $p['stored'], $p['original']);
        $ins->execute();
        $ins->close();
    }

    jsonOut(['status' => 'success', 'message' => 'Visit logged', 'id' => $visitId]);
}

if ($action === 'delete_project_visit') {
    $id = (int)($input['id'] ?? 0);
    if ($id <= 0) {
        jsonOut(['status' => 'error', 'message' => 'Invalid visit']);
    }
    $stmt = $link->prepare("SELECT * FROM tblproject_visits WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $visit = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$visit) {
        jsonOut(['status' => 'error', 'message' => 'Visit not found']);
    }
    $lead = getLeadRow($link, (int)$visit['lead_id']);
    if (!$lead || !userCanAccessProject($lead, $userId, $isAdmin)) {
        jsonOut(['status' => 'error', 'message' => 'Access denied']);
    }
    $canDelete = canManageProjectTeam($link, $lead, $userId, $isAdmin) || (int)$visit['iUserid'] === $userId;
    if (!$canDelete) {
        jsonOut(['status' => 'error', 'message' => 'You cannot delete this visit']);
    }

    $pstmt = $link->prepare("SELECT sPhoto FROM tblproject_visit_photos WHERE visit_id = ?");
    $pstmt->bind_param('i', $id);
    $pstmt->execute();
    $pres = $pstmt->get_result();
    $photoFiles = [];
    while ($prow = $pres->fetch_assoc()) {
        $photoFiles[] = $prow['sPhoto'];
    }
    $pstmt->close();

    $delPhotos = $link->prepare("DELETE FROM tblproject_visit_photos WHERE visit_id = ?");
    $delPhotos->bind_param('i', $id);
    $delPhotos->execute();
    $delPhotos->close();

    $del = $link->prepare("DELETE FROM tblproject_visits WHERE id = ?");
    $del->bind_param('i', $id);
    $ok = $del->execute();
    $del->close();

    if ($ok) {
        foreach ($photoFiles as $pf) {
            $path = __DIR__ . '/uploads/project-visit-photos/' . basename($pf);
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }

    jsonOut($ok
        ? ['status' => 'success', 'message' => 'Visit deleted']
        : ['status' => 'error', 'message' => 'Delete failed']);
}

jsonOut(['status' => 'error', 'message' => 'Invalid action']);
