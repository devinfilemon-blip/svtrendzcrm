<?php include 'layouts/session.php'; ?>
<?php 

header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

include 'layouts/config.php';
include_once 'layouts/crm-access.php';
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

    function sendResponse($status, $message, $data = null) {
        $payload = ['status' => $status, 'message' => $message];
        if ($data !== null) {
            $payload['data'] = $data;
        }
        echo json_encode($payload);
        exit;
    }

    // Older installs won't have this column yet; add it on the fly so the
    // "By Reference" lead source can capture who referred the lead.
    function ensureLeadReferenceColumn($link) {
        $result = mysqli_query($link, "SHOW COLUMNS FROM tblleads LIKE 'sReferenceName'");
        if ($result && mysqli_num_rows($result) > 0) {
            return;
        }
        mysqli_query($link, "ALTER TABLE tblleads ADD COLUMN sReferenceName VARCHAR(255) NULL AFTER sLead_source");
    }

    // Older installs won't have these columns yet; add them on the fly so
    // quotation line items can store HSN/SAC and Unit directly instead of
    // relying solely on a product-catalog join (which silently prints "-"
    // whenever a line item's product id doesn't resolve).
    function ensureQuotationItemColumns($link) {
        $result = mysqli_query($link, "SHOW COLUMNS FROM tblquotation_items LIKE 'sHsn'");
        if (!$result || mysqli_num_rows($result) === 0) {
            mysqli_query($link, "ALTER TABLE tblquotation_items ADD COLUMN sHsn VARCHAR(50) NULL AFTER product_id");
        }
        $result = mysqli_query($link, "SHOW COLUMNS FROM tblquotation_items LIKE 'sUnit'");
        if (!$result || mysqli_num_rows($result) === 0) {
            mysqli_query($link, "ALTER TABLE tblquotation_items ADD COLUMN sUnit VARCHAR(30) NULL AFTER sHsn");
        }
    }

    /**
     * Resolve the logged-in user's ID either from the PHP session (web/browser
     * flow) or from a login token sent by a mobile/app client (Authorization:
     * Bearer <token> header, or a "token" field in the JSON body). Returns 0
     * if neither is present/valid.
     */
    function resolveAuthUserId(mysqli $link, array $inputData): int {
        if (isset($_SESSION['user_id'])) {
            return (int)$_SESSION['user_id'];
        }

        $token = '';
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        foreach ($headers as $hName => $hValue) {
            if (strcasecmp($hName, 'Authorization') === 0) {
                $token = preg_match('/Bearer\s+(.+)/i', $hValue, $m) ? trim($m[1]) : trim($hValue);
                break;
            }
        }
        if ($token === '' && !empty($inputData['token'])) {
            $token = trim((string)$inputData['token']);
        }
        if ($token === '') {
            return 0;
        }

        $stmt = mysqli_prepare($link, "SELECT user_id, sExpire FROM tbltoken WHERE sToken = ? ORDER BY id DESC LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $token);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if (!$result || mysqli_num_rows($result) === 0) {
            return 0;
        }
        $row = mysqli_fetch_assoc($result);
        if (strtotime($row['sExpire']) < time()) {
            return 0;
        }
        return (int)$row['user_id'];
    }

    /** Normalize Assigned To as comma-separated user IDs (multi-select). */
    function normalizeAssignedToIds($value): string {
        if (is_array($value)) {
            $parts = $value;
        } else {
            $parts = preg_split('/\s*,\s*/', trim((string)$value), -1, PREG_SPLIT_NO_EMPTY);
        }
        $ids = [];
        foreach ($parts as $part) {
            $id = (int)$part;
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }
        return implode(',', array_values($ids));
    }

    /** SQL snippet: user is among comma-separated assigned IDs. */
    function sqlAssignedToHasUser(string $column = 'l.sAssigned_to', string $placeholder = '?'): string {
        return "FIND_IN_SET({$placeholder}, REPLACE({$column}, ' ', '')) > 0";
    }

    /** Resolve comma-separated user IDs to display names. */
    function resolveAssignedToNames(mysqli $link, $assignedRaw): string {
        $normalized = normalizeAssignedToIds($assignedRaw);
        if ($normalized === '') {
            return '';
        }
        $ids = array_map('intval', explode(',', $normalized));
        if (!$ids) {
            return '';
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids));
        $stmt = $link->prepare("SELECT iUserid, sName FROM tbluser WHERE iUserid IN ($placeholders)");
        if (!$stmt) {
            return '';
        }
        $stmt->bind_param($types, ...$ids);
        $stmt->execute();
        $result = $stmt->get_result();
        $map = [];
        while ($row = $result->fetch_assoc()) {
            $map[(int)$row['iUserid']] = $row['sName'];
        }
        $stmt->close();
        $names = [];
        foreach ($ids as $id) {
            if (isset($map[$id])) {
                $names[] = $map[$id];
            }
        }
        return implode(', ', $names);
    }

    /**
     * When a lead is created with a company name, ensure it exists in tblcustomer
     * (Customer List) with company name + email. Avoids duplicates by company name.
     */
    function syncLeadCompanyToCustomer(mysqli $link, array $lead): void {
        $company = trim((string)($lead['company_name'] ?? ''));
        if ($company === '') {
            return;
        }

        $email = trim((string)($lead['email'] ?? ''));
        $phone = trim((string)($lead['phone'] ?? ''));
        $contactPerson = trim((string)($lead['contact_person'] ?? ''));
        $industry = trim((string)($lead['industry'] ?? ''));
        $address = trim((string)($lead['address'] ?? ''));
        $tags = trim((string)($lead['tags'] ?? ''));
        $sourceId = (int)($lead['source_id'] ?? 0);
        $userId = (int)($lead['user_id'] ?? ($_SESSION['user_id'] ?? 0));
        if ($userId <= 0) {
            $userId = 0;
        }

        // Match existing customer by company name (case-insensitive)
        $check = $link->prepare('SELECT iCustomerid, sEmail FROM tblcustomer WHERE LOWER(TRIM(sCompanyname)) = LOWER(?) LIMIT 1');
        if (!$check) {
            return;
        }
        $check->bind_param('s', $company);
        $check->execute();
        $existing = $check->get_result()->fetch_assoc();
        $check->close();

        if ($existing) {
            $customerId = (int)$existing['iCustomerid'];
            // Fill empty email if lead has one
            if ($email !== '' && trim((string)($existing['sEmail'] ?? '')) === '') {
                $upd = $link->prepare('UPDATE tblcustomer SET sEmail = ? WHERE iCustomerid = ?');
                if ($upd) {
                    $upd->bind_param('si', $email, $customerId);
                    $upd->execute();
                    $upd->close();
                }
            }
        } else {
            $customertype = '';
            $status = 'Active';
            $notes = 'Auto-created from lead';
            $gstin = '';
            $ins = $link->prepare('INSERT INTO tblcustomer
                (sCompanyname, sGstin, sEmail, sBillingaddress, sShippingaddress, sCustomertype, sIndustrytype, iUserid, iSourceid, sTags, sStatus, sNotes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            if (!$ins) {
                return;
            }
            $ins->bind_param(
                'sssssssiiiss',
                $company,
                $gstin,
                $email,
                $address,
                $address,
                $customertype,
                $industry,
                $userId,
                $sourceId,
                $tags,
                $status,
                $notes
            );
            if (!$ins->execute()) {
                $ins->close();
                return;
            }
            $customerId = (int)$ins->insert_id;
            $ins->close();
        }

        // Optional: add a contact row if we have a person or phone and none exists yet
        if ($customerId > 0 && ($contactPerson !== '' || $phone !== '' || $email !== '')) {
            $hasContact = false;
            $cCheck = $link->prepare('SELECT iContactid FROM tblcontact WHERE iCustomerid = ? LIMIT 1');
            if ($cCheck) {
                $cCheck->bind_param('i', $customerId);
                $cCheck->execute();
                $hasContact = (bool)$cCheck->get_result()->fetch_assoc();
                $cCheck->close();
            }
            if (!$hasContact) {
                $cname = $contactPerson !== '' ? $contactPerson : $company;
                $refId = 0;
                $dept = '';
                $cIns = $link->prepare('INSERT INTO tblcontact (iCustomerid, sContactname, iContactid, sEmail, sPhone, sDepartment) VALUES (?, ?, ?, ?, ?, ?)');
                if ($cIns) {
                    $cIns->bind_param('isisss', $customerId, $cname, $refId, $email, $phone, $dept);
                    $cIns->execute();
                    $cIns->close();
                }
            }
        }
    }

            //Api for user data

        if ($method == 'POST') {
            $rawInput = file_get_contents("php://input");
            $inputData = json_decode($rawInput, true);
            if (!is_array($inputData)) {
                $inputData = [];
            }

            // Client-role sessions only need to log in / self-edit their profile / change their
            // password here; everything else about their project data goes through client-project-api.php.
            $crmClientAllowedActions = ['loginUser', 'updateMyProfile', 'changePassword'];
            if (crmIsClient() && !in_array(($inputData['action'] ?? ''), $crmClientAllowedActions, true)) {
                sendResponse('error', 'Access denied.');
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'addlead') {
    try {
        mysqli_report(MYSQLI_REPORT_OFF);
        ensureLeadReferenceColumn($link);

        $sLead_name = $_POST['sLead_name'] ?? '';
        $sEmail = $_POST['sEmail'] ?? '';
        $sPhone = $_POST['sPhone'] ?? '';
        $sAlternate_phone = $_POST['sAlternate_phone'] ?? '';
        $sLead_source = $_POST['sLead_source'] ?? '';
        $sReferenceName = trim((string)($_POST['sReferenceName'] ?? ''));
        $sLead_status = $_POST['sLead_status'] ?? '';
        $sLead_priority = $_POST['sLead_priority'] ?? '';
        $sLead_type = $_POST['sLead_type'] ?? '';
        $sCompany_name = $_POST['sCompany_name'] ?? '';
        $sIndustry_type = $_POST['sIndustry_type'] ?? '';
        $sDesignation = $_POST['sDesignation'] ?? '';
        $sWebsite = $_POST['sWebsite'] ?? '';
        $sLocation = $_POST['sLocation'] ?? '';
        $sAddress = $_POST['sAddress'] ?? '';
        $sAssigned_to = normalizeAssignedToIds($_POST['sAssigned_to'] ?? '');
        $sLead_owner = $_POST['sLead_owner'] ?? '';
        $sPreferred_communication = $_POST['sPreferred_communication'] ?? '';
        $sTags = $_POST['sTags'] ?? '';
        $sContactperson = $_POST['sContactperson'] ?? '';
        $sCreated_by = intval($_POST['sCreated_by'] ?? 0);

        $sProductnames = isset($_POST['products']) ? json_decode($_POST['products'], true) : [];
        $sQuantities = isset($_POST['quantities']) ? json_decode($_POST['quantities'], true) : [];
        $sRates = isset($_POST['rates']) ? json_decode($_POST['rates'], true) : [];
        $categories = isset($_POST['categories']) ? json_decode($_POST['categories'], true) : [];
        if (!is_array($sProductnames)) $sProductnames = [];
        if (!is_array($sQuantities)) $sQuantities = [];
        if (!is_array($sRates)) $sRates = [];
        if (!is_array($categories)) $categories = [];

        $saveUploadedFile = function ($field, $uploadDir = 'uploads/') {
            if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
                return '';
            }
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $ext = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
            $fileName = time() . '_' . uniqid() . '.' . $ext;
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES[$field]['tmp_name'], $targetPath)) {
                return $targetPath;
            }
            return '';
        };

        $file1 = $saveUploadedFile('fileUpload');
        $file2 = $saveUploadedFile('fileUpload2');
        $file3 = $saveUploadedFile('fileUpload3');

        $query = "INSERT INTO tblleads (
            sLead_name, sEmail, sPhone, sAlternate_phone, sLead_source, sReferenceName, sLead_status,
            sLead_priority, sLead_type, sCompany_name, sIndustry_type, sDesignation,
            sWebsite, sLocation, sAddress, sAssigned_to, sLead_owner, sPreferred_communication,
            sTags, sContactperson, sFileupload, sFileupload2, sFileupload3, sCreated_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($link, $query);
        if (!$stmt) {
            sendResponse("error", "Database error (prepare lead): " . mysqli_error($link));
        }

        mysqli_stmt_bind_param($stmt, "sssssssssssssssssssssssi",
            $sLead_name, $sEmail, $sPhone, $sAlternate_phone, $sLead_source, $sReferenceName,
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

        foreach ($sProductnames as $index => $product) {
            if ($product === null || $product === '') {
                continue;
            }
            $quantity = $sQuantities[$index] ?? '';
            $rate = $sRates[$index] ?? '';
            $category = $categories[$index] ?? '';

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

        // Also save company + email into Customer List when company name is present
        syncLeadCompanyToCustomer($link, [
            'company_name' => $sCompany_name,
            'email' => $sEmail,
            'phone' => $sPhone,
            'contact_person' => $sContactperson,
            'industry' => $sIndustry_type,
            'address' => $sAddress,
            'tags' => $sTags,
            'source_id' => (int)$sLead_source,
            'user_id' => $sCreated_by > 0 ? $sCreated_by : (int)($_SESSION['user_id'] ?? 0),
        ]);

        sendResponse("success", "lead added successfully.");
    } catch (Throwable $e) {
        sendResponse("error", "Add lead failed: " . $e->getMessage());
    }
}



// -------------------- Get Next Quotation No --------------------
elseif (isset($inputData['action']) && $inputData['action'] === 'getNextQuotationNo') {
    date_default_timezone_set('Asia/Kolkata');
    $yy = date('y'); // e.g. 26
    $mm = date('m'); // e.g. 07
    $prefix = "ITPL/{$yy}/{$mm}/";

    // Find highest increment for current year/month prefix
    $nextNo = 1;
    $stmt = $link->prepare("SELECT quotation_no FROM tblquotation WHERE quotation_no LIKE ? ORDER BY id DESC");
    $like = $prefix . '%';
    if ($stmt) {
        $stmt->bind_param('s', $like);
        $stmt->execute();
        $result = $stmt->get_result();
        $maxNo = 0;
        while ($row = $result->fetch_assoc()) {
            $lastNo = (string) ($row['quotation_no'] ?? '');
            if (preg_match('/^ITPL\/\d{2}\/\d{2}\/(\d+)$/', $lastNo, $matches)) {
                $num = intval($matches[1]);
                if ($num > $maxNo) {
                    $maxNo = $num;
                }
            }
        }
        $nextNo = $maxNo + 1;
        $stmt->close();
    }

    $nextQuotationNo = $prefix . str_pad((string) $nextNo, 2, '0', STR_PAD_LEFT);
    echo json_encode(["status" => "success", "next_quotation_no" => $nextQuotationNo]);
    exit;
}
else if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'updatelead') {
    ensureLeadReferenceColumn($link);
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
    $sReferenceName = trim((string)($_POST['sReferenceName'] ?? ''));
    $sLead_status = $_POST['sLead_status'] ?? '';
    $sLead_priority = $_POST['sLead_priority'] ?? '';
    $sLead_type = $_POST['sLead_type'] ?? '';
    $sCompany_name = $_POST['sCompany_name'] ?? '';
    $sIndustry_type = $_POST['sIndustry_type'] ?? '';
    $sDesignation = $_POST['sDesignation'] ?? '';
    $sWebsite = $_POST['sWebsite'] ?? '';
    $sLocation = $_POST['sLocation'] ?? '';
    $sAddress = $_POST['sAddress'] ?? '';
    $sAssigned_to = normalizeAssignedToIds($_POST['sAssigned_to'] ?? '');
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
        sLead_name = ?, sEmail = ?, sPhone = ?, sAlternate_phone = ?, sLead_source = ?, sReferenceName = ?, sLead_status = ?,
        sLead_priority = ?, sLead_type = ?, sCompany_name = ?, sIndustry_type = ?, sDesignation = ?,
        sWebsite = ?, sLocation = ?, sAddress = ?, sAssigned_to = ?, sLead_owner = ?,
        sPreferred_communication = ?, sTags = ?, sContactperson = ?
    ";
    $types = "ssssssssssssssssssss";
    $params = [
        $sLead_name, $sEmail, $sPhone, $sAlternate_phone, $sLead_source, $sReferenceName, $sLead_status,
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
    include_once __DIR__ . '/layouts/crm-access.php';
    crmEnsureAccessColumns($link);
    crmEnsureClientProjectColumns($link);
    if (!isset($_SESSION['user_id']) || !crmIsAdmin()) {
        sendResponse('error', 'Access denied. Only Admin can manage users.');
    }
    if (!empty($inputData['usercode']) && !empty($inputData['name']) && !empty($inputData['email']) && !empty($inputData['phone'])) {

        $usercode = mysqli_real_escape_string($link, $inputData['usercode']);
        $name = mysqli_real_escape_string($link, $inputData['name']);
        $email = mysqli_real_escape_string($link, $inputData['email']);
        $phone = mysqli_real_escape_string($link, $inputData['phone']);
        $Role = mysqli_real_escape_string($link, $inputData['Role']);
        $clientCompany = mysqli_real_escape_string($link, trim((string)($inputData['clientCompany'] ?? '')));
        list($accessLead, $accessProject, $accessFinance) = crmNormalizeAccessFlags($Role, $inputData);

        // Check if the user code already exists
        $checkQuery = "SELECT COUNT(*) as count FROM tbluser WHERE sUserCode = ?";
        $stmt = mysqli_prepare($link, $checkQuery);
        mysqli_stmt_bind_param($stmt, "s", $usercode);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $userCodeCount);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        if ($userCodeCount > 0) {
            sendResponse("error", "User code already exists.");
            return;
        }

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

        $password = '123456';
        $param_password = password_hash($password, PASSWORD_DEFAULT);
        $department = (int) $inputData['department'];

        // Insert the user
        $query = "INSERT INTO tbluser (sUserCode, sName, sEmail, sPhone, sRole, iDepid, sPassword_hash, sIs_active, iAccessLead, iAccessProject, iAccessFinance, sClientCompany) VALUES (?,?,?,?,?,?,?,1,?,?,?,?)";
        $stmt = mysqli_prepare($link, $query);
        if (!$stmt) {
            sendResponse("error", "Data Not Saved: " . mysqli_error($link));
        }
        mysqli_stmt_bind_param($stmt, "sssssisiiis", $usercode, $name, $email, $phone, $Role, $department, $param_password, $accessLead, $accessProject, $accessFinance, $clientCompany);
        $ret = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if (!$ret) {
            sendResponse("error", "Data Not Saved: " . mysqli_error($link));
        }

        sendResponse("success", "User added successfully.");
    } else {
        sendResponse("error", "Please fill in all required fields.");
    }
}
else if (isset($inputData['action']) && $inputData['action'] == "updateMyProfile") {
    try {
        mysqli_report(MYSQLI_REPORT_OFF);

        if (
            empty($inputData['userid']) ||
            empty($inputData['name']) ||
            empty($inputData['email']) ||
            empty($inputData['phone'])
        ) {
            sendResponse("error", "Please fill in all required fields.");
        }

        $userid = intval($inputData['userid']);
        $name = trim((string) $inputData['name']);
        $email = trim((string) $inputData['email']);
        $phone = trim((string) $inputData['phone']);

        if ($userid <= 0) {
            sendResponse("error", "Invalid user ID.");
        }
        if (!preg_match('/^\d{10}$/', $phone)) {
            sendResponse("error", "Phone number must be exactly 10 digits.");
        }

        $emailEsc = mysqli_real_escape_string($link, $email);
        $phoneEsc = mysqli_real_escape_string($link, $phone);
        $nameEsc = mysqli_real_escape_string($link, $name);

        $res = mysqli_query($link, "SELECT COUNT(*) AS cnt FROM tbluser WHERE sEmail = '{$emailEsc}' AND iUserid <> {$userid}");
        if ($res === false) {
            sendResponse("error", "Database error (check email): " . mysqli_error($link));
        }
        $row = mysqli_fetch_assoc($res);
        if (intval($row['cnt'] ?? 0) > 0) {
            sendResponse("error", "Email already exists for another user.");
        }

        $res = mysqli_query($link, "SELECT COUNT(*) AS cnt FROM tbluser WHERE sPhone = '{$phoneEsc}' AND iUserid <> {$userid}");
        if ($res === false) {
            sendResponse("error", "Database error (check phone): " . mysqli_error($link));
        }
        $row = mysqli_fetch_assoc($res);
        if (intval($row['cnt'] ?? 0) > 0) {
            sendResponse("error", "Phone already exists for another user.");
        }

        $sql = "UPDATE tbluser SET sName = '{$nameEsc}', sEmail = '{$emailEsc}', sPhone = '{$phoneEsc}' WHERE iUserid = {$userid}";
        if (!mysqli_query($link, $sql)) {
            sendResponse("error", "Data Not updated: " . mysqli_error($link));
        }

        sendResponse("success", "Profile updated successfully.");
    } catch (Throwable $e) {
        sendResponse("error", "Update profile failed: " . $e->getMessage());
    }
}
else if (isset($inputData['action']) && $inputData['action'] == "updateuser") {
    include_once __DIR__ . '/layouts/crm-access.php';
    crmEnsureAccessColumns($link);
    crmEnsureClientProjectColumns($link);
    if (!isset($_SESSION['user_id']) || !crmIsAdmin()) {
        sendResponse('error', 'Access denied. Only Admin can manage users.');
    }
    try {
        mysqli_report(MYSQLI_REPORT_OFF);

        if (
            empty($inputData['userid']) ||
            !isset($inputData['usercode']) ||
            empty($inputData['name']) ||
            empty($inputData['email']) ||
            empty($inputData['phone']) ||
            empty($inputData['department']) ||
            empty($inputData['Role'])
        ) {
            sendResponse("error", "Please fill in all required fields.");
        }

        $userid = intval($inputData['userid']);
        $usercode = trim((string) $inputData['usercode']);
        $name = trim((string) $inputData['name']);
        $email = trim((string) $inputData['email']);
        $phone = trim((string) $inputData['phone']);
        $department = intval($inputData['department']);
        $Role = trim((string) $inputData['Role']);
        list($accessLead, $accessProject, $accessFinance) = crmNormalizeAccessFlags($Role, $inputData);

        if ($userid <= 0) {
            sendResponse("error", "Invalid user ID.");
        }
        if (!preg_match('/^\d{10}$/', $phone)) {
            sendResponse("error", "Phone number must be exactly 10 digits.");
        }

        $usercodeEsc = mysqli_real_escape_string($link, $usercode);
        $emailEsc = mysqli_real_escape_string($link, $email);
        $phoneEsc = mysqli_real_escape_string($link, $phone);
        $nameEsc = mysqli_real_escape_string($link, $name);
        $roleEsc = mysqli_real_escape_string($link, $Role);
        $clientCompanyEsc = mysqli_real_escape_string($link, trim((string)($inputData['clientCompany'] ?? '')));

        if ($usercode !== '') {
            $sql = "SELECT COUNT(*) AS cnt FROM tbluser WHERE sUserCode = '{$usercodeEsc}' AND iUserid <> {$userid}";
            $res = mysqli_query($link, $sql);
            if ($res === false) {
                sendResponse("error", "Database error (check usercode): " . mysqli_error($link));
            }
            $row = mysqli_fetch_assoc($res);
            if (intval($row['cnt'] ?? 0) > 0) {
                sendResponse("error", "User code already exists for another user.");
            }
        }

        $res = mysqli_query($link, "SELECT COUNT(*) AS cnt FROM tbluser WHERE sEmail = '{$emailEsc}' AND iUserid <> {$userid}");
        if ($res === false) {
            sendResponse("error", "Database error (check email): " . mysqli_error($link));
        }
        $row = mysqli_fetch_assoc($res);
        if (intval($row['cnt'] ?? 0) > 0) {
            sendResponse("error", "Email already exists for another user.");
        }

        $res = mysqli_query($link, "SELECT COUNT(*) AS cnt FROM tbluser WHERE sPhone = '{$phoneEsc}' AND iUserid <> {$userid}");
        if ($res === false) {
            sendResponse("error", "Database error (check phone): " . mysqli_error($link));
        }
        $row = mysqli_fetch_assoc($res);
        if (intval($row['cnt'] ?? 0) > 0) {
            sendResponse("error", "Phone already exists for another user.");
        }

        $sql = "UPDATE tbluser
                SET sUserCode = '{$usercodeEsc}',
                    sName = '{$nameEsc}',
                    sEmail = '{$emailEsc}',
                    sPhone = '{$phoneEsc}',
                    sRole = '{$roleEsc}',
                    iDepid = {$department},
                    iAccessLead = {$accessLead},
                    iAccessProject = {$accessProject},
                    iAccessFinance = {$accessFinance},
                    sClientCompany = '{$clientCompanyEsc}'
                WHERE iUserid = {$userid}";

        if (!mysqli_query($link, $sql)) {
            sendResponse("error", "Data Not updated: " . mysqli_error($link));
        }

        sendResponse("success", "Data updated Successfully");
    } catch (Throwable $e) {
        sendResponse("error", "Update user failed: " . $e->getMessage());
    }
} else if (isset($inputData['action']) && $inputData['action']=="deleteuser"){
            include_once __DIR__ . '/layouts/crm-access.php';
            if (!isset($_SESSION['user_id']) || !crmIsAdmin()) {
                sendResponse('error', 'Access denied. Only Admin can manage users.');
            }
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
    include_once __DIR__ . '/layouts/crm-access.php';
    if (!isset($_SESSION['user_id']) || !crmIsAdmin()) {
        echo json_encode(['status' => 'error', 'message' => 'Access denied. Only Admin can manage users.', 'data' => []]);
        exit;
    }
    $output = [];

    $stmt = $link->prepare("SELECT * FROM tbluser");
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
            include_once __DIR__ . '/layouts/crm-access.php';
            $requestUserId = (int)$userid;
            $sessionUserId = (int)($_SESSION['user_id'] ?? 0);
            // Allow own profile view for any user; full user admin only for Admin
            if ($requestUserId !== $sessionUserId && !crmIsAdmin()) {
                sendResponse('error', 'Access denied.');
            }

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

        $phone = trim($inputData['phone']);
        $password = $inputData['password'];

        // Login by phone OR username/name
        $query = "SELECT u.iUserid, u.sPhone, u.sPassword_hash, u.sName, u.sIs_active, u.sRole, u.iDepid, u.iAccessLead, u.iAccessProject, u.iAccessFinance, d.sDepartment
                  FROM tbluser u
                  LEFT JOIN tbldepartment d ON d.iDepid = u.iDepid
                  WHERE u.sPhone = ? OR u.sName = ?
                  LIMIT 1";
        $stmt = mysqli_prepare($link, $query);
        mysqli_stmt_bind_param($stmt, "ss", $phone, $phone);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);

            if ((int)$user['sIs_active'] === 0) {
                sendResponse("error", "User is inactive.");
            }

            $hash = $user['sPassword_hash'] ?? '';
            $passwordOk = false;
            if (!empty($hash) && password_verify($password, $hash)) {
                $passwordOk = true;
            } elseif (!empty($hash) && hash_equals((string)$hash, (string)$password)) {
                // Fallback for legacy plain-text passwords
                $passwordOk = true;
            }

            if ($passwordOk) {

                $_SESSION['user_id'] = (int)$user['iUserid'];
                $_SESSION['username'] = $user['sName'];
                $_SESSION['userRole'] = $user['sRole'];
                $_SESSION['userDepId'] = (int)($user['iDepid'] ?? 0);
                $_SESSION['userDepartment'] = trim((string)($user['sDepartment'] ?? ''));
                $_SESSION['accessLead'] = (int)($user['iAccessLead'] ?? 0);
                $_SESSION['accessProject'] = (int)($user['iAccessProject'] ?? 0);
                $_SESSION['accessFinance'] = (int)($user['iAccessFinance'] ?? 0);
                $_SESSION['loggedin'] = true;

                $token = bin2hex(random_bytes(16));
                date_default_timezone_set('Asia/Kolkata');
                $expireTimestamp = strtotime("+4 hours", time());
                $expireDateTime = date('Y-m-d H:i:s', $expireTimestamp);
                $userId = (int)$user['iUserid'];

                $insertTokenQuery = "INSERT INTO tbltoken (user_id, sToken, sExpire) VALUES (?, ?, ?)";
                $stmtInsert = mysqli_prepare($link, $insertTokenQuery);
                mysqli_stmt_bind_param($stmtInsert, "iss", $userId, $token, $expireDateTime);
                mysqli_stmt_execute($stmtInsert);

                if (mysqli_stmt_affected_rows($stmtInsert) > 0) {
                    $_SESSION['token'] = $token;

                    sendResponse("success", "Login successful", [
                        "token" => $token,
                        "expires_in" => 14400,
                        "role" => $user['sRole'],
                        "user_id" => $userId
                    ]);
                } else {
                    // Still allow login even if token insert fails
                    $_SESSION['token'] = $token;
                    sendResponse("success", "Login successful", [
                        "token" => $token,
                        "expires_in" => 14400,
                        "role" => $user['sRole'],
                        "user_id" => $userId
                    ]);
                }

                mysqli_stmt_close($stmtInsert);
            } else {
                sendResponse("error", "Invalid password.");
            }
        } else {
            sendResponse("error", "Invalid username or phone number.");
        }

        mysqli_stmt_close($stmt);
        return;
    } else {
        sendResponse("error", "Please provide both username and password.");
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
                                                                        $gstin = isset($inputData['gstin']) ? mysqli_real_escape_string($link, $inputData['gstin']) : '';
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
                                    $customerQuery = "INSERT INTO tblcustomer (sCompanyname, sGstin, sEmail, sBillingaddress, sShippingaddress, sCustomertype, sIndustrytype, iUserid, iSourceid, sTags, sStatus, sNotes)
                                                      VALUES ('$companyname', '$gstin', '$email', '$billing', '$shipping', '$customertype', '$industry', $manager, $sources, '$tags', '$statusname', '$notes')";
                                
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
                                    $gstin = isset($inputData['gstin']) ? mysqli_real_escape_string($link, $inputData['gstin']) : '';
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
                                                      SET sCompanyname = '$companyname', sGstin = '$gstin', sEmail = '$email', sBillingaddress = '$billing', sShippingaddress = '$shipping', 
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
                    $sAssigned_to = normalizeAssignedToIds($inputData['sAssigned_to'] ?? '');
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
        $aid = intval($assignedTo);
        $conditions[] = "FIND_IN_SET('{$aid}', REPLACE(l.sAssigned_to, ' ', '')) > 0";
    }

    $whereClause = count($conditions) > 0 ? 'WHERE ' . implode(' AND ', $conditions) : '';

    $query = "
        SELECT
            l.*,
            GROUP_CONCAT(COALESCE(p.sProductname, ip.sProductName) SEPARATOR ', ') AS product_names,
            GROUP_CONCAT(c.sCategoryname SEPARATOR ', ') AS product_categories
        FROM tblleads l
        LEFT JOIN tblproductleads pl ON pl.lead_id = l.iLead_id
        LEFT JOIN tblproduct p ON pl.sProductname = p.iProductid
        LEFT JOIN tblinv_product ip ON pl.sProductname = ip.iProductid AND p.iProductid IS NULL
        LEFT JOIN tblcategoryname c ON p.iParentid = c.id
        $whereClause
        GROUP BY l.iLead_id
    ";

    $result = mysqli_query($link, $query);
    if ($result) {
        $statuses = mysqli_fetch_all($result, MYSQLI_ASSOC);
        foreach ($statuses as &$row) {
            $row['assigned_to_name'] = resolveAssignedToNames($link, $row['sAssigned_to'] ?? '');
        }
        unset($row);
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
            COALESCE(prod.sProductname, ip1.sProductName) AS sProductname,
            cat.sCategoryname

        FROM tblleads l
        LEFT JOIN tblproductleads p ON l.iLead_id = p.lead_id
        LEFT JOIN tblproduct prod ON p.sProductname = prod.iProductid
        LEFT JOIN tblinv_product ip1 ON p.sProductname = ip1.iProductid AND prod.iProductid IS NULL
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
            FIND_IN_SET('$id', REPLACE(l.sAssigned_to, ' ', '')) > 0
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
        COALESCE(prod.sProductname, ip2.sProductName) AS sProductname,
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
    LEFT JOIN tblinv_product ip2 ON p.sProductname = ip2.iProductid AND prod.iProductid IS NULL
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
    include_once __DIR__ . '/layouts/crm-access.php';
    $isAdmin   = crmCanSeeAllLeads($link);
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

    $assignClause = $isAdmin ? '' : "FIND_IN_SET(?, REPLACE(l.sAssigned_to, ' ', '')) > 0 AND ";

    $sql = "
      SELECT
        l.iLead_id, MAX(CONVERT(l.sCompany_name USING utf8mb4) COLLATE utf8mb4_general_ci) AS sCompany_name,
        MAX(CONVERT(l.sLead_name USING utf8mb4) COLLATE utf8mb4_general_ci) AS sLead_name,
        MAX(CONVERT(l.sContactperson USING utf8mb4) COLLATE utf8mb4_general_ci) AS sContactperson,
        MAX(l.sCreated_date) AS sCreated_date,
        MAX(CONVERT(pr.sPrioritylevel USING utf8mb4) COLLATE utf8mb4_general_ci) AS sPrioritylevel,
        p.pid, SUM(p.sQuantity) AS total_quantity, MAX(p.sRate) AS sRate,
        (SUM(p.sQuantity)*MAX(p.sRate)) AS total_amount,
        MAX(CONVERT(COALESCE(prod.sProductname, ip3.sProductName) USING utf8mb4) COLLATE utf8mb4_general_ci) AS sProductname,
        MAX(CONVERT(cat.sCategoryname USING utf8mb4) COLLATE utf8mb4_general_ci) AS sCategoryname
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
    LEFT JOIN tblproduct prod    ON p.sProductname = prod.iProductid
    LEFT JOIN tblinv_product ip3 ON p.sProductname = ip3.iProductid AND prod.iProductid IS NULL
    LEFT JOIN tblcategoryname cat ON prod.iParentid = cat.id
      LEFT JOIN tblpriority pr     ON l.sLead_priority = pr.id
      WHERE {$assignClause}COALESCE(NULLIF(r.sStatus, ''), l.sLead_status) = ?
      GROUP BY l.iLead_id, p.pid
      ORDER BY l.sContactperson ASC
    ";

    $stmt = $link->prepare($sql);
    if (!$stmt) {
        echo json_encode(['status'=>'error','message'=>'SQL error: ' . $link->error]);
        exit;
    }
    if ($isAdmin) {
        $stmt->bind_param("i", $status_id);
    } else {
        $stmt->bind_param("ii", $user_id, $status_id);
    }
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
        l.iLead_id, MAX(CONVERT(l.sCompany_name USING utf8mb4) COLLATE utf8mb4_general_ci) AS sCompany_name,
        MAX(CONVERT(l.sLead_name USING utf8mb4) COLLATE utf8mb4_general_ci) AS sLead_name,
        MAX(CONVERT(l.sContactperson USING utf8mb4) COLLATE utf8mb4_general_ci) AS sContactperson,
        MAX(l.sCreated_date) AS sCreated_date,
        MAX(CONVERT(pr.sPrioritylevel USING utf8mb4) COLLATE utf8mb4_general_ci) AS sPrioritylevel,
        p.pid, SUM(p.sQuantity) AS total_quantity, MAX(p.sRate) AS sRate,
        (SUM(p.sQuantity)*MAX(p.sRate)) AS total_amount,
        MAX(CONVERT(COALESCE(prod.sProductname, ip4.sProductName) USING utf8mb4) COLLATE utf8mb4_general_ci) AS sProductname,
        MAX(CONVERT(cat.sCategoryname USING utf8mb4) COLLATE utf8mb4_general_ci) AS sCategoryname
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
      LEFT JOIN tblproduct prod    ON p.sProductname = prod.iProductid
      LEFT JOIN tblinv_product ip4 ON p.sProductname = ip4.iProductid AND prod.iProductid IS NULL
      LEFT JOIN tblcategoryname cat ON prod.iParentid = cat.id
      LEFT JOIN tblpriority pr     ON l.sLead_priority = pr.id
      WHERE FIND_IN_SET(?, REPLACE(l.sAssigned_to, ' ', '')) > 0 AND COALESCE(NULLIF(r.sStatus, ''), l.sLead_status) = ?
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
            FIND_IN_SET('$id', REPLACE(l.sAssigned_to, ' ', '')) > 0
          

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

else if (isset($inputData['action']) && $inputData['action'] == "getleadbyid") {
    try {
        mysqli_report(MYSQLI_REPORT_OFF);

        if (empty($inputData['leadId'])) {
            echo json_encode(["status" => "error", "message" => "Invalid leadId"]);
            exit;
        }

        $leadId = intval($inputData['leadId']);
        $stmt = $link->prepare('
            SELECT l.*, u.sName
            FROM tblleads l
            LEFT JOIN tbluser u ON l.sCreated_by = u.iUserid
            WHERE l.iLead_id = ?
        ');
        if (!$stmt) {
            throw new Exception(mysqli_error($link));
        }
        $stmt->bind_param('i', $leadId);
        $stmt->execute();
        $result = $stmt->get_result();
        if (!$result || !($row = $result->fetch_assoc())) {
            echo json_encode(["status" => "error", "message" => "Lead not found"]);
            exit;
        }

        $gstin = '';
        $companyName = $row['sCompany_name'] ?? '';
        if ($companyName !== '') {
            $stmtGstin = @$link->prepare('SELECT sGstin FROM tblcustomer WHERE sCompanyname = ? ORDER BY iCustomerid DESC LIMIT 1');
            if ($stmtGstin) {
                $stmtGstin->bind_param('s', $companyName);
                if ($stmtGstin->execute()) {
                    $resultGstin = $stmtGstin->get_result();
                    if ($resultGstin && ($gstinRow = $resultGstin->fetch_assoc())) {
                        $gstin = $gstinRow['sGstin'] ?? '';
                    }
                }
                $stmtGstin->close();
            }
        }

        $products = [];
        $stmtProducts = $link->prepare('
            SELECT pl.*,
                   COALESCE(p.sProductname, ip.sProductName, pl.sProductname) AS product_name,
                   COALESCE(p.iProductid, ip.iProductid) AS product_id
            FROM tblproductleads pl
            LEFT JOIN tblproduct p ON (
                p.iProductid = pl.sProductname
                OR p.sProductname = pl.sProductname
            )
            LEFT JOIN tblinv_product ip ON ip.iProductid = pl.sProductname AND p.iProductid IS NULL
            WHERE pl.lead_id = ?
        ');
        if ($stmtProducts) {
            $stmtProducts->bind_param('i', $leadId);
            $stmtProducts->execute();
            $resultProducts = $stmtProducts->get_result();
            if ($resultProducts) {
                while ($productRow = $resultProducts->fetch_assoc()) {
                    // Prefer real product id when join found it
                    if (!empty($productRow['product_id'])) {
                        $productRow['sProductname'] = (string) $productRow['product_id'];
                    }
                    $products[] = $productRow;
                }
            }
            $stmtProducts->close();
        }

        // Do NOT dump entire product master (6000+ rows) — that crashes live with HTTP 500.
        // Return only products linked to this lead; dropdown uses fetch-products.php AJAX.
        $allProducts = [];
        $seenIds = [];
        foreach ($products as $productRow) {
            $pid = intval($productRow['sProductname'] ?? 0);
            $label = trim((string) ($productRow['product_name'] ?? ''));
            // The main query's COALESCE falls back to the raw stored id when tblproduct has no
            // match, so $label is non-empty even when unresolved — check product_id instead.
            $resolvedViaLegacy = !empty($productRow['product_id']);
            if ($pid > 0) {
                if (isset($seenIds[$pid])) {
                    continue;
                }
                $seenIds[$pid] = true;
                if (!$resolvedViaLegacy) {
                    // Not a legacy tblproduct id — try the inventory catalog (add-lead-master.php
                    // now sources its product picker from tblinv_product, not tblproduct).
                    $stmtInv = $link->prepare('SELECT iProductid, sProductName AS sProductname FROM tblinv_product WHERE iProductid = ?');
                    $foundInv = false;
                    if ($stmtInv) {
                        $stmtInv->bind_param('i', $pid);
                        $stmtInv->execute();
                        $invRes = $stmtInv->get_result();
                        if ($invRes && ($invRow = $invRes->fetch_assoc())) {
                            $allProducts[] = $invRow;
                            $foundInv = true;
                        }
                        $stmtInv->close();
                    }
                    if (!$foundInv && $label !== '') {
                        $allProducts[] = [
                            'iProductid' => $pid,
                            'sProductname' => $label
                        ];
                    }
                } else {
                    $allProducts[] = [
                        'iProductid' => $pid,
                        'sProductname' => $label
                    ];
                }
            } elseif ($label !== '') {
                // Legacy rows that stored product name text instead of id
                $allProducts[] = [
                    'iProductid' => $label,
                    'sProductname' => $label
                ];
            }
        }

        $categories = [];
        $stmtCategories = $link->prepare('SELECT * FROM tblcategoryname ORDER BY sCategoryname');
        if ($stmtCategories && $stmtCategories->execute()) {
            $resultCategories = $stmtCategories->get_result();
            if ($resultCategories) {
                while ($catRow = $resultCategories->fetch_assoc()) {
                    $categories[] = $catRow;
                }
            }
            $stmtCategories->close();
        }

        echo json_encode([
            "status" => "success",
            "lead" => $row,
            "created_by_display" => $row['sName'] ?? $row['sCreated_by'],
            "products" => $products,
            "allProducts" => $allProducts,
            "categories" => $categories,
            "gstin" => $gstin,
            "files" => [
                "file1" => $row['sFileupload'] ?? '',
                "file2" => $row['sFileupload2'] ?? '',
                "file3" => $row['sFileupload3'] ?? ''
            ]
        ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        exit;
    } catch (Throwable $e) {
        echo json_encode([
            "status" => "error",
            "message" => "getleadbyid failed: " . $e->getMessage()
        ]);
        exit;
    }
}

                    
          else// === Save Quotation ===
if (isset($inputData['action']) && $inputData['action'] === 'saveQuotation') {
    ensureQuotationItemColumns($link);

    $quotationId    = $inputData['quotation_id'] ?? 0;
    $quotation_no   = $inputData['quotation_no'];
    $quotation_date = $inputData['quotation_date'];
    $customer_name  = $inputData['customer_name'];
    $customer_address = $inputData['customer_address'] ?? '';
    $customer_phone = $inputData['customer_phone'] ?? '';
    $leadId         = intval($inputData['leadId'] ?? 0);
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
        (quotation_no, leadId, customer_name, customer_address, customer_phone, quotation_date, total_amount, delivery, payment, sTransport, specialnote, gst, validity, sTransportInsurance, sKindattn, sModeoftransport, sRefno, sDate, sGstin, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

 // s = string, i = integer, d = double/decimal
$stmt->bind_param(
    "sisssssssssdsssssss",
    $quotation_no, $leadId, $customer_name, $customer_address, $customer_phone, $quotation_date, $total_amount,
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
    $hsn       = $inputData['hsn'] ?? [];
    $unit      = $inputData['unit'] ?? [];

    $stmt = $link->prepare("INSERT INTO tblquotation_items (quotation_id, product_id, description, quantity, rate, amount, discount, after_discount, sHsn, sUnit) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    for ($i=0; $i < count($products); $i++) {
        $lineAmount = floatval($amount[$i] ?? 0);
        $lineQty = floatval($quantity[$i] ?? 0);
        $lineRate = floatval($rate[$i] ?? 0);
        $lineDiscount = floatval($discount[$i] ?? 0);
        $lineAfter = floatval($after_discount[$i] ?? 0);
        $lineHsn = trim((string)($hsn[$i] ?? ''));
        $lineUnit = trim((string)($unit[$i] ?? ''));

        // If amount not set but qty/rate are, derive amount
        if ($lineAmount <= 0 && $lineQty > 0 && $lineRate > 0) {
            $lineAmount = $lineQty * $lineRate;
        }
        // If after_discount missing/zero but amount is set, sync it
        if ($lineAfter <= 0 && $lineAmount > 0) {
            $lineAfter = $lineAmount - ($lineAmount * $lineDiscount / 100);
        }

        $stmt->bind_param("iisiddddss", $quotationId, $products[$i], $des[$i], $lineQty, $lineRate, $lineAmount, $lineDiscount, $lineAfter, $lineHsn, $lineUnit);
        $stmt->execute();
    }
    $stmt->close();

    echo json_encode(["status"=>"success","message"=>"Quotation saved successfully!","quotation_id"=>$quotationId]);
    exit;
}

// === Update Quotation ===
elseif (isset($inputData['action']) && $inputData['action'] === 'updateQuotation') {
    ensureQuotationItemColumns($link);
    include_once __DIR__ . '/layouts/crm-access.php';
    if (!crmCanManageQuotation($link)) {
        echo json_encode(["status"=>"error","message"=>"Access denied. Quotation edit requires Finance access."]);
        exit;
    }

    $quotation_id   = $inputData['quotation_id'] ?? 0;
    $quotation_no   = $inputData['quotation_no'] ?? '';
    $quotation_date = $inputData['quotation_date'] ?? '';
    $customer_name  = $inputData['customer_name'] ?? '';
    $customer_address = $inputData['customer_address'] ?? '';
    $customer_phone = $inputData['customer_phone'] ?? '';
    $leadId         = intval($inputData['leadId'] ?? 0);
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
    $hsns           = $inputData['hsn'] ?? [];
    $units          = $inputData['unit'] ?? [];

    // Update quotation header
    $stmt = $link->prepare("
        UPDATE tblquotation 
        SET quotation_no=?, leadId=?, customer_name=?, customer_address=?, customer_phone=?, quotation_date=?, total_amount=?, 
            delivery=?, payment=?, sTransport=?, specialnote=?, gst=?, validity=?, 
            sTransportInsurance=?, sKindattn=?, sModeoftransport=?, 
            sRefno=?, sDate=?, sGstin=? 
        WHERE id=?
    ");
    $stmt->bind_param(
        "sisssssssssssssssssi",
        $quotation_no, $leadId, $customer_name, $customer_address, $customer_phone, $quotation_date, $total_amount,
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
        (quotation_id, product_id, description, quantity, rate, amount, discount, after_discount, sHsn, sUnit)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    for($i=0; $i < count($products); $i++) {
        if(!empty($products[$i])) {
            $lineAmount = floatval($amounts[$i] ?? 0);
            $lineQty = floatval($quantities[$i] ?? 0);
            $lineRate = floatval($rates[$i] ?? 0);
            $lineDiscount = floatval($discounts[$i] ?? 0);
            $lineAfter = floatval($afterDiscounts[$i] ?? 0);
            $lineHsn = trim((string)($hsns[$i] ?? ''));
            $lineUnit = trim((string)($units[$i] ?? ''));

            if ($lineAmount <= 0 && $lineQty > 0 && $lineRate > 0) {
                $lineAmount = $lineQty * $lineRate;
            }
            if ($lineAfter <= 0 && $lineAmount > 0) {
                $lineAfter = $lineAmount - ($lineAmount * $lineDiscount / 100);
            }

            $stmtItem->bind_param(
                "iisiddddss",
                $quotation_id, $products[$i], $descriptions[$i], $lineQty,
                $lineRate, $lineAmount, $lineDiscount, $lineAfter, $lineHsn, $lineUnit
            );
            $stmtItem->execute();
        }
    }
    $stmtItem->close();

    echo json_encode(["status"=>"success","message"=>"Quotation updated successfully"]);
    exit;
}


elseif(isset($inputData['action']) && $inputData['action'] === 'deleteQuotation') {
    include_once __DIR__ . '/layouts/crm-access.php';
    if (!crmCanManageQuotation($link)) {
        echo json_encode(["status"=>"error","message"=>"Access denied. Quotation delete requires Finance access."]);
        exit;
    }
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
    include_once __DIR__ . '/layouts/crm-access.php';
    if (!crmCanListQuotation($link)) {
        echo json_encode(["status"=>"error","message"=>"Access denied.","quotations"=>[]]);
        exit;
    }
    crmEnsureQuotationStatusColumn($link);
    $result = $link->query("SELECT * FROM tblquotation ORDER BY id DESC");
    $quotations = [];
    while($row = $result->fetch_assoc()){
        if (empty($row['status'])) {
            $row['status'] = 'Draft';
        }
        $quotations[] = $row;
    }
    echo json_encode([
        "status"=>"success",
        "quotations"=>$quotations,
        "canManage"=> crmCanManageQuotation($link) ? 1 : 0,
        "statusOptions"=> crmQuotationStatusOptions()
    ]);
    exit;
}

// -------------------- Update Quotation Status --------------------
elseif(isset($inputData['action']) && $inputData['action'] === 'updateQuotationStatus') {
    include_once __DIR__ . '/layouts/crm-access.php';
    if (!crmCanManageQuotation($link)) {
        echo json_encode(["status"=>"error","message"=>"Access denied. Quotation status requires Finance access."]);
        exit;
    }
    crmEnsureQuotationStatusColumn($link);

    $id     = (int)($inputData['id'] ?? 0);
    $status = trim((string)($inputData['status'] ?? ''));
    if (!$id || !in_array($status, crmQuotationStatusOptions(), true)) {
        echo json_encode(["status"=>"error","message"=>"Invalid status."]);
        exit;
    }

    $stmt = $link->prepare("UPDATE tblquotation SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $id);
    if ($stmt->execute()) {
        echo json_encode(["status"=>"success","message"=>"Status updated","statusValue"=>$status]);
    } else {
        echo json_encode(["status"=>"error","message"=>$stmt->error]);
    }
    exit;
}

// -------------------- Get Quotation By ID --------------------
elseif(isset($inputData['action']) && $inputData['action'] === 'getQuotationById') {
    ensureQuotationItemColumns($link);
    $id = $inputData['id'] ?? 0;

    $stmt = $link->prepare("SELECT * FROM tblquotation WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $quotation = $stmt->get_result()->fetch_assoc();

  // Product ids saved here may belong to the legacy tblproduct catalog or, for quotations
  // created from a lead, the tblinv_product catalog — resolve against tblproduct first,
  // falling back to tblinv_product, same as getleadbyid. Prefer the HSN/Unit stored
  // directly on the line item (captured at save time); fall back to the product-catalog
  // join only for older rows saved before those columns existed.
  $stmt2 = $link->prepare("
    SELECT i.*, COALESCE(p.sProductname, ip.sProductName) AS sProductname,
           COALESCE(NULLIF(i.sHsn, ''), ip.sHsnCode) AS item_hsn,
           COALESCE(NULLIF(i.sUnit, ''), ip.sUnit) AS item_unit
    FROM tblquotation_items i
    LEFT JOIN tblproduct p ON i.product_id = p.iProductid
    LEFT JOIN tblinv_product ip ON i.product_id = ip.iProductid AND p.iProductid IS NULL
    WHERE i.quotation_id = ?
");
$stmt2->bind_param("i", $id);
$stmt2->execute();
$items = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

    // Do NOT dump entire product master — that causes 504 timeouts on large catalogs.
    // Return only products used on this quotation; dropdown search uses fetch-inv-products.php.
    $products = [];
    $seenIds = [];
    foreach ($items as $item) {
        $pid = intval($item['product_id'] ?? 0);
        if ($pid <= 0 || isset($seenIds[$pid])) {
            continue;
        }
        $seenIds[$pid] = true;
        $label = trim((string)($item['sProductname'] ?? ''));
        if ($label === '') {
            $label = 'Product #' . $pid;
        }
        $products[] = [
            'iProductid' => $pid,
            'sProductname' => $label
        ];
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
        // Fetch quotation items WITH product name (tblproduct first, tblinv_product fallback)
        $stmt2 = $link->prepare("
            SELECT qi.*, COALESCE(p.sProductname, ip.sProductName) AS sProductname
            FROM tblquotation_items qi
            LEFT JOIN tblproduct p ON qi.product_id = p.iProductid
            LEFT JOIN tblinv_product ip ON qi.product_id = ip.iProductid AND p.iProductid IS NULL
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
    // Cap rows to avoid gateway timeouts on large product masters
    $query = "SELECT p.iProductid, p.sProductname, c.sCategoryname 
              FROM tblproduct p
              LEFT JOIN tblcategoryname c ON p.iParentid = c.id
              WHERE p.sProductname IS NOT NULL AND TRIM(p.sProductname) <> ''
              ORDER BY p.sProductname ASC
              LIMIT 500";

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
    // Admin sees all reminders; User sees only own
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
    $user_id = (int)$_SESSION['user_id'];
    $isAdmin = isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'Admin';
    $query = "
        SELECT r.*, 
               u.sName AS assigned_to_name, 
               a.sName AS assigned_by_name
        FROM tblreminders r
        LEFT JOIN tbluser u ON r.iUserid = u.iUserid
        LEFT JOIN tbluser a ON r.sAssigned_by = a.iUserid
    ";
    if (!$isAdmin) {
        $query .= " WHERE r.iUserid = ?";
    }
    $query .= " ORDER BY r.sDate DESC";
    $stmt = $link->prepare($query);
    if (!$isAdmin) {
        $stmt->bind_param('i', $user_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        $reminders = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $reminders]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No reminder found']);
    }
    $stmt->close();
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

    else if ($inputData['action'] == 'getAllFollowups') {
    include_once __DIR__ . '/layouts/crm-access.php';
    if (!isset($_SESSION['user_id']) || !crmCanSeeAllLeads($link)) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }

    $stmt = $link->prepare("
        SELECT r.*, s.sStatus AS status_name, l.sCompany_name, u.sName AS user_name
        FROM tblreplayleads r
        LEFT JOIN tblstatus s ON r.sStatus = s.iStatusid
        LEFT JOIN tblleads l ON r.lead_id = l.iLead_id
        LEFT JOIN tbluser u ON r.userid = u.iUserid
        ORDER BY r.sFollowupdate DESC, r.rId DESC
    ");
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $followups = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $followups]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No follow-ups found']);
    }
}


// -------------------- Save Sales Order --------------------
else if (isset($inputData['action']) && $inputData['action'] === 'saveSalesOrder') {

    $salesOrderId   = $inputData['sales_order_id'] ?? 0;
    $sales_no       = $inputData['sales_no'];
    $sales_date     = $inputData['sales_date'];
    $customer_name  = $inputData['customer_name'];
    $leadId         = intval($inputData['leadId'] ?? 0);
    $total_amount   = $inputData['total_amount'];

    $kind_attn      = $inputData['kind_attn'];
    $mode_transport = $inputData['mode_transport'];
    $gstin          = $inputData['gstin'];
    $ref_no         = $inputData['ref_no'];
    $ref_date       = $inputData['ref_date'];
    $delivery       = $inputData['delivery'];
    $payments       = $inputData['payments'];
    $special_note   = $inputData['special_note'];
    $gst_percent    = $inputData['gst_percent'];
    $transport      = $inputData['transport'];
    $transport_insurance = $inputData['transport_insurance'];
    $validity       = $inputData['validity'];

    // Insert sales order
    $stmt = $link->prepare("INSERT INTO tblsalesorder 
        (sales_no, leadId, customer_name, sales_date, total_amount, delivery, payment, sTransport, specialnote, gst, validity, sTransportInsurance, sKindattn, sModeoftransport, sRefno, sDate, sGstin, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    $stmt->bind_param(
        "sisssssssdsssssss",
        $sales_no, $leadId, $customer_name, $sales_date, $total_amount,
        $delivery, $payments, $transport, $special_note, $gst_percent,
        $validity, $transport_insurance, $kind_attn, $mode_transport,
        $ref_no, $ref_date, $gstin
    );

    $stmt->execute();
    $salesOrderId = $stmt->insert_id;
    $stmt->close();

    // Insert items
    $products  = $inputData['product'];
    $des       = $inputData['des'];
    $quantity  = $inputData['quantity'];
    $rate      = $inputData['rate'];
    $discount  = $inputData['discount'];
    $amount    = $inputData['amount'];
    $after_discount = $inputData['after_discount'];

    $stmt = $link->prepare("INSERT INTO tblsalesorder_items (salesorder_id, product_id, description, quantity, rate, amount, discount, after_discount) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    for ($i=0; $i < count($products); $i++) {
        $stmt->bind_param("iisidddd", $salesOrderId, $products[$i], $des[$i], $quantity[$i], $rate[$i], $amount[$i], $discount[$i], $after_discount[$i]);
        $stmt->execute();
    }
    $stmt->close();

    echo json_encode(["status"=>"success","message"=>"Sales Order saved successfully!","sales_order_id"=>$salesOrderId]);
    exit;
}

// === Update Sales Order ===
elseif (isset($inputData['action']) && $inputData['action'] === 'updateSalesOrder') {

    $sales_order_id = $inputData['sales_order_id'] ?? 0;
    $sales_no       = $inputData['sales_no'] ?? '';
    $sales_date     = $inputData['sales_date'] ?? '';
    $customer_name  = $inputData['customer_name'] ?? '';
    $leadId         = intval($inputData['leadId'] ?? 0);
    $total_amount   = $inputData['total_amount'] ?? 0;

    $kind_attn      = $inputData['kind_attn'] ?? '';
    $mode_transport = $inputData['mode_transport'] ?? '';
    $gstin          = $inputData['gstin'] ?? '';
    $ref_no         = $inputData['ref_no'] ?? '';
    $ref_date       = $inputData['ref_date'] ?? '';
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

    // Update sales order header
    $stmt = $link->prepare("
        UPDATE tblsalesorder 
        SET sales_no=?, leadId=?, customer_name=?, sales_date=?, total_amount=?, 
            delivery=?, payment=?, sTransport=?, specialnote=?, gst=?, validity=?, 
            sTransportInsurance=?, sKindattn=?, sModeoftransport=?, 
            sRefno=?, sDate=?, sGstin=? 
        WHERE id=?
    ");
    $stmt->bind_param(
        "sisssssssssssssssi",
        $sales_no, $leadId, $customer_name, $sales_date, $total_amount,
        $delivery, $payments, $transport, $special_note, $gst_percent,
        $validity, $transport_insurance, $kind_attn, $mode_transport,
        $ref_no, $ref_date, $gstin, $sales_order_id
    );
    $stmt->execute();
    $stmt->close();

    // Delete existing items
    $stmt = $link->prepare("DELETE FROM tblsalesorder_items WHERE salesorder_id=?");
    $stmt->bind_param("i", $sales_order_id);
    $stmt->execute();
    $stmt->close();

    // Insert new items
    if (!empty($products)) {
        $stmt = $link->prepare("INSERT INTO tblsalesorder_items (salesorder_id, product_id, description, quantity, rate, amount, discount, after_discount) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        for ($i = 0; $i < count($products); $i++) {
            $stmt->bind_param("iisidddd", $sales_order_id, $products[$i], $descriptions[$i], $quantities[$i], $rates[$i], $amounts[$i], $discounts[$i], $afterDiscounts[$i]);
            $stmt->execute();
        }
        $stmt->close();
    }
    
    echo json_encode(["status" => "success", "message" => "Sales Order updated successfully!"]);
    exit;
}

// -------------------- Get Sales Order List --------------------
elseif(isset($inputData['action']) && $inputData['action'] === 'getSalesOrders') {
    $result = $link->query("SELECT * FROM tblsalesorder ORDER BY id DESC");
    $salesOrders = [];
    while($row = $result->fetch_assoc()){
        $salesOrders[] = $row;
    }
    echo json_encode(["status"=>"success","salesOrders"=>$salesOrders]);
    exit;
}

// -------------------- Get Sales Order By ID --------------------
elseif(isset($inputData['action']) && $inputData['action'] === 'getSalesOrderById') {
    $id = $inputData['id'] ?? 0;

    $stmt = $link->prepare("SELECT * FROM tblsalesorder WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $salesOrder = $stmt->get_result()->fetch_assoc();

    $stmt2 = $link->prepare("
        SELECT i.*, COALESCE(p.sProductname, ip.sProductName) AS sProductname
        FROM tblsalesorder_items i
        LEFT JOIN tblproduct p ON i.product_id = p.iProductid
        LEFT JOIN tblinv_product ip ON i.product_id = ip.iProductid AND p.iProductid IS NULL
        WHERE i.salesorder_id = ?
    ");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();
    $items = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

    // Only products used on this sales order (avoid full catalog dump / 504)
    $products = [];
    $seenIds = [];
    foreach ($items as $item) {
        $pid = intval($item['product_id'] ?? 0);
        if ($pid <= 0 || isset($seenIds[$pid])) {
            continue;
        }
        $seenIds[$pid] = true;
        $label = trim((string)($item['sProductname'] ?? ''));
        if ($label === '') {
            $label = 'Product #' . $pid;
        }
        $products[] = [
            'iProductid' => $pid,
            'sProductname' => $label
        ];
    }

    echo json_encode([
        "status" => "success",
        "salesOrder" => $salesOrder,
        "items" => $items,
        "allProducts" => $products
    ]);
    exit;
}

// -------------------- Global Search --------------------
elseif (isset($inputData['action']) && $inputData['action'] === 'globalsearch') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }

    $q = trim($inputData['query'] ?? '');
    if (strlen($q) < 2) {
        echo json_encode(['status' => 'success', 'data' => ['leads' => [], 'customers' => []]]);
        exit;
    }

    include_once __DIR__ . '/layouts/crm-access.php';
    $isAdmin = crmCanSeeAllLeads($link);
    $userId = (int)$_SESSION['user_id'];
    $like = '%' . $q . '%';
    $leads = [];
    $customers = [];

    $leadSql = "SELECT l.iLead_id, l.sCompany_name, l.sLead_name, l.sContactperson, l.sEmail, l.sPhone
        FROM tblleads l
        WHERE (l.sCompany_name LIKE ? OR l.sEmail LIKE ? OR l.sPhone LIKE ?
               OR l.sAlternate_phone LIKE ? OR l.sLead_name LIKE ? OR l.sContactperson LIKE ?)";
    if (!$isAdmin) {
        $leadSql .= " AND FIND_IN_SET(?, REPLACE(l.sAssigned_to, ' ', '')) > 0";
    }
    $leadSql .= " ORDER BY l.sCompany_name ASC LIMIT 15";

    $stmt = $link->prepare($leadSql);
    if ($stmt) {
        if ($isAdmin) {
            $stmt->bind_param('ssssss', $like, $like, $like, $like, $like, $like);
        } else {
            $stmt->bind_param('ssssssi', $like, $like, $like, $like, $like, $like, $userId);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $leads[] = $row;
        }
        $stmt->close();
    }

    $custSql = "SELECT c.iCustomerid, c.sCompanyname, c.sEmail,
        (SELECT ct.sPhone FROM tblcontact ct WHERE ct.iCustomerid = c.iCustomerid LIMIT 1) AS sPhone,
        (SELECT ct.sContactname FROM tblcontact ct WHERE ct.iCustomerid = c.iCustomerid LIMIT 1) AS sContactname
        FROM tblcustomer c
        WHERE c.sCompanyname LIKE ? OR c.sEmail LIKE ?
        OR EXISTS (
            SELECT 1 FROM tblcontact ct2
            WHERE ct2.iCustomerid = c.iCustomerid
              AND (ct2.sContactname LIKE ? OR ct2.sPhone LIKE ? OR ct2.sEmail LIKE ?)
        )
        ORDER BY c.sCompanyname ASC LIMIT 10";

    $stmt2 = $link->prepare($custSql);
    if ($stmt2) {
        $stmt2->bind_param('sssss', $like, $like, $like, $like, $like);
        $stmt2->execute();
        $res2 = $stmt2->get_result();
        while ($row = $res2->fetch_assoc()) {
            $customers[] = $row;
        }
        $stmt2->close();
    }

    echo json_encode(['status' => 'success', 'data' => ['leads' => $leads, 'customers' => $customers]]);
    exit;
}

// -------------------- Kanban Pipeline --------------------
elseif (isset($inputData['action']) && $inputData['action'] === 'kanban_data') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }

    include_once __DIR__ . '/layouts/crm-access.php';
    $isAdmin = crmCanSeeAllLeads($link);
    $userId = (int)$_SESSION['user_id'];
    $productId = isset($inputData['productId']) ? trim((string)$inputData['productId']) : '';

    $statuses = [];
    $statusRes = mysqli_query($link, "SELECT iStatusid, sStatus FROM tblstatus ORDER BY iStatusid ASC");
    if ($statusRes) {
        while ($s = mysqli_fetch_assoc($statusRes)) {
            $statuses[(int)$s['iStatusid']] = [
                'id' => (int)$s['iStatusid'],
                'name' => $s['sStatus'],
                'leads' => []
            ];
        }
    }

    $rateProductFilter = '';
    if ($productId !== '') {
        $safeProductId = mysqli_real_escape_string($link, $productId);
        $rateProductFilter = " AND (plr.sProductname = '{$safeProductId}' OR CAST(plr.sProductname AS CHAR) = '{$safeProductId}')";
    }

    $leadSql = "
        SELECT
            l.iLead_id,
            l.sCompany_name,
            l.sLead_name,
            l.sContactperson,
            l.sPhone,
            COALESCE(NULLIF(r.sStatus, ''), l.sLead_status) AS status_id,
            pr.sPrioritylevel,
            l.sAssigned_to,
            (
                SELECT COALESCE(SUM(
                    CAST(IFNULL(NULLIF(TRIM(plr.sRate), ''), '0') AS DECIMAL(15,2))
                ), 0)
                FROM tblproductleads plr
                WHERE plr.lead_id = l.iLead_id
                {$rateProductFilter}
            ) AS lead_rate
        FROM tblleads l
        LEFT JOIN (
            SELECT r1.lead_id, r1.sStatus
            FROM tblreplayleads r1
            INNER JOIN (
                SELECT lead_id, MAX(sCreatedTimestamp) AS latest_ts
                FROM tblreplayleads GROUP BY lead_id
            ) r2 ON r1.lead_id = r2.lead_id AND r1.sCreatedTimestamp = r2.latest_ts
        ) r ON r.lead_id = l.iLead_id
        LEFT JOIN tblpriority pr ON l.sLead_priority = pr.id
    ";

    $where = [];
    if (!$isAdmin) {
        $where[] = "FIND_IN_SET(" . $userId . ", REPLACE(l.sAssigned_to, ' ', '')) > 0";
    }
    if ($productId !== '') {
        $where[] = "EXISTS (
            SELECT 1 FROM tblproductleads pl
            WHERE pl.lead_id = l.iLead_id
              AND (pl.sProductname = '{$safeProductId}' OR CAST(pl.sProductname AS CHAR) = '{$safeProductId}')
        )";
    }
    if (!empty($where)) {
        $leadSql .= " WHERE " . implode(' AND ', $where);
    }
    $leadSql .= " ORDER BY l.sCompany_name ASC";

    $leadRes = mysqli_query($link, $leadSql);
    if ($leadRes) {
        while ($row = mysqli_fetch_assoc($leadRes)) {
            $sid = (int)$row['status_id'];
            if (!isset($statuses[$sid])) {
                $statuses[$sid] = [
                    'id' => $sid,
                    'name' => 'Status #' . $sid,
                    'leads' => []
                ];
            }
            $statuses[$sid]['leads'][] = [
                'iLead_id' => (int)$row['iLead_id'],
                'sCompany_name' => $row['sCompany_name'],
                'sLead_name' => $row['sLead_name'],
                'sContactperson' => $row['sContactperson'],
                'sPhone' => $row['sPhone'],
                'sPrioritylevel' => $row['sPrioritylevel'],
                'assigned_to_name' => resolveAssignedToNames($link, $row['sAssigned_to'] ?? ''),
                'lead_rate' => isset($row['lead_rate']) ? (float)$row['lead_rate'] : 0
            ];
        }
    }

    echo json_encode([
        'status' => 'success',
        'data' => array_values($statuses)
    ]);
    exit;
}

elseif (isset($inputData['action']) && $inputData['action'] === 'update_lead_status') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }

    $leadId = (int)($inputData['leadId'] ?? 0);
    $statusId = (int)($inputData['statusId'] ?? 0);
    include_once __DIR__ . '/layouts/crm-access.php';
    $isAdmin = crmCanSeeAllLeads($link);
    $userId = (int)$_SESSION['user_id'];

    if ($leadId <= 0 || $statusId <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid lead or status']);
        exit;
    }

    $check = $link->prepare("SELECT sAssigned_to FROM tblleads WHERE iLead_id = ?");
    $check->bind_param('i', $leadId);
    $check->execute();
    $leadRow = $check->get_result()->fetch_assoc();
    $check->close();

    if (!$leadRow) {
        echo json_encode(['status' => 'error', 'message' => 'Lead not found']);
        exit;
    }
    $assignedIds = normalizeAssignedToIds($leadRow['sAssigned_to'] ?? '');
    $assignedList = $assignedIds !== '' ? explode(',', $assignedIds) : [];
    if (!$isAdmin && !in_array((string)$userId, $assignedList, true)) {
        echo json_encode(['status' => 'error', 'message' => 'You cannot update this lead']);
        exit;
    }

    $upd = $link->prepare("UPDATE tblleads SET sLead_status = ? WHERE iLead_id = ?");
    $upd->bind_param('ii', $statusId, $leadId);
    $ok = $upd->execute();
    $upd->close();

    if (!$ok) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update lead status']);
        exit;
    }

    $replayStmt = $link->prepare("SELECT rId FROM tblreplayleads WHERE lead_id = ? ORDER BY sCreatedTimestamp DESC LIMIT 1");
    $replayStmt->bind_param('i', $leadId);
    $replayStmt->execute();
    $replayRow = $replayStmt->get_result()->fetch_assoc();
    $replayStmt->close();

    if ($replayRow) {
        $rId = (int)$replayRow['rId'];
        $updReplay = $link->prepare("UPDATE tblreplayleads SET sStatus = ? WHERE rId = ?");
        $updReplay->bind_param('ii', $statusId, $rId);
        $updReplay->execute();
        $updReplay->close();
    }

    echo json_encode(['status' => 'success', 'message' => 'Lead status updated']);
    exit;
}

// -------------------- Calendar Events --------------------
elseif (isset($inputData['action']) && $inputData['action'] === 'calendar_events') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }

    $isAdmin = (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'Admin');
    $userId = (int)$_SESSION['user_id'];
    $start = $inputData['start'] ?? date('Y-m-01');
    $end = $inputData['end'] ?? date('Y-m-t');

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start)) {
        $start = date('Y-m-01');
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
        $end = date('Y-m-t');
    }

    $events = [];

    $followSql = "
        SELECT r.rId, r.sFollowupdate, r.sDescription, l.sCompany_name, l.iLead_id
        FROM tblreplayleads r
        LEFT JOIN tblleads l ON r.lead_id = l.iLead_id
        WHERE r.sFollowupdate >= ? AND r.sFollowupdate <= ?
          AND r.sFollowupdate IS NOT NULL AND r.sFollowupdate != '' AND r.sFollowupdate != '0000-00-00'
    ";
    if (!$isAdmin) {
        $followSql .= " AND r.userid = ?";
    }

    $fStmt = $link->prepare($followSql);
    if ($fStmt) {
        if ($isAdmin) {
            $fStmt->bind_param('ss', $start, $end);
        } else {
            $fStmt->bind_param('ssi', $start, $end, $userId);
        }
        $fStmt->execute();
        $fRes = $fStmt->get_result();
        while ($row = $fRes->fetch_assoc()) {
            $title = 'Follow-up';
            if (!empty($row['sCompany_name'])) {
                $title .= ': ' . $row['sCompany_name'];
            }
            $events[] = [
                'id' => 'followup-' . $row['rId'],
                'title' => $title,
                'start' => $row['sFollowupdate'],
                'allDay' => true,
                'backgroundColor' => '#9333ea',
                'borderColor' => '#7c3aed',
                'extendedProps' => [
                    'type' => 'followup',
                    'leadId' => (int)$row['iLead_id'],
                    'description' => $row['sDescription'] ?? ''
                ]
            ];
        }
        $fStmt->close();
    }

    $remSql = "
        SELECT r.rrid, r.sDate, r.sDescription
        FROM tblreminders r
        WHERE r.sDate >= ? AND r.sDate <= ?
          AND r.sDate IS NOT NULL AND r.sDate != '' AND r.sDate != '0000-00-00'
    ";
    if (!$isAdmin) {
        $remSql .= " AND r.iUserid = ?";
    }

    $rStmt = $link->prepare($remSql);
    if ($rStmt) {
        if ($isAdmin) {
            $rStmt->bind_param('ss', $start, $end);
        } else {
            $rStmt->bind_param('ssi', $start, $end, $userId);
        }
        $rStmt->execute();
        $rRes = $rStmt->get_result();
        while ($row = $rRes->fetch_assoc()) {
            $desc = trim($row['sDescription'] ?? '');
            $title = 'Reminder' . ($desc !== '' ? ': ' . (strlen($desc) > 40 ? substr($desc, 0, 40) . '…' : $desc) : '');
            $events[] = [
                'id' => 'reminder-' . $row['rrid'],
                'title' => $title,
                'start' => $row['sDate'],
                'allDay' => true,
                'backgroundColor' => '#059669',
                'borderColor' => '#047857',
                'extendedProps' => [
                    'type' => 'reminder',
                    'description' => $desc
                ]
            ];
        }
        $rStmt->close();
    }

    echo json_encode(['status' => 'success', 'data' => $events]);
    exit;
}

// -------------------- Delete Sales Order --------------------
elseif(isset($inputData['action']) && $inputData['action'] === 'deleteSalesOrder') {
    $id = $inputData['id'] ?? 0;
    
    // Delete items first
    $stmt = $link->prepare("DELETE FROM tblsalesorder_items WHERE salesorder_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    
    // Delete sales order
    $stmt = $link->prepare("DELETE FROM tblsalesorder WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    
    echo json_encode(["status"=>"success","message"=>"Sales Order deleted successfully!"]);
    exit;
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

// ---- Follow-up weekly summary (last 7 days) ----
if ($method == 'POST' && isset($inputData['action']) && $inputData['action'] === 'followup_weekly') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
    $isAdmin = (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'Admin');
    $userId  = (int)$_SESSION['user_id'];
    $sql = "
        SELECT DATE(sFollowupdate) AS date, COUNT(*) AS count
        FROM tblreplayleads
        WHERE sFollowupdate IS NOT NULL
          AND sFollowupdate >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
          AND sFollowupdate < DATE_ADD(CURDATE(), INTERVAL 1 DAY)
    ";
    if (!$isAdmin) {
        $sql .= " AND userid = " . $userId;
    }
    $sql .= " GROUP BY DATE(sFollowupdate) ORDER BY date ASC";
    $res = mysqli_query($link, $sql);
    $rows = [];
    if ($res) {
        while ($r = mysqli_fetch_assoc($res)) {
            $rows[] = ['date' => $r['date'], 'count' => (int)$r['count']];
        }
    }
    echo json_encode(['status' => 'success', 'data' => $rows]);
    exit;
}

// ---- Daily Report module ----
function ensureDailyReportColumns($link) {
    static $done = false;
    if ($done) {
        return;
    }
    $cols = [];
    $res = mysqli_query($link, 'SHOW COLUMNS FROM tbldaily_report');
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $cols[$row['Field']] = true;
        }
    }
    if (!isset($cols['iTimeInChecked'])) {
        mysqli_query($link, 'ALTER TABLE tbldaily_report ADD COLUMN iTimeInChecked TINYINT(1) NOT NULL DEFAULT 0 AFTER sTimeOut');
    }
    if (!isset($cols['iTimeOutChecked'])) {
        mysqli_query($link, 'ALTER TABLE tbldaily_report ADD COLUMN iTimeOutChecked TINYINT(1) NOT NULL DEFAULT 0 AFTER iTimeInChecked');
    }
    $done = true;
}

if ($method == 'POST' && isset($inputData['action']) && $inputData['action'] === 'adddailyreport') {
    if (!isset($_SESSION['user_id'])) {
        sendResponse('error', 'Unauthorized');
    }
    if (empty($inputData['date'])) {
        sendResponse('error', 'Date is required');
    }
    ensureDailyReportColumns($link);

    $userId = (int)$_SESSION['user_id'];
    $date = mysqli_real_escape_string($link, $inputData['date']);
    $timeIn = mysqli_real_escape_string($link, $inputData['time_in'] ?? '');
    $timeOut = mysqli_real_escape_string($link, $inputData['time_out'] ?? '');
    $timeInChecked = !empty($inputData['time_in_check']) ? 1 : 0;
    $timeOutChecked = !empty($inputData['time_out_check']) ? 1 : 0;
    if ($timeInChecked && $timeIn === '') {
        $timeIn = '10:00';
    }
    if ($timeOutChecked && $timeOut === '') {
        $timeOut = '18:00';
    }
    $tasksPlanned = mysqli_real_escape_string($link, $inputData['tasks_planned'] ?? '');
    $tasksCompleted = mysqli_real_escape_string($link, $inputData['tasks_completed'] ?? '');
    $workDetails = mysqli_real_escape_string($link, $inputData['work_details'] ?? '');
    $pending = mysqli_real_escape_string($link, $inputData['pending'] ?? '');
    $blockers = mysqli_real_escape_string($link, $inputData['blockers'] ?? '');
    $tomorrowPlan = mysqli_real_escape_string($link, $inputData['tomorrow_plan'] ?? '');
    $remarks = mysqli_real_escape_string($link, $inputData['remarks'] ?? '');

    $query = "INSERT INTO tbldaily_report
        (iUserid, sDate, sTimeIn, sTimeOut, iTimeInChecked, iTimeOutChecked, sTasksPlanned, sTasksCompleted, sWorkDetails, sPending, sBlockers, sTomorrowPlan, sRemarks)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param(
        $stmt,
        "isssiisssssss",
        $userId,
        $date,
        $timeIn,
        $timeOut,
        $timeInChecked,
        $timeOutChecked,
        $tasksPlanned,
        $tasksCompleted,
        $workDetails,
        $pending,
        $blockers,
        $tomorrowPlan,
        $remarks
    );
    $ret = mysqli_stmt_execute($stmt);
    $err = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);

    if (!$ret) {
        sendResponse('error', 'Data Not Saved: ' . ($err ?: mysqli_error($link)));
    }
    sendResponse('success', 'Daily report saved successfully');
}

if ($method == 'POST' && isset($inputData['action']) && $inputData['action'] === 'fngetlistdailyreport') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
    ensureDailyReportColumns($link);

    $userId = (int)$_SESSION['user_id'];
    $isAdmin = (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'Admin');
    $filterDate = !empty($inputData['date']) ? mysqli_real_escape_string($link, $inputData['date']) : '';

    $sql = "SELECT r.*, u.sName
            FROM tbldaily_report r
            LEFT JOIN tbluser u ON u.iUserid = r.iUserid
            WHERE 1=1";
    if (!$isAdmin) {
        $sql .= " AND r.iUserid = " . $userId;
    }
    if ($filterDate !== '') {
        $sql .= " AND r.sDate = '" . $filterDate . "'";
    }
    $sql .= " ORDER BY r.sDate DESC, r.iReportid DESC";

    $result = mysqli_query($link, $sql);
    if ($result) {
        $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $rows]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No daily reports found']);
    }
    exit;
}

if ($method == 'POST' && isset($inputData['action']) && $inputData['action'] === 'getdailyreportbyid') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
    ensureDailyReportColumns($link);
    if (empty($inputData['id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid report ID']);
        exit;
    }

    $id = (int)$inputData['id'];
    $userId = (int)$_SESSION['user_id'];
    $isAdmin = (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'Admin');

    $stmt = $link->prepare('SELECT r.*, u.sName FROM tbldaily_report r LEFT JOIN tbluser u ON u.iUserid = r.iUserid WHERE r.iReportid = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if (!$row) {
        echo json_encode(['status' => 'error', 'message' => 'Report not found']);
        exit;
    }
    if (!$isAdmin && (int)$row['iUserid'] !== $userId) {
        echo json_encode(['status' => 'error', 'message' => 'You can only view your own reports']);
        exit;
    }
    echo json_encode(['status' => 'success', 'data' => $row]);
    exit;
}

if ($method == 'POST' && isset($inputData['action']) && $inputData['action'] === 'updatedailyreport') {
    if (!isset($_SESSION['user_id'])) {
        sendResponse('error', 'Unauthorized');
    }
    if (empty($inputData['id']) || empty($inputData['date'])) {
        sendResponse('error', 'Required fields missing');
    }
    ensureDailyReportColumns($link);

    $id = (int)$inputData['id'];
    $userId = (int)$_SESSION['user_id'];
    $isAdmin = (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'Admin');

    $check = $link->prepare('SELECT iUserid FROM tbldaily_report WHERE iReportid = ?');
    $check->bind_param('i', $id);
    $check->execute();
    $checkResult = $check->get_result();
    $existing = $checkResult->fetch_assoc();
    $check->close();

    if (!$existing) {
        sendResponse('error', 'Report not found');
    }
    if (!$isAdmin && (int)$existing['iUserid'] !== $userId) {
        sendResponse('error', 'You can only edit your own reports');
    }

    $date = mysqli_real_escape_string($link, $inputData['date']);
    $timeIn = mysqli_real_escape_string($link, $inputData['time_in'] ?? '');
    $timeOut = mysqli_real_escape_string($link, $inputData['time_out'] ?? '');
    $timeInChecked = !empty($inputData['time_in_check']) ? 1 : 0;
    $timeOutChecked = !empty($inputData['time_out_check']) ? 1 : 0;
    if ($timeInChecked && $timeIn === '') {
        $timeIn = '10:00';
    }
    if ($timeOutChecked && $timeOut === '') {
        $timeOut = '18:00';
    }
    $tasksPlanned = mysqli_real_escape_string($link, $inputData['tasks_planned'] ?? '');
    $tasksCompleted = mysqli_real_escape_string($link, $inputData['tasks_completed'] ?? '');
    $workDetails = mysqli_real_escape_string($link, $inputData['work_details'] ?? '');
    $pending = mysqli_real_escape_string($link, $inputData['pending'] ?? '');
    $blockers = mysqli_real_escape_string($link, $inputData['blockers'] ?? '');
    $tomorrowPlan = mysqli_real_escape_string($link, $inputData['tomorrow_plan'] ?? '');
    $remarks = mysqli_real_escape_string($link, $inputData['remarks'] ?? '');

    $query = "UPDATE tbldaily_report SET
        sDate = ?, sTimeIn = ?, sTimeOut = ?, iTimeInChecked = ?, iTimeOutChecked = ?,
        sTasksPlanned = ?, sTasksCompleted = ?,
        sWorkDetails = ?, sPending = ?, sBlockers = ?, sTomorrowPlan = ?, sRemarks = ?
        WHERE iReportid = ?";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param(
        $stmt,
        "sssiisssssssi",
        $date,
        $timeIn,
        $timeOut,
        $timeInChecked,
        $timeOutChecked,
        $tasksPlanned,
        $tasksCompleted,
        $workDetails,
        $pending,
        $blockers,
        $tomorrowPlan,
        $remarks,
        $id
    );
    $ret = mysqli_stmt_execute($stmt);
    $err = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);

    if (!$ret) {
        sendResponse('error', 'Data Not updated: ' . ($err ?: mysqli_error($link)));
    }
    sendResponse('success', 'Daily report updated successfully');
}

