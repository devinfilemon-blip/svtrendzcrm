<?php
include 'layouts/session.php';
include 'layouts/config.php';
include_once 'layouts/crm-access.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth-login.php');
    exit;
}

$isAdmin = crmIsAdmin();
$isClient = crmIsClient();
$userId = (int)$_SESSION['user_id'];

$taskId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($taskId <= 0) {
    http_response_code(400);
    echo 'Invalid task';
    exit;
}

$stmt = $link->prepare("SELECT t.*, p.iClientUserid FROM tblclient_project_tasks t
    LEFT JOIN tblclient_project p ON p.iId = t.project_id
    WHERE t.id = ? LIMIT 1");
$stmt->bind_param('i', $taskId);
$stmt->execute();
$task = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$task || empty($task['sAttachment'])) {
    http_response_code(404);
    echo 'File not found';
    exit;
}

// Admin: any task. Client: only a task on their own project. Staff: only a
// task assigned to them. Same scoping used elsewhere for this task.
$isOwnTask = ((int)$task['sAssigned_to'] === $userId);
$isClientProjectOwner = $isClient && ((int)$task['iClientUserid'] === $userId);
if (!$isAdmin && !$isOwnTask && !$isClientProjectOwner) {
    http_response_code(403);
    echo 'Access denied';
    exit;
}

$path = __DIR__ . '/uploads/client-task-attachments/' . basename($task['sAttachment']);
if (!is_file($path)) {
    http_response_code(404);
    echo 'File not found';
    exit;
}

$downloadName = $task['sAttachmentName'] ?: basename($path);
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($path) ?: 'application/octet-stream';

header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . str_replace('"', '', $downloadName) . '"');
header('Content-Length: ' . filesize($path));
header('X-Content-Type-Options: nosniff');
readfile($path);
exit;
