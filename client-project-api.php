<?php
include 'layouts/session.php';
include 'layouts/config.php';
include_once 'layouts/crm-access.php';

header('Content-Type: application/json');
date_default_timezone_set('Asia/Kolkata');
error_reporting(0);
ini_set('display_errors', '0');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$isAdmin = crmIsAdmin();
$isClient = crmIsClient();
// Staff (regular User accounts) are allowed in too — they can see and update
// status on client-project tasks assigned to them; per-action checks below
// scope exactly what they can see/do.

function jsonOut($payload)
{
    echo json_encode($payload);
    exit;
}

function ensureClientProjectTables($link)
{
    $sql1 = "CREATE TABLE IF NOT EXISTS tblclient_project (
      iId INT AUTO_INCREMENT PRIMARY KEY,
      sProjectName VARCHAR(255) NOT NULL,
      sDescription TEXT NULL,
      iClientUserid INT NOT NULL,
      iCreatedBy INT NOT NULL,
      sStatus VARCHAR(30) NOT NULL DEFAULT 'Active',
      dStartDate DATE NULL,
      dDueDate DATE NULL,
      dCreatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      dUpdatedAt DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
      INDEX idx_client (iClientUserid)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $sql2 = "CREATE TABLE IF NOT EXISTS tblclient_project_tasks (
      id INT AUTO_INCREMENT PRIMARY KEY,
      project_id INT NOT NULL,
      sTitle VARCHAR(255) NOT NULL,
      sDescription TEXT NULL,
      sAssigned_to INT NOT NULL,
      sCreated_by INT NOT NULL,
      sStatus VARCHAR(50) NOT NULL DEFAULT 'Pending',
      sDue_date DATE NULL,
      sCreated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      sUpdated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
      INDEX idx_project (project_id),
      INDEX idx_assigned (sAssigned_to)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $sql3 = "CREATE TABLE IF NOT EXISTS tblclient_project_activity (
      id INT AUTO_INCREMENT PRIMARY KEY,
      project_id INT NOT NULL,
      task_id INT NULL,
      sAction VARCHAR(50) NOT NULL,
      sDetail TEXT NOT NULL,
      iUserid INT NOT NULL,
      dCreatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      INDEX idx_project (project_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    foreach ([$sql1, $sql2, $sql3] as $sql) {
        if (!mysqli_query($link, $sql)) {
            jsonOut(['status' => 'error', 'message' => 'Could not prepare client project tables: ' . mysqli_error($link)]);
        }
    }
}

function ensureClientTaskAttachmentColumns($link)
{
    static $done = false;
    if ($done) {
        return;
    }
    $cols = [
        'sAttachment' => "ALTER TABLE tblclient_project_tasks ADD COLUMN sAttachment VARCHAR(255) NULL",
        'sAttachmentName' => "ALTER TABLE tblclient_project_tasks ADD COLUMN sAttachmentName VARCHAR(255) NULL",
    ];
    foreach ($cols as $col => $sql) {
        $check = @mysqli_query($link, "SHOW COLUMNS FROM tblclient_project_tasks LIKE '{$col}'");
        if ($check && mysqli_num_rows($check) === 0) {
            @mysqli_query($link, $sql);
        }
    }
    $done = true;
}

/**
 * Validates and moves an uploaded file (from $_FILES['attachment']) into the
 * client-task-attachments upload directory. Returns:
 *   null                                — no file was sent (attachment is optional)
 *   ['error' => 'message']              — a file was sent but is invalid
 *   ['stored' => .., 'original' => ..]  — saved successfully
 */
function saveClientTaskAttachment($file)
{
    if (!$file || !is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return ['error' => 'File upload failed. Please try again.'];
    }

    $tmp = $file['tmp_name'] ?? '';
    $origName = (string)($file['name'] ?? 'attachment');
    $size = (int)($file['size'] ?? 0);

    if ($tmp === '' || !is_uploaded_file($tmp)) {
        return ['error' => 'Invalid upload.'];
    }
    if ($size <= 0 || $size > 10 * 1024 * 1024) {
        return ['error' => 'Attachment must be between 1 byte and 10 MB.'];
    }

    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    $allowedExt = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'png', 'jpg', 'jpeg', 'gif', 'zip', 'txt', 'csv'];
    if ($ext === '' || !in_array($ext, $allowedExt, true)) {
        return ['error' => 'That file type is not allowed. Allowed: ' . implode(', ', $allowedExt) . '.'];
    }

    $uploadDir = __DIR__ . '/uploads/client-task-attachments';
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

function deleteClientTaskAttachmentFile($storedName)
{
    if (!$storedName) {
        return;
    }
    $path = __DIR__ . '/uploads/client-task-attachments/' . basename($storedName);
    if (is_file($path)) {
        @unlink($path);
    }
}

try {
    crmEnsureClientProjectColumns($link);
    ensureClientProjectTables($link);
    ensureClientTaskAttachmentColumns($link);
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

function logClientProjectActivity($link, $projectId, $taskId, $action, $detail, $userId)
{
    $stmt = $link->prepare("INSERT INTO tblclient_project_activity (project_id, task_id, sAction, sDetail, iUserid) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('iissi', $projectId, $taskId, $action, $detail, $userId);
    $stmt->execute();
    $stmt->close();
}

function resolveUserName($link, $id)
{
    $id = (int)$id;
    if ($id <= 0) {
        return '';
    }
    $stmt = $link->prepare("SELECT sName FROM tbluser WHERE iUserid = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ? $row['sName'] : '';
}

function getClientProjectRow($link, $projectId)
{
    $projectId = (int)$projectId;
    $stmt = $link->prepare("SELECT * FROM tblclient_project WHERE iId = ? LIMIT 1");
    $stmt->bind_param('i', $projectId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function staffHasTaskInProject($link, $projectId, $userId)
{
    $stmt = $link->prepare("SELECT 1 FROM tblclient_project_tasks WHERE project_id = ? AND sAssigned_to = ? LIMIT 1");
    $stmt->bind_param('ii', $projectId, $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (bool)$row;
}

/** Admin: any project. Client: only their own. Staff: only projects with a task assigned to them. */
function canAccessClientProject($link, $project, $isAdmin, $isClient, $userId)
{
    if (!$project) {
        return false;
    }
    if ($isAdmin) {
        return true;
    }
    if ($isClient) {
        return (int)$project['iClientUserid'] === $userId;
    }
    return staffHasTaskInProject($link, (int)$project['iId'], $userId);
}

if ($action === 'list_client_projects') {
    $sql = "SELECT p.*, u.sName AS client_name, u.sClientCompany AS client_company,
                (SELECT COUNT(*) FROM tblclient_project_tasks t WHERE t.project_id = p.iId) AS task_total,
                (SELECT COUNT(*) FROM tblclient_project_tasks t WHERE t.project_id = p.iId AND t.sStatus = 'Done') AS task_done
            FROM tblclient_project p
            LEFT JOIN tbluser u ON u.iUserid = p.iClientUserid";
    if ($isClient) {
        $sql .= " WHERE p.iClientUserid = ?";
    } elseif (!$isAdmin) {
        // Staff: only projects that have at least one task assigned to them.
        $sql .= " WHERE EXISTS (SELECT 1 FROM tblclient_project_tasks t2 WHERE t2.project_id = p.iId AND t2.sAssigned_to = ?)";
    }
    $sql .= " ORDER BY p.dCreatedAt DESC, p.iId DESC";

    $stmt = $link->prepare($sql);
    if ($isClient || !$isAdmin) {
        $stmt->bind_param('i', $userId);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $projects = [];
    while ($row = $result->fetch_assoc()) {
        $projects[] = [
            'iId' => (int)$row['iId'],
            'sProjectName' => $row['sProjectName'],
            'sDescription' => $row['sDescription'],
            'iClientUserid' => (int)$row['iClientUserid'],
            'client_name' => $row['client_name'],
            'client_company' => $row['client_company'],
            'sStatus' => $row['sStatus'],
            'dStartDate' => $row['dStartDate'],
            'dDueDate' => $row['dDueDate'],
            'dCreatedAt' => $row['dCreatedAt'],
            'task_total' => (int)$row['task_total'],
            'task_done' => (int)$row['task_done'],
        ];
    }
    $stmt->close();
    jsonOut(['status' => 'success', 'data' => $projects]);
}

if ($action === 'list_clients') {
    if (!$isAdmin) {
        jsonOut(['status' => 'error', 'message' => 'Access denied']);
    }
    $stmt = $link->prepare("SELECT iUserid, sName, sClientCompany FROM tbluser WHERE sRole = 'Client' ORDER BY sName");
    $stmt->execute();
    $result = $stmt->get_result();
    $clients = [];
    while ($row = $result->fetch_assoc()) {
        $clients[] = [
            'id' => (int)$row['iUserid'],
            'name' => $row['sName'],
            'company' => $row['sClientCompany'],
        ];
    }
    $stmt->close();
    jsonOut(['status' => 'success', 'data' => $clients]);
}

if ($action === 'get_client_project') {
    $projectId = (int)($input['project_id'] ?? 0);
    $project = getClientProjectRow($link, $projectId);
    if (!canAccessClientProject($link, $project, $isAdmin, $isClient, $userId)) {
        jsonOut(['status' => 'error', 'message' => 'Access denied']);
    }
    jsonOut([
        'status' => 'success',
        'data' => [
            'iId' => (int)$project['iId'],
            'sProjectName' => $project['sProjectName'],
            'sDescription' => $project['sDescription'],
            'iClientUserid' => (int)$project['iClientUserid'],
            'client_name' => resolveUserName($link, $project['iClientUserid']),
            'sStatus' => $project['sStatus'],
            'dStartDate' => $project['dStartDate'],
            'dDueDate' => $project['dDueDate'],
        ],
    ]);
}

if ($action === 'add_client_project') {
    if (!$isAdmin) {
        jsonOut(['status' => 'error', 'message' => 'Access denied']);
    }
    $name = trim((string)($input['sProjectName'] ?? ''));
    $description = trim((string)($input['sDescription'] ?? ''));
    $clientUserId = (int)($input['iClientUserid'] ?? 0);
    $status = trim((string)($input['sStatus'] ?? 'Active'));
    $startDate = trim((string)($input['dStartDate'] ?? ''));
    $dueDate = trim((string)($input['dDueDate'] ?? ''));

    if ($name === '' || $clientUserId <= 0) {
        jsonOut(['status' => 'error', 'message' => 'Project name and client are required']);
    }
    if (!in_array($status, ['Active', 'Completed', 'On Hold'], true)) {
        $status = 'Active';
    }

    $stmt = $link->prepare("SELECT iUserid FROM tbluser WHERE iUserid = ? AND sRole = 'Client' LIMIT 1");
    $stmt->bind_param('i', $clientUserId);
    $stmt->execute();
    $ok = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$ok) {
        jsonOut(['status' => 'error', 'message' => 'Selected client account not found']);
    }

    $startParam = $startDate !== '' ? $startDate : null;
    $dueParam = $dueDate !== '' ? $dueDate : null;
    $stmt = $link->prepare("INSERT INTO tblclient_project (sProjectName, sDescription, iClientUserid, iCreatedBy, sStatus, dStartDate, dDueDate)
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssiisss', $name, $description, $clientUserId, $userId, $status, $startParam, $dueParam);
    if (!$stmt->execute()) {
        jsonOut(['status' => 'error', 'message' => 'Could not save project: ' . $stmt->error]);
    }
    $newId = (int)$stmt->insert_id;
    $stmt->close();

    logClientProjectActivity($link, $newId, null, 'project_created', 'Project created', $userId);
    jsonOut(['status' => 'success', 'message' => 'Project added', 'id' => $newId]);
}

if ($action === 'update_client_project') {
    if (!$isAdmin) {
        jsonOut(['status' => 'error', 'message' => 'Access denied']);
    }
    $projectId = (int)($input['iId'] ?? 0);
    $project = getClientProjectRow($link, $projectId);
    if (!$project) {
        jsonOut(['status' => 'error', 'message' => 'Project not found']);
    }
    $name = trim((string)($input['sProjectName'] ?? ''));
    $description = trim((string)($input['sDescription'] ?? ''));
    $clientUserId = (int)($input['iClientUserid'] ?? 0);
    $status = trim((string)($input['sStatus'] ?? 'Active'));
    $startDate = trim((string)($input['dStartDate'] ?? ''));
    $dueDate = trim((string)($input['dDueDate'] ?? ''));

    if ($name === '' || $clientUserId <= 0) {
        jsonOut(['status' => 'error', 'message' => 'Project name and client are required']);
    }
    if (!in_array($status, ['Active', 'Completed', 'On Hold'], true)) {
        $status = 'Active';
    }

    $startParam = $startDate !== '' ? $startDate : null;
    $dueParam = $dueDate !== '' ? $dueDate : null;
    $stmt = $link->prepare("UPDATE tblclient_project
        SET sProjectName = ?, sDescription = ?, iClientUserid = ?, sStatus = ?, dStartDate = ?, dDueDate = ?
        WHERE iId = ?");
    $stmt->bind_param('ssisssi', $name, $description, $clientUserId, $status, $startParam, $dueParam, $projectId);
    $ok = $stmt->execute();
    $stmt->close();

    if ($ok && $status !== $project['sStatus']) {
        logClientProjectActivity($link, $projectId, null, 'status_changed', "Project status changed from {$project['sStatus']} to {$status}", $userId);
    }
    jsonOut($ok
        ? ['status' => 'success', 'message' => 'Project updated']
        : ['status' => 'error', 'message' => 'Update failed']);
}

if ($action === 'delete_client_project') {
    if (!$isAdmin) {
        jsonOut(['status' => 'error', 'message' => 'Access denied']);
    }
    $projectId = (int)($input['iId'] ?? 0);
    if ($projectId <= 0) {
        jsonOut(['status' => 'error', 'message' => 'Invalid project']);
    }
    $link->query("DELETE FROM tblclient_project_activity WHERE project_id = " . (int)$projectId);
    $link->query("DELETE FROM tblclient_project_tasks WHERE project_id = " . (int)$projectId);
    $stmt = $link->prepare("DELETE FROM tblclient_project WHERE iId = ?");
    $stmt->bind_param('i', $projectId);
    $ok = $stmt->execute();
    $stmt->close();
    jsonOut($ok
        ? ['status' => 'success', 'message' => 'Project deleted']
        : ['status' => 'error', 'message' => 'Delete failed']);
}

if ($action === 'list_client_project_tasks') {
    $projectId = (int)($input['project_id'] ?? 0);
    $project = getClientProjectRow($link, $projectId);
    if (!canAccessClientProject($link, $project, $isAdmin, $isClient, $userId)) {
        jsonOut(['status' => 'error', 'message' => 'Access denied']);
    }

    $stmt = $link->prepare("SELECT t.*, a.sName AS assigned_name, c.sName AS created_by_name
        FROM tblclient_project_tasks t
        LEFT JOIN tbluser a ON a.iUserid = t.sAssigned_to
        LEFT JOIN tbluser c ON c.iUserid = t.sCreated_by
        WHERE t.project_id = ?
        ORDER BY FIELD(t.sStatus, 'Pending', 'In Progress', 'Done'), t.sDue_date IS NULL, t.sDue_date ASC, t.id DESC");
    $stmt->bind_param('i', $projectId);
    $stmt->execute();
    $result = $stmt->get_result();
    $tasks = [];
    while ($row = $result->fetch_assoc()) {
        $tasks[] = [
            'id' => (int)$row['id'],
            'project_id' => (int)$row['project_id'],
            'sTitle' => $row['sTitle'],
            'sDescription' => $row['sDescription'],
            'sAssigned_to' => (int)$row['sAssigned_to'],
            'assigned_name' => $row['assigned_name'],
            'sCreated_by' => (int)$row['sCreated_by'],
            'created_by_name' => $row['created_by_name'],
            'sStatus' => $row['sStatus'],
            'sDue_date' => $row['sDue_date'],
            'sCreated_at' => $row['sCreated_at'],
            'sAttachmentName' => $row['sAttachmentName'] ?? null,
            'hasAttachment' => !empty($row['sAttachment']),
        ];
    }
    $stmt->close();
    jsonOut(['status' => 'success', 'data' => $tasks]);
}

if ($action === 'add_client_project_task') {
    $projectId = (int)($input['project_id'] ?? 0);
    $title = trim((string)($input['sTitle'] ?? ''));
    $description = trim((string)($input['sDescription'] ?? ''));
    $dueDate = trim((string)($input['sDue_date'] ?? ''));

    $project = getClientProjectRow($link, $projectId);
    if (!$project) {
        jsonOut(['status' => 'error', 'message' => 'Project not found']);
    }

    // Admin: full control over any project. Client: may only request a task on
    // their own project (assignee/status are not theirs to set — see below).
    $isOwnProject = $isClient && (int)$project['iClientUserid'] === $userId;
    if (!$isAdmin && !$isOwnProject) {
        jsonOut(['status' => 'error', 'message' => 'Access denied']);
    }

    if ($title === '') {
        jsonOut(['status' => 'error', 'message' => 'Title is required']);
    }
    if ($dueDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
        jsonOut(['status' => 'error', 'message' => 'Invalid due date']);
    }

    if ($isAdmin) {
        // No "Assigned To" field in the Add Task form anymore — a task an admin
        // adds defaults to that admin, so it shows up for them without extra clicks.
        // (An explicit sAssigned_to is still honored if a caller sends one.)
        $assignTo = (int)($input['sAssigned_to'] ?? 0);
        if ($assignTo <= 0) {
            $assignTo = $userId;
        }
        $status = trim((string)($input['sStatus'] ?? 'Pending'));
        if (!in_array($status, ['Pending', 'In Progress', 'Done'], true)) {
            $status = 'Pending';
        }
        $activityDetail = 'Task created: ' . $title;
    } else {
        // Client-requested task: lands on the project owner's plate and always
        // starts Pending — a client cannot assign staff or set a status.
        $assignTo = (int)$project['iCreatedBy'];
        $status = 'Pending';
        $activityDetail = 'Task requested by client: ' . $title;
    }

    // Attachment is optional — validate it before touching the DB so a bad
    // file never leaves a half-created task behind.
    $attachmentStored = null;
    $attachmentOriginal = null;
    $attResult = saveClientTaskAttachment($_FILES['attachment'] ?? null);
    if ($attResult && isset($attResult['error'])) {
        jsonOut(['status' => 'error', 'message' => $attResult['error']]);
    }
    if ($attResult) {
        $attachmentStored = $attResult['stored'];
        $attachmentOriginal = $attResult['original'];
    }

    $dueParam = $dueDate !== '' ? $dueDate : null;
    $stmt = $link->prepare("INSERT INTO tblclient_project_tasks (project_id, sTitle, sDescription, sAssigned_to, sCreated_by, sStatus, sDue_date, sAttachment, sAttachmentName)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('issiissss', $projectId, $title, $description, $assignTo, $userId, $status, $dueParam, $attachmentStored, $attachmentOriginal);
    if (!$stmt->execute()) {
        jsonOut(['status' => 'error', 'message' => 'Could not save task: ' . $stmt->error]);
    }
    $newId = (int)$stmt->insert_id;
    $stmt->close();

    if ($attachmentOriginal) {
        $activityDetail .= ' (attached: ' . $attachmentOriginal . ')';
    }
    logClientProjectActivity($link, $projectId, $newId, 'task_created', $activityDetail, $userId);
    jsonOut(['status' => 'success', 'message' => 'Task added', 'id' => $newId]);
}

if ($action === 'update_client_project_task') {
    $taskId = (int)($input['id'] ?? 0);
    if ($taskId <= 0) {
        jsonOut(['status' => 'error', 'message' => 'Invalid task']);
    }
    $stmt = $link->prepare("SELECT * FROM tblclient_project_tasks WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $taskId);
    $stmt->execute();
    $task = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$task) {
        jsonOut(['status' => 'error', 'message' => 'Task not found']);
    }

    $isOwnTask = ((int)$task['sAssigned_to'] === $userId);
    $isClientOwnTask = $isClient && ((int)$task['sCreated_by'] === $userId);
    if (!$isAdmin && !$isOwnTask && !$isClientOwnTask) {
        jsonOut(['status' => 'error', 'message' => 'Access denied']);
    }

    // Client editing a task they requested themselves: title/description/due
    // date only — assignee and status stay out of their hands, same as when
    // the task was first created.
    if ($isClientOwnTask && !$isAdmin) {
        $title = trim((string)($input['sTitle'] ?? $task['sTitle']));
        $description = array_key_exists('sDescription', $input) ? trim((string)$input['sDescription']) : $task['sDescription'];
        $dueDate = trim((string)($input['sDue_date'] ?? ($task['sDue_date'] ?? '')));

        if ($title === '') {
            jsonOut(['status' => 'error', 'message' => 'Title is required']);
        }
        if ($dueDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
            jsonOut(['status' => 'error', 'message' => 'Invalid due date']);
        }
        $dueParam = $dueDate !== '' ? $dueDate : null;

        // Attachment: a new file replaces the old one; "remove_attachment"
        // clears it without uploading a replacement. Validate before writing.
        $attachmentStored = $task['sAttachment'];
        $attachmentOriginal = $task['sAttachmentName'];
        $removeAttachment = !empty($input['remove_attachment']);
        $oldStored = $task['sAttachment'];
        $newAttachmentSaved = false;

        $attResult = saveClientTaskAttachment($_FILES['attachment'] ?? null);
        if ($attResult && isset($attResult['error'])) {
            jsonOut(['status' => 'error', 'message' => $attResult['error']]);
        }
        if ($attResult) {
            $attachmentStored = $attResult['stored'];
            $attachmentOriginal = $attResult['original'];
            $newAttachmentSaved = true;
        } elseif ($removeAttachment) {
            $attachmentStored = null;
            $attachmentOriginal = null;
        }

        $stmt = $link->prepare("UPDATE tblclient_project_tasks SET sTitle = ?, sDescription = ?, sDue_date = ?, sAttachment = ?, sAttachmentName = ? WHERE id = ?");
        $stmt->bind_param('sssssi', $title, $description, $dueParam, $attachmentStored, $attachmentOriginal, $taskId);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok && $oldStored && ($newAttachmentSaved || $removeAttachment) && $oldStored !== $attachmentStored) {
            deleteClientTaskAttachmentFile($oldStored);
        }

        jsonOut($ok
            ? ['status' => 'success', 'message' => 'Task updated']
            : ['status' => 'error', 'message' => 'Update failed']);
    }

    // Assigned staff (non-admin) may only change status, not reassign/retitle.
    if (!$isAdmin) {
        $status = trim((string)($input['sStatus'] ?? $task['sStatus']));
        if (!in_array($status, ['Pending', 'In Progress', 'Done'], true)) {
            jsonOut(['status' => 'error', 'message' => 'Invalid status']);
        }
        $stmt = $link->prepare("UPDATE tblclient_project_tasks SET sStatus = ? WHERE id = ?");
        $stmt->bind_param('si', $status, $taskId);
        $ok = $stmt->execute();
        $stmt->close();
        if ($ok && $status !== $task['sStatus']) {
            logClientProjectActivity($link, (int)$task['project_id'], $taskId, 'status_changed', "\"{$task['sTitle']}\" status changed from {$task['sStatus']} to {$status}", $userId);
        }
        jsonOut($ok
            ? ['status' => 'success', 'message' => 'Task status updated']
            : ['status' => 'error', 'message' => 'Update failed']);
    }

    $title = trim((string)($input['sTitle'] ?? $task['sTitle']));
    $description = array_key_exists('sDescription', $input) ? trim((string)$input['sDescription']) : $task['sDescription'];
    $assignTo = (int)($input['sAssigned_to'] ?? $task['sAssigned_to']);
    $dueDate = trim((string)($input['sDue_date'] ?? ($task['sDue_date'] ?? '')));
    $status = trim((string)($input['sStatus'] ?? $task['sStatus']));

    if (!in_array($status, ['Pending', 'In Progress', 'Done'], true)) {
        jsonOut(['status' => 'error', 'message' => 'Invalid status']);
    }
    if ($dueDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
        jsonOut(['status' => 'error', 'message' => 'Invalid due date']);
    }
    $dueParam = $dueDate !== '' ? $dueDate : null;

    $stmt = $link->prepare("UPDATE tblclient_project_tasks
        SET sTitle = ?, sDescription = ?, sAssigned_to = ?, sStatus = ?, sDue_date = ?
        WHERE id = ?");
    $stmt->bind_param('ssissi', $title, $description, $assignTo, $status, $dueParam, $taskId);
    $ok = $stmt->execute();
    $stmt->close();

    if ($ok && $status !== $task['sStatus']) {
        logClientProjectActivity($link, (int)$task['project_id'], $taskId, 'status_changed', "\"{$title}\" status changed from {$task['sStatus']} to {$status}", $userId);
    }
    jsonOut($ok
        ? ['status' => 'success', 'message' => 'Task updated']
        : ['status' => 'error', 'message' => 'Update failed']);
}

if ($action === 'delete_client_project_task') {
    $taskId = (int)($input['id'] ?? 0);
    if ($taskId <= 0) {
        jsonOut(['status' => 'error', 'message' => 'Invalid task']);
    }
    $stmt = $link->prepare("SELECT * FROM tblclient_project_tasks WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $taskId);
    $stmt->execute();
    $task = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$task) {
        jsonOut(['status' => 'error', 'message' => 'Task not found']);
    }

    // Admin: any task. Client: only a task they requested themselves.
    $isClientOwnTask = $isClient && ((int)$task['sCreated_by'] === $userId);
    if (!$isAdmin && !$isClientOwnTask) {
        jsonOut(['status' => 'error', 'message' => 'Access denied']);
    }

    $del = $link->prepare("DELETE FROM tblclient_project_tasks WHERE id = ?");
    $del->bind_param('i', $taskId);
    $ok = $del->execute();
    $del->close();
    if ($ok) {
        logClientProjectActivity($link, (int)$task['project_id'], null, 'task_deleted', 'Task deleted: ' . $task['sTitle'], $userId);
    }
    jsonOut($ok
        ? ['status' => 'success', 'message' => 'Task deleted']
        : ['status' => 'error', 'message' => 'Delete failed']);
}

if ($action === 'list_client_project_activity') {
    $projectId = (int)($input['project_id'] ?? 0);
    $project = getClientProjectRow($link, $projectId);
    if (!canAccessClientProject($link, $project, $isAdmin, $isClient, $userId)) {
        jsonOut(['status' => 'error', 'message' => 'Access denied']);
    }
    $stmt = $link->prepare("SELECT a.*, u.sName AS user_name, t.sTitle AS task_title, t.sDescription AS task_description
        FROM tblclient_project_activity a
        LEFT JOIN tbluser u ON u.iUserid = a.iUserid
        LEFT JOIN tblclient_project_tasks t ON t.id = a.task_id
        WHERE a.project_id = ?
        ORDER BY a.dCreatedAt DESC, a.id DESC");
    $stmt->bind_param('i', $projectId);
    $stmt->execute();
    $result = $stmt->get_result();
    $activity = [];
    while ($row = $result->fetch_assoc()) {
        $activity[] = [
            'id' => (int)$row['id'],
            'task_id' => $row['task_id'] !== null ? (int)$row['task_id'] : null,
            'sAction' => $row['sAction'],
            'sDetail' => $row['sDetail'],
            'user_name' => $row['user_name'],
            'dCreatedAt' => $row['dCreatedAt'],
            'task_title' => $row['task_title'],
            'task_description' => $row['task_description'],
            'from_client' => (int)$row['iUserid'] === (int)$project['iClientUserid'],
        ];
    }
    $stmt->close();
    jsonOut(['status' => 'success', 'data' => $activity]);
}

if ($action === 'add_client_comment') {
    $projectId = (int)($input['project_id'] ?? 0);
    $comment = trim((string)($input['comment'] ?? ''));
    $project = getClientProjectRow($link, $projectId);
    if (!canAccessClientProject($link, $project, $isAdmin, $isClient, $userId)) {
        jsonOut(['status' => 'error', 'message' => 'Access denied']);
    }
    if ($comment === '') {
        jsonOut(['status' => 'error', 'message' => 'Comment cannot be empty']);
    }
    logClientProjectActivity($link, $projectId, null, 'comment', $comment, $userId);
    jsonOut(['status' => 'success', 'message' => 'Comment added']);
}

jsonOut(['status' => 'error', 'message' => 'Invalid action']);