if ($method == 'POST' && isset($inputData['action']) && $inputData['action'] === 'deletedailyreport') {
    if (!isset($_SESSION['user_id'])) {
        sendResponse('error', 'Unauthorized');
    }
    if (empty($inputData['id'])) {
        sendResponse('error', 'Invalid report ID');
    }

    $id = (int)$inputData['id'];
    $userId = (int)$_SESSION['user_id'];
    $isAdmin = (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'Admin');

    $check = $link->prepare('SELECT iUserid FROM tbldaily_report WHERE iReportid = ?');
    $check->bind_param('i', $id);
    $check->execute();
    $checkResult = $check->get_result();
    $existing = $checkResult->fetch_assoc();
    $check->close();

    if (!$existing) {
        sendResponse('error', 'Report not found');
    }
    if (!$isAdmin && (int)$existing['iUserid'] !== $userId) {
        sendResponse('error', 'You can only delete your own reports');
    }

    $stmt = mysqli_prepare($link, 'DELETE FROM tbldaily_report WHERE iReportid = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ret = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if (!$ret) {
        sendResponse('error', 'Data Not deleted: ' . mysqli_error($link));
    }
    sendResponse('success', 'Daily report deleted successfully');
}

// ---- Location Module (App Location Reports) ----
if ($method == 'POST' && isset($inputData['action']) && $inputData['action'] === 'submitreport') {
    $userId = resolveAuthUserId($link, $inputData);
    if ($userId <= 0) {
        sendResponse('error', 'Unauthorized');
    }
    if (!isset($inputData['latitude']) || !isset($inputData['longitude']) || $inputData['latitude'] === '' || $inputData['longitude'] === '') {
        sendResponse('error', 'Latitude and longitude are required');
    }

    $latitude = (float)$inputData['latitude'];
    $longitude = (float)$inputData['longitude'];

    $hasEndLocation = isset($inputData['endLatitude']) && isset($inputData['endLongitude'])
        && $inputData['endLatitude'] !== '' && $inputData['endLongitude'] !== '';
    $endLatitude = $hasEndLocation ? (float)$inputData['endLatitude'] : null;
    $endLongitude = $hasEndLocation ? (float)$inputData['endLongitude'] : null;

    date_default_timezone_set('Asia/Kolkata');
    $dateTime = !empty($inputData['datetime'])
        ? mysqli_real_escape_string($link, $inputData['datetime'])
        : date('Y-m-d H:i:s');
    $endDateTime = !empty($inputData['endDatetime'])
        ? mysqli_real_escape_string($link, $inputData['endDatetime'])
        : null;

    $query = "INSERT INTO tbllocation (iUserid, dLatitude, dLongitude, dEndLatitude, dEndLongitude, sDateTime, sEndDateTime) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "iddddss", $userId, $latitude, $longitude, $endLatitude, $endLongitude, $dateTime, $endDateTime);
    $ret = mysqli_stmt_execute($stmt);
    $err = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);

    if (!$ret) {
        sendResponse('error', 'Location not saved: ' . ($err ?: mysqli_error($link)));
    }
    sendResponse('success', 'Location report submitted successfully');
}

