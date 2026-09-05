<?php

header('Content-Type: application/json');
include 'layouts/session.php';
include 'layouts/config.php';
include_once __DIR__ . '/layouts/crm-access.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user_id  = (int)$_SESSION['user_id'];
$isAdmin  = crmCanSeeAllLeads($link);

// Latest follow-up entry per lead (mirrors the logic used for the pipeline
// status summary), then keep only the ones whose due date has passed.
$sql = "
SELECT  r.lead_id,
        r.sDescription,
        r.sFollowupdate,
        s.sStatus   AS status_name,
        u.sName     AS user_name,
        l.sCompany_name,
        DATEDIFF(CURDATE(), r.sFollowupdate) AS days_overdue
FROM    tblreplayleads r
JOIN (
    SELECT  lead_id, MAX(sCreatedTimestamp) AS latest_ts
    FROM    tblreplayleads
    GROUP BY lead_id
) latest ON latest.lead_id = r.lead_id AND latest.latest_ts = r.sCreatedTimestamp
LEFT JOIN tblstatus s ON r.sStatus = s.iStatusid
LEFT JOIN tbluser   u ON r.userid  = u.iUserid
LEFT JOIN tblleads  l ON r.lead_id = l.iLead_id
WHERE   r.sFollowupdate < CURDATE()
" . ($isAdmin ? "" : "AND r.userid = ?") . "
ORDER BY r.sFollowupdate ASC
LIMIT 25
";

$stmt = $link->prepare($sql);
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => $link->error]);
    exit;
}

if (!$isAdmin) {
    $stmt->bind_param("i", $user_id);
}
$stmt->execute();
$res = $stmt->get_result();

$out = [];
while ($row = $res->fetch_assoc()) {
    $out[] = [
        'lead_id'      => (int)$row['lead_id'],
        'company'      => $row['sCompany_name'] !== null && $row['sCompany_name'] !== '' ? $row['sCompany_name'] : '—',
        'description'  => $row['sDescription'] !== null && $row['sDescription'] !== '' ? $row['sDescription'] : '—',
        'due_date'     => $row['sFollowupdate'],
        'days_overdue' => (int)$row['days_overdue'],
        'status'       => $row['status_name'] !== null && $row['status_name'] !== '' ? $row['status_name'] : '—',
        'user'         => $row['user_name'] !== null && $row['user_name'] !== '' ? $row['user_name'] : '—',
    ];
}

echo json_encode([
    'status'   => 'success',
    'data'     => $out,
    'count'    => count($out),
    'is_admin' => $isAdmin
]);
