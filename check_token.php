<?php
session_start();
include 'layouts/config.php'; 
date_default_timezone_set('Asia/Kolkata');

function checkTokenExpiration($link) {
    if (!isset($_SESSION['token'])) {
        return json_encode(["status" => "error", "message" => "No token found. Please log in."]);
    }

    $query = "SELECT sToken, sExpire FROM tbltoken WHERE sToken = ? ORDER BY id DESC LIMIT 1";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "s", $_SESSION['token']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $expireTime = $row['sExpire'];
        $expireTimestamp = strtotime($expireTime);
        $currentTimestamp = time();

        if ($currentTimestamp < $expireTimestamp) {
            return json_encode(["status" => "success", "message" => "Token is valid."]);
        } else {
            $deleteQuery = "DELETE FROM tbltoken WHERE sToken = ?";
            $deleteStmt = mysqli_prepare($link, $deleteQuery);
            mysqli_stmt_bind_param($deleteStmt, "s", $_SESSION['token']);
            mysqli_stmt_execute($deleteStmt);

            if (mysqli_stmt_affected_rows($deleteStmt) > 0) {
                $_SESSION['token_expired_message'] = "Token has expired. Please log in again.";
            }
            
           
            return json_encode(["status" => "error", "message" => "Token has expired. Please log in again."]);
        }
    } else {
        return json_encode(["status" => "error", "message" => "Token not found. Please log in."]);
    }

   mysqli_stmt_close($stmt);
}

header('Content-Type: application/json');
echo checkTokenExpiration($link);
?>
