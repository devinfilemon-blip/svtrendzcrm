<?php
include 'layouts/session.php';
include 'layouts/config.php';
include_once 'layouts/crm-access.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth-login.php');
    exit;
}
if (crmIsClient()) {
    http_response_code(403);
    echo 'Access denied';
    exit;
}

$isAdmin = crmIsAdmin();
$userId = (int)$_SESSION['user_id'];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo 'Invalid expense';
    exit;
}

$stmt = $link->prepare("SELECT e.*, l.sAssigned_to, l.sLead_owner FROM tblproject_expenses e
    LEFT JOIN tblleads l ON l.iLead_id = e.lead_id
    WHERE e.id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$expense = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$expense || empty($expense['sReceipt'])) {
    http_response_code(404);
    echo 'File not found';
    exit;
}

$assignedIds = array_filter(array_map('intval', preg_split('/\s*,\s*/', trim((string)$expense['sAssigned_to']), -1, PREG_SPLIT_NO_EMPTY)));
$isOwner = (int)$expense['sLead_owner'] === $userId;
$isAssigned = in_array($userId, $assignedIds, true);
$isSubmitter = (int)$expense['iUserid'] === $userId;

if (!$isAdmin && !$isOwner && !$isAssigned && !$isSubmitter) {
    http_response_code(403);
    echo 'Access denied';
    exit;
}

$path = __DIR__ . '/uploads/project-expenses/' . basename($expense['sReceipt']);
if (!is_file($path)) {
    http_response_code(404);
    echo 'File not found';
    exit;
}

$downloadName = $expense['sReceiptName'] ?: basename($path);
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($path) ?: 'application/octet-stream';

header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . str_replace('"', '', $downloadName) . '"');
header('Content-Length: ' . filesize($path));
header('X-Content-Type-Options: nosniff');
readfile($path);
exit;
