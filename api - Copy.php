<?php include 'layouts/session.php';

?>
<?php 

header('Content-Type: application/json');
error_reporting(0); // or use error_reporting(E_ERROR); in production
ini_set('display_errors', 0);
session_start();

include 'layouts/config.php'; 
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
// header('Content-Type: application/json');

function generateRandomPassword($length = 6) {
    $characters = '0123456789';  // Only numeric characters
    $password = '';
    $charactersLength = strlen($characters);
    
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[rand(0, $charactersLength - 1)];
    }
    return $password;
}



        header('Content-Type: application/json');  

        $method = $_SERVER['REQUEST_METHOD'];

    function sendResponse($status, $message) {
        echo json_encode(['status' => $status, 'message' => $message]); 
        exit;
    }

            //Api for user data

        if ($method == 'POST') {
            $inputData = json_decode(file_get_contents("php://input"), true);

            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'addlead') {
    
    $sLead_name = $_POST['sLead_name'] ?? '';
    $sEmail = $_POST['sEmail'] ?? '';
    $sPhone = $_POST['sPhone'] ?? '';
    $sAlternate_phone = $_POST['sAlternate_phone'] ?? '';
    $sLead_source = $_POST['sLead_source'] ?? '';
    $sLead_status = $_POST['sLead_status'] ?? '';
    $sLead_priority = $_POST['sLead_priority'] ?? '';
    $sLead_type = $_POST['sLead_type'] ?? '';
    $sCompany_name = $_POST['sCompany_name'] ?? '';
    $sIndustry_type = $_POST['sIndustry_type'] ?? '';
    $sDesignation = $_POST['sDesignation'] ?? '';
    $sWebsite = $_POST['sWebsite'] ?? '';
    $sLocation = $_POST['sLocation'] ?? '';
    $sAddress = $_POST['sAddress'] ?? '';
    $sAssigned_to = $_POST['sAssigned_to'] ?? '';
    $sLead_owner = $_POST['sLead_owner'] ?? '';
    $sPreferred_communication = $_POST['sPreferred_communication'] ?? '';
    $sTags = $_POST['sTags'] ?? '';
    $sContactperson = $_POST['sContactperson'] ?? '';
    $sCreated_by = $_POST['sCreated_by'] ?? null;

    $sProductnames = isset($_POST['products']) ? json_decode($_POST['products'], true) : [];
    $sQuantities = isset($_POST['quantities']) ? json_decode($_POST['quantities'], true) : [];
    $sRates = isset($_POST['rates']) ? json_decode($_POST['rates'], true) : [];
  $categories = json_decode($_POST['categories']);
    function saveFile($field, $uploadDir = "uploads/") {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] == UPLOAD_ERR_OK) {
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $ext = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
            $fileName = time() . '_' . uniqid() . '.' . $ext;
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES[$field]['tmp_name'], $targetPath)) {
                return $targetPath;
            }
        }
        return '';
    }

    $file1 = saveFile('fileUpload');
    $file2 = saveFile('fileUpload2');
    $file3 = saveFile('fileUpload3');

    $query = "INSERT INTO tblleads (
        sLead_name, sEmail, sPhone, sAlternate_phone, sLead_source, sLead_status,
        sLead_priority, sLead_type, sCompany_name, sIndustry_type, sDesignation,
        sWebsite, sLocation, sAddress, sAssigned_to, sLead_owner, sPreferred_communication,
        sTags, sContactperson, sFileupload, sFileupload2, sFileupload3, sCreated_by
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)";

    $stmt = mysqli_prepare($link, $query);
    if (!$stmt) {
        sendResponse("error", "Database error (prepare lead): " . mysqli_error($link));
    }

    mysqli_stmt_bind_param($stmt, "ssssssssssssssssssssssi",
        $sLead_name, $sEmail, $sPhone, $sAlternate_phone, $sLead_source,
        $sLead_status, $sLead_priority, $sLead_type, $sCompany_name, $sIndustry_type,
        $sDesignation, $sWebsite, $sLocation, $sAddress, $sAssigned_to, $sLead_owner,
        $sPreferred_communication, $sTags, $sContactperson,
        $file1, $file2, $file3, $sCreated_by
    );

    if (!mysqli_stmt_execute($stmt)) {
        sendResponse("error", "Database error (execute lead): " . mysqli_stmt_error($stmt));
    }

    $lead_id = mysqli_insert_id($link);
    mysqli_stmt_close($stmt);

    // Insert into tblproductleads
// Inside the 'addlead' POST handler:
foreach ($sProductnames as $index => $product) {
    $quantity = $sQuantities[$index] ?? '';
    $rate = $sRates[$index] ?? '';
    $category = $categories[$index] ?? '';  // Get category value

    $query1 = "INSERT INTO tblproductleads (sProductname, sQuantity, sRate, lead_id, category) VALUES (?, ?, ?, ?, ?)";
    $stmt1 = mysqli_prepare($link, $query1);
    if (!$stmt1) {
        sendResponse("error", "Database error (prepare product): " . mysqli_error($link));
    }

    mysqli_stmt_bind_param($stmt1, "sssis", $product, $quantity, $rate, $lead_id, $category);

    if (!mysqli_stmt_execute($stmt1)) {
        sendResponse("error", "Database error (execute product): " . mysqli_stmt_error($stmt1));
    }

    mysqli_stmt_close($stmt1);
}


    sendResponse("success", "Customer added successfully.");
}

