<?php 
include 'layouts/config.php'; 
error_reporting(E_ALL);
ini_set('display_errors', 1);


// Prepare and execute the SQL query to fetch contact types
$stmt = $link->prepare("SELECT iContactid, sContact
 FROM tblcontacttype ORDER BY iContactid");
$stmt->execute();
$result = $stmt->get_result();

// Create an array to store the contact types
$contactTypes = [];
while ($row = $result->fetch_assoc()) {
    $contactTypes[] = $row;
}

// Return the contact types as a JSON response
echo json_encode($contactTypes);
?>
