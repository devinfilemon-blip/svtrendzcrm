<?php 
session_start();
header('Content-Type: application/json');
include 'layouts/config.php';

// Start the session to get user details (make sure session_start() is called at the top of the script)

if (isset($_POST['action']) && $_POST['action'] === 'addreplay') {
    $description = $_POST['description'] ?? '';
    $followupDate = $_POST['followupdate'] ?? '';
    $statusLead = $_POST['statusleadss'] ?? '';
    $leadId = $_POST['leadId'] ?? '';
    $userId = $_SESSION['user_id'] ?? 0;

    $uploadDir = "uploads/";
    $filePaths = ["", "", ""]; // Max 3
 if (empty($description) || empty($followupDate) || empty($statusLead)) {
                echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
                exit;
            }
    // Check and move each file (supports up to 3 files)
    if (isset($_FILES['fileUpload'])) {
        foreach ($_FILES['fileUpload']['tmp_name'] as $index => $tmpName) {
            if ($_FILES['fileUpload']['size'][$index] > 0) {
                $originalName = basename($_FILES['fileUpload']['name'][$index]);
                $ext = pathinfo($originalName, PATHINFO_EXTENSION);
                $newName = time() . "_$index." . $ext;
                $targetFile = $uploadDir . $newName;

                if (move_uploaded_file($tmpName, $targetFile)) {
                    $filePaths[$index] = $targetFile;
                }
            }
        }
    }

    // Insert into DB
    $stmt = $link->prepare('
        INSERT INTO tblreplayleads 
        (sDescription, sFollowupdate, sStatus, lead_id, sFileupload, sFileupload2, sFileupload3, userid, sCreatedTimestamp) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ');
    
    $stmt->bind_param(
        'ssssssss',
        $description,
        $followupDate,
        $statusLead,
        $leadId,
        $filePaths[0],
        $filePaths[1],
        $filePaths[2],
        $userId
    );

if ($stmt->execute()) {

    // Get status name from tblstatus
    $statusName = '';
    $statusStmt = $link->prepare("SELECT sStatus FROM tblstatus WHERE iStatusid = ?");
    $statusStmt->bind_param("i", $statusLead);
    $statusStmt->execute();
    $statusStmt->bind_result($statusName);
    $statusStmt->fetch();
    $statusStmt->close();

    // ✅ Notify Lead Assigned User
    $q = "SELECT u.sPhone, u.sName, l.sLead_name , l.sCompany_name
          FROM tblleads l 
          LEFT JOIN tbluser u ON l.sAssigned_to = u.iUserid 
          WHERE l.iLead_id = ?";
    $stmt2 = $link->prepare($q);
    $stmt2->bind_param("i", $leadId);
    $stmt2->execute();
    $stmt2->bind_result($assignedPhone, $assignedName, $leadName, $companyName);
    $stmt2->fetch();
    $stmt2->close();

  

    echo json_encode([
        "status" => "success",
        "message" => "Reply and files saved successfully."
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Database insert failed."
    ]);
}
exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'fetchReplies') {
    $leadId = isset($_GET['lead_id']) ? $_GET['lead_id'] : null;

    // Validate leadId
    if (!$leadId) {
        $response = [
            'status' => 'error',
            'message' => 'Lead ID is required to fetch replies.'
        ];
        echo json_encode($response);
        exit;
    }

    // Fetch replies from the database for the given lead ID, including the user name
    $stmt = $link->prepare('
       SELECT rl.rId, rl.lead_id, rl.sDescription, rl.sFollowupdate, s.sStatus, 
       rl.sFileupload, rl.sFileupload2, rl.sFileupload3, 
       u.sName, rl.sCreatedTimestamp
        FROM tblreplayleads rl
        LEFT JOIN tblstatus s ON rl.sStatus = s.iStatusid
        LEFT JOIN tbluser u ON rl.userid = u.iUserid
        WHERE rl.lead_id = ?
    ');
    $stmt->bind_param('i', $leadId);
    $stmt->execute();
    $result = $stmt->get_result();
    $replies = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
           $replies[] = [
    'rId' => $row['rId'],
    'lead_id' => $row['lead_id'],
    'sDescription' => $row['sDescription'],
    'sFollowupdate' => $row['sFollowupdate'],
    'sStatus' => $row['sStatus'],
    'sFileupload' => $row['sFileupload'],
    'sFileupload2' => $row['sFileupload2'],
    'sFileupload3' => $row['sFileupload3'],
    'sName' => $row['sName'],
    'sCreatedTimestamp' => $row['sCreatedTimestamp'],
];
        }
    }

    echo json_encode([
        'status' => 'success',
        'replies' => $replies,
    ]);
    exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $replyId = isset($_POST['replyId']) ? (int)$_POST['replyId'] : 0;

    if (!$replyId) {
        echo json_encode(['status' => 'error', 'message' => 'Reply ID is required.']);
        exit;
    }

    $stmt = $link->prepare('DELETE FROM tblreplayleads WHERE rId = ?');
    $stmt->bind_param('i', $replyId);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Reply deleted successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete reply.']);
    }
    exit;
}

?>