else if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'updatelead') {
    $leadId = $_POST['leadId'] ?? null;

    // Validate leadId
    if (empty($leadId) || !is_numeric($leadId)) {
        sendResponse("error", "Invalid lead ID for update.");
        exit;
    }
    $leadId = (int)$leadId;

    // Sanitize input
    $sLead_name = $_POST['sLead_name'] ?? '';
    $sEmail = $_POST['sEmail'] ?? '';
    $sPhone = $_POST['sPhone'] ?? '';
    $sAlternate_phone = $_POST['sAlternate_phone'] ?? '';
    $sLead_source = $_POST['sLead_source'] ?? '';
    $sLead_status = $_POST['sLead_status'] ?? '';
    $sLead_priority = $_POST['sLead_priority'] ?? '';
    $sLead_type = $_POST['sLead_type'] ?? '';
    $sCompany_name = $_POST['sCompany_name'] ?? '';
    $sIndustry_type = $_POST['sIndustry_type'] ?? '';
    $sDesignation = $_POST['sDesignation'] ?? '';
    $sWebsite = $_POST['sWebsite'] ?? '';
    $sLocation = $_POST['sLocation'] ?? '';
    $sAddress = $_POST['sAddress'] ?? '';
    $sAssigned_to = $_POST['sAssigned_to'] ?? '';
    $sLead_owner = $_POST['sLead_owner'] ?? '';
    $sPreferred_communication = $_POST['sPreferred_communication'] ?? '';
    $sTags = $_POST['sTags'] ?? '';
    $sContactperson = $_POST['sContactperson'] ?? '';

    // Decode product data and categories
    $sProductnames = isset($_POST['products']) ? json_decode($_POST['products'], true) : [];
    $sQuantities = isset($_POST['quantities']) ? json_decode($_POST['quantities'], true) : [];
    $sRates = isset($_POST['rates']) ? json_decode($_POST['rates'], true) : [];
    $categories = json_decode($_POST['categories']) ?? [];

    // Save file uploads
    function saveFile($field, $uploadDir = "uploads/") {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] == UPLOAD_ERR_OK) {
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $ext = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
            $fileName = time() . '_' . uniqid() . '.' . $ext;
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES[$field]['tmp_name'], $targetPath)) {
                return $targetPath;
            }
        }
        return '';
    }

    // Handle file uploads
    $file1 = saveFile('fileUpload');
    $file2 = saveFile('fileUpload2');
    $file3 = saveFile('fileUpload3');

    // Build SQL dynamically to only include file columns if new files were uploaded
    $updateFields = "
        sLead_name = ?, sEmail = ?, sPhone = ?, sAlternate_phone = ?, sLead_source = ?, sLead_status = ?, 
        sLead_priority = ?, sLead_type = ?, sCompany_name = ?, sIndustry_type = ?, sDesignation = ?, 
        sWebsite = ?, sLocation = ?, sAddress = ?, sAssigned_to = ?, sLead_owner = ?, 
        sPreferred_communication = ?, sTags = ?, sContactperson = ?
    ";
    $types = "sssssssssssssssssss";
    $params = [
        $sLead_name, $sEmail, $sPhone, $sAlternate_phone, $sLead_source, $sLead_status,
        $sLead_priority, $sLead_type, $sCompany_name, $sIndustry_type, $sDesignation,
        $sWebsite, $sLocation, $sAddress, $sAssigned_to, $sLead_owner,
        $sPreferred_communication, $sTags, $sContactperson
    ];

    if ($file1) {
        $updateFields .= ", sFileupload = ?";
        $types .= "s";
        $params[] = $file1;
    }
    if ($file2) {
        $updateFields .= ", sFileupload2 = ?";
        $types .= "s";
        $params[] = $file2;
    }
    if ($file3) {
        $updateFields .= ", sFileupload3 = ?";
        $types .= "s";
        $params[] = $file3;
    }

    $updateFields .= " WHERE iLead_id = ?";
    $types .= "i";
    $params[] = $leadId;

    // Update lead information
    $query = "UPDATE tblleads SET $updateFields";
    $stmt = mysqli_prepare($link, $query);

    if (!$stmt) {
        sendResponse("error", "Prepare failed: " . mysqli_error($link));
        exit;
    }

    // Bind params by reference
    $params_ref = [];
    foreach ($params as &$param) {
        $params_ref[] = &$param;
    }
    call_user_func_array([$stmt, 'bind_param'], array_merge([$types], $params_ref));

    $ret = mysqli_stmt_execute($stmt);
    if (!$ret) {
        sendResponse("error", "Error updating the lead. " . mysqli_stmt_error($stmt));
        exit;
    }

    mysqli_stmt_close($stmt);

    // Delete old product entries for this lead (do it once before adding new products)
    $queryDelete = "DELETE FROM tblproductleads WHERE lead_id = ?";
    $stmtDelete = mysqli_prepare($link, $queryDelete);
    if (!$stmtDelete) {
        sendResponse("error", "Prepare failed (delete products): " . mysqli_error($link));
        exit;
    }
    mysqli_stmt_bind_param($stmtDelete, 'i', $leadId);
    mysqli_stmt_execute($stmtDelete);
    mysqli_stmt_close($stmtDelete);

    // Insert new products
    foreach ($sProductnames as $index => $product) {
        $quantity = $sQuantities[$index] ?? '';
        $rate = $sRates[$index] ?? '';
        $category = $categories[$index] ?? '';  // Get category value

        $query1 = "INSERT INTO tblproductleads (sProductname, sQuantity, sRate, lead_id, category) VALUES (?, ?, ?, ?, ?)";
        $stmt1 = mysqli_prepare($link, $query1);
        if (!$stmt1) {
            sendResponse("error", "Prepare failed (insert product): " . mysqli_error($link));
            exit;
        }

        mysqli_stmt_bind_param($stmt1, "sssis", $product, $quantity, $rate, $leadId, $category);
        $executeRet = mysqli_stmt_execute($stmt1);
        if (!$executeRet) {
            sendResponse("error", "Execute failed (insert product): " . mysqli_stmt_error($stmt1));
            exit;
        }
        mysqli_stmt_close($stmt1);
    }

    // Final success response
    sendResponse("success", "Lead and products updated successfully.");
}





     else if ($inputData['action'] == "adduser") {
    if (!empty($inputData['name']) && !empty($inputData['email']) && !empty($inputData['phone'])) {

        $name = mysqli_real_escape_string($link, $inputData['name']);
        $email = mysqli_real_escape_string($link, $inputData['email']);
        $phone = mysqli_real_escape_string($link, $inputData['phone']);
        $department = mysqli_real_escape_string($link, $inputData['department']);
        $Role = mysqli_real_escape_string($link, $inputData['Role']);

        // Check if the email already exists
        $checkQuery = "SELECT COUNT(*) as count FROM tbluser WHERE sEmail = ?";
        $stmt = mysqli_prepare($link, $checkQuery);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $emailCount);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if ($emailCount > 0) {
            sendResponse("error", "Email already exists.");
            return;
        }

        // Check if the phone number already exists
        $checkQuery = "SELECT COUNT(*) as count FROM tbluser WHERE sPhone = ?";
        $stmt = mysqli_prepare($link, $checkQuery);
        mysqli_stmt_bind_param($stmt, "s", $phone);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $phoneCount);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if ($phoneCount > 0) {
            sendResponse("error", "Phone number already exists.");
            return;
        }

        $password = generateRandomPassword(6);  // Random password
        $param_password = password_hash($password, PASSWORD_DEFAULT); // Hashed password

        // Insert the user
        $query = "INSERT INTO tbluser (sName, sEmail, sPhone, sRole, iDepid, sPassword_hash, sIs_active) VALUES (?,?,?,?,?,?,1)";
        $stmt = mysqli_prepare($link, $query);
        mysqli_stmt_bind_param($stmt, "ssssis", $name, $email, $phone, $Role, $department, $param_password);
        $ret = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if (!$ret) {
            sendResponse("error", "Data Not Saved: " . mysqli_error($link));
        } else {
          
        //   print_r($response);
                  //   file_put_contents("whatsapp_log.txt", date('Y-m-d H:i:s') . " " . print_r($response, true) . "\n", FILE_APPEND);



            sendResponse("success", "Data Saved Successfully");
        }
    }
}
else    if($inputData['action']=="updateuser"){
        if (!empty($inputData['userid']) &&!empty($inputData['name']) && !empty($inputData['email']) && !empty($inputData['phone']) && !empty($inputData['department']) && !empty($inputData['Role'])) {
            // Sanitize the user input
            $userid = mysqli_real_escape_string($link, $inputData['userid']);
            $name = mysqli_real_escape_string($link, $inputData['name']);
            $email = mysqli_real_escape_string($link, $inputData['email']);
            $phone = mysqli_real_escape_string($link, $inputData['phone']);
            $department = mysqli_real_escape_string($link, $inputData['department']);
               $Role = mysqli_real_escape_string($link, $inputData['Role']);
          
            $query = "update tbluser set sName=?, sEmail=?, sPhone=?,sRole=?,iDepid=? where iUserid = ?";
            $stmt = mysqli_prepare($link,$query);
            mysqli_stmt_bind_param($stmt, "ssssii", $name, $email, $phone,$Role,$department,$userid);
            $ret = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
    
            // exit();
    
            if(!$ret){
                sendResponse("error", "Data Not updated: " . mysqli_error($link));
            }else{
                sendResponse("success", "Data updated Successfully");

            }

            return;   
        }

            }else  if($inputData['action']=="deleteuser"){
            if (!empty($inputData['userid']) ) {
            $userid = mysqli_real_escape_string($link, $inputData['userid']);
           
            $query = "DELETE from tbluser where iUserid = ?";
            $stmt = mysqli_prepare($link,$query);
            mysqli_stmt_bind_param($stmt, "i", $inputData['userid']);
            $ret = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
    
            if(!$ret){
                sendResponse("error", "Data Not deleted: " . mysqli_error($link));
            }else{
                sendResponse("success", "Data deleted Successfully");

            }
    
             }
        
        }if ($inputData['action'] == "fngetlist") {
    $output = [];

    $stmt = $link->prepare("SELECT * FROM tbluser WHERE LOWER(sName) NOT IN ('abc', 'abc user')");
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $output[] = $row;
    }


    echo json_encode(["status" => "success", "data" => $output]);

         
        } else  if($inputData['action']=="getuserbyid"){
        $output="";
        if (!empty($inputData['userid']) ) {
           
            $userid = mysqli_real_escape_string($link, $inputData['userid']);

            $stmt = $link->prepare('select * from tbluser where iUserid = ?');
            $stmt->bind_param('i',$userid);
            $stmt->execute();
            $result = $stmt->get_result();
            while($row = $result->fetch_assoc()){
            
                $output= $row;
    
            }
            echo json_encode(array("status" => "success", "data"=>$output));
             }else{
              
             }
            
            }else
        if ($inputData['action'] == "loginUser") {

    if (!empty($inputData['phone']) && !empty($inputData['password'])) {

        $phone = $inputData['phone'];
        $password = $inputData['password'];

        // Include sRole in SELECT query
        $query = "SELECT iUserId, sPhone, sPassword_hash, sName, sIs_active, sRole FROM tbluser WHERE sPhone = ?";
        $stmt = mysqli_prepare($link, $query);
        mysqli_stmt_bind_param($stmt, "s", $phone);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);

            if ($user['sIs_active'] == 0) {
                sendResponse("error", "User is inactive.");
            }

            if (password_verify($password, $user['sPassword_hash'])) {

                session_start();
                $_SESSION['user_id'] = $user['iUserId'];
                $_SESSION['username'] = $user['sName'];
                $_SESSION['userRole'] = $user['sRole']; // ✅ Set user role in session

                $token = bin2hex(random_bytes(16));
                date_default_timezone_set('Asia/Kolkata');
                $expireTimestamp = strtotime("+4 hours", time());
                $expireDateTime = date('Y-m-d H:i:s', $expireTimestamp);

                $insertTokenQuery = "INSERT INTO tbltoken (user_id, sToken, sExpire) VALUES (?, ?, ?)";
                $stmtInsert = mysqli_prepare($link, $insertTokenQuery);
                mysqli_stmt_bind_param($stmtInsert, "iss", $user['iUserId'], $token, $expireDateTime);
                mysqli_stmt_execute($stmtInsert);

                if (mysqli_stmt_affected_rows($stmtInsert) > 0) {
                    $_SESSION['token'] = $token;

                    sendResponse("success", "Login successful", [
                        "token" => $token,
                        "expires_in" => 14400
                    ]);
                } else {
                    sendResponse("error", "Failed to generate token.");
                }

                mysqli_stmt_close($stmtInsert);
            } else {
                sendResponse("error", "Invalid password.");
            }
        } else {
            sendResponse("error", "Invalid phone number.");
        }

        mysqli_stmt_close($stmt);
        return;
    } else {
        sendResponse("error", "Please provide both phone number and password.");
        return;
    }
}

        else 

        if ($inputData['action'] == "fetchUserData") {
            $userid = $inputData['userid'];
            
     
            $userid = mysqli_real_escape_string($link, $userid);
                 
            $query = "SELECT * FROM tbluser WHERE iUserid = ?";
            $stmt = mysqli_prepare($link, $query);
            mysqli_stmt_bind_param($stmt, "i", $userid);
            mysqli_stmt_execute($stmt);
        
            $result = mysqli_stmt_get_result($stmt);
        
        
            if (mysqli_num_rows($result) > 0) {
                $data = mysqli_fetch_assoc($result);
          
                echo json_encode([
                    'status' => 'success',
                    'data' => $data
                ]);
            } else {
               
                echo json_encode([
                    'status' => 'error',
                    'message' => 'User not found'
                ]);
            }
        
            mysqli_stmt_close($stmt);
        } else

        if ($inputData['action'] == 'changePassword') {
            $userid = isset($inputData['userid']) ? $inputData['userid'] : null; 
            $current = isset($inputData['current']) ? $inputData['current'] : null;
            $newpass = isset($inputData['newpass']) ? $inputData['newpass'] : null;
            $confirm = isset($inputData['confirm']) ? $inputData['confirm'] : null;
        
            file_put_contents('php://stderr', "UserID: $userid, Current: $current, New: $newpass, Confirm: $confirm\n");
            if (empty($current) || empty($newpass) || empty($confirm)) {
                echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
                exit;
            }
            if ($newpass !== $confirm) {
                echo json_encode(['status' => 'error', 'message' => 'New password and confirm password do not match.']);
                exit;
            }
            $query = "SELECT * FROM tbluser WHERE iUserid = ?";
            $stmt = $link->prepare($query);
            $stmt->bind_param('i', $userid);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
        
            if ($user) {
                if (password_verify($current, $user['sPassword_hash'])) {
              
                    $newPasswordHash = password_hash($newpass, PASSWORD_DEFAULT);
        
                    $updateQuery = "UPDATE tbluser SET sPassword_hash = ? WHERE iUserid = ?";
                    $updateStmt = $link->prepare($updateQuery);
                    $updateStmt->bind_param('si', $newPasswordHash, $userid);
        
                    if ($updateStmt->execute()) {
                        echo json_encode(['status' => 'success', 'message' => 'Password updated successfully.']);
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'Error updating password. Please try again.']);
                    }
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect.']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'User not found.']);
            }
        }else

        if ($inputData['action'] == 'forgetPassword') {

            $email = isset($inputData['email']) ? $inputData['email'] : null;
            if (empty($email)) {
                echo json_encode(['status' => 'error', 'message' => 'Email is required.']);
                exit;
            }
        
            // Query to check if the user exists in the database
            $query = "SELECT * FROM tbluser WHERE sEmail = ?";
            $stmt = $link->prepare($query);
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
        
            if ($user) {
                // Set the password to a fixed value '123456'
                $password = "12345678";  // Fixed password
        
                // Hash the password before saving it to the database
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Hashing the fixed password
        
                // Update the user's password in the database
                $updateQuery = "UPDATE tbluser SET sPassword_hash = ? WHERE sEmail = ?";
                $updateStmt = $link->prepare($updateQuery);
                $updateStmt->bind_param('ss', $hashedPassword, $email);
        
                if ($updateStmt->execute()) {
                    // Send password to WhatsApp (if needed)
                    $phoneNumber = $user['sPhone'];
                    // sendPasswordToWhatsApp($phoneNumber, $password); // Uncomment this line to send the password to WhatsApp
        
                    echo json_encode(['status' => 'success', 'message' => 'Password has been reset. The new password has been sent to your WhatsApp.']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Error updating password. Please try again later.']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Email not found in our records.']);
            }

                //         function sendPasswordToWhatsApp($phoneNumber, $newPassword) {
        //         $apiUrl = "https://api.whatsapp.com/send?token=&phone=$phoneNumber&text=" . urlencode("Your new password is: $newPassword");
        //         $ch = curl_init($apiUrl);
        //         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //         curl_setopt($ch, CURLOPT_HEADER, 0);
        //         $response = curl_exec($ch);
        //         curl_close($ch);
            
        
        //         if ($response) {
            
        //             file_put_contents('whatsapp_log.txt', "Message sent successfully to $phoneNumber: $response\n", FILE_APPEND);
        //         } else {
                
        //             file_put_contents('whatsapp_log.txt', "Error sending message to $phoneNumber\n", FILE_APPEND);
        //         }
        //  }   
        }






        // api for Status data


        else
    
        if ($inputData['action'] == "addstatus") {
            if (!empty($inputData['statushere'])) {
        
                $statushere = mysqli_real_escape_string($link, $inputData['statushere']);
               
                $query = "INSERT INTO tblstatus (sStatus) VALUES (?)";
                $stmt = mysqli_prepare($link, $query);
                mysqli_stmt_bind_param($stmt, "s", $statushere);
                $ret = mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
        
                if (!$ret) {
                    sendResponse("error", "Data Not Saved: " . mysqli_error($link));
                } else {
                    sendResponse("success", "Data Saved Successfully");
                }
    
            }
            
            }

           else
                if ($inputData['action'] == 'fngetliststatus') {
                  
                    $query = "SELECT * FROM tblstatus";
                    $result = mysqli_query($link, $query);
            
                    if ($result) {
                        $statuses = mysqli_fetch_all($result, MYSQLI_ASSOC);
                        echo json_encode(['status' => 'success', 'data' => $statuses]);
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'No statuses found']);
                    }
                } 



                else     if($inputData['action']=="updatestatus"){
                    if (!empty($inputData['id']) &&!empty($inputData['statushere']) ) {
                        // Sanitize the user input
                        $id = mysqli_real_escape_string($link, $inputData['id']);
                        $statushere = mysqli_real_escape_string($link, $inputData['statushere']);
                     
                        $query = "update tblstatus set sStatus=? where iStatusid = ?";
                        $stmt = mysqli_prepare($link,$query);
                        mysqli_stmt_bind_param($stmt, "si", $statushere,$id);
                        $ret = mysqli_stmt_execute($stmt);
                        mysqli_stmt_close($stmt);
                
                        // exit();
                
                        if(!$ret){
                            sendResponse("error", "Data Not updated: " . mysqli_error($link));
                        }else{
                            sendResponse("success", "Data updated Successfully");
            
                        }
            
                        return;   
                    }
            
                    }

                    else  if($inputData['action']=="getstatusbyid"){
                        $output="";
                        if (!empty($inputData['id']) ) {
                           
                            $id = mysqli_real_escape_string($link, $inputData['id']);
                
                            $stmt = $link->prepare('select * from tblstatus where iStatusid= ?');
                            $stmt->bind_param('i',$id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            while($row = $result->fetch_assoc()){
                            
                                $output= $row;
                    
                            }
                            echo json_encode(array("data"=>$output));
                        }
                            }

                            else  if($inputData['action']=="deletestatus"){
                                if (!empty($inputData['id']) ) {
                                $id = mysqli_real_escape_string($link, $inputData['id']);
                                   
                            $query = "DELETE from tblstatus where iStatusid	= ?";
                             $stmt = mysqli_prepare($link,$query);
                             mysqli_stmt_bind_param($stmt, "i", $inputData['id']);
                             $ret = mysqli_stmt_execute($stmt);
                             mysqli_stmt_close($stmt);
                            
                            if(!$ret){
                                sendResponse("error", "Data Not deleted: " . mysqli_error($link));
                             }else{
                                 sendResponse("success", "Data deleted Successfully");
                        
                              }
                            
                                }
                                
                                }



                                //here is contact type page api 



                                else
    
                                if ($inputData['action'] == "addcontact") {
                                    if (!empty($inputData['contact'])) {
                                
                                 $contact = mysqli_real_escape_string($link, $inputData['contact']);
                                       
                                 $query = "INSERT INTO tblcontacttype (sContact) VALUES (?)";
                                  $stmt = mysqli_prepare($link, $query);
                                  mysqli_stmt_bind_param($stmt, "s", $contact);
                                 $ret = mysqli_stmt_execute($stmt);
                                 mysqli_stmt_close($stmt);
                                
                                 if (!$ret) {
                                      sendResponse("error", "Data Not Saved: " . mysqli_error($link));
                                  } else {
                                 sendResponse("success", "Data Saved Successfully");
                                  }
                            
                                    }
                                    
                                    }
                        
                                   else
                                  if ($inputData['action'] == 'fngetlistcontact') {
                                          
                                     $query = "SELECT * FROM tblcontacttype";
                                      $result = mysqli_query($link, $query);
                                    
                                      if ($result) {
                                          $statuses = mysqli_fetch_all($result, MYSQLI_ASSOC);
                                         echo json_encode(['status' => 'success', 'data' => $statuses]);
                                     } else {
                                         echo json_encode(['status' => 'error', 'message' => 'No contact found']);
                                            }
                                        } 
                        
                        
                        
                                        else     if($inputData['action']=="updatecontact"){
                                      if (!empty($inputData['id']) &&!empty($inputData['contact']) ) {
                                              // Sanitize the user input
                                          $id = mysqli_real_escape_string($link, $inputData['id']);
                                          $contact = mysqli_real_escape_string($link, $inputData['contact']);
                                             
                                         $query = "update tblcontacttype set sContact=? where iContactid = ?";
                                         $stmt = mysqli_prepare($link,$query);
                                         mysqli_stmt_bind_param($stmt, "si", $contact,$id);
                                         $ret = mysqli_stmt_execute($stmt);
                                         mysqli_stmt_close($stmt);
                                        
                                                // exit();
                                        
                                         if(!$ret){
                                             sendResponse("error", "Data Not updated: " . mysqli_error($link));
                                        }else{
                                           sendResponse("success", "Data updated Successfully");
                                    
                                          }
                                    
                                          return;   
                                     }
                                    
                                            }
                        
                                    else  if($inputData['action']=="getcontactbyid"){
                                     $output="";
                                     if (!empty($inputData['id']) ) {
                                                   
                                     $id = mysqli_real_escape_string($link, $inputData['id']);
                                        
                                      $stmt = $link->prepare('select * from tblcontacttype where iContactid = ?');
                                       $stmt->bind_param('i',$id);
                                     $stmt->execute();
                                      $result = $stmt->get_result();
                                  while($row = $result->fetch_assoc()){
                                                    
                            $output= $row;
                                        
                                  }
                             echo json_encode(array("data"=>$output));
                                  }
                                                    }

                             else  if($inputData['action']=="deletecontact"){
                                  if (!empty($inputData['id']) ) {
                                  $id = mysqli_real_escape_string($link, $inputData['id']);
                                                           
                                     $query = "DELETE from tblcontacttype where iContactid = ?";
                                  $stmt = mysqli_prepare($link,$query);
                                     mysqli_stmt_bind_param($stmt, "i", $inputData['id']);
                                     $ret = mysqli_stmt_execute($stmt);
                                      mysqli_stmt_close($stmt);
                                                    
                                           if(!$ret){
                                         sendResponse("error", "Data Not deleted: " . mysqli_error($link));
                                           }else{
                                    sendResponse("success", "Data deleted Successfully");
                                     }
                                                    
                                                        }
                                                        
                                                 }  
    
                                                 // api for sources


                                  else
    
                                if ($inputData['action'] == "addsources") {
                               if (!empty($inputData['sources'])) {
                                                 
                             $sources = mysqli_real_escape_string($link, $inputData['sources']);
                                                        
                              $query = "INSERT INTO tblsources (Ssources) VALUES (?)";
                                  $stmt = mysqli_prepare($link, $query);
                                  mysqli_stmt_bind_param($stmt, "s", $sources);
                                  $ret = mysqli_stmt_execute($stmt);
                                   mysqli_stmt_close($stmt);
                                                 
                                    if (!$ret) {
                                      sendResponse("error", "Data Not Saved: " . mysqli_error($link));
                                       } else {
                                         sendResponse("success", "Data Saved Successfully");
                                                   }
                                             
                                                     }
                                                     
                                                     }
                                         
                                     else
                                  if ($inputData['action'] == 'fngetlistsources') {
                                                           
                                    $query = "SELECT * FROM tblsources";
                                    $result = mysqli_query($link, $query);
                                                     
                                     if ($result) {
                                    $statuses = mysqli_fetch_all($result, MYSQLI_ASSOC);
                                    echo json_encode(['status' => 'success', 'data' => $statuses]);
                                         } else {
                                     echo json_encode(['status' => 'error', 'message' => 'No sources found']);
                                               }
                                                         } 
                                         
                                         
                                         
                                 else     if($inputData['action']=="updatesources"){
                                 if (!empty($inputData['id']) &&!empty($inputData['sources']) ) {
                                                               // Sanitize the user input
                                  $id = mysqli_real_escape_string($link, $inputData['id']);
                                     $sources = mysqli_real_escape_string($link, $inputData['sources']);
                                                              
                                 $query = "update tblsources set sSources=? where iSourceid = ?";
                                $stmt = mysqli_prepare($link,$query);
                                mysqli_stmt_bind_param($stmt, "si", $sources,$id);
                                $ret = mysqli_stmt_execute($stmt);
                                  mysqli_stmt_close($stmt);
                                                         
                                                                 // exit();
                                                         
                                     if(!$ret){
                                     sendResponse("error", "Data Not updated: " . mysqli_error($link));
                                       }else{
                                  sendResponse("success", "Data updated Successfully");
                                                     
                                          }
                                                     
                                      return;   
                                     }
                                                     
                                    }
                                         
                      else  if($inputData['action']=="getsourcesbyid"){
                                       $output="";
                                      if (!empty($inputData['id']) ) {
                                                                    
                              $id = mysqli_real_escape_string($link, $inputData['id']);
                                                         
                                  $stmt = $link->prepare('select * from tblsources where iSourceid = ?');
                                $stmt->bind_param('i',$id);
                                $stmt->execute();
                                 $result = $stmt->get_result();
                                 while($row = $result->fetch_assoc()){
                                                                   
                                        $output= $row;
                                                         
                                          }
                                             echo json_encode(array("data"=>$output));
                                              }
                                            }
                                         
                                     else  if($inputData['action']=="deletesources"){
                                     if (!empty($inputData['id']) ) {
                                      $id = mysqli_real_escape_string($link, $inputData['id']);
                                                                            
                                        $query = "DELETE from tblsources where iSourceid = ?";
                                        $stmt = mysqli_prepare($link,$query);
                                         mysqli_stmt_bind_param($stmt, "i", $inputData['id']);
                                      $ret = mysqli_stmt_execute($stmt);
                                         mysqli_stmt_close($stmt);
                                                                     
                                           if(!$ret){
                                          sendResponse("error", "Data Not deleted: " . mysqli_error($link));
                                           }else{
                                             sendResponse("success", "Data deleted Successfully");
                                                                 
                                            }
                                                                     
                                          }
                                                                         
                                         } 


              //api for  pripority  
              
              
              else
    
              if ($inputData['action'] == "addpriority") {
             if (!empty($inputData['priority'])) {
                               
           $priority = mysqli_real_escape_string($link, $inputData['priority']);
                                      
            $query = "INSERT INTO tblpriority (sPrioritylevel) VALUES (?)";
                $stmt = mysqli_prepare($link, $query);
                mysqli_stmt_bind_param($stmt, "s", $priority);
                $ret = mysqli_stmt_execute($stmt);
                 mysqli_stmt_close($stmt);
                               
                  if (!$ret) {
                    sendResponse("error", "Data Not Saved: " . mysqli_error($link));
                     } else {
                       sendResponse("success", "Data Saved Successfully");
                                 }
                           
                                   }
                                   
                                   }
                       
                   else
                if ($inputData['action'] == 'fngetlistpriority') {
                                         
                  $query = "SELECT * FROM tblpriority";
                  $result = mysqli_query($link, $query);
                                   
                   if ($result) {
                  $statuses = mysqli_fetch_all($result, MYSQLI_ASSOC);
                  echo json_encode(['status' => 'success', 'data' => $statuses]);
                       } else {
                   echo json_encode(['status' => 'error', 'message' => 'No priority found']);
                             }
                        } 
                       
                       
                       
               else     if($inputData['action']=="updatepriority"){
               if (!empty($inputData['id']) &&!empty($inputData['priority']) ) {
                                             // Sanitize the user input
                $id = mysqli_real_escape_string($link, $inputData['id']);
                   $priority = mysqli_real_escape_string($link, $inputData['priority']);
                                            
               $query = "update tblpriority set sPrioritylevel=? where id = ?";
              $stmt = mysqli_prepare($link,$query);
              mysqli_stmt_bind_param($stmt, "si", $priority,$id);
              $ret = mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                                       
                                               // exit();
                                       
                   if(!$ret){
                   sendResponse("error", "Data Not updated: " . mysqli_error($link));
                     }else{
                sendResponse("success", "Data updated Successfully");
                                   
                        }
                                   
                    return;   
                   }
                                   
                     }
                       
             else  if($inputData['action']=="getprioritybyid"){
                     $output="";
                    if (!empty($inputData['id']) ) {
                                                  
            $id = mysqli_real_escape_string($link, $inputData['id']);
                                       
                $stmt = $link->prepare('select * from tblpriority where id = ?');
              $stmt->bind_param('i',$id);
              $stmt->execute();
               $result = $stmt->get_result();
               while($row = $result->fetch_assoc()){
                                                 
                      $output= $row;
                                       
                        }
                           echo json_encode(array("data"=>$output));
                            }
                          }
                       
                   else  if($inputData['action']=="deletepriority"){
                   if (!empty($inputData['id']) ) {
                    $id = mysqli_real_escape_string($link, $inputData['id']);
                                                          
                      $query = "DELETE from tblpriority where id = ?";
                      $stmt = mysqli_prepare($link,$query);
                       mysqli_stmt_bind_param($stmt, "i", $inputData['id']);
                    $ret = mysqli_stmt_execute($stmt);
                       mysqli_stmt_close($stmt);
                                                   
                         if(!$ret){
                        sendResponse("error", "Data Not deleted: " . mysqli_error($link));
                         }else{
                           sendResponse("success", "Data deleted Successfully");
                                               
                          }
                                                   
                        }
                                                       
                       } 




         //api for communication     
         
         
         else
    
         if ($inputData['action'] == "addcommunication") {
        if (!empty($inputData['communication'])) {
                          
      $communication = mysqli_real_escape_string($link, $inputData['communication']);
                                 
       $query = "INSERT INTO tblcommunication(sCommunication) VALUES (?)";
           $stmt = mysqli_prepare($link, $query);
           mysqli_stmt_bind_param($stmt, "s", $communication);
           $ret = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
                          
             if (!$ret) {
               sendResponse("error", "Data Not Saved: " . mysqli_error($link));
                } else {
                  sendResponse("success", "Data Saved Successfully");
                            }
                      
                              }
                              
                              }
                  
              else
           if ($inputData['action'] == 'fngetlistcommunication') {
                                    
             $query = "SELECT * FROM tblcommunication";
             $result = mysqli_query($link, $query);
                              
              if ($result) {
             $statuses = mysqli_fetch_all($result, MYSQLI_ASSOC);
             echo json_encode(['status' => 'success', 'data' => $statuses]);
                  } else {
              echo json_encode(['status' => 'error', 'message' => 'No communication found']);
                        }
                                  } 
                  
                  
                  
          else     if($inputData['action']=="updatecommunication"){
          if (!empty($inputData['id']) &&!empty($inputData['communication']) ) {
                                        // Sanitize the user input
           $id = mysqli_real_escape_string($link, $inputData['id']);
              $communication = mysqli_real_escape_string($link, $inputData['communication']);
                                       
          $query = "update tblcommunication set sCommunication=? where id = ?";
         $stmt = mysqli_prepare($link,$query);
         mysqli_stmt_bind_param($stmt, "si", $communication,$id);
         $ret = mysqli_stmt_execute($stmt);
           mysqli_stmt_close($stmt);
                                  
                                          // exit();
                                  
              if(!$ret){
              sendResponse("error", "Data Not updated: " . mysqli_error($link));
                }else{
           sendResponse("success", "Data updated Successfully");
                              
                   }
                              
               return;   
              }
                              
                }
                  
        else  if($inputData['action']=="getcommunicationbyid"){
                $output="";
               if (!empty($inputData['id']) ) {
                                             
       $id = mysqli_real_escape_string($link, $inputData['id']);
                                  
           $stmt = $link->prepare('select * from tblcommunication where id = ?');
         $stmt->bind_param('i',$id);
         $stmt->execute();
          $result = $stmt->get_result();
          while($row = $result->fetch_assoc()){
                                            
                 $output= $row;
                                  
                   }
                      echo json_encode(array("data"=>$output));
                       }
                     }
                  
              else  if($inputData['action']=="deletecommunication"){
              if (!empty($inputData['id']) ) {
               $id = mysqli_real_escape_string($link, $inputData['id']);
                                                     
                        $query = "DELETE from tblcommunication where id = ?";
                        $stmt = mysqli_prepare($link,$query);
                        mysqli_stmt_bind_param($stmt, "i", $inputData['id']);
                         $ret = mysqli_stmt_execute($stmt);
                        mysqli_stmt_close($stmt);
                                                    
                            if(!$ret){
                        sendResponse("error", "Data Not deleted: " . mysqli_error($link));
                            }else{
                            sendResponse("success", "Data deleted Successfully");
                                                
                            }
                                                    
                        }
                                                  
                  } 


       //api for category name 
       else
    
       if ($inputData['action'] == "addcategory") {
      if (!empty($inputData['category'])) {
                        
    $category = mysqli_real_escape_string($link, $inputData['category']);
                               
    $parent_id = !empty($inputData['parent_id']) ? intval($inputData['parent_id']) : null;

$query = "INSERT INTO tblcategoryname(sCategoryname, parent_id) VALUES (?, ?)";
$stmt = mysqli_prepare($link, $query);
mysqli_stmt_bind_param($stmt, "si", $category, $parent_id);

         $ret = mysqli_stmt_execute($stmt);
          mysqli_stmt_close($stmt);
                        
           if (!$ret) {
             sendResponse("error", "Data Not Saved: " . mysqli_error($link));
              } else {
                sendResponse("success", "Data Saved Successfully");
                          }
                    
                            }
                            
                            }
                
            else
         if ($inputData['action'] == 'fngetlistcategory') {
                                  
           $query = "SELECT * FROM tblcategoryname";
           $result = mysqli_query($link, $query);
                            
            if ($result) {
           $statuses = mysqli_fetch_all($result, MYSQLI_ASSOC);
           echo json_encode(['status' => 'success', 'data' => $statuses]);
                } else {
            echo json_encode(['status' => 'error', 'message' => 'No category found']);
                      }
                                } 
                
                
                
        else     if($inputData['action']=="updatecategory"){
        if (!empty($inputData['id']) &&!empty($inputData['category']) ) {
                                      // Sanitize the user input
         $id = mysqli_real_escape_string($link, $inputData['id']);
            $category = mysqli_real_escape_string($link, $inputData['category']);
                                     
      $parent_id = !empty($inputData['parent_id']) ? intval($inputData['parent_id']) : null;

$query = "UPDATE tblcategoryname SET sCategoryname=?, parent_id=? WHERE id=?";
$stmt = mysqli_prepare($link, $query);
mysqli_stmt_bind_param($stmt, "sii", $category, $parent_id, $id);

       $ret = mysqli_stmt_execute($stmt);
         mysqli_stmt_close($stmt);
                                
                                        // exit();
                                
            if(!$ret){
            sendResponse("error", "Data Not updated: " . mysqli_error($link));
              }else{
         sendResponse("success", "Data updated Successfully");
                            
                 }
                            
             return;   
            }
                            
              }
                
      else  if($inputData['action']=="getcategorybyid"){
              $output="";
             if (!empty($inputData['id']) ) {
                                           
     $id = mysqli_real_escape_string($link, $inputData['id']);
                                
         $stmt = $link->prepare('select * from tblcategoryname where id = ?');
       $stmt->bind_param('i',$id);
       $stmt->execute();
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
                                          
               $output= $row;
                                
                 }
                    echo json_encode(array("data"=>$output));
                     }
                   }
                
            else  if($inputData['action']=="deletecategory"){
            if (!empty($inputData['id']) ) {
             $id = mysqli_real_escape_string($link, $inputData['id']);
                                                   
                      $query = "DELETE from tblcategoryname where id = ?";
                      $stmt = mysqli_prepare($link,$query);
                      mysqli_stmt_bind_param($stmt, "i", $inputData['id']);
                       $ret = mysqli_stmt_execute($stmt);
                      mysqli_stmt_close($stmt);
                                                  
                          if(!$ret){
                      sendResponse("error", "Data Not deleted: " . mysqli_error($link));
                          }else{
                          sendResponse("success", "Data deleted Successfully");
                                              
                          }
                                                  
                      }
                                                
                }      
                
                
                //api for department 



                else
    
                if ($inputData['action'] == "adddepartment") {
               if (!empty($inputData['department'])) {
                                 
             $department = mysqli_real_escape_string($link, $inputData['department']);
                                        
              $query = "INSERT INTO tbldepartment(sDepartment) VALUES (?)";
                  $stmt = mysqli_prepare($link, $query);
                  mysqli_stmt_bind_param($stmt, "s", $department);
                  $ret = mysqli_stmt_execute($stmt);
                   mysqli_stmt_close($stmt);
                                 
                    if (!$ret) {
                      sendResponse("error", "Data Not Saved: " . mysqli_error($link));
                       } else {
                         sendResponse("success", "Data Saved Successfully");
                                   }
                             
                                     }
                                     
                                     }
                         
                     else
                  if ($inputData['action'] == 'fngetlistdepartment') {
                                           
                    $query = "SELECT * FROM tbldepartment";
                    $result = mysqli_query($link, $query);
                                     
                     if ($result) {
                    $statuses = mysqli_fetch_all($result, MYSQLI_ASSOC);
                    echo json_encode(['status' => 'success', 'data' => $statuses]);
                         } else {
                     echo json_encode(['status' => 'error', 'message' => 'No department found']);
                               }
                                         } 
                         
                         
                         
                 else     if($inputData['action']=="updatedepartment"){
                 if (!empty($inputData['id']) &&!empty($inputData['department']) ) {
                                               // Sanitize the user input
                  $id = mysqli_real_escape_string($link, $inputData['id']);
                     $department = mysqli_real_escape_string($link, $inputData['department']);
                                              
                 $query = "update tbldepartment set sDepartment=? where iDepid = ?";
                $stmt = mysqli_prepare($link,$query);
                mysqli_stmt_bind_param($stmt, "si", $department,$id);
                $ret = mysqli_stmt_execute($stmt);
                  mysqli_stmt_close($stmt);
                                         
                                                 // exit();
                                         
                     if(!$ret){
                     sendResponse("error", "Data Not updated: " . mysqli_error($link));
                       }else{
                  sendResponse("success", "Data updated Successfully");
                                     
                          }
                                     
                      return;   
                     }
                                     
                       }
                         
               else  if($inputData['action']=="getdepartmentbyid"){
                       $output="";
                      if (!empty($inputData['id']) ) {
                                                    
              $id = mysqli_real_escape_string($link, $inputData['id']);
                                         
                  $stmt = $link->prepare('select * from tbldepartment where iDepid= ?');
                $stmt->bind_param('i',$id);
                $stmt->execute();
                 $result = $stmt->get_result();
                 while($row = $result->fetch_assoc()){
                                                   
                        $output= $row;
                                         
                          }
                             echo json_encode(array("data"=>$output));
                              }
                            }
                         
                     else  if($inputData['action']=="deletedepartment"){
                        
                     if (!empty($inputData['id']) ) {
                      $id = mysqli_real_escape_string($link, $inputData['id']);
                                                            
                               $query = "DELETE from tbldepartment where iDepid= ?";
                               $stmt = mysqli_prepare($link,$query);
                               mysqli_stmt_bind_param($stmt, "i", $inputData['id']);
                                $ret = mysqli_stmt_execute($stmt);
                               mysqli_stmt_close($stmt);
                                                           
                                   if(!$ret){
                               sendResponse("error", "Data Not deleted: " . mysqli_error($link));
                                   }else{
                                   sendResponse("success", "Data deleted Successfully");
                                                       
                                   }
                                                           
                               }
                                                         
                                  } 


                         //api for tasktype



                         else
    
                         if ($inputData['action'] == "addtask") {
                        if (!empty($inputData['task'])) {
                                          
                      $task = mysqli_real_escape_string($link, $inputData['task']);
                                                 
                       $query = "INSERT INTO tbltasktype(sTasktype) VALUES (?)";
                           $stmt = mysqli_prepare($link, $query);
                           mysqli_stmt_bind_param($stmt, "s", $task);
                           $ret = mysqli_stmt_execute($stmt);
                            mysqli_stmt_close($stmt);
                                          
                             if (!$ret) {
                               sendResponse("error", "Data Not Saved: " . mysqli_error($link));
                                } else {
                                  sendResponse("success", "Data Saved Successfully");
                                            }
                                      
                                              }
                                              
                                              }
                                  
                              else
                           if ($inputData['action'] == 'fngetlisttask') {
                                                    
                             $query = "SELECT * FROM tbltasktype";
                             $result = mysqli_query($link, $query);
                                              
                              if ($result) {
                             $statuses = mysqli_fetch_all($result, MYSQLI_ASSOC);
                             echo json_encode(['status' => 'success', 'data' => $statuses]);
                                  } else {
                              echo json_encode(['status' => 'error', 'message' => 'No task found']);
                                        }
                                                  } 
                                  
                                  
                                  
                          else     if($inputData['action']=="updatetask"){
                          if (!empty($inputData['id']) &&!empty($inputData['task']) ) {
                                                        // Sanitize the user input
                     $id = mysqli_real_escape_string($link, $inputData['id']);
                        $task = mysqli_real_escape_string($link, $inputData['task']);
                                                       
                        $query = "update tbltasktype set sTasktype=? where id = ?";
                        $stmt = mysqli_prepare($link,$query);
                         mysqli_stmt_bind_param($stmt, "si", $task,$id);
                         $ret = mysqli_stmt_execute($stmt);
                           mysqli_stmt_close($stmt);
                                                  
                                                          // exit();
                                                  
                              if(!$ret){
                              sendResponse("error", "Data Not updated: " . mysqli_error($link));
                                }else{
                           sendResponse("success", "Data updated Successfully");
                                              
                                   }
                                              
                               return;   
                              }
                                              
                                }
                                  
                        else  if($inputData['action']=="gettaskbyid"){
                                $output="";
                               if (!empty($inputData['id']) ) {
                                                             
                       $id = mysqli_real_escape_string($link, $inputData['id']);
                                                  
                           $stmt = $link->prepare('select * from tbltasktype where id = ?');
                         $stmt->bind_param('i',$id);
                         $stmt->execute();
                          $result = $stmt->get_result();
                          while($row = $result->fetch_assoc()){
                                                            
                                 $output= $row;
                                                  
                                   }
                                      echo json_encode(array("data"=>$output));
                                       }
                                     }
                                  
                              else  if($inputData['action']=="deletetask"){
                              if (!empty($inputData['id']) ) {
                               $id = mysqli_real_escape_string($link, $inputData['id']);
                                                                     
                                        $query = "DELETE from tbltasktype where id = ?";
                                        $stmt = mysqli_prepare($link,$query);
                                        mysqli_stmt_bind_param($stmt, "i", $inputData['id']);
                                         $ret = mysqli_stmt_execute($stmt);
                                        mysqli_stmt_close($stmt);
                                                                    
                                            if(!$ret){
                                        sendResponse("error", "Data Not deleted: " . mysqli_error($link));
                                            }else{
                                            sendResponse("success", "Data deleted Successfully");
                                                                
                                            }
                                                                    
                                        }
                                                                  
                                  } 

                            // api for customers 
                                  else
    
                                  if ($inputData['action'] == "addcustomer") {
                                    $companyname = mysqli_real_escape_string($link, $inputData['companyname']);
                                    $email = mysqli_real_escape_string($link, $inputData['email']);
                                    $billing = mysqli_real_escape_string($link, $inputData['billing']);
                                    $shipping = mysqli_real_escape_string($link, $inputData['shipping']);
                                    $customertype = mysqli_real_escape_string($link, $inputData['customertype']);
                                    $industry = mysqli_real_escape_string($link, $inputData['industry']);
                                    $manager = mysqli_real_escape_string($link, $inputData['manager']);
                                    $sources = mysqli_real_escape_string($link, $inputData['sources']);
                                    $tags = mysqli_real_escape_string($link, $inputData['tags']);
                                    $statusname = mysqli_real_escape_string($link, $inputData['statusname']);
                                    $notes = mysqli_real_escape_string($link, $inputData['notes']);
                                
                                    // Validate if any required customer fields are empty
                                    if (empty($companyname) || empty($email) || empty($billing) || empty($shipping) || empty($customertype) || empty($industry) || empty($manager) || empty($sources) || empty($tags) || empty($statusname) || empty($notes)) {
                                        echo json_encode([
                                            'status' => 'error',
                                            'message' => 'Please fill in all customer information.'
                                        ]);
                                        exit;
                                    }
                                

                                    // $checkEmailQuery = "SELECT COUNT(*) FROM tblcustomer WHERE sEmail = '$email'";
                                    // $result = mysqli_query($link, $checkEmailQuery);
                                    // $row = mysqli_fetch_array($result);
                                
                                    // if ($row[0] > 0) {
                                    //     echo json_encode([
                                    //         'status' => 'error',
                                    //         'message' => 'The email address is already registered.'
                                    //     ]);
                                    //     exit;
                                    // }
                                    // Insert customer data into tblcustomer
                                    $customerQuery = "INSERT INTO tblcustomer (sCompanyname, sEmail, sBillingaddress, sShippingaddress, sCustomertype, sIndustrytype, iUserid, iSourceid, sTags, sStatus, sNotes)
                                                      VALUES ('$companyname', '$email', '$billing', '$shipping', '$customertype', '$industry', $manager, $sources, '$tags', '$statusname', '$notes')";
                                
                                    if (mysqli_query($link, $customerQuery)) {
                                        // Get the last inserted customer ID
                                        $customerId = mysqli_insert_id($link);
                                
                                        // Insert contact data into tblcontact
                                        $contactNames = $inputData['contactNames'];
                                        $referredContacts = $inputData['referredContacts'];
                                        $contactEmails = $inputData['contactEmails'];
                                        $phones = $inputData['phones'];
                                        $departments = $inputData['departments'];
                                
                                        // Loop through each contact and insert into tblcontact
                                      foreach ($contactNames as $index => $contactname) {
                                        // Ensure all arrays have the same length before proceeding
                                        if (!isset($referredContacts[$index]) || !isset($contactEmails[$index]) || !isset($phones[$index]) || !isset($departments[$index])) {
                                            echo json_encode([
                                                'status' => 'error',
                                                'message' => 'Mismatch in contact data.'
                                            ]);
                                            exit;
                                        }
                                        $referredcontact = $referredContacts[$index];
                                        $contactemail = $contactEmails[$index];
                                        $phone = $phones[$index];
                                        $department = $departments[$index];

                                        // Sanitize contact data
                                        $contactname = mysqli_real_escape_string($link, $contactname);
                                        $referredcontact = mysqli_real_escape_string($link, $referredcontact);
                                        $contactemail = mysqli_real_escape_string($link, $contactemail);
                                        $phone = mysqli_real_escape_string($link, $phone);
                                        $department = mysqli_real_escape_string($link, $department);

                                        // Insert contact data into tblcontact
                                        $contactQuery = "INSERT INTO tblcontact (iCustomerid, sContactname, iContactid, sEmail, sPhone, sDepartment)
                                                        VALUES ($customerId, '$contactname', '$referredcontact', '$contactemail', '$phone', '$department')";

                                        if (!mysqli_query($link, $contactQuery)) {
                                            // If there's an error inserting the contact
                                            echo json_encode([
                                                'status' => 'error',
                                                'message' => 'Error adding contact: ' . mysqli_error($link)
                                            ]);
                                            exit;
                                        }
                                    }
                                        // Success response
                                        echo json_encode([
                                            'status' => 'success',
                                            'message' => 'Customer and contacts added successfully.'
                                        ]);
                                    } else {
                                        // If there's an error inserting the customer
                                        echo json_encode([
                                            'status' => 'error',
                                            'message' => 'Error adding customer: ' . mysqli_error($link)
                                        ]);
                                    }
                                
                                  
                                    mysqli_close($link);
                                } 


                                else if ($inputData['action'] == "updatecustomer") {
                                    $customerId = isset($inputData['customerId']) ? (int)$inputData['customerId'] : 0;
                                
                                    // Check if customerId is valid
                                    if ($customerId == 0) {
                                        echo json_encode(['status' => 'error', 'message' => 'Invalid customer ID']);
                                        exit;
                                    }
                                
                                    $companyname = mysqli_real_escape_string($link, $inputData['companyname']);
                                    $email = mysqli_real_escape_string($link, $inputData['email']);
                                    $billing = mysqli_real_escape_string($link, $inputData['billing']);
                                    $shipping = mysqli_real_escape_string($link, $inputData['shipping']);
                                    $customertype = mysqli_real_escape_string($link, $inputData['customertype']);
                                    $industry = mysqli_real_escape_string($link, $inputData['industry']);
                                    $manager = mysqli_real_escape_string($link, $inputData['manager']);
                                    $sources = mysqli_real_escape_string($link, $inputData['sources']);
                                    $tags = mysqli_real_escape_string($link, $inputData['tags']);
                                    $statusname = mysqli_real_escape_string($link, $inputData['statusname']);
                                    $notes = mysqli_real_escape_string($link, $inputData['notes']);
                                
                                    // Check if the email is already used by another customer (excluding the current one)
                                    // $checkEmailQuery = "SELECT COUNT(*) FROM tblcustomer WHERE sEmail = '$email' AND iCustomerid != $customerId";
                                    // $result = mysqli_query($link, $checkEmailQuery);
                                    // $row = mysqli_fetch_array($result);
                                
                                    // if ($row[0] > 0) {
                                    //     echo json_encode([
                                    //         'status' => 'error',
                                    //         'message' => 'The email address is already registered for another customer.'
                                    //     ]);
                                    //     exit;
                                    // }
                                
                              
                                    $customerQuery = "UPDATE tblcustomer 
                                                      SET sCompanyname = '$companyname', sEmail = '$email', sBillingaddress = '$billing', sShippingaddress = '$shipping', 
                                                          sCustomertype = '$customertype', sIndustrytype = '$industry', iUserid = $manager, iSourceid = $sources, 
                                                          sTags = '$tags', sStatus = '$statusname', sNotes = '$notes' 
                                                      WHERE iCustomerid = $customerId";
                                
                                    if (mysqli_query($link, $customerQuery)) {
                                 
                                        $contactNames = $inputData['contactNames'];
                                        $referredContacts = $inputData['referredContacts'];
                                        $contactEmails = $inputData['contactEmails'];
                                        $phones = $inputData['phones'];
                                        $departments = $inputData['departments'];

                                      
                                            $deleteContactQuery = "DELETE FROM tblcontact WHERE iCustomerid = $customerId ";
                                            if (!mysqli_query($link, $deleteContactQuery)) {
                                                // If there's an error deleting the contact
                                                echo json_encode([
                                                    'status' => 'error',
                                                    'message' => 'Error deleting contact: ' . mysqli_error($link)
                                                ]);
                                                exit;
                                            }
                                        
                                
                                        foreach ($contactNames as $index => $contactname) {
                                            $referredcontact = $referredContacts[$index];
                                            $contactemail = $contactEmails[$index];
                                            $phone = $phones[$index];
                                            $department = $departments[$index];
                                
                                        
                                            $contactname = mysqli_real_escape_string($link, $contactname);
                                            $referredcontact = mysqli_real_escape_string($link, $referredcontact);
                                            $contactemail = mysqli_real_escape_string($link, $contactemail);
                                            $phone = mysqli_real_escape_string($link, $phone);
                                            $department = mysqli_real_escape_string($link, $department);
                                
                                          
                                            $checkContactQuery = "SELECT COUNT(*) FROM tblcontact WHERE iCustomerid = $customerId";
                                            $contactResult = mysqli_query($link, $checkContactQuery);
                                            $contactRow = mysqli_fetch_array($contactResult);
                                
                                        
                                
                                     
                                            $insertContactQuery = "INSERT INTO tblcontact (iCustomerid, sContactname, iContactid, sEmail, sPhone, sDepartment) 
                                                                   VALUES ($customerId, '$contactname', '$referredcontact', '$contactemail', '$phone', '$department')";
                                
                                            if (!mysqli_query($link, $insertContactQuery)) {
                                          
                                                echo json_encode([
                                                    'status' => 'error',
                                                    'message' => 'Error inserting contact: ' . mysqli_error($link)
                                                ]);
                                                exit;
                                            }
                                        }
                                
                                        // Success response
                                        echo json_encode([
                                            'status' => 'success',
                                            'message' => 'Customer and contacts updated successfully.'
                                        ]);
                                    } else {
                                        // If there's an error updating the customer
                                        echo json_encode([
                                            'status' => 'error',
                                            'message' => 'Error updating customer: ' . mysqli_error($link)
                                        ]);
                                    }
                                
                                    // Close the database connection
                                    mysqli_close($link);
                                }
                                


                    else
                    if ($inputData['action'] == 'fngetlistcustomer') {
                                             
                      $query = "SELECT * FROM tblcustomer";
                      $result = mysqli_query($link, $query);
                                       
                       if ($result) {
                      $statuses = mysqli_fetch_all($result, MYSQLI_ASSOC);
                      echo json_encode(['status' => 'success', 'data' => $statuses]);
                           } else {
                       echo json_encode(['status' => 'error', 'message' => 'No customer found']);
                                 }

                                 
                    }
                                           
                                           

                                           
                else if($inputData['action'] == "deletecustomer") {
                    if (!empty($inputData['id'])) {
                        $id = mysqli_real_escape_string($link, $inputData['id']);
                
                      
                        mysqli_begin_transaction($link);
                        
                       
                        $query1 = "DELETE FROM tblcontact WHERE iCustomerid = ?";
                        $stmt1 = mysqli_prepare($link, $query1);
                        mysqli_stmt_bind_param($stmt1, "i", $inputData['id']);
                        $ret1 = mysqli_stmt_execute($stmt1);
                        mysqli_stmt_close($stmt1);
                
                   
                        $query2 = "DELETE FROM tblcustomer WHERE iCustomerid=?";
                        $stmt2 = mysqli_prepare($link, $query2);
                        mysqli_stmt_bind_param($stmt2, "i", $inputData['id']);
                        $ret2 = mysqli_stmt_execute($stmt2);
                        mysqli_stmt_close($stmt2);
                
                       
                        if ($ret1 && $ret2) {
                        
                            mysqli_commit($link);
                            sendResponse("success", "Data deleted successfully.");
                        } else {
                          
                            mysqli_rollback($link);
                            sendResponse("error", "Data not deleted: " . mysqli_error($link));
                        }
                    }
                }
                 

                                  else if ($inputData['action'] == 'getcustomerbyid') {
                                  
                                    $customerId = mysqli_real_escape_string($link, $inputData['customerId']);
                                
                                 
                                    $query = "SELECT * FROM tblcustomer WHERE iCustomerid = $customerId";
                                    $result = mysqli_query($link, $query);
                                
                                    if ($result) {
                                        $customer = null;
                                        $contacts = [];
                               
                                        if ($row = mysqli_fetch_assoc($result)) {
                                            $customer = $row;  
                                
                                     
                                            $query1 = "SELECT * FROM tblcontact WHERE iCustomerid = $customerId";
                                            $result1 = mysqli_query($link, $query1);
                                            
                                            if ($result1) {
                                              
                                                while ($row1 = mysqli_fetch_assoc($result1)) {
                                                    $contacts[] = $row1;
                                                }
                                               
                                                $customer['contacts'] = $contacts;
                                            } else {
                                              
                                                echo json_encode(['status' => 'error', 'message' => 'Error fetching contacts: ' . mysqli_error($link)]);
                                                exit;
                                            }
                                        }
                                
                                        // Return the customer data with contacts
                                        echo json_encode([
                                            'status' => 'success',
                                            'customer' => $customer
                                        ]);
                                    } else {
                                        // Error in customer query
                                        echo json_encode(['status' => 'error', 'message' => 'No customer found or query error']);
                                    }
                                
                                    // Close the database connection
                                    mysqli_close($link);
                                
                                } 
                                
                 else if ($inputData['action'] === 'getContactTypes') {
                    // Query the tblcontacttype or related table to fetch the contact types
                    $stmt = $link->prepare('SELECT iContactid, sContact FROM tblcontacttype ORDER BY iContactid');
                    $stmt->execute();
                    $result = $stmt->get_result();
                
                    // Prepare an array to hold contact types
                    $contactTypes = [];
                
                    while ($row = $result->fetch_assoc()) {
                        $contactTypes[] = [
                            'id' => $row['iContactid'],
                            'name' => $row['sContact']
                        ];
                    }
                
                    // Return the contact types as JSON
                    echo json_encode([
                        'status' => 'success',
                        'contactTypes' => $contactTypes
                    ]);
                }                   
         
    //             else if ($inputData['action'] == 'addlead') {
    //     $sLead_name = $_POST['sLead_name'] ?? '';
    //     $sEmail = $_POST['sEmail'] ?? '';
    //     $sPhone = $_POST['sPhone'] ?? '';
    //     $sAlternate_phone = $_POST['sAlternate_phone'] ?? '';
    //     $sLead_source = $_POST['sLead_source'] ?? '';
    //     $sLead_status = $_POST['sLead_status'] ?? '';
    //     $sLead_priority = $_POST['sLead_priority'] ?? '';
    //     $sLead_type = $_POST['sLead_type'] ?? '';
    //     $sCompany_name = $_POST['sCompany_name'] ?? '';
    //     $sIndustry_type = $_POST['sIndustry_type'] ?? '';
    //     $sDesignation = $_POST['sDesignation'] ?? '';
    //     $sWebsite = $_POST['sWebsite'] ?? '';
    //     $sLocation = $_POST['sLocation'] ?? '';
    //     $sAddress = $_POST['sAddress'] ?? '';
    //     $sAssigned_to = $_POST['sAssigned_to'] ?? '';
    //     $sLead_owner = $_POST['sLead_owner'] ?? '';
    //     $sPreferred_communication = $_POST['sPreferred_communication'] ?? '';
    //     $sTags = $_POST['sTags'] ?? '';
    //     $sContactperson = $_POST['sContactperson'] ?? '';

    //     // Decode JSON arrays from POST strings
    //     $sProductnames = isset($_POST['products']) ? json_decode($_POST['products'], true) : [];
    //     $sQuantities = isset($_POST['quantities']) ? json_decode($_POST['quantities'], true) : [];
    //     $sRates = isset($_POST['rates']) ? json_decode($_POST['rates'], true) : [];

    //     // Check upload directory exists
    //     $uploadDir = "uploads/";
    //     if (!is_dir($uploadDir)) {
    //         mkdir($uploadDir, 0777, true);
    //     }

    //     // Process file uploads
    //     $filePaths = ["", "", ""]; // For fileUpload, fileUpload2, fileUpload3
    //     $fileFields = ['fileUpload', 'fileUpload2', 'fileUpload3'];

    //     foreach ($fileFields as $index => $field) {
    //         if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
    //             $ext = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
    //             $filename = time() . "_" . $index . "." . $ext;
    //             $targetFile = $uploadDir . $filename;

    //             if (move_uploaded_file($_FILES[$field]['tmp_name'], $targetFile)) {
    //                 $filePaths[$index] = $targetFile;
    //             } else {
    //                 sendResponse("error", "Failed to move uploaded file: " . $field);
    //             }
    //         }
    //     }

    //     // Insert lead into tblleads
    //     $query = "INSERT INTO tblleads (
    //         sLead_name, sEmail, sPhone, sAlternate_phone, sLead_source, sLead_status, sLead_priority, sLead_type, 
    //         sCompany_name, sIndustry_type, sDesignation, sWebsite, sLocation, sAddress, sAssigned_to, sLead_owner, 
    //         sPreferred_communication, sTags, sContactperson, sFileupload, sFileupload2, sFileupload3
    //     ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    //     $stmt = mysqli_prepare($link, $query);
    //     if (!$stmt) {
    //         sendResponse("error", "Database error (prepare): " . mysqli_error($link));
    //     }

    //     mysqli_stmt_bind_param($stmt, "ssssssssssssssssssssss", 
    //         $sLead_name, $sEmail, $sPhone, $sAlternate_phone, $sLead_source, $sLead_status, $sLead_priority, $sLead_type,
    //         $sCompany_name, $sIndustry_type, $sDesignation, $sWebsite, $sLocation, $sAddress, $sAssigned_to, $sLead_owner,
    //         $sPreferred_communication, $sTags, $sContactperson, $filePaths[0], $filePaths[1], $filePaths[2]
    //     );

    //     $ret = mysqli_stmt_execute($stmt);
    //     if (!$ret) {
    //         sendResponse("error", "Database error (execute): " . mysqli_stmt_error($stmt));
    //     }

    //     $lead_id = mysqli_insert_id($link);
    //     mysqli_stmt_close($stmt);

    //     // Insert product leads
    //     foreach ($sProductnames as $index => $product) {
    //         $quantity = $sQuantities[$index] ?? '';
    //         $rate = $sRates[$index] ?? '';

    //         $query1 = "INSERT INTO tblproductleads (sProductname, sQuantity, sRate, lead_id) VALUES (?, ?, ?, ?)";
    //         $stmt1 = mysqli_prepare($link, $query1);
    //         if (!$stmt1) {
    //             sendResponse("error", "Database error (prepare product): " . mysqli_error($link));
    //         }
    //         mysqli_stmt_bind_param($stmt1, "sssi", $product, $quantity, $rate, $lead_id);
    //         if (!mysqli_stmt_execute($stmt1)) {
    //             sendResponse("error", "Database error (execute product): " . mysqli_stmt_error($stmt1));
    //         }
    //         mysqli_stmt_close($stmt1);
    //     }

    //     sendResponse("success", "Customer added successfully.");
    // } 
                
                
                else if ($inputData['action'] == 'updatelead') {
                    // Sanitize and collect lead data
                    $leadId = $inputData['leadId'];
                    $sLead_name = $inputData['sLead_name'];
                    $sEmail = $inputData['sEmail'];
                    $sPhone = $inputData['sPhone'];
                    $sAlternate_phone = $inputData['sAlternate_phone'];
                    $sLead_source = $inputData['sLead_source'];
                    $sLead_status = $inputData['sLead_status'];
                    $sLead_priority = $inputData['sLead_priority'];
                    $sLead_type = $inputData['sLead_type'];
                    $sCompany_name = $inputData['sCompany_name'];
                    $sIndustry_type = $inputData['sIndustry_type'];
                    $sDesignation = $inputData['sDesignation'];
                    $sWebsite = $inputData['sWebsite'];
                    $sLocation = $inputData['sLocation'];
                    $sAddress = $inputData['sAddress'];
                    $sAssigned_to = $inputData['sAssigned_to'];
                    $sLead_owner = $inputData['sLead_owner'];
                    $sPreferred_communication = $inputData['sPreferred_communication'];
                    $sTags = $inputData['sTags'];
              
                    $sContactperson=$inputData['sContactperson'];
                
                    // The product details (Arrays)
                    $sProductnames = $inputData['products'];
                    $sQuantities = $inputData['quantities'];
                    $sRates = $inputData['rates'];
                
                    // Update lead data in tblleads
                    $query = "UPDATE tblleads SET 
                        sLead_name = ?, sEmail = ?, sPhone = ?, sAlternate_phone = ?, sLead_source = ?, sLead_status = ?, sLead_priority = ?, sLead_type = ?, 
                        sCompany_name = ?, sIndustry_type = ?, sDesignation = ?, sWebsite = ?, sLocation = ?, sAddress = ?, sAssigned_to = ?, sLead_owner = ?, 
                         sPreferred_communication = ?, sTags = ?,sContactperson=?
                        WHERE iLead_id = ?";
                
                    $stmt = mysqli_prepare($link, $query);
                    mysqli_stmt_bind_param($stmt, "sssssssssssssssssssi", 
                        $sLead_name, $sEmail, $sPhone, $sAlternate_phone, $sLead_source, $sLead_status, $sLead_priority, $sLead_type, 
                        $sCompany_name, $sIndustry_type, $sDesignation, $sWebsite, $sLocation, $sAddress, $sAssigned_to, $sLead_owner, 
                       $sPreferred_communication,  $sTags,$sContactperson, $leadId
                    );
                
                    $ret = mysqli_stmt_execute($stmt);
                
                    // Check if the update was successful
                    if (!$ret) {
                        sendResponse("error", "Error updating the lead. Please try again.");
                    } else {
                        // Clear existing product details for this lead
                        $queryDelete = "DELETE FROM tblproductleads WHERE lead_id = ?";
                        $stmtDelete = mysqli_prepare($link, $queryDelete);
                        mysqli_stmt_bind_param($stmtDelete, 'i', $leadId);
                        mysqli_stmt_execute($stmtDelete);
                        mysqli_stmt_close($stmtDelete);
                
                        // Insert updated product details into tblproductleads
                        foreach ($sProductnames as $index => $product) {
                            $quantity = (string)$sQuantities[$index];  // Ensure it's a string
                            $rate = (string)$sRates[$index];           // Ensure it's a string
                    
                            // Prepare query to insert into tblproductleads
                            $query1 = "INSERT INTO tblproductleads (sProductname, sQuantity, sRate, lead_id) VALUES (?, ?, ?, ?)";
                            $stmt1 = mysqli_prepare($link, $query1);
                            mysqli_stmt_bind_param($stmt1, "sssi", $product, $quantity, $rate, $leadId);
                            
                            // Check if query executed successfully
                            if (!mysqli_stmt_execute($stmt1)) {
                                echo "Error inserting product into tblproductleads: " . mysqli_error($link);
                            }
                    
                            mysqli_stmt_close($stmt1);
                        }
                    
                        sendResponse("success", "Lead and products updated successfully.");
                    }
                }
                

            else if ($inputData['action'] == 'fngetlistlead') {
    $fromDate = !empty($inputData['fromDate']) ? $inputData['fromDate'] : null;
    $toDate = !empty($inputData['toDate']) ? $inputData['toDate'] : null;
    $assignedTo = !empty($inputData['assignedTo']) ? $inputData['assignedTo'] : null;

    $conditions = [];
    if ($fromDate) {
        $conditions[] = "DATE(l.sCreated_date) >= '" . mysqli_real_escape_string($link, $fromDate) . "'";
    }
    if ($toDate) {
        $conditions[] = "DATE(l.sCreated_date) <= '" . mysqli_real_escape_string($link, $toDate) . "'";
    }
    if ($assignedTo) {
        $conditions[] = "l.sAssigned_to = '" . intval($assignedTo) . "'";
    }

    $whereClause = count($conditions) > 0 ? 'WHERE ' . implode(' AND ', $conditions) : '';

    $query = "
        SELECT 
            l.*, 
            u.sName AS assigned_to_name,
            GROUP_CONCAT(p.sProductname SEPARATOR ', ') AS product_names,
            GROUP_CONCAT(c.sCategoryname SEPARATOR ', ') AS product_categories
        FROM tblleads l
        LEFT JOIN tbluser u ON l.sAssigned_to = u.iUserid
        LEFT JOIN tblproductleads pl ON pl.lead_id = l.iLead_id
        LEFT JOIN tblproduct p ON pl.sProductname = p.iProductid
        LEFT JOIN tblcategoryname c ON p.iParentid = c.id
        $whereClause
        GROUP BY l.iLead_id
    ";

    $result = mysqli_query($link, $query);
    if ($result) {
        $statuses = mysqli_fetch_all($result, MYSQLI_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $statuses]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No customer found']);
    }
}