if ($method == 'POST' && isset($inputData['action']) && $inputData['action'] === 'getlocation') {
    $userId = resolveAuthUserId($link, $inputData);
    if ($userId <= 0) {
        sendResponse('error', 'Unauthorized');
    }
    // Admin-only "see everyone" filtering still relies on the web session role;
    // token-only (app) calls are always treated as a normal (non-admin) user.
    $isAdmin = (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'Admin');
    $filterUserId = !empty($inputData['user_id']) ? (int)$inputData['user_id'] : 0;
    $filterDate = !empty($inputData['date']) ? mysqli_real_escape_string($link, $inputData['date']) : '';

    $sql = "SELECT loc.*, u.sName
            FROM tbllocation loc
            LEFT JOIN tbluser u ON u.iUserid = loc.iUserid
            WHERE 1=1";
    if (!$isAdmin) {
        $sql .= " AND loc.iUserid = " . $userId;
    } elseif ($filterUserId > 0) {
        $sql .= " AND loc.iUserid = " . $filterUserId;
    }
    if ($filterDate !== '') {
        $sql .= " AND DATE(loc.sDateTime) = '" . $filterDate . "'";
    }
    $sql .= " ORDER BY loc.sDateTime DESC, loc.iLocationid DESC";

    $result = mysqli_query($link, $sql);
    if ($result) {
        $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
        sendResponse('success', 'Location data fetched successfully', $rows);
    } else {
        sendResponse('error', 'No location data found');
    }
}

