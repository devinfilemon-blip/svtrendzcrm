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
    echo 'Invalid photo';
    exit;
}

$stmt = $link->prepare("SELECT p.*, v.lead_id, v.iUserid AS visit_userid, l.sAssigned_to, l.sLead_owner
    FROM tblproject_visit_photos p
    LEFT JOIN tblproject_visits v ON v.id = p.visit_id
    LEFT JOIN tblleads l ON l.iLead_id = v.lead_id
    WHERE p.id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$photo = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$photo) {
    http_response_code(404);
    echo 'File not found';
    exit;
}

$assignedIds = array_filter(array_map('intval', preg_split('/\s*,\s*/', trim((string)$photo['sAssigned_to']), -1, PREG_SPLIT_NO_EMPTY)));
$isOwner = (int)$photo['sLead_owner'] === $userId;
$isAssigned = in_array($userId, $assignedIds, true);
$isCreator = (int)$photo['visit_userid'] === $userId;

if (!$isAdmin && !$isOwner && !$isAssigned && !$isCreator) {
    http_response_code(403);
    echo 'Access denied';
    exit;
}

$path = __DIR__ . '/uploads/project-visit-photos/' . basename($photo['sPhoto']);
if (!is_file($path)) {
    http_response_code(404);
    echo 'File not found';
    exit;
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($path) ?: 'image/jpeg';

header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . str_replace('"', '', basename($photo['sPhotoName'] ?: $path)) . '"');
header('Content-Length: ' . filesize($path));
header('X-Content-Type-Options: nosniff');
readfile($path);
exit;