else if ($inputData['action'] == 'myleads') {
    $id = (int)$inputData['id'];

    $query = "
        SELECT 
            l.iLead_id,
            l.sCompany_name,
            l.sLead_name,
            l.sContactperson,
            l.sCreated_date,
            pr.sPrioritylevel,

            -- ✅ Status: Replay first (rs.sStatus), fallback to lead (ls.sStatus)
            COALESCE(rs.sStatus, ls.sStatus) AS lead_status_text,

            p.pid,
            p.sQuantity,
            p.sRate,
            (p.sQuantity * p.sRate) AS total_amount,
            prod.sProductname,
            cat.sCategoryname

        FROM tblleads l
        LEFT JOIN tblproductleads p ON l.iLead_id = p.lead_id
        LEFT JOIN tblproduct prod ON p.sProductname = prod.iProductid
        LEFT JOIN tblcategoryname cat ON p.category = cat.id
        LEFT JOIN tblpriority pr ON l.sLead_priority = pr.id

        -- ✅ Lead status name
        LEFT JOIN tblstatus ls ON l.sLead_status = ls.iStatusid

        -- ✅ Latest replay for each lead
        LEFT JOIN (
            SELECT r1.*
            FROM tblreplayleads r1
            INNER JOIN (
                SELECT lead_id, MAX(sCreatedTimestamp) AS MaxDate
                FROM tblreplayleads
                GROUP BY lead_id
            ) r2 ON r1.lead_id = r2.lead_id AND r1.sCreatedTimestamp = r2.MaxDate
        ) r ON l.iLead_id = r.lead_id

        -- ✅ Replay status name (if available)
        LEFT JOIN tblstatus rs ON r.sStatus = rs.iStatusid

        WHERE 
            l.sAssigned_to = $id 
            AND COALESCE(rs.sStatus, ls.sStatus) NOT IN ('Order Lost', 'Cold Lead', 'Closed')

        ORDER BY l.sContactperson ASC
    ";

    $result = mysqli_query($link, $query);

    if (!$result) {
        echo json_encode(['status' => 'error', 'message' => 'SQL error: ' . mysqli_error($link)]);
        exit;
    }

    $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
    $leads = [];

    foreach ($rows as $row) {
        $leadId = $row['iLead_id'];

        if (!isset($leads[$leadId])) {
            $leads[$leadId] = [
                'iLead_id' => $leadId,
                'sCompany_name' => $row['sCompany_name'],
                'sLead_name' => $row['sLead_name'],
                'sContactperson' => $row['sContactperson'],
                'sPrioritylevel' => $row['sPrioritylevel'],
                'sLead_status' => $row['lead_status_text'], // ✅ uses replay status if available
                'sCreated_date' => $row['sCreated_date'],
                'products' => [],
                'productNames' => [],
                'categoryNames' => []
            ];
        }

        $productName = !empty($row['sProductname']) ? $row['sProductname'] : '';
        $categoryName = !empty($row['sCategoryname']) ? $row['sCategoryname'] : '';

        $leads[$leadId]['productNames'][] = $productName;
        $leads[$leadId]['categoryNames'][] = $categoryName;

        $leads[$leadId]['products'][] = [
            'pid' => $row['pid'],
            'sProductname' => $productName,
            'sCategoryname' => $categoryName,
            'sRate' => $row['sRate'],
            'sQuantity' => $row['sQuantity'],
            'total_amount' => $row['total_amount']
        ];
    }

    foreach ($leads as $leadId => $lead) {
        $leads[$leadId]['productNames'] = implode(', ', $lead['productNames']);
        $leads[$leadId]['categoryNames'] = implode(', ', $lead['categoryNames']);
    }

    echo json_encode(['status' => 'success', 'data' => array_values($leads)]);
}