// ---- Mobile App: Projects & Project Tasks (token-authenticated) ----

// tblproject_tasks is normally created lazily by project-api.php; the mobile
// app can hit these actions first (no browser session), so guarantee it here too.
function mobileEnsureProjectTasksTable(mysqli $link): void {
    static $done = false;
    if ($done) {
        return;
    }
    mysqli_query($link, "CREATE TABLE IF NOT EXISTS tblproject_tasks (
      id INT AUTO_INCREMENT PRIMARY KEY,
      lead_id INT NOT NULL,
      sTitle VARCHAR(255) NOT NULL,
      sDescription TEXT NULL,
      sAssigned_to INT NOT NULL,
      sCreated_by INT NOT NULL,
      sStatus VARCHAR(50) NOT NULL DEFAULT 'Pending',
      sDue_date DATE NULL,
      sCreated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      sUpdated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
      INDEX idx_lead (lead_id),
      INDEX idx_assigned (sAssigned_to),
      INDEX idx_status (sStatus)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $done = true;
}

/** Comma-separated "Assigned To" string -> array of positive int user IDs. */
function mobileNormalizeAssignedIds($value): array {
    $parts = preg_split('/\s*,\s*/', trim((string)$value), -1, PREG_SPLIT_NO_EMPTY);
    $ids = [];
    foreach ($parts as $part) {
        $id = (int)$part;
        if ($id > 0) {
            $ids[$id] = $id;
        }
    }
    return array_values($ids);
}

// Every project (lead) the authenticated user owns or is assigned to, with task counts.
if ($method == 'POST' && isset($inputData['action']) && $inputData['action'] === 'mobile_list_projects') {
    $userId = resolveAuthUserId($link, $inputData);
    if ($userId <= 0) {
        sendResponse('error', 'Unauthorized');
    }
    mobileEnsureProjectTasksTable($link);

    $sql = "SELECT
                l.iLead_id, l.sCompany_name, l.sLead_name, l.sPhone, l.sEmail,
                l.sAssigned_to, l.sLead_owner, l.sCreated_date,
                COALESCE(NULLIF(r.sStatus, ''), l.sLead_status) AS status_id,
                st.sStatus AS status_name,
                owner.sName AS lead_owner_name,
                (SELECT COUNT(*) FROM tblproject_tasks t WHERE t.lead_id = l.iLead_id) AS task_total,
                (SELECT COUNT(*) FROM tblproject_tasks t WHERE t.lead_id = l.iLead_id AND t.sStatus = 'Done') AS task_done,
                (SELECT COUNT(*) FROM tblproject_tasks t WHERE t.lead_id = l.iLead_id AND t.sAssigned_to = ?) AS my_tasks
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
            LEFT JOIN tbluser owner ON owner.iUserid = l.sLead_owner
            LEFT JOIN tblstatus st ON st.iStatusid = COALESCE(NULLIF(r.sStatus, ''), l.sLead_status)
            WHERE (" . sqlAssignedToHasUser('l.sAssigned_to') . " OR l.sLead_owner = ?)
            ORDER BY l.sCreated_date DESC, l.iLead_id DESC";

    $stmt = $link->prepare($sql);
    if (!$stmt) {
        sendResponse('error', 'Query prepare failed: ' . $link->error);
    }
    $stmt->bind_param('iii', $userId, $userId, $userId);
    if (!$stmt->execute()) {
        sendResponse('error', 'Query failed: ' . $stmt->error);
    }
    $result = $stmt->get_result();
    $projects = [];
    while ($row = $result->fetch_assoc()) {
        $projects[] = [
            'id' => (int)$row['iLead_id'],
            'company_name' => $row['sCompany_name'],
            'lead_name' => $row['sLead_name'],
            'phone' => $row['sPhone'],
            'email' => $row['sEmail'],
            'lead_owner_id' => (int)$row['sLead_owner'],
            'lead_owner_name' => $row['lead_owner_name'] ?: '',
            'created_date' => $row['sCreated_date'],
            'status_id' => $row['status_id'],
            'status_name' => $row['status_name'] ?: '',
            'task_total' => (int)$row['task_total'],
            'task_done' => (int)$row['task_done'],
            'my_tasks' => (int)$row['my_tasks'],
        ];
    }
    $stmt->close();
    sendResponse('success', 'Projects fetched successfully', $projects);
}

// Tasks assigned to the authenticated user (optionally scoped to one project / status).
if ($method == 'POST' && isset($inputData['action']) && $inputData['action'] === 'mobile_list_tasks') {
    $userId = resolveAuthUserId($link, $inputData);
    if ($userId <= 0) {
        sendResponse('error', 'Unauthorized');
    }
    mobileEnsureProjectTasksTable($link);

    $leadId = !empty($inputData['project_id']) ? (int)$inputData['project_id'] : 0;
    $statusFilter = !empty($inputData['status']) ? trim((string)$inputData['status']) : '';
    if ($statusFilter !== '' && !in_array($statusFilter, ['Pending', 'In Progress', 'Done'], true)) {
        $statusFilter = '';
    }

    if ($leadId > 0) {
        $chk = $link->prepare("SELECT sAssigned_to, sLead_owner FROM tblleads WHERE iLead_id = ? LIMIT 1");
        $chk->bind_param('i', $leadId);
        $chk->execute();
        $lead = $chk->get_result()->fetch_assoc();
        $chk->close();
        if (!$lead) {
            sendResponse('error', 'Project not found');
        }
        $assignedIds = mobileNormalizeAssignedIds($lead['sAssigned_to'] ?? '');
        $owner = (int)($lead['sLead_owner'] ?? 0);
        if (!in_array($userId, $assignedIds, true) && $owner !== $userId) {
            sendResponse('error', 'Access denied');
        }
    }

    $sql = "SELECT t.*, l.sCompany_name, l.sLead_name, a.sName AS assigned_name, c.sName AS created_by_name
            FROM tblproject_tasks t
            LEFT JOIN tblleads l ON l.iLead_id = t.lead_id
            LEFT JOIN tbluser a ON a.iUserid = t.sAssigned_to
            LEFT JOIN tbluser c ON c.iUserid = t.sCreated_by
            WHERE t.sAssigned_to = ?";
    $types = 'i';
    $params = [$userId];
    if ($leadId > 0) {
        $sql .= " AND t.lead_id = ?";
        $types .= 'i';
        $params[] = $leadId;
    }
    if ($statusFilter !== '') {
        $sql .= " AND t.sStatus = ?";
        $types .= 's';
        $params[] = $statusFilter;
    }
    $sql .= " ORDER BY FIELD(t.sStatus, 'Pending', 'In Progress', 'Done'), t.sDue_date IS NULL, t.sDue_date ASC, t.id DESC";

    $stmt = $link->prepare($sql);
    if (!$stmt) {
        sendResponse('error', 'Query prepare failed: ' . $link->error);
    }
    $stmt->bind_param($types, ...$params);
    if (!$stmt->execute()) {
        sendResponse('error', 'Query failed: ' . $stmt->error);
    }
    $result = $stmt->get_result();
    $tasks = [];
    while ($row = $result->fetch_assoc()) {
        $tasks[] = [
            'id' => (int)$row['id'],
            'project_id' => (int)$row['lead_id'],
            'project_name' => $row['sCompany_name'] ?: $row['sLead_name'],
            'title' => $row['sTitle'],
            'description' => $row['sDescription'],
            'assigned_to' => (int)$row['sAssigned_to'],
            'assigned_name' => $row['assigned_name'],
            'created_by' => (int)$row['sCreated_by'],
            'created_by_name' => $row['created_by_name'],
            'status' => $row['sStatus'],
            'due_date' => $row['sDue_date'],
            'created_at' => $row['sCreated_at'],
        ];
    }
    $stmt->close();
    sendResponse('success', 'Tasks fetched successfully', $tasks);
}

// ---- Sales Management: Client Payments ----
function paymentComputeStatus($amount, $received)
{
    $amount = (float)$amount;
    $received = (float)$received;
    if ($amount <= 0) {
        return 'Pending';
    }
    if ($received <= 0) {
        return 'Pending';
    }
    if ($received + 0.00001 >= $amount) {
        return 'Received';
    }
    return 'Partial';
}

function ensureClientPaymentTable($link)
{
    static $done = false;
    if ($done) {
        return;
    }
    mysqli_query($link, "CREATE TABLE IF NOT EXISTS tblclient_payment (
      iPaymentid INT AUTO_INCREMENT PRIMARY KEY,
      iCustomerid INT NULL,
      leadId INT NOT NULL DEFAULT 0,
      iQuotationid INT NULL,
      sClientname VARCHAR(255) NOT NULL,
      sInvoiceNo VARCHAR(100) NULL,
      sPaymentdate DATE NOT NULL,
      dAmount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
      dReceived DECIMAL(12,2) NOT NULL DEFAULT 0.00,
      dPending DECIMAL(12,2) NOT NULL DEFAULT 0.00,
      sStatus VARCHAR(20) NOT NULL DEFAULT 'Pending',
      sMode VARCHAR(50) NULL,
      sReference VARCHAR(100) NULL,
      sNotes TEXT NULL,
      iUserid INT NULL,
      sCreatedTimeStamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      sModifiedTimestamp DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
      INDEX idx_customer (iCustomerid),
      INDEX idx_lead (leadId),
      INDEX idx_status (sStatus),
      INDEX idx_date (sPaymentdate)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $done = true;
}

if ($method == 'POST' && isset($inputData['action']) && $inputData['action'] === 'addpayment') {
    if (!isset($_SESSION['user_id'])) {
        sendResponse('error', 'Unauthorized');
    }
    ensureClientPaymentTable($link);

    $clientName = trim((string)($inputData['sClientname'] ?? ''));
    $paymentDate = trim((string)($inputData['sPaymentdate'] ?? ''));
    $leadId = (int)($inputData['leadId'] ?? 0);
    if ($leadId <= 0) {
        sendResponse('error', 'Please select a Won client');
    }
    if ($paymentDate === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $paymentDate)) {
        sendResponse('error', 'Valid payment date is required');
    }

    $customerId = (int)($inputData['iCustomerid'] ?? 0);
    $invoiceNo = trim((string)($inputData['sInvoiceNo'] ?? ''));
    $amount = round((float)($inputData['dAmount'] ?? 0), 2);
    $received = round((float)($inputData['dReceived'] ?? 0), 2);
    if ($amount < 0) $amount = 0;
    if ($received < 0) $received = 0;
    if ($received > $amount) $received = $amount;
    $pending = round(max(0, $amount - $received), 2);
    $status = paymentComputeStatus($amount, $received);
    $mode = trim((string)($inputData['sMode'] ?? ''));
    $reference = trim((string)($inputData['sReference'] ?? ''));
    $notes = trim((string)($inputData['sNotes'] ?? ''));
    $userId = (int)$_SESSION['user_id'];
    $quotationId = 0;

    // Prefer company name from selected Won lead
    $ls = $link->prepare('SELECT sCompany_name FROM tblleads WHERE iLead_id = ? LIMIT 1');
    if ($ls) {
        $ls->bind_param('i', $leadId);
        $ls->execute();
        $lrow = $ls->get_result()->fetch_assoc();
        $ls->close();
        if ($lrow && trim((string)$lrow['sCompany_name']) !== '') {
            $clientName = trim((string)$lrow['sCompany_name']);
        }
    }
    if ($clientName === '') {
        sendResponse('error', 'Client name is required');
    }

    // Resolve customer id by company name if not provided
    if ($customerId <= 0) {
        $cs = $link->prepare('SELECT iCustomerid FROM tblcustomer WHERE LOWER(TRIM(sCompanyname)) = LOWER(?) LIMIT 1');
        if ($cs) {
            $cs->bind_param('s', $clientName);
            $cs->execute();
            $crow = $cs->get_result()->fetch_assoc();
            $cs->close();
            if ($crow) {
                $customerId = (int)$crow['iCustomerid'];
            }
        }
    }
    if ($customerId <= 0) {
        $customerId = 0;
    }

    $stmt = $link->prepare('INSERT INTO tblclient_payment
        (iCustomerid, leadId, iQuotationid, sClientname, sInvoiceNo, sPaymentdate, dAmount, dReceived, dPending, sStatus, sMode, sReference, sNotes, iUserid)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    if (!$stmt) {
        sendResponse('error', 'Database error: ' . mysqli_error($link));
    }
    $stmt->bind_param(
        'iiisssdddssssi',
        $customerId,
        $leadId,
        $quotationId,
        $clientName,
        $invoiceNo,
        $paymentDate,
        $amount,
        $received,
        $pending,
        $status,
        $mode,
        $reference,
        $notes,
        $userId
    );
    $ok = $stmt->execute();
    $err = $stmt->error;
    $stmt->close();
    if (!$ok) {
        sendResponse('error', 'Payment not saved: ' . $err);
    }
    sendResponse('success', 'Payment saved successfully');
}

if ($method == 'POST' && isset($inputData['action']) && $inputData['action'] === 'fngetlistpayment') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
    ensureClientPaymentTable($link);

    $statusFilter = trim((string)($inputData['status'] ?? ''));
    $clientFilter = trim((string)($inputData['client'] ?? ''));

    $sql = 'SELECT * FROM tblclient_payment WHERE 1=1';
    $types = '';
    $params = [];
    if ($statusFilter !== '' && in_array($statusFilter, ['Pending', 'Partial', 'Received'], true)) {
        $sql .= ' AND sStatus = ?';
        $types .= 's';
        $params[] = $statusFilter;
    }
    if ($clientFilter !== '') {
        $sql .= ' AND sClientname LIKE ?';
        $types .= 's';
        $params[] = '%' . $clientFilter . '%';
    }
    $sql .= ' ORDER BY sPaymentdate DESC, iPaymentid DESC';

    $rows = [];
    if ($types !== '') {
        $stmt = $link->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();
    } else {
        $result = mysqli_query($link, $sql);
        if ($result) {
            $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
        }
    }

    $summary = [
        'total_amount' => 0,
        'total_received' => 0,
        'total_pending' => 0,
        'open_count' => 0,
    ];
    // Summary across filtered rows
    foreach ($rows as $r) {
        $summary['total_amount'] += (float)$r['dAmount'];
        $summary['total_received'] += (float)$r['dReceived'];
        $summary['total_pending'] += (float)$r['dPending'];
        if (($r['sStatus'] ?? '') !== 'Received') {
            $summary['open_count']++;
        }
    }

    echo json_encode(['status' => 'success', 'data' => $rows, 'summary' => $summary]);
    exit;
}

if ($method == 'POST' && isset($inputData['action']) && $inputData['action'] === 'getpaymentbyid') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
    ensureClientPaymentTable($link);
    $id = (int)($inputData['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid payment ID']);
        exit;
    }
    $stmt = $link->prepare('SELECT * FROM tblclient_payment WHERE iPaymentid = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) {
        echo json_encode(['status' => 'error', 'message' => 'Payment not found']);
        exit;
    }
    echo json_encode(['status' => 'success', 'data' => $row]);
    exit;
}

