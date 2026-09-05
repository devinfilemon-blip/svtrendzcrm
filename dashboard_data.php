<?php
include 'layouts/session.php';
include 'layouts/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

date_default_timezone_set('Asia/Kolkata');

$user_id = (int)$_SESSION['user_id'];
$isAdmin = (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'Admin');
$today = date('Y-m-d');

// Reminder count (user's own reminders)
$stmt1 = $link->prepare("SELECT COUNT(*) FROM tblreminders WHERE iUserid = ? AND sDate = ?");
$stmt1->bind_param("is", $user_id, $today);
$stmt1->execute();
$stmt1->bind_result($reminderCount);
$stmt1->fetch();
$stmt1->close();

// Follow-up count (user's own follow-ups)
$stmt2 = $link->prepare("SELECT COUNT(*) FROM tblreplayleads WHERE userid = ? AND sFollowupdate = ?");
$stmt2->bind_param("is", $user_id, $today);
$stmt2->execute();
$stmt2->bind_result($followupCount);
$stmt2->fetch();
$stmt2->close();

// Lead count: Admin = all leads, User = assigned only
if ($isAdmin) {
    $stmt3 = $link->prepare("SELECT COUNT(*) FROM tblleads");
    $stmt3->execute();
} else {
    $stmt3 = $link->prepare("SELECT COUNT(*) FROM tblleads WHERE FIND_IN_SET(?, REPLACE(sAssigned_to, ' ', '')) > 0");
    $stmt3->bind_param("i", $user_id);
    $stmt3->execute();
}
$stmt3->bind_result($assignedCount);
$stmt3->fetch();
$stmt3->close();

// Today's re-engage count
$reengageCount = 0;
mysqli_query($link, "CREATE TABLE IF NOT EXISTS tblreengage (
  iReengageid INT AUTO_INCREMENT PRIMARY KEY,
  iUserid INT NOT NULL,
  iLeadid INT NOT NULL DEFAULT 0,
  sCompanyname VARCHAR(255) NOT NULL DEFAULT '',
  sDescription TEXT NULL,
  sDate DATE NOT NULL,
  iCreatedBy INT NULL,
  sCreatedTimeStamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  sModifiedTimestamp DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_user (iUserid),
  INDEX idx_lead (iLeadid),
  INDEX idx_date (sDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

if ($isAdmin) {
    $stmt4 = $link->prepare("SELECT COUNT(*) FROM tblreengage WHERE sDate = ?");
    $stmt4->bind_param("s", $today);
} else {
    $stmt4 = $link->prepare("SELECT COUNT(*) FROM tblreengage WHERE iUserid = ? AND sDate = ?");
    $stmt4->bind_param("is", $user_id, $today);
}
$stmt4->execute();
$stmt4->bind_result($reengageCount);
$stmt4->fetch();
$stmt4->close();

echo json_encode([
    'status' => 'success',
    'reminders' => $reminderCount,
    'followups' => $followupCount,
    'assigned' => $assignedCount,
    'reengage' => (int)$reengageCount,
    'is_admin' => $isAdmin
]);