else if ($inputData['action'] == 'leadownerleads') {
    $id = (int)$inputData['id'];

   
      $query = "
    SELECT 
        l.iLead_id,
        l.sCompany_name,
        l.sLead_name,
        l.sContactperson,
        l.sCreated_date,
        pr.sPrioritylevel,
        COALESCE(NULLIF(r.sStatus, ''), l.sLead_status) AS lead_status_id,
        stat.sStatus AS lead_status_text,
        p.pid,
        p.sQuantity,
        p.sRate,
        (p.sQuantity * p.sRate) AS total_amount,
        prod.sProductname,
        cat.sCategoryname
    FROM tblleads l
    LEFT JOIN (
        SELECT r1.lead_id, r1.sStatus
        FROM tblreplayleads r1
        INNER JOIN (
            SELECT lead_id, MAX(sCreatedTimestamp) AS latest_ts
            FROM tblreplayleads
            GROUP BY lead_id
        ) r2 ON r1.lead_id = r2.lead_id AND r1.sCreatedTimestamp = r2.latest_ts
    ) r ON r.lead_id = l.iLead_id
    LEFT JOIN tblstatus stat ON stat.iStatusid = COALESCE(NULLIF(r.sStatus, ''), l.sLead_status)
    LEFT JOIN tblproductleads p ON l.iLead_id = p.lead_id
    LEFT JOIN tblproduct prod ON p.sProductname = prod.iProductid
    LEFT JOIN tblcategoryname cat ON p.category = cat.id
    LEFT JOIN tblpriority pr ON l.sLead_priority = pr.id
    WHERE 
        l.sLead_owner = $id
        AND stat.sStatus NOT IN ('Order Lost', 'Cold Lead', 'Closed')
    ORDER BY l.sContactperson ASC
";

    

    $result = mysqli_query($link, $query);

    if (!$result) {
        echo json_encode(['status' => 'error', 'message' => 'SQL error: ' . mysqli_error($link)]);
        exit;
    }

    $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
    $leads = [];

    foreach ($rows as $row) {
        $leadId = $row['iLead_id'];

        if (!isset($leads[$leadId])) {
            $leads[$leadId] = [
                'iLead_id' => $leadId,
                'sCompany_name' => $row['sCompany_name'],
                'sLead_name' => $row['sLead_name'],
                'sContactperson' => $row['sContactperson'],
                'sPrioritylevel' => $row['sPrioritylevel'],
                'sLead_status' => $row['lead_status_text'],
                'sCreated_date' => $row['sCreated_date'],
                'products' => [],
                'productNames' => [],
                'categoryNames' => []
            ];
        }

        $productName = !empty($row['sProductname']) ? $row['sProductname'] : '';
        $categoryName = !empty($row['sCategoryname']) ? $row['sCategoryname'] : '';

        $leads[$leadId]['productNames'][] = $productName;
        $leads[$leadId]['categoryNames'][] = $categoryName;

        $leads[$leadId]['products'][] = [
            'pid' => $row['pid'],
            'sProductname' => $productName,
            'sCategoryname' => $categoryName,
            'sRate' => $row['sRate'],
            'sQuantity' => $row['sQuantity'],
            'total_amount' => $row['total_amount']
        ];
    }

    foreach ($leads as $leadId => $lead) {
        $leads[$leadId]['productNames'] = implode(', ', $lead['productNames']);
        $leads[$leadId]['categoryNames'] = implode(', ', $lead['categoryNames']);
    }

    echo json_encode(['status' => 'success', 'data' => array_values($leads)]);
}