if ($method == 'POST' && isset($inputData['action']) && $inputData['action'] === 'updatepayment') {
    if (!isset($_SESSION['user_id'])) {
        sendResponse('error', 'Unauthorized');
    }
    ensureClientPaymentTable($link);

    $id = (int)($inputData['id'] ?? 0);
    $clientName = trim((string)($inputData['sClientname'] ?? ''));
    $paymentDate = trim((string)($inputData['sPaymentdate'] ?? ''));
    $leadId = (int)($inputData['leadId'] ?? 0);
    if ($id <= 0 || $leadId <= 0 || $paymentDate === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $paymentDate)) {
        sendResponse('error', 'Required fields missing. Please select a Won client.');
    }

    $customerId = (int)($inputData['iCustomerid'] ?? 0);
    $invoiceNo = trim((string)($inputData['sInvoiceNo'] ?? ''));
    $amount = round((float)($inputData['dAmount'] ?? 0), 2);
    $received = round((float)($inputData['dReceived'] ?? 0), 2);
    if ($amount < 0) $amount = 0;
    if ($received < 0) $received = 0;
    if ($received > $amount) $received = $amount;
    $pending = round(max(0, $amount - $received), 2);
    $status = paymentComputeStatus($amount, $received);
    $mode = trim((string)($inputData['sMode'] ?? ''));
    $reference = trim((string)($inputData['sReference'] ?? ''));
    $notes = trim((string)($inputData['sNotes'] ?? ''));

    $ls = $link->prepare('SELECT sCompany_name FROM tblleads WHERE iLead_id = ? LIMIT 1');
    if ($ls) {
        $ls->bind_param('i', $leadId);
        $ls->execute();
        $lrow = $ls->get_result()->fetch_assoc();
        $ls->close();
        if ($lrow && trim((string)$lrow['sCompany_name']) !== '') {
            $clientName = trim((string)$lrow['sCompany_name']);
        }
    }
    if ($clientName === '') {
        sendResponse('error', 'Client name is required');
    }
    if ($customerId <= 0) {
        $cs = $link->prepare('SELECT iCustomerid FROM tblcustomer WHERE LOWER(TRIM(sCompanyname)) = LOWER(?) LIMIT 1');
        if ($cs) {
            $cs->bind_param('s', $clientName);
            $cs->execute();
            $crow = $cs->get_result()->fetch_assoc();
            $cs->close();
            if ($crow) {
                $customerId = (int)$crow['iCustomerid'];
            }
        }
    }

    $stmt = $link->prepare('UPDATE tblclient_payment SET
        iCustomerid = ?, leadId = ?, sClientname = ?, sInvoiceNo = ?, sPaymentdate = ?,
        dAmount = ?, dReceived = ?, dPending = ?, sStatus = ?, sMode = ?, sReference = ?, sNotes = ?
        WHERE iPaymentid = ?');
    if (!$stmt) {
        sendResponse('error', 'Database error: ' . mysqli_error($link));
    }
    $stmt->bind_param(
        'iisssdddssssi',
        $customerId,
        $leadId,
        $clientName,
        $invoiceNo,
        $paymentDate,
        $amount,
        $received,
        $pending,
        $status,
        $mode,
        $reference,
        $notes,
        $id
    );
    $ok = $stmt->execute();
    $err = $stmt->error;
    $stmt->close();
    if (!$ok) {
        sendResponse('error', 'Payment not updated: ' . $err);
    }
    sendResponse('success', 'Payment updated successfully');
}

