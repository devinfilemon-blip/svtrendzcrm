
<?php
// Start the session
session_start();

// Include the database connection (adjust the path as necessary)
include 'layouts/config.php';  // Assuming you have a config.php file that handles the database connection

// Check if the token exists in the session
if (isset($_SESSION['token'])) {
    // Get the token from the session
    $token = $_SESSION['token'];

    // Prepare SQL query to delete the token from the database
    $query = "DELETE FROM tbltoken WHERE sToken = ?";
    $stmt = mysqli_prepare($link, $query);

    if ($stmt) {
        // Bind the token to the query
        mysqli_stmt_bind_param($stmt, "s", $token);
        // Execute the query
        mysqli_stmt_execute($stmt);

        // Check if the token was deleted
        if (mysqli_stmt_affected_rows($stmt) > 0) {
            // Token deleted successfully
            echo "Token has been deleted from the database.<br>";
        } else {
            // Token deletion failed
            echo "Error deleting the token from the database.<br>";
        }

        // Close the prepared statement
        mysqli_stmt_close($stmt);
    } else {
        echo "Database query failed.<br>";
    }

    // Unset all of the session variables
    $_SESSION = array();

    // Delete the session cookie (if exists)
    if (isset($_COOKIE['ltp_login_token'])) {
        unset($_COOKIE['ltp_login_token']);
        setcookie('ltp_login_token', '', time() - 3600, '/');  // Expire the cookie
    }

    // Destroy the session
    session_destroy();
}

// Redirect to login page
header("location: auth-login.php");
exit;
?>