/* ================================================================
 *  leads_by_status  (expects numeric status_id, returns latest‑status leads)
 * ============================================================== */
elseif ($inputData['action'] === 'leads_by_status') {

    session_start();
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status'=>'error','message'=>'Unauthorized']);
        exit;
    }
    $user_id   = (int)$_SESSION['user_id'];
    $status_id = isset($inputData['status']) ? (int)$inputData['status'] : 0;

    if ($status_id <= 0) {
        echo json_encode(['status'=>'error','message'=>'Invalid status']);
        exit;
    }
// Fetch status name
$status_name = '';
$status_stmt = $link->prepare("SELECT sStatus FROM tblstatus WHERE iStatusid = ?");
$status_stmt->bind_param("i", $status_id);
$status_stmt->execute();
$status_result = $status_stmt->get_result();
if ($status_row = $status_result->fetch_assoc()) {
    $status_name = $status_row['sStatus'];
}

    
    $sql = "
      SELECT
        l.iLead_id, l.sCompany_name, l.sLead_name, l.sContactperson,
        l.sCreated_date, pr.sPrioritylevel,
        p.pid, SUM(p.sQuantity) AS total_quantity, p.sRate,
        (SUM(p.sQuantity)*p.sRate) AS total_amount,
        prod.sProductname, cat.sCategoryname
      FROM tblleads l
      LEFT JOIN (
        SELECT r1.lead_id, r1.sStatus
        FROM tblreplayleads r1
        INNER JOIN (
          SELECT lead_id, MAX(sCreatedTimestamp) AS latest_ts
          FROM tblreplayleads GROUP BY lead_id
        ) r2 ON r1.lead_id = r2.lead_id AND r1.sCreatedTimestamp = r2.latest_ts
      ) r ON r.lead_id = l.iLead_id
      LEFT JOIN tblproductleads p  ON l.iLead_id = p.lead_id
      LEFT JOIN tblproduct prod    ON p.pid      = prod.iProductid
      LEFT JOIN tblcategoryname cat ON prod.iParentid = cat.id
      LEFT JOIN tblpriority pr     ON l.sLead_priority = pr.id
      WHERE l.sAssigned_to = ? AND COALESCE(NULLIF(r.sStatus, ''), l.sLead_status) = ?
      GROUP BY l.iLead_id, p.pid
      ORDER BY l.sContactperson ASC
    ";

    $stmt = $link->prepare($sql);
    $stmt->bind_param("ii",$user_id,$status_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows) {
        $leads=[];
        while($row=$res->fetch_assoc()){
            $id=$row['iLead_id'];
            if(!isset($leads[$id])){
                $leads[$id]=[
                  'iLead_id'=>$id,
                  'sCompany_name'=>$row['sCompany_name'],
                  'sLead_name'=>$row['sLead_name'],
                  'sContactperson'=>$row['sContactperson'],
                  'sPrioritylevel'=>$row['sPrioritylevel'],
                  'sCreated_date'=>$row['sCreated_date'],
                  'products'=>[]
                ];
            }
            $leads[$id]['products'][]=[
              'pid'=>$row['pid'],
              'sProductname'=>$row['sProductname'],
              'sCategoryname'=>$row['sCategoryname'],
              'total_quantity'=>$row['total_quantity'],
              'sRate'=>$row['sRate'],
              'total_amount'=>$row['total_amount']
            ];
        }
      echo json_encode([
    'status' => 'success',
    'data' => array_values($leads),
    'status_name' => $status_name
]);

    } else {
        echo json_encode(['status'=>'error','message'=>'No leads found for this status']);
    }
}