if ($method == 'POST' && isset($inputData['action']) && $inputData['action'] === 'deletepayment') {
    if (!isset($_SESSION['user_id'])) {
        sendResponse('error', 'Unauthorized');
    }
    ensureClientPaymentTable($link);
    $id = (int)($inputData['id'] ?? 0);
    if ($id <= 0) {
        sendResponse('error', 'Invalid payment ID');
    }
    $stmt = mysqli_prepare($link, 'DELETE FROM tblclient_payment WHERE iPaymentid = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ret = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    if (!$ret) {
        sendResponse('error', 'Payment not deleted: ' . mysqli_error($link));
    }
    sendResponse('success', 'Payment deleted successfully');
}

// ==================== P&L Account Statement ====================

include_once __DIR__ . '/layouts/crm-access.php';

function ensurePnlTable($link) {
    static $done = false;
    if ($done) {
        return;
    }
    mysqli_query($link, "CREATE TABLE IF NOT EXISTS tblpnl_entry (
      iPnLid INT AUTO_INCREMENT PRIMARY KEY,
      sType VARCHAR(20) NOT NULL DEFAULT 'Income',
      sCategory VARCHAR(100) NOT NULL DEFAULT '',
      sParticulars VARCHAR(255) NOT NULL DEFAULT '',
      sEntrydate DATE NOT NULL,
      dAmount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
      sReference VARCHAR(100) NULL,
      sNotes TEXT NULL,
      iUserid INT NULL,
      sCreatedTimeStamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      sModifiedTimestamp DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
      INDEX idx_type (sType),
      INDEX idx_category (sCategory),
      INDEX idx_date (sEntrydate)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $done = true;
}

function requirePnlFinanceAdmin($link) {
    if (!isset($_SESSION['user_id'])) {
        sendResponse('error', 'Unauthorized');
    }
    if (!crmIsFinanceAdmin($link)) {
        sendResponse('error', 'Access denied. Only Finance Admin can manage P&L.');
    }
}

if ($method == 'POST' && isset($inputData['action']) && $inputData['action'] === 'addpnl') {
    requirePnlFinanceAdmin($link);
    ensurePnlTable($link);

    $type = trim((string)($inputData['sType'] ?? 'Income'));
    if ($type !== 'Income' && $type !== 'Expense') {
        $type = 'Income';
    }
    $category = trim((string)($inputData['sCategory'] ?? ''));
    $particulars = trim((string)($inputData['sParticulars'] ?? ''));
    $entryDate = trim((string)($inputData['sEntrydate'] ?? ''));
    $amount = round((float)($inputData['dAmount'] ?? 0), 2);
    $reference = trim((string)($inputData['sReference'] ?? ''));
    $notes = trim((string)($inputData['sNotes'] ?? ''));
    $userId = (int)$_SESSION['user_id'];

    if ($particulars === '') {
        sendResponse('error', 'Particulars / description is required');
    }
    if ($entryDate === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $entryDate)) {
        sendResponse('error', 'Valid entry date is required');
    }
    if ($amount <= 0) {
        sendResponse('error', 'Amount must be greater than zero');
    }
    if ($category === '') {
        $category = $type === 'Income' ? 'Sales' : 'General Expense';
    }

    $stmt = $link->prepare('INSERT INTO tblpnl_entry
        (sType, sCategory, sParticulars, sEntrydate, dAmount, sReference, sNotes, iUserid)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    if (!$stmt) {
        sendResponse('error', 'Database error: ' . mysqli_error($link));
    }
    $stmt->bind_param('ssssdssi', $type, $category, $particulars, $entryDate, $amount, $reference, $notes, $userId);
    $ok = $stmt->execute();
    $err = $stmt->error;
    $stmt->close();
    if (!$ok) {
        sendResponse('error', 'P&L entry not saved: ' . $err);
    }
    sendResponse('success', 'P&L entry saved successfully');
}

if ($method == 'POST' && isset($inputData['action']) && $inputData['action'] === 'updatepnl') {
    requirePnlFinanceAdmin($link);
    ensurePnlTable($link);

    $id = (int)($inputData['id'] ?? 0);
    if ($id <= 0) {
        sendResponse('error', 'Invalid entry ID');
    }

    $type = trim((string)($inputData['sType'] ?? 'Income'));
    if ($type !== 'Income' && $type !== 'Expense') {
        $type = 'Income';
    }
    $category = trim((string)($inputData['sCategory'] ?? ''));
    $particulars = trim((string)($inputData['sParticulars'] ?? ''));
    $entryDate = trim((string)($inputData['sEntrydate'] ?? ''));
    $amount = round((float)($inputData['dAmount'] ?? 0), 2);
    $reference = trim((string)($inputData['sReference'] ?? ''));
    $notes = trim((string)($inputData['sNotes'] ?? ''));

    if ($particulars === '') {
        sendResponse('error', 'Particulars / description is required');
    }
    if ($entryDate === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $entryDate)) {
        sendResponse('error', 'Valid entry date is required');
    }
    if ($amount <= 0) {
        sendResponse('error', 'Amount must be greater than zero');
    }
    if ($category === '') {
        $category = $type === 'Income' ? 'Sales' : 'General Expense';
    }

    $stmt = $link->prepare('UPDATE tblpnl_entry SET
        sType=?, sCategory=?, sParticulars=?, sEntrydate=?, dAmount=?, sReference=?, sNotes=?
        WHERE iPnLid=?');
    if (!$stmt) {
        sendResponse('error', 'Database error: ' . mysqli_error($link));
    }
    $stmt->bind_param('ssssdssi', $type, $category, $particulars, $entryDate, $amount, $reference, $notes, $id);
    $ok = $stmt->execute();
    $err = $stmt->error;
    $stmt->close();
    if (!$ok) {
        sendResponse('error', 'P&L entry not updated: ' . $err);
    }
    sendResponse('success', 'P&L entry updated successfully');
}

if ($method == 'POST' && isset($inputData['action']) && $inputData['action'] === 'getpnlbyid') {
    requirePnlFinanceAdmin($link);
    ensurePnlTable($link);
    $id = (int)($inputData['id'] ?? 0);
    if ($id <= 0) {
        sendResponse('error', 'Invalid entry ID');
    }
    $stmt = $link->prepare('SELECT * FROM tblpnl_entry WHERE iPnLid = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) {
        sendResponse('error', 'Entry not found');
    }
    echo json_encode(['status' => 'success', 'message' => 'OK', 'data' => $row]);
    exit;
}

if ($method == 'POST' && isset($inputData['action']) && $inputData['action'] === 'fngetlistpnl') {
    requirePnlFinanceAdmin($link);
    ensurePnlTable($link);

    $type = trim((string)($inputData['type'] ?? ''));
    $from = trim((string)($inputData['from'] ?? ''));
    $to = trim((string)($inputData['to'] ?? ''));
    $search = trim((string)($inputData['search'] ?? ''));

    $sql = 'SELECT * FROM tblpnl_entry WHERE 1=1';
    $types = '';
    $params = [];

    if ($type === 'Income' || $type === 'Expense') {
        $sql .= ' AND sType = ?';
        $types .= 's';
        $params[] = $type;
    }
    if ($from !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
        $sql .= ' AND sEntrydate >= ?';
        $types .= 's';
        $params[] = $from;
    }
    if ($to !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
        $sql .= ' AND sEntrydate <= ?';
        $types .= 's';
        $params[] = $to;
    }
    if ($search !== '') {
        $sql .= ' AND (sParticulars LIKE ? OR sCategory LIKE ? OR sReference LIKE ? OR sNotes LIKE ?)';
        $like = '%' . $search . '%';
        $types .= 'ssss';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }
    $sql .= ' ORDER BY sEntrydate DESC, iPnLid DESC';

    $rows = [];
    $totalIncome = 0;
    $totalExpense = 0;

    if ($types !== '') {
        $stmt = $link->prepare($sql);
        if (!$stmt) {
            sendResponse('error', 'Database error: ' . mysqli_error($link));
        }
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
            $amt = (float)$row['dAmount'];
            if ($row['sType'] === 'Income') {
                $totalIncome += $amt;
            } else {
                $totalExpense += $amt;
            }
        }
        $stmt->close();
    } else {
        $result = mysqli_query($link, $sql);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $rows[] = $row;
                $amt = (float)$row['dAmount'];
                if ($row['sType'] === 'Income') {
                    $totalIncome += $amt;
                } else {
                    $totalExpense += $amt;
                }
            }
        }
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'OK',
        'data' => $rows,
        'summary' => [
            'total_income' => round($totalIncome, 2),
            'total_expense' => round($totalExpense, 2),
            'net_profit' => round($totalIncome - $totalExpense, 2),
            'count' => count($rows)
        ]
    ]);
    exit;
}

