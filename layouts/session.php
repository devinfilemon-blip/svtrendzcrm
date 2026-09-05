<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
// if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
//     header("location: auth-login.php");
//     exit;
// }

// Client-role accounts are locked to their own portal + Client Project API.
// Everything else in the app redirects them back, regardless of whether that
// page defines its own access guard.
if (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'Client') {
    $crmClientAllowedScripts = [
        'client-dashboard.php',
        'client-portal-projects.php',
        'client-portal-project-view.php',
        'client-project-api.php',
        'download-client-task-attachment.php',
        'logout.php',
        'check_token.php',
        'MyProfile.php',
        'newpassword.php',
        // api.php itself is allowlisted so a Client's session can reach it at all;
        // api.php then narrows this down to only the loginUser/updateMyProfile/changePassword actions.
        'api.php',
    ];
    $crmCurrentScript = basename($_SERVER['SCRIPT_NAME']);
    if (!in_array($crmCurrentScript, $crmClientAllowedScripts, true)) {
        header('Location: client-dashboard.php');
        exit;
    }
}
?>