// -------------------------------------------------------------
// Leads by Status for Specific User (AJAX, expects userid & status)
// -------------------------------------------------------------
elseif ($inputData['action'] === 'leads_by_status_user') {
    session_start();
    // Validate session
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status'=>'error','message'=>'Unauthorized']);
        exit;
    }

    // Validate and sanitize input
    $user_id = isset($inputData['userid']) ? (int)$inputData['userid'] : (int)$_SESSION['user_id'];
    $status_id = isset($inputData['status']) ? (int)$inputData['status'] : 0;

    if ($user_id <= 0) {
        echo json_encode(['status'=>'error','message'=>'Invalid user']);
        exit;
    }
    if ($status_id <= 0) {
        echo json_encode(['status'=>'error','message'=>'Invalid status']);
        exit;
    }

    // Fetch status name
    $status_name = '';
    $status_stmt = $link->prepare("SELECT sStatus FROM tblstatus WHERE iStatusid = ?");
    $status_stmt->bind_param("i", $status_id);
    $status_stmt->execute();
    $status_result = $status_stmt->get_result();
    if ($status_row = $status_result->fetch_assoc()) {
        $status_name = $status_row['sStatus'];
    }
    $status_stmt->close();

    // Query leads for the given user and status
    $sql = "
      SELECT
        l.iLead_id, l.sCompany_name, l.sLead_name, l.sContactperson,
        l.sCreated_date, pr.sPrioritylevel,
        p.pid, SUM(p.sQuantity) AS total_quantity, p.sRate,
        (SUM(p.sQuantity)*p.sRate) AS total_amount,
        prod.sProductname, cat.sCategoryname
      FROM tblleads l
      LEFT JOIN (
        SELECT r1.lead_id, r1.sStatus
        FROM tblreplayleads r1
        INNER JOIN (
          SELECT lead_id, MAX(sCreatedTimestamp) AS latest_ts
          FROM tblreplayleads GROUP BY lead_id
        ) r2 ON r1.lead_id = r2.lead_id AND r1.sCreatedTimestamp = r2.latest_ts
      ) r ON r.lead_id = l.iLead_id
      LEFT JOIN tblproductleads p  ON l.iLead_id = p.lead_id
      LEFT JOIN tblproduct prod    ON p.pid      = prod.iProductid
      LEFT JOIN tblcategoryname cat ON prod.iParentid = cat.id
      LEFT JOIN tblpriority pr     ON l.sLead_priority = pr.id
      WHERE l.sAssigned_to = ? AND COALESCE(NULLIF(r.sStatus, ''), l.sLead_status) = ?
      GROUP BY l.iLead_id, p.pid
      ORDER BY l.sContactperson ASC
    ";

    $stmt = $link->prepare($sql);
    if (!$stmt) {
        echo json_encode(['status'=>'error','message'=>'SQL error: ' . $link->error]);
        exit;
    }
    $stmt->bind_param("ii", $user_id, $status_id);
    $stmt->execute();
    $res = $stmt->get_result();

    $leads = [];
    if ($res && $res->num_rows) {
        while ($row = $res->fetch_assoc()) {
            $id = $row['iLead_id'];
            if (!isset($leads[$id])) {
                $leads[$id] = [
                    'iLead_id'      => $id,
                    'sCompany_name' => $row['sCompany_name'],
                    'sLead_name'    => $row['sLead_name'],
                    'sContactperson'=> $row['sContactperson'],
                    'sPrioritylevel'=> $row['sPrioritylevel'],
                    'sCreated_date' => $row['sCreated_date'],
                    'products'      => []
                ];
            }
            $leads[$id]['products'][] = [
                'pid'           => $row['pid'],
                'sProductname'  => $row['sProductname'],
                'sCategoryname' => $row['sCategoryname'],
                'total_quantity'=> $row['total_quantity'],
                'sRate'         => $row['sRate'],
                'total_amount'  => $row['total_amount']
            ];
        }
        echo json_encode([
            'status'      => 'success',
            'data'        => array_values($leads),
            'status_name' => $status_name
        ]);
    } else {
        echo json_encode(['status'=>'error','message'=>'No leads found for this status']);
    }
    $stmt->close();
}