if ($method == 'POST' && isset($inputData['action']) && $inputData['action'] === 'getpnlstatement') {
    requirePnlFinanceAdmin($link);
    ensurePnlTable($link);

    $from = trim((string)($inputData['from'] ?? ''));
    $to = trim((string)($inputData['to'] ?? ''));
    if ($from === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
        $from = date('Y-m-01');
    }
    if ($to === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
        $to = date('Y-m-d');
    }

    $stmt = $link->prepare('SELECT * FROM tblpnl_entry WHERE sEntrydate >= ? AND sEntrydate <= ? ORDER BY sType ASC, sCategory ASC, sEntrydate ASC');
    $stmt->bind_param('ss', $from, $to);
    $stmt->execute();
    $result = $stmt->get_result();

    $incomeByCat = [];
    $expenseByCat = [];
    $incomeRows = [];
    $expenseRows = [];
    $totalIncome = 0;
    $totalExpense = 0;

    while ($row = $result->fetch_assoc()) {
        $amt = (float)$row['dAmount'];
        $cat = $row['sCategory'] !== '' ? $row['sCategory'] : 'Uncategorized';
        if ($row['sType'] === 'Income') {
            $incomeRows[] = $row;
            $totalIncome += $amt;
            if (!isset($incomeByCat[$cat])) $incomeByCat[$cat] = 0;
            $incomeByCat[$cat] += $amt;
        } else {
            $expenseRows[] = $row;
            $totalExpense += $amt;
            if (!isset($expenseByCat[$cat])) $expenseByCat[$cat] = 0;
            $expenseByCat[$cat] += $amt;
        }
    }
    $stmt->close();

    $incomeCategories = [];
    foreach ($incomeByCat as $cat => $sum) {
        $incomeCategories[] = ['category' => $cat, 'amount' => round($sum, 2)];
    }
    $expenseCategories = [];
    foreach ($expenseByCat as $cat => $sum) {
        $expenseCategories[] = ['category' => $cat, 'amount' => round($sum, 2)];
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'OK',
        'from' => $from,
        'to' => $to,
        'income' => $incomeRows,
        'expense' => $expenseRows,
        'income_by_category' => $incomeCategories,
        'expense_by_category' => $expenseCategories,
        'summary' => [
            'total_income' => round($totalIncome, 2),
            'total_expense' => round($totalExpense, 2),
            'net_profit' => round($totalIncome - $totalExpense, 2)
        ]
    ]);
    exit;
}

if ($method == 'POST' && isset($inputData['action']) && $inputData['action'] === 'deletepnl') {
    requirePnlFinanceAdmin($link);
    ensurePnlTable($link);
    $id = (int)($inputData['id'] ?? 0);
    if ($id <= 0) {
        sendResponse('error', 'Invalid entry ID');
    }
    $stmt = mysqli_prepare($link, 'DELETE FROM tblpnl_entry WHERE iPnLid = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ret = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    if (!$ret) {
        sendResponse('error', 'P&L entry not deleted: ' . mysqli_error($link));
    }
    sendResponse('success', 'P&L entry deleted successfully');
}

// ==================== Re-engage module ====================

function ensureReengageTable($link) {
    static $done = false;
    if ($done) {
        return;
    }
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
    $done = true;
}

if ($method == 'POST' && isset($inputData['action']) && $inputData['action'] === 'addreengage') {
    if (!isset($_SESSION['user_id'])) {
        sendResponse('error', 'Unauthorized');
    }
    ensureReengageTable($link);

    $userId = (int)($inputData['iUserid'] ?? 0);
    $leadId = (int)($inputData['iLeadid'] ?? 0);
    $company = trim((string)($inputData['sCompanyname'] ?? ''));
    $description = trim((string)($inputData['sDescription'] ?? ''));
    $date = trim((string)($inputData['sDate'] ?? ''));
    $createdBy = (int)$_SESSION['user_id'];

    if ($userId <= 0) {
        sendResponse('error', 'Please select a user');
    }
    if ($leadId <= 0 && $company === '') {
        sendResponse('error', 'Please select a company from leads');
    }
    if ($date === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $date = date('Y-m-d');
    }

    if ($leadId > 0 && $company === '') {
        $ls = $link->prepare('SELECT sCompany_name FROM tblleads WHERE iLead_id = ? LIMIT 1');
        if ($ls) {
            $ls->bind_param('i', $leadId);
            $ls->execute();
            $lrow = $ls->get_result()->fetch_assoc();
            $ls->close();
            if ($lrow) {
                $company = trim((string)$lrow['sCompany_name']);
            }
        }
    }
    if ($company === '') {
        sendResponse('error', 'Company name is required');
    }

    $stmt = $link->prepare('INSERT INTO tblreengage (iUserid, iLeadid, sCompanyname, sDescription, sDate, iCreatedBy) VALUES (?, ?, ?, ?, ?, ?)');
    if (!$stmt) {
        sendResponse('error', 'Database error: ' . mysqli_error($link));
    }
    $stmt->bind_param('iisssi', $userId, $leadId, $company, $description, $date, $createdBy);
    $ok = $stmt->execute();
    $err = $stmt->error;
    $stmt->close();
    if (!$ok) {
        sendResponse('error', 'Re-engage not saved: ' . $err);
    }
    sendResponse('success', 'Re-engage saved successfully');
}

if ($method == 'POST' && isset($inputData['action']) && $inputData['action'] === 'updatereengage') {
    if (!isset($_SESSION['user_id'])) {
        sendResponse('error', 'Unauthorized');
    }
    ensureReengageTable($link);

    $id = (int)($inputData['id'] ?? 0);
    $userId = (int)($inputData['iUserid'] ?? 0);
    $leadId = (int)($inputData['iLeadid'] ?? 0);
    $company = trim((string)($inputData['sCompanyname'] ?? ''));
    $description = trim((string)($inputData['sDescription'] ?? ''));
    $date = trim((string)($inputData['sDate'] ?? ''));

    if ($id <= 0) {
        sendResponse('error', 'Invalid entry ID');
    }
    if ($userId <= 0) {
        sendResponse('error', 'Please select a user');
    }
    if ($date === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        sendResponse('error', 'Valid date is required');
    }

    if ($leadId > 0 && $company === '') {
        $ls = $link->prepare('SELECT sCompany_name FROM tblleads WHERE iLead_id = ? LIMIT 1');
        if ($ls) {
            $ls->bind_param('i', $leadId);
            $ls->execute();
            $lrow = $ls->get_result()->fetch_assoc();
            $ls->close();
            if ($lrow) {
                $company = trim((string)$lrow['sCompany_name']);
            }
        }
    }
    if ($company === '') {
        sendResponse('error', 'Please select a company from leads');
    }

    $stmt = $link->prepare('UPDATE tblreengage SET iUserid=?, iLeadid=?, sCompanyname=?, sDescription=?, sDate=? WHERE iReengageid=?');
    if (!$stmt) {
        sendResponse('error', 'Database error: ' . mysqli_error($link));
    }
    $stmt->bind_param('iisssi', $userId, $leadId, $company, $description, $date, $id);
    $ok = $stmt->execute();
    $err = $stmt->error;
    $stmt->close();
    if (!$ok) {
        sendResponse('error', 'Re-engage not updated: ' . $err);
    }
    sendResponse('success', 'Re-engage updated successfully');
}

if ($method == 'POST' && isset($inputData['action']) && $inputData['action'] === 'getreengagebyid') {
    if (!isset($_SESSION['user_id'])) {
        sendResponse('error', 'Unauthorized');
    }
    ensureReengageTable($link);
    $id = (int)($inputData['id'] ?? 0);
    if ($id <= 0) {
        sendResponse('error', 'Invalid entry ID');
    }
    $stmt = $link->prepare('SELECT r.*, u.sName AS user_name FROM tblreengage r LEFT JOIN tbluser u ON u.iUserid = r.iUserid WHERE r.iReengageid = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) {
        sendResponse('error', 'Entry not found');
    }
    echo json_encode(['status' => 'success', 'message' => 'OK', 'data' => $row]);
    exit;
}

if ($method == 'POST' && isset($inputData['action']) && $inputData['action'] === 'fngetlistreengage') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized', 'data' => []]);
        exit;
    }
    ensureReengageTable($link);

    $userId = (int)$_SESSION['user_id'];
    $isAdmin = (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'Admin');
    $filterDate = trim((string)($inputData['date'] ?? ''));
    $filterUser = (int)($inputData['userid'] ?? 0);

    $sql = "SELECT r.*, u.sName AS user_name
            FROM tblreengage r
            LEFT JOIN tbluser u ON u.iUserid = r.iUserid
            WHERE 1=1";
    $types = '';
    $params = [];

    if (!$isAdmin) {
        $sql .= ' AND r.iUserid = ?';
        $types .= 'i';
        $params[] = $userId;
    } elseif ($filterUser > 0) {
        $sql .= ' AND r.iUserid = ?';
        $types .= 'i';
        $params[] = $filterUser;
    }
    if ($filterDate !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filterDate)) {
        $sql .= ' AND r.sDate = ?';
        $types .= 's';
        $params[] = $filterDate;
    }
    $sql .= ' ORDER BY r.sDate DESC, r.iReengageid DESC';

    $rows = [];
    if ($types !== '') {
        $stmt = $link->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();
    } else {
        $result = mysqli_query($link, $sql);
        if ($result) {
            $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
        }
    }

    echo json_encode(['status' => 'success', 'data' => $rows]);
    exit;
}

if ($method == 'POST' && isset($inputData['action']) && $inputData['action'] === 'gettodaysreengage') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized', 'data' => [], 'count' => 0]);
        exit;
    }
    ensureReengageTable($link);
    date_default_timezone_set('Asia/Kolkata');
    $today = date('Y-m-d');
    $userId = (int)$_SESSION['user_id'];
    $isAdmin = (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'Admin');

    if ($isAdmin) {
        $stmt = $link->prepare(
            "SELECT r.*, u.sName AS user_name
             FROM tblreengage r
             LEFT JOIN tbluser u ON u.iUserid = r.iUserid
             WHERE r.sDate = ?
             ORDER BY r.iReengageid DESC
             LIMIT 50"
        );
        $stmt->bind_param('s', $today);
    } else {
        $stmt = $link->prepare(
            "SELECT r.*, u.sName AS user_name
             FROM tblreengage r
             LEFT JOIN tbluser u ON u.iUserid = r.iUserid
             WHERE r.iUserid = ? AND r.sDate = ?
             ORDER BY r.iReengageid DESC
             LIMIT 50"
        );
        $stmt->bind_param('is', $userId, $today);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();

    echo json_encode(['status' => 'success', 'data' => $rows, 'count' => count($rows), 'date' => $today]);
    exit;
}

if ($method == 'POST' && isset($inputData['action']) && $inputData['action'] === 'deletereengage') {
    if (!isset($_SESSION['user_id'])) {
        sendResponse('error', 'Unauthorized');
    }
    ensureReengageTable($link);
    $id = (int)($inputData['id'] ?? 0);
    if ($id <= 0) {
        sendResponse('error', 'Invalid entry ID');
    }

    $userId = (int)$_SESSION['user_id'];
    $isAdmin = (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'Admin');

    if (!$isAdmin) {
        $check = $link->prepare('SELECT iUserid, iCreatedBy FROM tblreengage WHERE iReengageid = ?');
        $check->bind_param('i', $id);
        $check->execute();
        $existing = $check->get_result()->fetch_assoc();
        $check->close();
        if (!$existing) {
            sendResponse('error', 'Entry not found');
        }
        if ((int)$existing['iUserid'] !== $userId && (int)$existing['iCreatedBy'] !== $userId) {
            sendResponse('error', 'You can only delete your own re-engage entries');
        }
    }

    $stmt = mysqli_prepare($link, 'DELETE FROM tblreengage WHERE iReengageid = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ret = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    if (!$ret) {
        sendResponse('error', 'Re-engage not deleted: ' . mysqli_error($link));
    }
    sendResponse('success', 'Re-engage deleted successfully');
}

// ==================== Client Management (software buyers + monthly billing) ====================

function ensureSoftwareClientTables($link)
{
    static $done = false;
    if ($done) {
        return;
    }
    mysqli_query($link, "CREATE TABLE IF NOT EXISTS tblsoftware_client (
      iClientid INT AUTO_INCREMENT PRIMARY KEY,
      sClientname VARCHAR(255) NOT NULL,
      sCompanyname VARCHAR(255) NULL,
      sContactperson VARCHAR(255) NULL,
      sEmail VARCHAR(150) NULL,
      sPhone VARCHAR(50) NULL,
      sPlan VARCHAR(100) NULL,
      dMonthlyamount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
      iBillingday INT NOT NULL DEFAULT 1,
      sStartdate DATE NULL,
      sStatus VARCHAR(20) NOT NULL DEFAULT 'Active',
      sAddress TEXT NULL,
      sNotes TEXT NULL,
      iUserid INT NULL,
      sCreatedTimeStamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      sModifiedTimestamp DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
      INDEX idx_status (sStatus),
      INDEX idx_name (sClientname)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    mysqli_query($link, "CREATE TABLE IF NOT EXISTS tblclient_monthly (
      iMonthlyid INT AUTO_INCREMENT PRIMARY KEY,
      iClientid INT NOT NULL,
      sClientname VARCHAR(255) NOT NULL,
      sMonth VARCHAR(7) NOT NULL,
      sDuedate DATE NOT NULL,
      dAmount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
      dReceived DECIMAL(12,2) NOT NULL DEFAULT 0.00,
      dPending DECIMAL(12,2) NOT NULL DEFAULT 0.00,
      sStatus VARCHAR(20) NOT NULL DEFAULT 'Pending',
      sPaiddate DATE NULL,
      sMode VARCHAR(50) NULL,
      sInvoiceNo VARCHAR(100) NULL,
      sReport TEXT NULL,
      sNotes TEXT NULL,
      iUserid INT NULL,
      sCreatedTimeStamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      sModifiedTimestamp DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
      UNIQUE KEY uq_client_month (iClientid, sMonth),
      INDEX idx_client (iClientid),
      INDEX idx_month (sMonth),
      INDEX idx_due (sDuedate),
      INDEX idx_status (sStatus)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $done = true;
}

function clientMgmtComputeStatus($amount, $received, $dueDate = null)
{
    $amount = (float)$amount;
    $received = (float)$received;
    if ($amount > 0 && $received + 0.00001 >= $amount) {
        return 'Paid';
    }
    if ($received > 0) {
        return 'Partial';
    }
    if ($dueDate && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate) && $dueDate < date('Y-m-d')) {
        return 'Overdue';
    }
    return 'Pending';
}

function clientMgmtDueDate($yearMonth, $billingDay)
{
    $billingDay = max(1, min(28, (int)$billingDay));
    if (!preg_match('/^\d{4}-\d{2}$/', $yearMonth)) {
        return date('Y-m-d');
    }
    $parts = explode('-', $yearMonth);
    $y = (int)$parts[0];
    $m = (int)$parts[1];
    $lastDay = (int)date('t', mktime(0, 0, 0, $m, 1, $y));
    $day = min($billingDay, $lastDay);
    return sprintf('%04d-%02d-%02d', $y, $m, $day);
}

function clientMgmtRefreshOverdue($link)
{
    $today = date('Y-m-d');
    mysqli_query($link, "UPDATE tblclient_monthly
        SET sStatus = 'Overdue'
        WHERE sDuedate < '{$today}'
          AND dPending > 0
          AND sStatus IN ('Pending', 'Overdue')");
}

if ($method == 'POST' && isset($inputData['action']) && $inputData['action'] === 'addsoftwareclient') {
    if (!isset($_SESSION['user_id'])) {
        sendResponse('error', 'Unauthorized');
    }
    ensureSoftwareClientTables($link);

    $clientName = trim((string)($inputData['sClientname'] ?? ''));
    if ($clientName === '') {
        sendResponse('error', 'Client name is required');
    }
    $company = trim((string)($inputData['sCompanyname'] ?? ''));
    $contact = trim((string)($inputData['sContactperson'] ?? ''));
    $email = trim((string)($inputData['sEmail'] ?? ''));
    $phone = trim((string)($inputData['sPhone'] ?? ''));
    $plan = trim((string)($inputData['sPlan'] ?? ''));
    $amount = round((float)($inputData['dMonthlyamount'] ?? 0), 2);
    if ($amount < 0) {
        $amount = 0;
    }
    $billingDay = (int)($inputData['iBillingday'] ?? 1);
    if ($billingDay < 1) {
        $billingDay = 1;
    }
    if ($billingDay > 28) {
        $billingDay = 28;
    }
    $startDate = trim((string)($inputData['sStartdate'] ?? ''));
    if ($startDate === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
        $startDate = date('Y-m-d');
    }
    $status = trim((string)($inputData['sStatus'] ?? 'Active'));
    if (!in_array($status, ['Active', 'Inactive', 'Suspended'], true)) {
        $status = 'Active';
    }
    $address = trim((string)($inputData['sAddress'] ?? ''));
    $notes = trim((string)($inputData['sNotes'] ?? ''));
    $userId = (int)$_SESSION['user_id'];

    $stmt = $link->prepare('INSERT INTO tblsoftware_client
        (sClientname, sCompanyname, sContactperson, sEmail, sPhone, sPlan, dMonthlyamount, iBillingday, sStartdate, sStatus, sAddress, sNotes, iUserid)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    if (!$stmt) {
        sendResponse('error', 'Database error: ' . mysqli_error($link));
    }
    $stmt->bind_param(
        'ssssssdissssi',
        $clientName,
        $company,
        $contact,
        $email,
        $phone,
        $plan,
        $amount,
        $billingDay,
        $startDate,
        $status,
        $address,
        $notes,
        $userId
    );
    $ok = $stmt->execute();
    $err = $stmt->error;
    $newId = (int)$stmt->insert_id;
    $stmt->close();
    if (!$ok) {
        sendResponse('error', 'Client not saved: ' . $err);
    }
    sendResponse('success', 'Client saved successfully', ['id' => $newId]);
}

if ($method == 'POST' && isset($inputData['action']) && $inputData['action'] === 'fngetlistsoftwareclient') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
    ensureSoftwareClientTables($link);

    $statusFilter = trim((string)($inputData['status'] ?? ''));
    $search = trim((string)($inputData['search'] ?? ''));

    $sql = 'SELECT * FROM tblsoftware_client WHERE 1=1';
    $types = '';
    $params = [];
    if ($statusFilter !== '' && in_array($statusFilter, ['Active', 'Inactive', 'Suspended'], true)) {
        $sql .= ' AND sStatus = ?';
        $types .= 's';
        $params[] = $statusFilter;
    }
    if ($search !== '') {
        $sql .= ' AND (sClientname LIKE ? OR sCompanyname LIKE ? OR sPhone LIKE ? OR sEmail LIKE ?)';
        $types .= 'ssss';
        $like = '%' . $search . '%';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }
    $sql .= ' ORDER BY sClientname ASC, iClientid DESC';

    $rows = [];
    if ($types !== '') {
        $stmt = $link->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();
    } else {
        $result = mysqli_query($link, $sql);
        if ($result) {
            $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
        }
    }

    $summary = [
        'total_clients' => count($rows),
        'active_clients' => 0,
        'monthly_revenue' => 0,
    ];
    foreach ($rows as $r) {
        if (($r['sStatus'] ?? '') === 'Active') {
            $summary['active_clients']++;
            $summary['monthly_revenue'] += (float)$r['dMonthlyamount'];
        }
    }

    echo json_encode(['status' => 'success', 'data' => $rows, 'summary' => $summary]);
    exit;
}

if ($method == 'POST' && isset($inputData['action']) && $inputData['action'] === 'getsoftwareclientbyid') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
    ensureSoftwareClientTables($link);
    $id = (int)($inputData['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid client ID']);
        exit;
    }
    $stmt = $link->prepare('SELECT * FROM tblsoftware_client WHERE iClientid = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) {
        echo json_encode(['status' => 'error', 'message' => 'Client not found']);
        exit;
    }
    echo json_encode(['status' => 'success', 'data' => $row]);
    exit;
}

