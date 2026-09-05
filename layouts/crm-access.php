<?php
/**
 * Access helpers for role + module-based menu/page locks.
 *
 * Admin  → full CRM access
 * User   → Reminder, Re-engage, Daily Report (own records only)
 *          + optional modules: Lead, Project, Finance
 */

if (!function_exists('crmEnsureAccessColumns')) {
    function crmEnsureAccessColumns($db = null) {
        static $done = false;
        if ($done) {
            return;
        }
        if (!$db || !($db instanceof mysqli)) {
            global $link;
            $db = (isset($link) && $link instanceof mysqli) ? $link : null;
        }
        if (!$db) {
            return;
        }
        $cols = ['iAccessLead', 'iAccessProject', 'iAccessFinance'];
        foreach ($cols as $col) {
            $check = @$db->query("SHOW COLUMNS FROM tbluser LIKE '{$col}'");
            if ($check && $check->num_rows === 0) {
                @$db->query("ALTER TABLE tbluser ADD COLUMN {$col} TINYINT(1) NOT NULL DEFAULT 0");
            }
        }
        $done = true;
    }
}

if (!function_exists('crmLoadUserDepartment')) {
    function crmLoadUserDepartment($db = null) {
        if (!empty($_SESSION['userDepartment'])) {
            return trim((string)$_SESSION['userDepartment']);
        }
        if (empty($_SESSION['user_id'])) {
            return '';
        }

        if (!$db || !($db instanceof mysqli)) {
            global $link;
            $db = (isset($link) && $link instanceof mysqli) ? $link : null;
        }
        if (!$db) {
            return '';
        }

        $uid = (int)$_SESSION['user_id'];
        $stmt = $db->prepare(
            'SELECT u.iDepid, d.sDepartment
             FROM tbluser u
             LEFT JOIN tbldepartment d ON d.iDepid = u.iDepid
             WHERE u.iUserid = ? LIMIT 1'
        );
        if (!$stmt) {
            return '';
        }
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) {
            return '';
        }

        $_SESSION['userDepId'] = (int)($row['iDepid'] ?? 0);
        $_SESSION['userDepartment'] = trim((string)($row['sDepartment'] ?? ''));
        return $_SESSION['userDepartment'];
    }
}

if (!function_exists('crmLoadUserAccess')) {
    /**
     * Load module flags into session (and refresh from DB if missing).
     */
    function crmLoadUserAccess($db = null) {
        if (isset($_SESSION['accessLead'], $_SESSION['accessProject'], $_SESSION['accessFinance'])) {
            return;
        }
        if (empty($_SESSION['user_id'])) {
            $_SESSION['accessLead'] = 0;
            $_SESSION['accessProject'] = 0;
            $_SESSION['accessFinance'] = 0;
            return;
        }
        if (!$db || !($db instanceof mysqli)) {
            global $link;
            $db = (isset($link) && $link instanceof mysqli) ? $link : null;
        }
        if (!$db) {
            $_SESSION['accessLead'] = 0;
            $_SESSION['accessProject'] = 0;
            $_SESSION['accessFinance'] = 0;
            return;
        }
        crmEnsureAccessColumns($db);
        $uid = (int)$_SESSION['user_id'];
        $stmt = $db->prepare(
            'SELECT iAccessLead, iAccessProject, iAccessFinance FROM tbluser WHERE iUserid = ? LIMIT 1'
        );
        if (!$stmt) {
            $_SESSION['accessLead'] = 0;
            $_SESSION['accessProject'] = 0;
            $_SESSION['accessFinance'] = 0;
            return;
        }
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $_SESSION['accessLead'] = (int)($row['iAccessLead'] ?? 0);
        $_SESSION['accessProject'] = (int)($row['iAccessProject'] ?? 0);
        $_SESSION['accessFinance'] = (int)($row['iAccessFinance'] ?? 0);
    }
}

if (!function_exists('crmIsAdmin')) {
    function crmIsAdmin() {
        return isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'Admin';
    }
}

if (!function_exists('crmIsClient')) {
    function crmIsClient() {
        return isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'Client';
    }
}

if (!function_exists('crmEnsureClientProjectColumns')) {
    function crmEnsureClientProjectColumns($db = null) {
        static $done = false;
        if ($done) {
            return;
        }
        if (!$db || !($db instanceof mysqli)) {
            global $link;
            $db = (isset($link) && $link instanceof mysqli) ? $link : null;
        }
        if (!$db) {
            return;
        }
        $check = @$db->query("SHOW COLUMNS FROM tbluser LIKE 'sClientCompany'");
        if ($check && $check->num_rows === 0) {
            @$db->query("ALTER TABLE tbluser ADD COLUMN sClientCompany VARCHAR(255) NULL");
        }
        $done = true;
    }
}

if (!function_exists('crmNormalizeAccessFlags')) {
    /**
     * Parse access flags from request; Admin role always gets all modules.
     */
    function crmNormalizeAccessFlags($role, $inputData = []) {
        $isAdmin = (strcasecmp(trim((string)$role), 'Admin') === 0);
        if ($isAdmin) {
            return [1, 1, 1];
        }
        $lead = !empty($inputData['accessLead']) || !empty($inputData['iAccessLead']) ? 1 : 0;
        $project = !empty($inputData['accessProject']) || !empty($inputData['iAccessProject']) ? 1 : 0;
        $finance = !empty($inputData['accessFinance']) || !empty($inputData['iAccessFinance']) ? 1 : 0;
        return [$lead, $project, $finance];
    }
}