// else
//              if ($inputData['action'] == 'addreplay') {
//     $description = $inputData['description'];
//     $followUpDate = $inputData['followupdate'];
//     $statusLead = $inputData['statusleadss'];
//     $leadId = $inputData['leadId'];

//     $uploadDir = 'uploads/';
    

//    $fileFields = ['fileUpload', 'fileUpload2', 'fileUpload3'];
// $filePaths = array_fill(0, 3, "");

// foreach ($fileFields as $index => $field) {
//     if (isset($_FILES[$field]) && $_FILES[$field]['error'] === 0) {
//         $fileTmpName = $_FILES[$field]['tmp_name'];
//         $fileName = uniqid() . "_" . basename($_FILES[$field]['name']); 
//         $targetFile = "uploads/" . $fileName;

//         if (move_uploaded_file($fileTmpName, $targetFile)) {
//             $filePaths[$index] = $targetFile;
//         } else {
//             error_log("Error moving file: " . $_FILES[$field]['name']);
//         }
//     }
// }

// error_log("Stored File Paths: " . print_r($filePaths, true));




//     // Insert replay data into the database
//   $query = "INSERT INTO tblreplayleads (lead_id, description, follow_up_date, status, sFileupload, sFileupload2, sFileupload3) 
//           VALUES (?, ?, ?, ?, ?, ?, ?)";

// $stmt = $link->prepare($query);
// $stmt->bind_param('issssss', $leadId, $description, $followUpDate, $statusLead, $filePaths[0], $filePaths[1], $filePaths[2]);

// if ($stmt->execute()) {
//     echo json_encode(["status" => "success", "message" => "Replay added successfully."]);
// } else {
//     error_log("Database Insert Error: " . $stmt->error);
//     echo json_encode(["status" => "error", "message" => "Failed to add replay."]);
// }

// }
else if ($inputData['action'] == 'assignedLeads') {
    $id = (int)$inputData['user_id'];

    $query = "
        SELECT 
            l.iLead_id,
            l.sCompany_name,
            l.sLead_name,
            l.sContactperson,
            pr.sPrioritylevel,
            
            -- ✅ Final status (replay > fallback to lead status)
            COALESCE(rs.sStatus, ls.sStatus) AS lead_status_text,

            p.pid,
            SUM(p.sQuantity) AS total_quantity,
            p.sRate,
            (SUM(p.sQuantity) * p.sRate) AS total_amount

        FROM tblleads l
        LEFT JOIN tblproductleads p ON l.iLead_id = p.lead_id
        LEFT JOIN tblpriority pr ON l.sLead_priority = pr.id

        -- ✅ Fallback: original lead status
        LEFT JOIN tblstatus ls ON l.sLead_status = ls.iStatusid

        -- ✅ Latest replay for each lead
        LEFT JOIN (
            SELECT r1.*
            FROM tblreplayleads r1
            INNER JOIN (
                SELECT lead_id, MAX(sCreatedTimestamp) AS MaxDate
                FROM tblreplayleads
                GROUP BY lead_id
            ) r2 ON r1.lead_id = r2.lead_id AND r1.sCreatedTimestamp = r2.MaxDate
        ) r ON l.iLead_id = r.lead_id

        -- ✅ Replay status
        LEFT JOIN tblstatus rs ON r.sStatus = rs.iStatusid

        WHERE 
            l.sAssigned_to = '$id'
          

        GROUP BY l.iLead_id, p.pid
        ORDER BY l.sContactperson ASC
    ";

    $result = mysqli_query($link, $query);

    if (!$result) {
        echo json_encode(['status' => 'error', 'message' => 'SQL Error: ' . mysqli_error($link)]);
        exit;
    }

    $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
    $leads = [];

    foreach ($rows as $row) {
        $leadId = $row['iLead_id'];

        if (!isset($leads[$leadId])) {
            $leads[$leadId] = [
                'iLead_id' => $leadId,
                'sCompany_name' => $row['sCompany_name'],
                'sLead_name' => $row['sLead_name'],
                'sContactperson' => $row['sContactperson'],
                'sPrioritylevel' => $row['sPrioritylevel'],
                'status' => $row['lead_status_text'],  // ✅ Final status
                'products' => []
            ];
        }

        $leads[$leadId]['products'][] = [
            'pid' => $row['pid'],
            'total_quantity' => $row['total_quantity'],
            'sRate' => $row['sRate'],
            'total_amount' => $row['total_amount']
        ];
    }

    echo json_encode(['status' => 'success', 'data' => array_values($leads)]);
}



                
                else  if($inputData['action']=="deletelead"){
                    if (!empty($inputData['id']) ) {
                    $id = mysqli_real_escape_string($link, $inputData['id']);
                       
                $query = "DELETE from tblleads where iLead_id	= ?";
                 $stmt = mysqli_prepare($link,$query);
                 mysqli_stmt_bind_param($stmt, "i", $inputData['id']);
                 $ret = mysqli_stmt_execute($stmt);
                 mysqli_stmt_close($stmt);
                
                if(!$ret){
                    sendResponse("error", "Data Not deleted: " . mysqli_error($link));
                 }else{
                     sendResponse("success", "Data deleted Successfully");
            
                  }
                
                    }
                    
                    }
                 // Backend logic in api.php