if ($method == 'POST' && isset($inputData['action']) && $inputData['action'] === 'updatesoftwareclient') {
    if (!isset($_SESSION['user_id'])) {
        sendResponse('error', 'Unauthorized');
    }
    ensureSoftwareClientTables($link);

    $id = (int)($inputData['id'] ?? 0);
    $clientName = trim((string)($inputData['sClientname'] ?? ''));
    if ($id <= 0 || $clientName === '') {
        sendResponse('error', 'Client name and ID are required');
    }
    $company = trim((string)($inputData['sCompanyname'] ?? ''));
    $contact = trim((string)($inputData['sContactperson'] ?? ''));
    $email = trim((string)($inputData['sEmail'] ?? ''));
    $phone = trim((string)($inputData['sPhone'] ?? ''));
    $plan = trim((string)($inputData['sPlan'] ?? ''));
    $amount = round((float)($inputData['dMonthlyamount'] ?? 0), 2);
    if ($amount < 0) {
        $amount = 0;
    }
    $billingDay = (int)($inputData['iBillingday'] ?? 1);
    if ($billingDay < 1) {
        $billingDay = 1;
    }
    if ($billingDay > 28) {
        $billingDay = 28;
    }
    $startDate = trim((string)($inputData['sStartdate'] ?? ''));
    if ($startDate === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
        $startDate = date('Y-m-d');
    }
    $status = trim((string)($inputData['sStatus'] ?? 'Active'));
    if (!in_array($status, ['Active', 'Inactive', 'Suspended'], true)) {
        $status = 'Active';
    }
    $address = trim((string)($inputData['sAddress'] ?? ''));
    $notes = trim((string)($inputData['sNotes'] ?? ''));

    $stmt = $link->prepare('UPDATE tblsoftware_client SET
        sClientname = ?, sCompanyname = ?, sContactperson = ?, sEmail = ?, sPhone = ?, sPlan = ?,
        dMonthlyamount = ?, iBillingday = ?, sStartdate = ?, sStatus = ?, sAddress = ?, sNotes = ?
        WHERE iClientid = ?');
    if (!$stmt) {
        sendResponse('error', 'Database error: ' . mysqli_error($link));
    }
    $stmt->bind_param(
        'ssssssdissssi',
        $clientName,
        $company,
        $contact,
        $email,
        $phone,
        $plan,
        $amount,
        $billingDay,
        $startDate,
        $status,
        $address,
        $notes,
        $id
    );
    $ok = $stmt->execute();
    $err = $stmt->error;
    $stmt->close();
    if (!$ok) {
        sendResponse('error', 'Client not updated: ' . $err);
    }
    sendResponse('success', 'Client updated successfully');
}

if ($method == 'POST' && isset($inputData['action']) && $inputData['action'] === 'deletesoftwareclient') {
    if (!isset($_SESSION['user_id'])) {
        sendResponse('error', 'Unauthorized');
    }
    ensureSoftwareClientTables($link);
    $id = (int)($inputData['id'] ?? 0);
    if ($id <= 0) {
        sendResponse('error', 'Invalid client ID');
    }
    $stmt = mysqli_prepare($link, 'DELETE FROM tblsoftware_client WHERE iClientid = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ret = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    if (!$ret) {
        sendResponse('error', 'Client not deleted: ' . mysqli_error($link));
    }
    // Keep monthly history; optionally could cascade — leave history intact
    sendResponse('success', 'Client deleted successfully');
}

if ($method == 'POST' && isset($inputData['action']) && $inputData['action'] === 'addclientmonthly') {
    if (!isset($_SESSION['user_id'])) {
        sendResponse('error', 'Unauthorized');
    }
    ensureSoftwareClientTables($link);

    $clientId = (int)($inputData['iClientid'] ?? 0);
    $month = trim((string)($inputData['sMonth'] ?? ''));
    if ($clientId <= 0) {
        sendResponse('error', 'Please select a client');
    }
    if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
        sendResponse('error', 'Valid billing month is required (YYYY-MM)');
    }

    $cs = $link->prepare('SELECT * FROM tblsoftware_client WHERE iClientid = ? LIMIT 1');
    $cs->bind_param('i', $clientId);
    $cs->execute();
    $client = $cs->get_result()->fetch_assoc();
    $cs->close();
    if (!$client) {
        sendResponse('error', 'Client not found');
    }

    $clientName = trim((string)$client['sClientname']);
    $dueDate = trim((string)($inputData['sDuedate'] ?? ''));
    if ($dueDate === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
        $dueDate = clientMgmtDueDate($month, (int)$client['iBillingday']);
    }
    $amount = isset($inputData['dAmount']) && $inputData['dAmount'] !== ''
        ? round((float)$inputData['dAmount'], 2)
        : round((float)$client['dMonthlyamount'], 2);
    $received = round((float)($inputData['dReceived'] ?? 0), 2);
    if ($amount < 0) {
        $amount = 0;
    }
    if ($received < 0) {
        $received = 0;
    }
    if ($received > $amount) {
        $received = $amount;
    }
    $pending = round(max(0, $amount - $received), 2);
    $status = clientMgmtComputeStatus($amount, $received, $dueDate);
    $paidDate = trim((string)($inputData['sPaiddate'] ?? ''));
    if ($paidDate === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $paidDate)) {
        $paidDate = ($status === 'Paid') ? date('Y-m-d') : null;
    }
    $mode = trim((string)($inputData['sMode'] ?? ''));
    $invoiceNo = trim((string)($inputData['sInvoiceNo'] ?? ''));
    $report = trim((string)($inputData['sReport'] ?? ''));
    $notes = trim((string)($inputData['sNotes'] ?? ''));
    $userId = (int)$_SESSION['user_id'];

    $dup = $link->prepare('SELECT iMonthlyid FROM tblclient_monthly WHERE iClientid = ? AND sMonth = ? LIMIT 1');
    $dup->bind_param('is', $clientId, $month);
    $dup->execute();
    $exists = $dup->get_result()->fetch_assoc();
    $dup->close();
    if ($exists) {
        sendResponse('error', 'Monthly entry already exists for this client and month. Please edit it instead.');
    }

    $stmt = $link->prepare('INSERT INTO tblclient_monthly
        (iClientid, sClientname, sMonth, sDuedate, dAmount, dReceived, dPending, sStatus, sPaiddate, sMode, sInvoiceNo, sReport, sNotes, iUserid)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    if (!$stmt) {
        sendResponse('error', 'Database error: ' . mysqli_error($link));
    }
    $stmt->bind_param(
        'isssdddssssssi',
        $clientId,
        $clientName,
        $month,
        $dueDate,
        $amount,
        $received,
        $pending,
        $status,
        $paidDate,
        $mode,
        $invoiceNo,
        $report,
        $notes,
        $userId
    );
    $ok = $stmt->execute();
    $err = $stmt->error;
    $stmt->close();
    if (!$ok) {
        sendResponse('error', 'Monthly report not saved: ' . $err);
    }
    sendResponse('success', 'Monthly report saved successfully');
}

if ($method == 'POST' && isset($inputData['action']) && $inputData['action'] === 'fngetlistclientmonthly') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
    ensureSoftwareClientTables($link);
    clientMgmtRefreshOverdue($link);

    $statusFilter = trim((string)($inputData['status'] ?? ''));
    $monthFilter = trim((string)($inputData['month'] ?? ''));
    $clientFilter = trim((string)($inputData['client'] ?? ''));
    $dueFilter = trim((string)($inputData['due'] ?? '')); // overdue | upcoming | all

    $sql = 'SELECT m.*, c.sPlan, c.iBillingday, c.sPhone AS client_phone
            FROM tblclient_monthly m
            LEFT JOIN tblsoftware_client c ON c.iClientid = m.iClientid
            WHERE 1=1';
    $types = '';
    $params = [];
    if ($statusFilter !== '' && in_array($statusFilter, ['Pending', 'Partial', 'Paid', 'Overdue'], true)) {
        $sql .= ' AND m.sStatus = ?';
        $types .= 's';
        $params[] = $statusFilter;
    }
    if ($monthFilter !== '' && preg_match('/^\d{4}-\d{2}$/', $monthFilter)) {
        $sql .= ' AND m.sMonth = ?';
        $types .= 's';
        $params[] = $monthFilter;
    }
    if ($clientFilter !== '') {
        $sql .= ' AND m.sClientname LIKE ?';
        $types .= 's';
        $params[] = '%' . $clientFilter . '%';
    }
    if ($dueFilter === 'overdue') {
        $sql .= " AND m.sDuedate < CURDATE() AND m.dPending > 0";
    } elseif ($dueFilter === 'upcoming') {
        $sql .= " AND m.sDuedate >= CURDATE() AND m.sDuedate <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND m.dPending > 0";
    }
    $sql .= ' ORDER BY m.sDuedate ASC, m.iMonthlyid DESC';

    $rows = [];
    if ($types !== '') {
        $stmt = $link->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();
    } else {
        $result = mysqli_query($link, $sql);
        if ($result) {
            $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
        }
    }

    $summary = [
        'total_amount' => 0,
        'total_received' => 0,
        'total_pending' => 0,
        'overdue_count' => 0,
        'paid_count' => 0,
        'open_count' => 0,
    ];
    $today = date('Y-m-d');
    foreach ($rows as $r) {
        $summary['total_amount'] += (float)$r['dAmount'];
        $summary['total_received'] += (float)$r['dReceived'];
        $summary['total_pending'] += (float)$r['dPending'];
        if (($r['sStatus'] ?? '') === 'Paid') {
            $summary['paid_count']++;
        } else {
            $summary['open_count']++;
        }
        if ((float)$r['dPending'] > 0 && ($r['sDuedate'] ?? '') < $today) {
            $summary['overdue_count']++;
        }
    }

    echo json_encode(['status' => 'success', 'data' => $rows, 'summary' => $summary]);
    exit;
}

if ($method == 'POST' && isset($inputData['action']) && $inputData['action'] === 'getclientmonthlybyid') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
    ensureSoftwareClientTables($link);
    $id = (int)($inputData['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid entry ID']);
        exit;
    }
    $stmt = $link->prepare('SELECT * FROM tblclient_monthly WHERE iMonthlyid = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) {
        echo json_encode(['status' => 'error', 'message' => 'Entry not found']);
        exit;
    }
    echo json_encode(['status' => 'success', 'data' => $row]);
    exit;
}

if ($method == 'POST' && isset($inputData['action']) && $inputData['action'] === 'updateclientmonthly') {
    if (!isset($_SESSION['user_id'])) {
        sendResponse('error', 'Unauthorized');
    }
    ensureSoftwareClientTables($link);

    $id = (int)($inputData['id'] ?? 0);
    $clientId = (int)($inputData['iClientid'] ?? 0);
    $month = trim((string)($inputData['sMonth'] ?? ''));
    if ($id <= 0 || $clientId <= 0 || !preg_match('/^\d{4}-\d{2}$/', $month)) {
        sendResponse('error', 'Required fields missing');
    }

    $cs = $link->prepare('SELECT * FROM tblsoftware_client WHERE iClientid = ? LIMIT 1');
    $cs->bind_param('i', $clientId);
    $cs->execute();
    $client = $cs->get_result()->fetch_assoc();
    $cs->close();
    if (!$client) {
        sendResponse('error', 'Client not found');
    }

    $clientName = trim((string)$client['sClientname']);
    $dueDate = trim((string)($inputData['sDuedate'] ?? ''));
    if ($dueDate === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
        $dueDate = clientMgmtDueDate($month, (int)$client['iBillingday']);
    }
    $amount = round((float)($inputData['dAmount'] ?? 0), 2);
    $received = round((float)($inputData['dReceived'] ?? 0), 2);
    if ($amount < 0) {
        $amount = 0;
    }
    if ($received < 0) {
        $received = 0;
    }
    if ($received > $amount) {
        $received = $amount;
    }
    $pending = round(max(0, $amount - $received), 2);
    $status = clientMgmtComputeStatus($amount, $received, $dueDate);
    $paidDate = trim((string)($inputData['sPaiddate'] ?? ''));
    if ($paidDate === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $paidDate)) {
        $paidDate = ($status === 'Paid') ? date('Y-m-d') : null;
    }
    $mode = trim((string)($inputData['sMode'] ?? ''));
    $invoiceNo = trim((string)($inputData['sInvoiceNo'] ?? ''));
    $report = trim((string)($inputData['sReport'] ?? ''));
    $notes = trim((string)($inputData['sNotes'] ?? ''));

    $dup = $link->prepare('SELECT iMonthlyid FROM tblclient_monthly WHERE iClientid = ? AND sMonth = ? AND iMonthlyid <> ? LIMIT 1');
    $dup->bind_param('isi', $clientId, $month, $id);
    $dup->execute();
    $exists = $dup->get_result()->fetch_assoc();
    $dup->close();
    if ($exists) {
        sendResponse('error', 'Another entry already exists for this client and month');
    }

    $stmt = $link->prepare('UPDATE tblclient_monthly SET
        iClientid = ?, sClientname = ?, sMonth = ?, sDuedate = ?, dAmount = ?, dReceived = ?, dPending = ?,
        sStatus = ?, sPaiddate = ?, sMode = ?, sInvoiceNo = ?, sReport = ?, sNotes = ?
        WHERE iMonthlyid = ?');
    if (!$stmt) {
        sendResponse('error', 'Database error: ' . mysqli_error($link));
    }
    $stmt->bind_param(
        'isssdddssssssi',
        $clientId,
        $clientName,
        $month,
        $dueDate,
        $amount,
        $received,
        $pending,
        $status,
        $paidDate,
        $mode,
        $invoiceNo,
        $report,
        $notes,
        $id
    );
    $ok = $stmt->execute();
    $err = $stmt->error;
    $stmt->close();
    if (!$ok) {
        sendResponse('error', 'Monthly report not updated: ' . $err);
    }
    sendResponse('success', 'Monthly report updated successfully');
}

if ($method == 'POST' && isset($inputData['action']) && $inputData['action'] === 'deleteclientmonthly') {
    if (!isset($_SESSION['user_id'])) {
        sendResponse('error', 'Unauthorized');
    }
    ensureSoftwareClientTables($link);
    $id = (int)($inputData['id'] ?? 0);
    if ($id <= 0) {
        sendResponse('error', 'Invalid entry ID');
    }
    $stmt = mysqli_prepare($link, 'DELETE FROM tblclient_monthly WHERE iMonthlyid = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ret = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    if (!$ret) {
        sendResponse('error', 'Entry not deleted: ' . mysqli_error($link));
    }
    sendResponse('success', 'Monthly entry deleted successfully');
}

if ($method == 'POST' && isset($inputData['action']) && $inputData['action'] === 'generatemonthlyforclients') {
    if (!isset($_SESSION['user_id'])) {
        sendResponse('error', 'Unauthorized');
    }
    ensureSoftwareClientTables($link);

    $month = trim((string)($inputData['sMonth'] ?? ''));
    if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
        $month = date('Y-m');
    }
    $userId = (int)$_SESSION['user_id'];

    $result = mysqli_query($link, "SELECT * FROM tblsoftware_client WHERE sStatus = 'Active' ORDER BY sClientname ASC");
    if (!$result) {
        sendResponse('error', 'Could not load clients');
    }

    $created = 0;
    $skipped = 0;
    $stmt = $link->prepare('INSERT IGNORE INTO tblclient_monthly
        (iClientid, sClientname, sMonth, sDuedate, dAmount, dReceived, dPending, sStatus, sPaiddate, sMode, sInvoiceNo, sReport, sNotes, iUserid)
        VALUES (?, ?, ?, ?, ?, 0, ?, ?, NULL, NULL, NULL, NULL, NULL, ?)');

    while ($client = mysqli_fetch_assoc($result)) {
        $clientId = (int)$client['iClientid'];
        $clientName = trim((string)$client['sClientname']);
        $amount = round((float)$client['dMonthlyamount'], 2);
        $dueDate = clientMgmtDueDate($month, (int)$client['iBillingday']);
        $pending = $amount;
        $status = clientMgmtComputeStatus($amount, 0, $dueDate);

        $check = $link->prepare('SELECT iMonthlyid FROM tblclient_monthly WHERE iClientid = ? AND sMonth = ? LIMIT 1');
        $check->bind_param('is', $clientId, $month);
        $check->execute();
        $exists = $check->get_result()->fetch_assoc();
        $check->close();
        if ($exists) {
            $skipped++;
            continue;
        }

        if ($stmt) {
            $stmt->bind_param(
                'isssddsi',
                $clientId,
                $clientName,
                $month,
                $dueDate,
                $amount,
                $pending,
                $status,
                $userId
            );
            if ($stmt->execute()) {
                $created++;
            }
        }
    }
    if ($stmt) {
        $stmt->close();
    }

    sendResponse('success', "Generated {$created} monthly entries for {$month} ({$skipped} already existed)", [
        'created' => $created,
        'skipped' => $skipped,
        'month' => $month,
    ]);
}

?>