if (!function_exists('crmHasModule')) {
    /**
     * @param string $module lead|project|finance
     */
    function crmHasModule($module, $db = null) {
        if (crmIsAdmin()) {
            return true;
        }
        crmLoadUserAccess($db);
        $module = strtolower(trim((string)$module));
        if ($module === 'lead') {
            return !empty($_SESSION['accessLead']);
        }
        if ($module === 'project') {
            return !empty($_SESSION['accessProject']);
        }
        if ($module === 'finance') {
            return !empty($_SESSION['accessFinance']);
        }
        return false;
    }
}

if (!function_exists('crmCanSeeAllLeads')) {
    /**
     * Admin or Lead Management module: all leads (not only assigned).
     */
    function crmCanSeeAllLeads($db = null) {
        return crmIsAdmin() || crmHasModule('lead', $db);
    }
}

if (!function_exists('crmCanListQuotation')) {
    /** Lead module (list only) or Finance module or Admin */
    function crmCanListQuotation($db = null) {
        return crmIsAdmin() || crmHasModule('lead', $db) || crmHasModule('finance', $db);
    }
}

if (!function_exists('crmCanManageQuotation')) {
    /** Edit/delete quotation: Finance module or Admin only */
    function crmCanManageQuotation($db = null) {
        return crmIsAdmin() || crmHasModule('finance', $db);
    }
}

if (!function_exists('crmCanAccessSales')) {
    function crmCanAccessSales($db = null) {
        return crmIsAdmin() || crmHasModule('finance', $db);
    }
}

if (!function_exists('crmEnsureQuotationStatusColumn')) {
    /** Adds tblquotation.status (Draft/Sent/Accepted/Rejected) on first use if it isn't there yet. */
    function crmEnsureQuotationStatusColumn($db = null) {
        static $done = false;
        if ($done) {
            return;
        }
        if (!$db || !($db instanceof mysqli)) {
            global $link;
            $db = (isset($link) && $link instanceof mysqli) ? $link : null;
        }
        if (!$db) {
            return;
        }
        $check = @$db->query("SHOW COLUMNS FROM tblquotation LIKE 'status'");
        if ($check && $check->num_rows === 0) {
            @$db->query("ALTER TABLE tblquotation ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'Draft' AFTER quotation_no");
        }
        $done = true;
    }
}

if (!function_exists('crmQuotationStatusOptions')) {
    function crmQuotationStatusOptions() {
        return ['Draft', 'Sent', 'Accepted', 'Rejected'];
    }
}

if (!function_exists('crmRequireAdmin')) {
    function crmRequireAdmin($db = null) {
        if (crmIsAdmin()) {
            return;
        }
        $wantsJson = false;
        if (!empty($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            $wantsJson = true;
        }
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            $wantsJson = true;
        }
        if ($wantsJson) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Access denied. Admin only.']);
            exit;
        }
        header('Location: index.php');
        exit;
    }
}

if (!function_exists('crmRequireModule')) {
    function crmRequireModule($module, $db = null) {
        if (crmHasModule($module, $db)) {
            return;
        }
        $wantsJson = false;
        if (!empty($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            $wantsJson = true;
        }
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            $wantsJson = true;
        }
        if ($wantsJson) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Access denied.']);
            exit;
        }
        header('Location: index.php');
        exit;
    }
}

if (!function_exists('crmIsFinanceAdmin')) {
    /**
     * True when Admin OR Finance module (legacy name kept for P&L pages).
     * Admin has full access; Finance-module users can use Sales / P&L.
     */
    function crmIsFinanceAdmin($db = null) {
        return crmCanAccessSales($db);
    }
}

if (!function_exists('crmRequireLoggedIn')) {
    /**
     * For Client Project Management pages any logged-in staff (Admin or User)
     * may open — visibility of individual projects/tasks is scoped separately.
     * Client-role sessions never reach these pages at all (redirected centrally
     * in layouts/session.php before this ever runs).
     */
    function crmRequireLoggedIn() {
        if (!empty($_SESSION['user_id'])) {
            return;
        }
        $wantsJson = false;
        if (!empty($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            $wantsJson = true;
        }
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            $wantsJson = true;
        }
        if ($wantsJson) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Please log in.']);
            exit;
        }
        header('Location: auth-login.php');
        exit;
    }
}

if (!function_exists('crmRequireAdminOrRedirectClient')) {
    /**
     * For Client Project Management staff pages: Admin only.
     * A logged-in Client is sent back to their own portal instead of index.php.
     */
    function crmRequireAdminOrRedirectClient($db = null) {
        if (crmIsAdmin()) {
            return;
        }
        $wantsJson = false;
        if (!empty($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            $wantsJson = true;
        }
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            $wantsJson = true;
        }
        if ($wantsJson) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Access denied. Admin only.']);
            exit;
        }
        header('Location: ' . (crmIsClient() ? 'client-portal-projects.php' : 'index.php'));
        exit;
    }
}

if (!function_exists('crmClientOwnsProject')) {
    function crmClientOwnsProject($db, $projectId) {
        $projectId = (int)$projectId;
        if ($projectId <= 0 || empty($_SESSION['user_id']) || !($db instanceof mysqli)) {
            return false;
        }
        $clientUserId = (int)$_SESSION['user_id'];
        $stmt = $db->prepare('SELECT 1 FROM tblclient_project WHERE iId = ? AND iClientUserid = ? LIMIT 1');
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('ii', $projectId, $clientUserId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (bool)$row;
    }
}

if (!function_exists('crmRequireFinanceAdmin')) {
    function crmRequireFinanceAdmin($db = null) {
        if (crmCanAccessSales($db)) {
            return;
        }
        $wantsJson = false;
        if (!empty($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            $wantsJson = true;
        }
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            $wantsJson = true;
        }
        if ($wantsJson) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Access denied. Finance access required.']);
            exit;
        }
        header('Location: index.php');
        exit;
    }
}