else if ($inputData['action'] == "getleadbyid") {
    $output = "";
    if (!empty($inputData['leadId'])) {
        $leadId = mysqli_real_escape_string($link, $inputData['leadId']);
        
        // Query for the lead data
      $stmt = $link->prepare('
    SELECT l.*, u.sName
    FROM tblleads l 
    LEFT JOIN tbluser u ON l.sCreated_by = u.iUserid 
    WHERE l.iLead_id = ?
');

        $stmt->bind_param('i', $leadId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // Query to get associated product data
            $stmtProducts = $link->prepare('SELECT * FROM tblproductleads WHERE lead_id = ?');
            $stmtProducts->bind_param('i', $leadId);
            $stmtProducts->execute();
            $resultProducts = $stmtProducts->get_result();
            
            // Fetch all associated products
            $products = [];
            while ($productRow = $resultProducts->fetch_assoc()) {
                $products[] = $productRow;
            }
    
            // Fetch all available products (for dropdown options)
            $stmtAllProducts = $link->prepare('SELECT * FROM tblproduct ORDER BY sProductname');
            $stmtAllProducts->execute();
            $resultAllProducts = $stmtAllProducts->get_result();
            $allProducts = [];
            while ($productRow = $resultAllProducts->fetch_assoc()) {
                $allProducts[] = $productRow;
            }

// Fetch all available categories
$stmtCategories = $link->prepare('SELECT * FROM tblcategoryname ORDER BY sCategoryName');
$stmtCategories->execute();
$resultCategories = $stmtCategories->get_result();
$categories = [];
while ($catRow = $resultCategories->fetch_assoc()) {
    $categories[] = $catRow;
}

// Send categories in response
echo json_encode(array(
    "status" => "success", 
  "lead" => $row,
"created_by_display" => $row['sName'] ?? $row['sCreated_by'],

    "products" => $products,
    "allProducts" => $allProducts,
    "categories" => $categories, // ✅ Add this line
    "files" => array(
        "file1" => $row['sFileupload'] ?? '',
        "file2" => $row['sFileupload2'] ?? '',
        "file3" => $row['sFileupload3'] ?? ''
    )
));


        } else {
            echo json_encode(array("status" => "error", "message" => "Lead not found"));
        }
    } else {
        echo json_encode(array("status" => "error", "message" => "Invalid leadId"));
    }
}

                    
          else// === Save Quotation ===
if (isset($inputData['action']) && $inputData['action'] === 'saveQuotation') {

    $quotationId    = $inputData['quotation_id'] ?? 0;
    $quotation_no   = $inputData['quotation_no'];
    $quotation_date = $inputData['quotation_date'];
    $customer_name  = $inputData['customer_name'];
    $leadId         = $inputData['leadId'];
    $total_amount   = $inputData['total_amount'];

    $kind_attn      = $inputData['kind_attn'];
    $mode_transport = $inputData['mode_transport'];
    $gstin          = $inputData['gstin'];
    $pr_refno       = $inputData['pr_refno'];
    $pr_date        = $inputData['pr_date'];
    $delivery       = $inputData['delivery'];
    $payments       = $inputData['payments'];
    $special_note   = $inputData['special_note'];
    $gst_percent    = $inputData['gst_percent'];
    $transport      = $inputData['transport'];
    $transport_insurance = $inputData['transport_insurance'];
    $validity       = $inputData['validity'];

    // Insert quotation
    $stmt = $link->prepare("INSERT INTO tblquotation 
        (quotation_no, leadId, customer_name, quotation_date, total_amount, delivery, payment, sTransport, specialnote, gst, validity, sTransportInsurance, sKindattn, sModeoftransport, sRefno, sDate, sGstin, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

 // s = string, i = integer, d = double/decimal
$stmt->bind_param(
    "sisssssssdsssssss",
    $quotation_no, $leadId, $customer_name, $quotation_date, $total_amount,
    $delivery, $payments, $transport, $special_note, $gst_percent,
    $validity, $transport_insurance, $kind_attn, $mode_transport,
    $pr_refno, $pr_date, $gstin
);


    $stmt->execute();
    $quotationId = $stmt->insert_id;
    $stmt->close();

    // Insert items
    $products  = $inputData['product'];
    $des       = $inputData['des'];
    $quantity  = $inputData['quantity'];
    $rate      = $inputData['rate'];
    $discount  = $inputData['discount'];
    $amount    = $inputData['amount'];
    $after_discount = $inputData['after_discount'];

    $stmt = $link->prepare("INSERT INTO tblquotation_items (quotation_id, product_id, description, quantity, rate, amount, discount, after_discount) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    for ($i=0; $i < count($products); $i++) {
        $stmt->bind_param("iisidddd", $quotationId, $products[$i], $des[$i], $quantity[$i], $rate[$i], $amount[$i], $discount[$i], $after_discount[$i]);
        $stmt->execute();
    }
    $stmt->close();

    echo json_encode(["status"=>"success","message"=>"Quotation saved successfully!","quotation_id"=>$quotationId]);
    exit;
}

// === Update Quotation ===
elseif (isset($inputData['action']) && $inputData['action'] === 'updateQuotation') {

    $quotation_id   = $inputData['quotation_id'] ?? 0;
    $quotation_no   = $inputData['quotation_no'] ?? '';
    $quotation_date = $inputData['quotation_date'] ?? '';
    $customer_name  = $inputData['customer_name'] ?? '';
    $leadId         = $inputData['leadId'] ?? 0;
    $total_amount   = $inputData['total_amount'] ?? 0;

    $kind_attn      = $inputData['kind_attn'] ?? '';
    $mode_transport = $inputData['mode_transport'] ?? '';
    $gstin          = $inputData['gstin'] ?? '';
    $pr_refno       = $inputData['pr_refno'] ?? '';
    $pr_date        = $inputData['pr_date'] ?? '';
    $delivery       = $inputData['delivery'] ?? '';
    $payments       = $inputData['payments'] ?? '';
    $special_note   = $inputData['special_note'] ?? '';
    $gst_percent    = $inputData['gst_percent'] ?? '';
    $transport      = $inputData['transport'] ?? '';
    $transport_insurance = $inputData['transport_insurance'] ?? '';
    $validity       = $inputData['validity'] ?? '';

    $products       = $inputData['product'] ?? [];
    $descriptions   = $inputData['des'] ?? [];
    $quantities     = $inputData['quantity'] ?? [];
    $rates          = $inputData['rate'] ?? [];
    $discounts      = $inputData['discount'] ?? [];
    $amounts        = $inputData['amount'] ?? [];
    $afterDiscounts = $inputData['after_discount'] ?? [];

    // Update quotation header
    $stmt = $link->prepare("
        UPDATE tblquotation 
        SET quotation_no=?, leadId=?, customer_name=?, quotation_date=?, total_amount=?, 
            delivery=?, payment=?, sTransport=?, specialnote=?, gst=?, validity=?, 
            sTransportInsurance=?, sKindattn=?, sModeoftransport=?, 
            sRefno=?, sDate=?, sGstin=? 
        WHERE id=?
    ");
    $stmt->bind_param(
        "sisssssssssssssssi",
        $quotation_no, $leadId, $customer_name, $quotation_date, $total_amount,
        $delivery, $payments, $transport, $special_note, $gst_percent,
        $validity, $transport_insurance, $kind_attn, $mode_transport,
        $pr_refno, $pr_date, $gstin, $quotation_id
    );
    $stmt->execute();
    $stmt->close();

    // Delete old items
    $stmtDel = $link->prepare("DELETE FROM tblquotation_items WHERE quotation_id=?");
    $stmtDel->bind_param("i", $quotation_id);
    $stmtDel->execute();
    $stmtDel->close();

    // Insert new items
    $stmtItem = $link->prepare("
        INSERT INTO tblquotation_items 
        (quotation_id, product_id, description, quantity, rate, amount, discount, after_discount) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    for($i=0; $i < count($products); $i++) {
        if(!empty($products[$i])) {
            $stmtItem->bind_param(
                "iisidddd",
                $quotation_id, $products[$i], $descriptions[$i], $quantities[$i],
                $rates[$i], $amounts[$i], $discounts[$i], $afterDiscounts[$i]
            );
            $stmtItem->execute();
        }
    }
    $stmtItem->close();

    echo json_encode(["status"=>"success","message"=>"Quotation updated successfully"]);
    exit;
}





elseif(isset($inputData['action']) && $inputData['action'] === 'deleteQuotation') {
    $id = $inputData['id'] ?? 0;

    $stmt = $link->prepare("DELETE FROM tblquotation_items WHERE quotation_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $stmt2 = $link->prepare("DELETE FROM tblquotation WHERE id=?");
    $stmt2->bind_param("i", $id);
    if($stmt2->execute()){
        echo json_encode(["status"=>"success","message"=>"Quotation deleted successfully"]);
    } else {
        echo json_encode(["status"=>"error","message"=>$stmt2->error]);
    }
    exit;
}

// -------------------- Get Quotation List --------------------
elseif(isset($inputData['action']) && $inputData['action'] === 'getQuotations') {
    $result = $link->query("SELECT * FROM tblquotation ORDER BY id DESC");
    $quotations = [];
    while($row = $result->fetch_assoc()){
        $quotations[] = $row;
    }
    echo json_encode(["status"=>"success","quotations"=>$quotations]);
    exit;
}

// -------------------- Get Quotation By ID --------------------
elseif(isset($inputData['action']) && $inputData['action'] === 'getQuotationById') {
    $id = $inputData['id'] ?? 0;

    $stmt = $link->prepare("SELECT * FROM tblquotation WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $quotation = $stmt->get_result()->fetch_assoc();

  $stmt2 = $link->prepare("
    SELECT i.*, p.sProductname 
    FROM tblquotation_items i
    LEFT JOIN tblproduct p ON i.product_id = p.iProductid
    WHERE i.quotation_id = ?
");
$stmt2->bind_param("i", $id);
$stmt2->execute();
$items = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);


    $products = [];
    $res = $link->query("SELECT iProductid, sProductname FROM tblproduct");
    while ($row = $res->fetch_assoc()) {
        $products[] = $row;
    }

    echo json_encode([
        "status" => "success",
        "quotation" => $quotation,
        "items" => $items,
        "allProducts" => $products
    ]);
    exit;
}



else if (isset($inputData['action']) && $inputData['action'] === 'getQuotationByLeadId') {
    $leadId = $inputData['leadId'] ?? 0;

    if (!$leadId) {
        echo json_encode(["status" => "error", "message" => "Lead ID is required"]);
        exit;
    }

    // Fetch quotation for given leadId
    $stmt = $link->prepare("SELECT * FROM tblquotation WHERE leadId = ? LIMIT 1");
    $stmt->bind_param("i", $leadId);
    $stmt->execute();
    $result = $stmt->get_result();
    $quotation = $result->fetch_assoc();

    if ($quotation) {
        // Fetch quotation items WITH product name
        $stmt2 = $link->prepare("
            SELECT qi.*, p.sProductname 
            FROM tblquotation_items qi
            LEFT JOIN tblproduct p ON qi.product_id = p.iProductid
            WHERE qi.quotation_id = ?
        ");
        $stmt2->bind_param("i", $quotation['id']);
        $stmt2->execute();
        $itemsResult = $stmt2->get_result();
        $items = $itemsResult->fetch_all(MYSQLI_ASSOC);

        echo json_encode([
            "status"    => "success",
            "quotation" => $quotation,
            "items"     => $items
        ]);
    } else {
        echo json_encode([
            "status"  => "not_found",
            "message" => "No quotation found for this lead"
        ]);
    }

    exit;
}




                    //adding product master


                    else
    
             if ($inputData['action'] == "addproduct") {
    if (!empty($inputData['product'])) {
        $product = mysqli_real_escape_string($link, $inputData['product']);
        $parent_id = !empty($inputData['parent_id']) ? intval($inputData['parent_id']) : NULL;

        $query = "INSERT INTO tblproduct (sProductname, iParentid) VALUES (?, ?)";
        $stmt = mysqli_prepare($link, $query);
        mysqli_stmt_bind_param($stmt, "si", $product, $parent_id);
        $ret = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if (!$ret) {
            sendResponse("error", "Data Not Saved: " . mysqli_error($link));
        } else {
            sendResponse("success", "Data Saved Successfully");
        }
    }
}

                         
                   else if ($inputData['action'] == 'fngetlistproduct') {
    $query = "SELECT p.iProductid, p.sProductname, c.sCategoryname 
              FROM tblproduct p
              LEFT JOIN tblcategoryname c ON p.iParentid = c.id
              ORDER BY p.iParentid DESC";

    $result = mysqli_query($link, $query);

    if ($result) {
        $products = mysqli_fetch_all($result, MYSQLI_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $products]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No product found']);
    }
}

                         
                         
                         
             else if ($inputData['action'] == "updateproduct") {
    if (!empty($inputData['id']) && !empty($inputData['product'])) {
        $id = intval($inputData['id']);
        $product = mysqli_real_escape_string($link, $inputData['product']);
        $parent_id = !empty($inputData['parent_id']) ? intval($inputData['parent_id']) : NULL;

        $query = "UPDATE tblproduct SET sProductname=?, iParentid=? WHERE iProductid=?";
        $stmt = mysqli_prepare($link, $query);
        mysqli_stmt_bind_param($stmt, "sii", $product, $parent_id, $id);
        $ret = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if (!$ret) {
            sendResponse("error", "Data Not updated: " . mysqli_error($link));
        } else {
            sendResponse("success", "Data updated Successfully");
        }
        return;
    }
}

                         
               else  if($inputData['action']=="getproductbyid"){
                       $output="";
                      if (!empty($inputData['id']) ) {
                                                    
              $id = mysqli_real_escape_string($link, $inputData['id']);
                                         
                  $stmt = $link->prepare('select * from tblproduct where iProductid= ?');
                $stmt->bind_param('i',$id);
                $stmt->execute();
                 $result = $stmt->get_result();
                 while($row = $result->fetch_assoc()){
                                                   
                        $output= $row;
                                         
                          }
                             echo json_encode(array("data"=>$output));
                              }
                            }
                         
                     else  if($inputData['action']=="deleteproduct"){
                        
                     if (!empty($inputData['id']) ) {
                      $id = mysqli_real_escape_string($link, $inputData['id']);
                                                            
                               $query = "DELETE from tblproduct where iProductid= ?";
                               $stmt = mysqli_prepare($link,$query);
                               mysqli_stmt_bind_param($stmt, "i", $inputData['id']);
                                $ret = mysqli_stmt_execute($stmt);
                               mysqli_stmt_close($stmt);
                                                           
                                   if(!$ret){
                               sendResponse("error", "Data Not deleted: " . mysqli_error($link));
                                   }else{
                                   sendResponse("success", "Data deleted Successfully");
                                                       
                                   }
                                                           
                               }
                                                         
                                  }
                                  
                                  
                    
  else
    
           if ($inputData['action'] == "addreminder") {
    if (!empty($inputData['description'])) {

          $user_id = $_SESSION['user_id'];
        $description = mysqli_real_escape_string($link, $inputData['description']);
        $date = mysqli_real_escape_string($link, $inputData['date']);
        $iUserid = !empty($inputData['iUserid']) ? intval($inputData['iUserid']) : null;

        if ($iUserid === null) {
            sendResponse("error", "User ID is missing or invalid");
        }

       $query = "INSERT INTO tblreminders (iUserid, sDescription, sDate, sAssigned_by) VALUES (?, ?, ?, ?)";
$stmt = mysqli_prepare($link, $query);

if (!$stmt) {
    sendResponse("error", "Prepare failed: " . mysqli_error($link));
}

mysqli_stmt_bind_param($stmt, "issi", $iUserid, $description, $date, $user_id);
$ret = mysqli_stmt_execute($stmt);

if (!$ret) {
    sendResponse("error", "Execution failed: " . mysqli_stmt_error($stmt));
} else {
    sendResponse("success", "Data Saved Successfully");
}


        mysqli_stmt_close($stmt);
    } else {
        sendResponse("error", "Description is required");
    }
}


                         
              else if ($inputData['action'] == 'fngetlistreminder') {
    // Join tblreminders with tbluser to get user name for each reminder
  $query = "
    SELECT r.*, 
           u.sName AS assigned_to_name, 
           a.sName AS assigned_by_name
    FROM tblreminders r
    LEFT JOIN tbluser u ON r.iUserid = u.iUserid
    LEFT JOIN tbluser a ON r.sAssigned_by = a.iUserid
";


    $result = mysqli_query($link, $query);

    if ($result) {
        $reminders = mysqli_fetch_all($result, MYSQLI_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $reminders]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No reminder found']);
    }
}

                         
                         
                         
    else if ($inputData['action'] == "updatereminder") {
    if (!empty($inputData['id']) && !empty($inputData['description'])) {
        $id = intval($inputData['id']);
        $description = mysqli_real_escape_string($link, $inputData['description']);
        $date = mysqli_real_escape_string($link, $inputData['date']);
        $iUserid = !empty($inputData['iUserid']) ? intval($inputData['iUserid']) : NULL;

        $user_id = $_SESSION['user_id']; // who updated it

        $query = "UPDATE tblreminders SET iUserid=?, sDescription=?, sDate=?, sAssigned_by=? WHERE rrid=?";
        $stmt = mysqli_prepare($link, $query);
        mysqli_stmt_bind_param($stmt, "sssii", $iUserid, $description, $date, $user_id, $id);
        $ret = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if (!$ret) {
            sendResponse("error", "Data Not updated: " . mysqli_error($link));
        } else {
            sendResponse("success", "Data updated Successfully");
        }
        return;
    }
}


                         
               else  if($inputData['action']=="getreminderbyid"){
                       $output="";
                      if (!empty($inputData['id']) ) {
                                                    
              $id = mysqli_real_escape_string($link, $inputData['id']);
                                         
                  $stmt = $link->prepare('select * from tblreminders where rrid= ?');
                $stmt->bind_param('i',$id);
                $stmt->execute();
                 $result = $stmt->get_result();
                 while($row = $result->fetch_assoc()){
                                                   
                        $output= $row;
                                         
                          }
                             echo json_encode(array("data"=>$output));
                              }
                            }
                         
                     else  if($inputData['action']=="deletereminder"){
                        
                     if (!empty($inputData['id']) ) {
                      $id = mysqli_real_escape_string($link, $inputData['id']);
                                                            
                               $query = "DELETE from tblreminders where rrid= ?";
                               $stmt = mysqli_prepare($link,$query);
                               mysqli_stmt_bind_param($stmt, "i", $inputData['id']);
                                $ret = mysqli_stmt_execute($stmt);
                               mysqli_stmt_close($stmt);
                                                           
                                   if(!$ret){
                               sendResponse("error", "Data Not deleted: " . mysqli_error($link));
                                   }else{
                                   sendResponse("success", "Data deleted Successfully");
                                                       
                                   }
                                                           
                               }
                                                         
                                  }
                                    else
                  if ($inputData['action'] == 'fngetlistreminder1') {
                                           
              if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $today = date('Y-m-d');  // Get today's date

   $query = "
    SELECT r.*, u.sName AS assigned_by_name 
    FROM tblreminders r 
    LEFT JOIN tbluser u ON r.sAssigned_by = u.iUserid 
    WHERE r.iUserid = ? AND r.sDate = ?
";
$stmt = $link->prepare($query);
$stmt->bind_param("is", $user_id, $today);

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $reminders = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $reminders]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No reminders found']);
    }
    }


    else if ($inputData['action'] == 'getTodaysFollowups') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $today = date('Y-m-d');

    $stmt = $link->prepare("
        SELECT r.*, s.sStatus AS status_name, l.sCompany_name 
        FROM tblreplayleads r 
        LEFT JOIN tblstatus s ON r.sStatus = s.iStatusid 
        LEFT JOIN tblleads l ON r.lead_id = l.iLead_id  -- Join tblleads to get company name
        WHERE r.userid = ? AND r.sFollowupdate = ?
    ");
    $stmt->bind_param("is", $user_id, $today);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $followups = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $followups]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No follow-ups found']);
    }
}


}



    $method = $_SERVER['REQUEST_METHOD'];


        if ($method == 'GET') {
    // Autocomplete query
    if (isset($_GET['query'])) {
        $searchQuery = $_GET['query'];
        $sql = "SELECT sCompanyname FROM tblcustomer WHERE sCompanyname LIKE ? LIMIT 10";
        $stmt = $link->prepare($sql);
        $searchTerm = "%" . $searchQuery . "%";
        $stmt->bind_param("s", $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        $companies = [];
        while ($row = $result->fetch_assoc()) {
            $companies[] = $row;
        }
        echo json_encode(['status' => 'success', 'data' => $companies]);
        exit;
    }

    // Fetch full customer details
 else if (isset($_GET['companyname'])) {
    $companyname = $_GET['companyname'];

    // First, get customer info
    $sql = "SELECT iCustomerid, sCompanyname, sEmail, sIndustrytype, sTags, sBillingaddress FROM tblcustomer WHERE sCompanyname = ? LIMIT 1";
    $stmt = $link->prepare($sql);
    $stmt->bind_param("s", $companyname);
    $stmt->execute();
    $result = $stmt->get_result();
    $customer = $result->fetch_assoc();

    if ($customer) {
        $customerId = $customer['iCustomerid'];

        // Get first contact for this customer
        $sql2 = "SELECT sPhone, sContactname FROM tblcontact WHERE iCustomerid = ? LIMIT 1";
        $stmt2 = $link->prepare($sql2);
        $stmt2->bind_param("i", $customerId);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        $contact = $result2->fetch_assoc();

        // Merge contact info into customer array
        if ($contact) {
            $customer['sPhone'] = $contact['sPhone'];
            $customer['sContactname'] = $contact['sContactname'];
        } else {
            $customer['sPhone'] = '';
            $customer['sContactname'] = '';
        }

        echo json_encode(['status' => 'success', 'data' => $customer]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Company not found']);
    }
    exit;
}else {
            // If no 'query' parameter, fetch all users from the 'tbluser' table
            $query = "SELECT * FROM tbluser";
            $result = mysqli_query($link, $query);
    
            if ($result) {
                $users = mysqli_fetch_all($result, MYSQLI_ASSOC);
                echo json_encode(['status' => 'success', 'data' => $users]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No users found']);
            }
        }
    

    

   
  


}


    
?>




