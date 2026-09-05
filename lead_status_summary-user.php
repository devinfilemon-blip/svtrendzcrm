<?php

header('Content-Type: application/json');
include 'layouts/session.php';
include 'layouts/config.php';


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit;
}
$user_id = isset($_GET['userid']) ? (int)$_GET['userid'] : (int)$_SESSION['user_id'];

$sql = "
SELECT  s.iStatusid AS status_id,
        s.sStatus   AS status,
        COALESCE(cnt.total, 0) AS count
FROM    tblstatus s
LEFT JOIN (
    SELECT  COALESCE(NULLIF(r.sStatus, ''), l.sLead_status) AS status_id,
            COUNT(*) AS total
    FROM    tblleads l
    LEFT JOIN (
        SELECT  r1.lead_id,
                r1.sStatus
        FROM    tblreplayleads r1
        JOIN (
            SELECT  lead_id,
                    MAX(sCreatedTimestamp) AS latest_ts
            FROM    tblreplayleads
            GROUP BY lead_id
        ) r2 ON  r2.lead_id = r1.lead_id
             AND r1.sCreatedTimestamp = r2.latest_ts
    ) r ON r.lead_id = l.iLead_id
    WHERE FIND_IN_SET(?, REPLACE(l.sAssigned_to, ' ', '')) > 0
    GROUP BY status_id
) AS cnt ON cnt.status_id = s.iStatusid
ORDER BY s.iStatusid;

";


$stmt = $link->prepare($sql);
if (!$stmt) {                       // catch future SQL typos
    echo json_encode(['status'=>'error','message'=>$link->error]);
    exit;
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

$out = [];
while ($row = $res->fetch_assoc()) {
    $out[] = [
        'status'    => $row['status'],
        'status_id' => (int)$row['status_id'],
        'count'     => (int)$row['count']
    ];
}
echo json_encode(['status'=>'success','data'=>$out]);
