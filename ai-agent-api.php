<?php
include 'layouts/session.php';
include 'layouts/config.php';
include 'layouts/ai-config.php';

header('Content-Type: application/json');
date_default_timezone_set('Asia/Kolkata');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please log in to use the AI assistant.']);
    exit;
}

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!is_array($input)) {
    $input = $_POST;
}

$action = $input['action'] ?? '';

if ($action === 'chat') {
    handleChat($link, $input);
} elseif ($action === 'confirm') {
    handleConfirm($link, $input);
} elseif ($action === 'cancel') {
    handleCancel($input);
} elseif ($action === 'status') {
    echo json_encode([
        'status' => 'success',
        'configured' => (GEMINI_API_KEY !== ''),
        'model' => GEMINI_MODEL,
        'user' => $_SESSION['username'] ?? 'User',
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
exit;

function jsonOut(array $payload): void
{
    echo json_encode($payload);
    exit;
}

function handleChat(mysqli $link, array $input): void
{
    if (GEMINI_API_KEY === '') {
        jsonOut([
            'status' => 'error',
            'message' => 'Gemini API key is not set. Add it in layouts/ai-config.php (free key from Google AI Studio).',
        ]);
    }

    $message = trim((string)($input['message'] ?? ''));
    if ($message === '') {
        jsonOut(['status' => 'error', 'message' => 'Please type a message.']);
    }

    $history = $input['history'] ?? [];
    if (!is_array($history)) {
        $history = [];
    }
    $history = array_slice($history, -20);

    $crmContext = buildCrmContext($link);
    $crmContext = enrichContextForMessage($link, $crmContext, $message);
    $systemPrompt = buildSystemPrompt($crmContext);

    $contents = [];
    foreach ($history as $turn) {
        $role = ($turn['role'] ?? '') === 'model' ? 'model' : 'user';
        $text = trim((string)($turn['text'] ?? ''));
        if ($text === '') {
            continue;
        }
        $contents[] = [
            'role' => $role,
            'parts' => [['text' => $text]],
        ];
    }
    $contents[] = [
        'role' => 'user',
        'parts' => [['text' => $message]],
    ];

    $gemini = callGemini($systemPrompt, $contents);
    if (($gemini['status'] ?? '') !== 'success') {
        jsonOut([
            'status' => 'error',
            'message' => $gemini['message'] ?? 'AI request failed.',
        ]);
    }

    $parsed = parseAgentResponse($gemini['text']);
    $reply = $parsed['reply'];
    $proposed = $parsed['proposed_action'];

    $pending = null;
    if (is_array($proposed) && !empty($proposed['type'])) {
        $token = bin2hex(random_bytes(16));
        if (!isset($_SESSION['ai_pending_actions']) || !is_array($_SESSION['ai_pending_actions'])) {
            $_SESSION['ai_pending_actions'] = [];
        }
        // Keep session small
        if (count($_SESSION['ai_pending_actions']) > 20) {
            $_SESSION['ai_pending_actions'] = array_slice($_SESSION['ai_pending_actions'], -10, null, true);
        }
        $_SESSION['ai_pending_actions'][$token] = [
            'type' => $proposed['type'],
            'payload' => $proposed['payload'] ?? [],
            'summary' => $proposed['summary'] ?? 'Confirm this action?',
            'created_at' => time(),
        ];
        $pending = [
            'token' => $token,
            'type' => $proposed['type'],
            'summary' => $proposed['summary'] ?? 'Confirm this action?',
            'payload' => $proposed['payload'] ?? [],
        ];
    }

    jsonOut([
        'status' => 'success',
        'reply' => $reply,
        'pending_action' => $pending,
    ]);
}

function handleConfirm(mysqli $link, array $input): void
{
    $token = (string)($input['token'] ?? '');
    if ($token === '' || empty($_SESSION['ai_pending_actions'][$token])) {
        jsonOut(['status' => 'error', 'message' => 'This action expired or was already used. Ask again.']);
    }

    $pending = $_SESSION['ai_pending_actions'][$token];
    unset($_SESSION['ai_pending_actions'][$token]);

    $type = $pending['type'] ?? '';
    $payload = is_array($pending['payload'] ?? null) ? $pending['payload'] : [];

    if ($type === 'create_lead') {
        $result = createLeadFromAi($link, $payload);
        jsonOut($result);
    }
    if ($type === 'create_reminder') {
        $result = createReminderFromAi($link, $payload);
        jsonOut($result);
    }
    if ($type === 'create_daily_report') {
        $result = createDailyReportFromAi($link, $payload);
        jsonOut($result);
    }
    if ($type === 'create_project_task') {
        $result = createProjectTaskFromAi($link, $payload);
        jsonOut($result);
    }

    jsonOut(['status' => 'error', 'message' => 'Unknown action type.']);
}

function handleCancel(array $input): void
{
    $token = (string)($input['token'] ?? '');
    if ($token !== '' && isset($_SESSION['ai_pending_actions'][$token])) {
        unset($_SESSION['ai_pending_actions'][$token]);
    }
    jsonOut(['status' => 'success', 'message' => 'Cancelled.']);
}

function resolveAssignedNamesForAi(mysqli $link, $assignedRaw): string
{
    $parts = preg_split('/\s*,\s*/', trim((string)$assignedRaw), -1, PREG_SPLIT_NO_EMPTY);
    $ids = [];
    foreach ($parts as $part) {
        $id = (int)$part;
        if ($id > 0) {
            $ids[$id] = $id;
        }
    }
    if (!$ids) {
        return '';
    }
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));
    $idList = array_values($ids);
    $stmt = $link->prepare("SELECT iUserid, sName FROM tbluser WHERE iUserid IN ($placeholders)");
    if (!$stmt) {
        return '';
    }
    $stmt->bind_param($types, ...$idList);
    $stmt->execute();
    $result = $stmt->get_result();
    $map = [];
    while ($row = $result->fetch_assoc()) {
        $map[(int)$row['iUserid']] = $row['sName'];
    }
    $stmt->close();
    $names = [];
    foreach ($idList as $id) {
        if (isset($map[$id])) {
            $names[] = $map[$id];
        }
    }
    return implode(', ', $names);
}

function leadScopeSql(bool $isAdmin): string
{
    return $isAdmin
        ? '1=1'
        : "(FIND_IN_SET(?, REPLACE(l.sAssigned_to, ' ', '')) > 0 OR l.sLead_owner = ? OR l.sCreated_by = ?)";
}

function fetchLeadsByCreatedRange(mysqli $link, bool $isAdmin, int $userId, string $fromDate, string $toDate, int $limit = 25): array
{
    $scope = leadScopeSql($isAdmin);
    // Match List Leads fields: company name + product names + assigned user
    $sql = "SELECT
                l.iLead_id,
                l.sCompany_name,
                l.sLead_name,
                l.sContactperson,
                l.sPhone,
                l.sEmail,
                l.sLead_priority,
                l.sCreated_date,
                DATE(l.sCreated_date) AS created_day,
                COALESCE(rs.sStatus, ls.sStatus) AS status_name,
                l.sAssigned_to,
                GROUP_CONCAT(DISTINCT prod.sProductname SEPARATOR ', ') AS product_names
            FROM tblleads l
            LEFT JOIN tblstatus ls ON l.sLead_status = ls.iStatusid
            LEFT JOIN (
                SELECT r1.lead_id, r1.sStatus
                FROM tblreplayleads r1
                INNER JOIN (
                    SELECT lead_id, MAX(sCreatedTimestamp) AS MaxDate
                    FROM tblreplayleads
                    GROUP BY lead_id
                ) r2 ON r1.lead_id = r2.lead_id AND r1.sCreatedTimestamp = r2.MaxDate
            ) r ON r.lead_id = l.iLead_id
            LEFT JOIN tblstatus rs ON r.sStatus = rs.iStatusid
            LEFT JOIN tblproductleads pl ON pl.lead_id = l.iLead_id
            LEFT JOIN tblproduct prod ON pl.sProductname = prod.iProductid
            WHERE {$scope}
              AND DATE(l.sCreated_date) >= ?
              AND DATE(l.sCreated_date) <= ?
            GROUP BY l.iLead_id
            ORDER BY l.sCreated_date DESC, l.iLead_id DESC
            LIMIT ?";
    $stmt = $link->prepare($sql);
    if (!$stmt) {
        return [];
    }
    if ($isAdmin) {
        $stmt->bind_param('ssi', $fromDate, $toDate, $limit);
    } else {
        $stmt->bind_param('iiissi', $userId, $userId, $userId, $fromDate, $toDate, $limit);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $company = trim((string)($row['sCompany_name'] ?? ''));
        $leadName = trim((string)($row['sLead_name'] ?? ''));
        $rows[] = [
            'id' => (int)$row['iLead_id'],
            // Same primary label users see on List Leads (Company Name)
            'company_name' => $company,
            'display_name' => $company !== '' ? $company : ($leadName !== '' ? $leadName : 'Lead #' . $row['iLead_id']),
            'lead_title' => $leadName, // sLead_name (interest/title) — NOT the List Leads company column
            'contact_person' => $row['sContactperson'],
            'phone' => $row['sPhone'],
            'email' => $row['sEmail'],
            'product_names' => $row['product_names'] ?: '',
            'assigned_to' => resolveAssignedNamesForAi($link, $row['sAssigned_to'] ?? ''),
            'status' => $row['status_name'],
            'priority' => $row['sLead_priority'],
            'created_date' => $row['sCreated_date'],
            'created_day' => $row['created_day'],
        ];
    }
    $stmt->close();
    return $rows;
}

function countLeadsByCreatedRange(mysqli $link, bool $isAdmin, int $userId, string $fromDate, string $toDate): int
{
    $scope = leadScopeSql($isAdmin);
    $sql = "SELECT COUNT(*) AS c FROM tblleads l
            WHERE {$scope}
              AND DATE(l.sCreated_date) >= ?
              AND DATE(l.sCreated_date) <= ?";
    $stmt = $link->prepare($sql);
    if (!$stmt) {
        return 0;
    }
    if ($isAdmin) {
        $stmt->bind_param('ss', $fromDate, $toDate);
    } else {
        $stmt->bind_param('iiiss', $userId, $userId, $userId, $fromDate, $toDate);
    }
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return (int)$count;
}

function parseDateRangeFromMessage(string $message, string $today): ?array
{
    $msg = strtolower($message);
    $ts = strtotime($today . ' 12:00:00');
    if ($ts === false) {
        $ts = time();
    }

    // Explicit YYYY-MM-DD or DD-MM-YYYY / DD/MM/YYYY
    if (preg_match_all('/\b(\d{4}-\d{2}-\d{2})\b/', $message, $m) && count($m[1]) >= 1) {
        $dates = $m[1];
        sort($dates);
        return ['from' => $dates[0], 'to' => $dates[count($dates) - 1], 'label' => 'custom date'];
    }
    if (preg_match_all('/\b(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})\b/', $message, $m, PREG_SET_ORDER)) {
        $dates = [];
        foreach ($m as $part) {
            $dates[] = sprintf('%04d-%02d-%02d', (int)$part[3], (int)$part[2], (int)$part[1]);
        }
        sort($dates);
        return ['from' => $dates[0], 'to' => $dates[count($dates) - 1], 'label' => 'custom date'];
    }

    if (preg_match('/\b(today|aaj)\b/u', $msg)) {
        return ['from' => $today, 'to' => $today, 'label' => 'today'];
    }
    if (preg_match('/\b(yesterday|kal)\b/u', $msg)) {
        $d = date('Y-m-d', strtotime('-1 day', $ts));
        return ['from' => $d, 'to' => $d, 'label' => 'yesterday'];
    }
    if (preg_match('/\b(this week|current week|is week)\b/', $msg)) {
        $from = date('Y-m-d', strtotime('monday this week', $ts));
        $to = date('Y-m-d', strtotime('sunday this week', $ts));
        return ['from' => $from, 'to' => $to, 'label' => 'this week'];
    }
    if (preg_match('/\b(last week|previous week)\b/', $msg)) {
        $from = date('Y-m-d', strtotime('monday last week', $ts));
        $to = date('Y-m-d', strtotime('sunday last week', $ts));
        return ['from' => $from, 'to' => $to, 'label' => 'last week'];
    }
    if (preg_match('/\b(this month|current month)\b/', $msg)) {
        return ['from' => date('Y-m-01', $ts), 'to' => date('Y-m-t', $ts), 'label' => 'this month'];
    }
    if (preg_match('/\b(last month|previous month)\b/', $msg)) {
        $from = date('Y-m-01', strtotime('first day of last month', $ts));
        $to = date('Y-m-t', strtotime('last day of last month', $ts));
        return ['from' => $from, 'to' => $to, 'label' => 'last month'];
    }
    if (preg_match('/\blast\s+(\d+)\s+days?\b/', $msg, $m)) {
        $n = max(1, min(90, (int)$m[1]));
        $from = date('Y-m-d', strtotime('-' . ($n - 1) . ' days', $ts));
        return ['from' => $from, 'to' => $today, 'label' => "last {$n} days"];
    }

    return null;
}

function tableExists(mysqli $link, string $table, bool $refresh = false): bool
{
    static $cache = [];
    if ($refresh) {
        unset($cache[$table]);
    }
    if (isset($cache[$table])) {
        return $cache[$table];
    }
    $safe = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    $res = $link->query("SHOW TABLES LIKE '{$safe}'");
    $cache[$table] = ($res && $res->num_rows > 0);
    return $cache[$table];
}

function aiWonStatusId(): string
{
    return '6'; // Won
}

function fetchWonProjectsForAi(mysqli $link, bool $isAdmin, int $userId, int $limit = 20): array
{
    if (!tableExists($link, 'tblproject_tasks')) {
        // Still list won leads even if tasks table missing
    }
    $wonStatusId = aiWonStatusId();
    $limit = max(1, min(50, $limit));
    $hasTasks = tableExists($link, 'tblproject_tasks');

    $taskTotalSql = $hasTasks
        ? '(SELECT COUNT(*) FROM tblproject_tasks t WHERE t.lead_id = l.iLead_id)'
        : '0';
    $taskDoneSql = $hasTasks
        ? "(SELECT COUNT(*) FROM tblproject_tasks t WHERE t.lead_id = l.iLead_id AND t.sStatus = 'Done')"
        : '0';
    $taskPendingSql = $hasTasks
        ? "(SELECT COUNT(*) FROM tblproject_tasks t WHERE t.lead_id = l.iLead_id AND t.sStatus = 'Pending')"
        : '0';
    $taskProgressSql = $hasTasks
        ? "(SELECT COUNT(*) FROM tblproject_tasks t WHERE t.lead_id = l.iLead_id AND t.sStatus = 'In Progress')"
        : '0';
    $myTasksSql = $hasTasks
        ? '(SELECT COUNT(*) FROM tblproject_tasks t WHERE t.lead_id = l.iLead_id AND t.sAssigned_to = ?)'
        : '0';

    $sql = "SELECT
                l.iLead_id,
                l.sCompany_name,
                l.sLead_name,
                l.sAssigned_to,
                l.sLead_owner,
                owner.sName AS lead_owner_name,
                {$taskTotalSql} AS task_total,
                {$taskDoneSql} AS task_done,
                {$taskPendingSql} AS task_pending,
                {$taskProgressSql} AS task_in_progress,
                {$myTasksSql} AS my_tasks
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
            WHERE COALESCE(NULLIF(r.sStatus, ''), l.sLead_status) = ?";

    if (!$isAdmin) {
        $sql .= " AND (FIND_IN_SET(?, REPLACE(l.sAssigned_to, ' ', '')) > 0 OR l.sLead_owner = ?)";
    }
    $sql .= " ORDER BY l.iLead_id DESC LIMIT {$limit}";

    $stmt = $link->prepare($sql);
    if (!$stmt) {
        return [];
    }
    if ($hasTasks) {
        if ($isAdmin) {
            $stmt->bind_param('is', $userId, $wonStatusId);
        } else {
            $stmt->bind_param('isii', $userId, $wonStatusId, $userId, $userId);
        }
    } else {
        if ($isAdmin) {
            $stmt->bind_param('s', $wonStatusId);
        } else {
            $stmt->bind_param('sii', $wonStatusId, $userId, $userId);
        }
    }
    if (!$stmt->execute()) {
        $stmt->close();
        return [];
    }
    $result = $stmt->get_result();
    $projects = [];
    while ($row = $result->fetch_assoc()) {
        $projects[] = [
            'lead_id' => (int)$row['iLead_id'],
            'company_name' => $row['sCompany_name'],
            'lead_name' => $row['sLead_name'],
            'assigned_to' => resolveAssignedNamesForAi($link, $row['sAssigned_to'] ?? ''),
            'lead_owner' => $row['lead_owner_name'] ?: '',
            'task_total' => (int)$row['task_total'],
            'task_done' => (int)$row['task_done'],
            'task_pending' => (int)$row['task_pending'],
            'task_in_progress' => (int)$row['task_in_progress'],
            'my_tasks' => (int)$row['my_tasks'],
            'tasks_url' => 'project-tasks.php?lead_id=' . (int)$row['iLead_id'],
        ];
    }
    $stmt->close();
    return $projects;
}

function fetchProjectTasksForAi(mysqli $link, int $leadId, int $userId, bool $mineOnly = false, int $limit = 40): array
{
    if (!tableExists($link, 'tblproject_tasks') || $leadId <= 0) {
        return [];
    }
    $limit = max(1, min(80, $limit));
    $sql = "SELECT t.id, t.lead_id, t.sTitle, t.sDescription, t.sAssigned_to, t.sCreated_by, t.sStatus, t.sDue_date,
                   a.sName AS assigned_name, c.sName AS created_by_name, l.sCompany_name
            FROM tblproject_tasks t
            LEFT JOIN tbluser a ON a.iUserid = t.sAssigned_to
            LEFT JOIN tbluser c ON c.iUserid = t.sCreated_by
            LEFT JOIN tblleads l ON l.iLead_id = t.lead_id
            WHERE t.lead_id = ?";
    if ($mineOnly) {
        $sql .= ' AND t.sAssigned_to = ?';
    }
    $sql .= " ORDER BY FIELD(t.sStatus, 'Pending', 'In Progress', 'Done'), t.sDue_date IS NULL, t.sDue_date ASC, t.id DESC LIMIT {$limit}";

    $stmt = $link->prepare($sql);
    if (!$stmt) {
        return [];
    }
    if ($mineOnly) {
        $stmt->bind_param('ii', $leadId, $userId);
    } else {
        $stmt->bind_param('i', $leadId);
    }
    if (!$stmt->execute()) {
        $stmt->close();
        return [];
    }
    $result = $stmt->get_result();
    $tasks = [];
    while ($row = $result->fetch_assoc()) {
        $tasks[] = [
            'id' => (int)$row['id'],
            'lead_id' => (int)$row['lead_id'],
            'company_name' => $row['sCompany_name'],
            'title' => $row['sTitle'],
            'description' => $row['sDescription'],
            'assigned_to' => $row['assigned_name'],
            'created_by' => $row['created_by_name'],
            'status' => $row['sStatus'],
            'due_date' => $row['sDue_date'],
        ];
    }
    $stmt->close();
    return $tasks;
}

function fetchMyProjectTasksForAi(mysqli $link, bool $isAdmin, int $userId, bool $openOnly = true, int $limit = 30): array
{
    if (!tableExists($link, 'tblproject_tasks')) {
        return [];
    }
    $limit = max(1, min(80, $limit));
    $wonStatusId = aiWonStatusId();
    $sql = "SELECT t.id, t.lead_id, t.sTitle, t.sDescription, t.sStatus, t.sDue_date,
                   a.sName AS assigned_name, l.sCompany_name, l.sLead_name
            FROM tblproject_tasks t
            INNER JOIN tblleads l ON l.iLead_id = t.lead_id
            LEFT JOIN (
                SELECT r1.lead_id, r1.sStatus
                FROM tblreplayleads r1
                INNER JOIN (
                    SELECT lead_id, MAX(sCreatedTimestamp) AS latest_ts
                    FROM tblreplayleads
                    GROUP BY lead_id
                ) r2 ON r1.lead_id = r2.lead_id AND r1.sCreatedTimestamp = r2.latest_ts
            ) r ON r.lead_id = l.iLead_id
            LEFT JOIN tbluser a ON a.iUserid = t.sAssigned_to
            WHERE COALESCE(NULLIF(r.sStatus, ''), l.sLead_status) = ?
              AND t.sAssigned_to = ?";
    if ($openOnly) {
        $sql .= " AND t.sStatus IN ('Pending', 'In Progress')";
    }
    if (!$isAdmin) {
        $sql .= " AND (FIND_IN_SET(?, REPLACE(l.sAssigned_to, ' ', '')) > 0 OR l.sLead_owner = ? OR t.sAssigned_to = ?)";
    }
    $sql .= " ORDER BY FIELD(t.sStatus, 'Pending', 'In Progress', 'Done'), t.sDue_date IS NULL, t.sDue_date ASC LIMIT {$limit}";

    $stmt = $link->prepare($sql);
    if (!$stmt) {
        return [];
    }
    if ($isAdmin) {
        $stmt->bind_param('si', $wonStatusId, $userId);
    } else {
        $stmt->bind_param('siiii', $wonStatusId, $userId, $userId, $userId, $userId);
    }
    if (!$stmt->execute()) {
        $stmt->close();
        return [];
    }
    $result = $stmt->get_result();
    $tasks = [];
    while ($row = $result->fetch_assoc()) {
        $tasks[] = [
            'id' => (int)$row['id'],
            'lead_id' => (int)$row['lead_id'],
            'company_name' => $row['sCompany_name'] ?: $row['sLead_name'],
            'title' => $row['sTitle'],
            'description' => $row['sDescription'],
            'assigned_to' => $row['assigned_name'],
            'status' => $row['sStatus'],
            'due_date' => $row['sDue_date'],
            'tasks_url' => 'project-tasks.php?lead_id=' . (int)$row['lead_id'],
        ];
    }
    $stmt->close();
    return $tasks;
}

function countDailyReportsForAi(mysqli $link, bool $isAdmin, int $userId, string $fromDate, string $toDate, ?int $employeeId = null): int
{
    if (!tableExists($link, 'tbldaily_report')) {
        return 0;
    }
    $sql = 'SELECT COUNT(*) FROM tbldaily_report WHERE sDate >= ? AND sDate <= ?';
    $types = 'ss';
    $params = [$fromDate, $toDate];
    if (!$isAdmin) {
        $sql .= ' AND iUserid = ?';
        $types .= 'i';
        $params[] = $userId;
    } elseif ($employeeId !== null && $employeeId > 0) {
        $sql .= ' AND iUserid = ?';
        $types .= 'i';
        $params[] = $employeeId;
    }
    $stmt = $link->prepare($sql);
    if (!$stmt) {
        return 0;
    }
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return (int)$count;
}

function fetchDailyReportsForAi(mysqli $link, bool $isAdmin, int $userId, string $fromDate, string $toDate, int $limit = 20, ?int $employeeId = null): array
{
    if (!tableExists($link, 'tbldaily_report')) {
        return [];
    }
    $limit = max(1, min(50, $limit));
    $sql = "SELECT r.iReportid, r.iUserid, r.sDate, r.sTimeIn, r.sTimeOut, r.sTasksPlanned, r.sTasksCompleted,
                   r.sWorkDetails, r.sPending, r.sBlockers, r.sTomorrowPlan, r.sRemarks, u.sName
            FROM tbldaily_report r
            LEFT JOIN tbluser u ON u.iUserid = r.iUserid
            WHERE r.sDate >= ? AND r.sDate <= ?";
    $types = 'ss';
    $params = [$fromDate, $toDate];
    if (!$isAdmin) {
        $sql .= ' AND r.iUserid = ?';
        $types .= 'i';
        $params[] = $userId;
    } elseif ($employeeId !== null && $employeeId > 0) {
        $sql .= ' AND r.iUserid = ?';
        $types .= 'i';
        $params[] = $employeeId;
    }
    $sql .= " ORDER BY r.sDate DESC, r.iReportid DESC LIMIT {$limit}";

    $stmt = $link->prepare($sql);
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param($types, ...$params);
    if (!$stmt->execute()) {
        $stmt->close();
        return [];
    }
    $result = $stmt->get_result();
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = [
            'id' => (int)$row['iReportid'],
            'employee_id' => (int)$row['iUserid'],
            'employee_name' => $row['sName'] ?: '',
            'date' => $row['sDate'],
            'time_in' => $row['sTimeIn'],
            'time_out' => $row['sTimeOut'],
            'tasks_planned' => $row['sTasksPlanned'],
            'tasks_completed' => $row['sTasksCompleted'],
            'work_details' => $row['sWorkDetails'],
            'pending' => $row['sPending'],
            'blockers' => $row['sBlockers'],
            'tomorrow_plan' => $row['sTomorrowPlan'],
            'remarks' => $row['sRemarks'],
        ];
    }
    $stmt->close();
    return $rows;
}

function enrichContextForMessage(mysqli $link, array $ctx, string $message): array
{
    $userId = (int)$ctx['current_user_id'];
    $isAdmin = !empty($ctx['is_admin']);
    $today = $ctx['today'];
    $range = parseDateRangeFromMessage($message, $today);

    // If user asks about leads/dates, attach a focused date query result
    $asksLeads = (bool)preg_match('/\b(lead|leads|created|create|new lead|kitne|how many|count|list|show|date|dates|aaj|kal)\b/ui', $message);
    if ($range && $asksLeads) {
        $from = $range['from'];
        $to = $range['to'];
        $ctx['date_query'] = [
            'label' => $range['label'],
            'from' => $from,
            'to' => $to,
            'lead_count' => countLeadsByCreatedRange($link, $isAdmin, $userId, $from, $to),
            'leads' => fetchLeadsByCreatedRange($link, $isAdmin, $userId, $from, $to, 30),
            'note' => 'Use date_query for answers about this period. created_day/created_date come from tblleads.sCreated_date.',
        ];
    }

    // Project / task focused query
    $asksProjects = (bool)preg_match('/\b(project|projects|won project|task|tasks|kanban|pending task|my task|project management)\b/ui', $message);
    if ($asksProjects) {
        $ctx['project_query'] = [
            'won_projects' => fetchWonProjectsForAi($link, $isAdmin, $userId, 30),
            'my_open_tasks' => fetchMyProjectTasksForAi($link, $isAdmin, $userId, false, 40),
            'note' => 'Won projects = leads with effective status Won (id 6). Tasks statuses: Pending, In Progress, Done.',
        ];

        // If a company/project name is mentioned, attach that project's tasks
        if (!empty($ctx['won_projects'])) {
            foreach ($ctx['won_projects'] as $project) {
                $company = trim((string)($project['company_name'] ?? ''));
                $leadName = trim((string)($project['lead_name'] ?? ''));
                $matched = false;
                if ($company !== '' && stripos($message, $company) !== false) {
                    $matched = true;
                } elseif ($leadName !== '' && stripos($message, $leadName) !== false) {
                    $matched = true;
                }
                if ($matched) {
                    $ctx['project_query']['focused_project'] = $project;
                    $ctx['project_query']['focused_tasks'] = fetchProjectTasksForAi($link, (int)$project['lead_id'], $userId, false, 50);
                    break;
                }
            }
        }
    }

    // Daily report focused query
    $asksDaily = (bool)preg_match('/\b(daily report|daily reports|work report|timesheet|time in|time out|tasks completed|tomorrow.?s plan|blocker|pending work)\b/ui', $message);
    if ($asksDaily || ($range && (bool)preg_match('/\b(report|reports|work|attendance)\b/ui', $message))) {
        $from = $range['from'] ?? $today;
        $to = $range['to'] ?? $today;
        $employeeFilter = null;
        // Admin may ask for a specific employee by name
        if ($isAdmin && !empty($ctx['users'])) {
            foreach ($ctx['users'] as $u) {
                $name = trim((string)($u['name'] ?? ''));
                if ($name !== '' && stripos($message, $name) !== false) {
                    $employeeFilter = (int)$u['id'];
                    break;
                }
            }
        }
        $ctx['daily_report_query'] = [
            'label' => $range['label'] ?? 'today',
            'from' => $from,
            'to' => $to,
            'employee_id' => $employeeFilter,
            'report_count' => countDailyReportsForAi($link, $isAdmin, $userId, $from, $to, $employeeFilter),
            'reports' => fetchDailyReportsForAi($link, $isAdmin, $userId, $from, $to, 30, $employeeFilter),
            'note' => 'Daily reports are in tbldaily_report. Non-admins only see their own. Admins see all unless employee filter set.',
        ];
    }

    return $ctx;
}

function buildCrmContext(mysqli $link): array
{
    $userId = (int)$_SESSION['user_id'];
    $isAdmin = (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'Admin');
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $weekFrom = date('Y-m-d', strtotime('monday this week'));
    $weekTo = date('Y-m-d', strtotime('sunday this week'));
    $monthFrom = date('Y-m-01');
    $monthTo = date('Y-m-t');

    $ctx = [
        'today' => $today,
        'timezone' => 'Asia/Kolkata',
        'current_user_id' => $userId,
        'current_user_name' => $_SESSION['username'] ?? 'User',
        'current_user_role' => $_SESSION['userRole'] ?? 'User',
        'is_admin' => $isAdmin,
        'date_guide' => [
            'lead_created_field' => 'sCreated_date',
            'today' => $today,
            'yesterday' => $yesterday,
            'this_week' => [$weekFrom, $weekTo],
            'this_month' => [$monthFrom, $monthTo],
        ],
        'counts' => [],
        'todays_reminders' => [],
        'todays_followups' => [],
        'upcoming_followups' => [],
        'recent_leads' => [],
        'leads_by_created_day' => [],
        'lead_statuses' => [],
        'lead_sources' => [],
        'users' => [],
        'won_projects' => [],
        'my_open_project_tasks' => [],
        'daily_reports' => [
            'today' => [],
            'yesterday' => [],
            'recent' => [],
        ],
    ];

    // Reminder / follow-up counts
    $stmt = $link->prepare('SELECT COUNT(*) FROM tblreminders WHERE iUserid = ? AND sDate = ?');
    $stmt->bind_param('is', $userId, $today);
    $stmt->execute();
    $stmt->bind_result($reminderCount);
    $stmt->fetch();
    $stmt->close();
    $ctx['counts']['my_reminders_today'] = (int)$reminderCount;

    $stmt = $link->prepare('SELECT COUNT(*) FROM tblreplayleads WHERE userid = ? AND sFollowupdate = ?');
    $stmt->bind_param('is', $userId, $today);
    $stmt->execute();
    $stmt->bind_result($followupCount);
    $stmt->fetch();
    $stmt->close();
    $ctx['counts']['my_followups_today'] = (int)$followupCount;

    // Lead counts by created date
    if ($isAdmin) {
        $res = $link->query('SELECT COUNT(*) AS c FROM tblleads');
        $ctx['counts']['leads_total'] = (int)($res->fetch_assoc()['c'] ?? 0);
    } else {
        $stmt = $link->prepare("SELECT COUNT(*) FROM tblleads l WHERE FIND_IN_SET(?, REPLACE(l.sAssigned_to, ' ', '')) > 0 OR l.sLead_owner = ? OR l.sCreated_by = ?");
        $stmt->bind_param('iii', $userId, $userId, $userId);
        $stmt->execute();
        $stmt->bind_result($leadCount);
        $stmt->fetch();
        $stmt->close();
        $ctx['counts']['leads_total'] = (int)$leadCount;
    }

    $ctx['counts']['leads_created_today'] = countLeadsByCreatedRange($link, $isAdmin, $userId, $today, $today);
    $ctx['counts']['leads_created_yesterday'] = countLeadsByCreatedRange($link, $isAdmin, $userId, $yesterday, $yesterday);
    $ctx['counts']['leads_created_this_week'] = countLeadsByCreatedRange($link, $isAdmin, $userId, $weekFrom, $weekTo);
    $ctx['counts']['leads_created_this_month'] = countLeadsByCreatedRange($link, $isAdmin, $userId, $monthFrom, $monthTo);

    // Project management counts
    $wonProjects = fetchWonProjectsForAi($link, $isAdmin, $userId, 25);
    $ctx['won_projects'] = $wonProjects;
    $ctx['counts']['won_projects'] = count($wonProjects);
    $ctx['counts']['project_tasks_total'] = 0;
    $ctx['counts']['project_tasks_pending'] = 0;
    $ctx['counts']['project_tasks_in_progress'] = 0;
    $ctx['counts']['project_tasks_done'] = 0;
    $ctx['counts']['my_project_tasks'] = 0;
    foreach ($wonProjects as $p) {
        $ctx['counts']['project_tasks_total'] += (int)($p['task_total'] ?? 0);
        $ctx['counts']['project_tasks_pending'] += (int)($p['task_pending'] ?? 0);
        $ctx['counts']['project_tasks_in_progress'] += (int)($p['task_in_progress'] ?? 0);
        $ctx['counts']['project_tasks_done'] += (int)($p['task_done'] ?? 0);
        $ctx['counts']['my_project_tasks'] += (int)($p['my_tasks'] ?? 0);
    }
    $ctx['my_open_project_tasks'] = fetchMyProjectTasksForAi($link, $isAdmin, $userId, true, 20);
    $ctx['counts']['my_open_project_tasks'] = count($ctx['my_open_project_tasks']);

    // Daily report counts + samples
    $ctx['counts']['daily_reports_today'] = countDailyReportsForAi($link, $isAdmin, $userId, $today, $today);
    $ctx['counts']['daily_reports_yesterday'] = countDailyReportsForAi($link, $isAdmin, $userId, $yesterday, $yesterday);
    $ctx['counts']['daily_reports_this_week'] = countDailyReportsForAi($link, $isAdmin, $userId, $weekFrom, $weekTo);
    $ctx['daily_reports']['today'] = fetchDailyReportsForAi($link, $isAdmin, $userId, $today, $today, 15);
    $ctx['daily_reports']['yesterday'] = fetchDailyReportsForAi($link, $isAdmin, $userId, $yesterday, $yesterday, 15);
    $from7 = date('Y-m-d', strtotime('-6 days'));
    $ctx['daily_reports']['recent'] = fetchDailyReportsForAi($link, $isAdmin, $userId, $from7, $today, 15);
    $myTodayReports = fetchDailyReportsForAi($link, false, $userId, $today, $today, 1);
    $ctx['my_daily_report_today'] = $myTodayReports[0] ?? null;
    $ctx['counts']['i_submitted_daily_report_today'] = $ctx['my_daily_report_today'] ? 1 : 0;

    // Today's reminders
    $stmt = $link->prepare('SELECT rrid, sDescription, sDate FROM tblreminders WHERE iUserid = ? AND sDate = ? ORDER BY rrid DESC LIMIT 8');
    $stmt->bind_param('is', $userId, $today);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $ctx['todays_reminders'][] = [
            'id' => (int)$row['rrid'],
            'description' => $row['sDescription'],
            'date' => $row['sDate'],
        ];
    }
    $stmt->close();

    // Today's follow-ups
    $stmt = $link->prepare('SELECT r.rId, r.lead_id, r.sDescription, r.sFollowupdate, s.sStatus AS status_name, l.sCompany_name, l.sLead_name
        FROM tblreplayleads r
        LEFT JOIN tblstatus s ON r.sStatus = s.iStatusid
        LEFT JOIN tblleads l ON r.lead_id = l.iLead_id
        WHERE r.userid = ? AND r.sFollowupdate = ?
        ORDER BY r.rId DESC LIMIT 8');
    $stmt->bind_param('is', $userId, $today);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $ctx['todays_followups'][] = [
            'id' => (int)$row['rId'],
            'lead_id' => (int)$row['lead_id'],
            'lead_name' => $row['sLead_name'],
            'description' => $row['sDescription'],
            'company' => $row['sCompany_name'],
            'followup_date' => $row['sFollowupdate'],
            'status' => $row['status_name'],
        ];
    }
    $stmt->close();

    // Upcoming follow-ups (next 7 days)
    $weekAhead = date('Y-m-d', strtotime('+7 days'));
    $stmt = $link->prepare('SELECT r.rId, r.lead_id, r.sDescription, r.sFollowupdate, l.sLead_name, l.sCompany_name
        FROM tblreplayleads r
        LEFT JOIN tblleads l ON r.lead_id = l.iLead_id
        WHERE r.userid = ? AND r.sFollowupdate > ? AND r.sFollowupdate <= ?
        ORDER BY r.sFollowupdate ASC LIMIT 10');
    $stmt->bind_param('iss', $userId, $today, $weekAhead);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $ctx['upcoming_followups'][] = [
            'id' => (int)$row['rId'],
            'lead_id' => (int)$row['lead_id'],
            'lead_name' => $row['sLead_name'],
            'company' => $row['sCompany_name'],
            'description' => $row['sDescription'],
            'followup_date' => $row['sFollowupdate'],
        ];
    }
    $stmt->close();

    // Recent leads with created dates
    $ctx['recent_leads'] = fetchLeadsByCreatedRange($link, $isAdmin, $userId, '2000-01-01', $today, 15);

    // Leads created today / yesterday lists
    $ctx['leads_created_today_list'] = fetchLeadsByCreatedRange($link, $isAdmin, $userId, $today, $today, 20);
    $ctx['leads_created_yesterday_list'] = fetchLeadsByCreatedRange($link, $isAdmin, $userId, $yesterday, $yesterday, 20);

    // Daily created counts (last 14 days)
    $from14 = date('Y-m-d', strtotime('-13 days'));
    $scope = leadScopeSql($isAdmin);
    $sql = "SELECT DATE(l.sCreated_date) AS day, COUNT(*) AS c
            FROM tblleads l
            WHERE {$scope}
              AND DATE(l.sCreated_date) >= ?
              AND DATE(l.sCreated_date) <= ?
            GROUP BY DATE(l.sCreated_date)
            ORDER BY day DESC";
    $stmt = $link->prepare($sql);
    if ($stmt) {
        if ($isAdmin) {
            $stmt->bind_param('ss', $from14, $today);
        } else {
            $stmt->bind_param('iiiss', $userId, $userId, $userId, $from14, $today);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $ctx['leads_by_created_day'][] = [
                'date' => $row['day'],
                'count' => (int)$row['c'],
            ];
        }
        $stmt->close();
    }

    // Status / source lookups
    $res = $link->query('SELECT iStatusid, sStatus FROM tblstatus ORDER BY iStatusid');
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $ctx['lead_statuses'][] = ['id' => (int)$row['iStatusid'], 'name' => $row['sStatus']];
        }
    }
    $res = $link->query('SELECT iSourceid, sSources FROM tblsources ORDER BY iSourceid');
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $ctx['lead_sources'][] = ['id' => (int)$row['iSourceid'], 'name' => $row['sSources']];
        }
    }

    // Users
    $res = $link->query('SELECT iUserid, sName, sRole FROM tbluser ORDER BY sName LIMIT 40');
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $ctx['users'][] = [
                'id' => (int)$row['iUserid'],
                'name' => $row['sName'],
                'role' => $row['sRole'],
            ];
        }
    }

    return $ctx;
}

function buildSystemPrompt(array $ctx): string
{
    $json = json_encode($ctx, JSON_UNESCAPED_UNICODE);
    $today = $ctx['today'];
    return <<<PROMPT
You are infiCRM AI Assistant, a helpful sales CRM agent inside infiCRM.
Today is {$today} (Asia/Kolkata). Current user: {$ctx['current_user_name']} (id {$ctx['current_user_id']}, role {$ctx['current_user_role']}).

IMPORTANT DATE RULES:
- Lead created date field is sCreated_date (shown as created_date / created_day).
- Always use counts.leads_created_today / yesterday / this_week / this_month for date questions.
- If date_query exists, prefer date_query.lead_count and date_query.leads for that exact period.
- Do NOT invent dates or counts. If count is 0, say zero clearly.
- When listing leads, include created_day (YYYY-MM-DD).
- "Today's leads" means leads where created_day = {$today}.
- Follow-up dates use followup_date / sFollowupdate. Reminder dates use sDate.
- Never confuse total leads with today's newly created leads.

PROJECT MANAGEMENT RULES:
- Won projects are in won_projects (leads with status Won). Use counts.won_projects and task counts.
- Task statuses are exactly: Pending, In Progress, Done.
- my_open_project_tasks = current user's Pending/In Progress tasks across won projects.
- If project_query exists, prefer it for project/task questions. If focused_project/focused_tasks exist, answer about that project.
- When listing projects: "{company_name} | Tasks: {task_done}/{task_total} done | My tasks: {my_tasks}".
- When listing tasks: "{title} | Status: {status} | Due: {due_date} | Project: {company_name}".
- Pages: project-management.php (list), project-tasks.php?lead_id=... (kanban board).

DAILY REPORT RULES:
- Daily reports live in daily_reports.today / yesterday / recent and counts.daily_reports_*.
- Non-admins only see their own reports. Admins may see all employees.
- If daily_report_query exists, prefer it for the asked date range / employee.
- my_daily_report_today and counts.i_submitted_daily_report_today show whether current user submitted today.
- Time in/out are 24-hour HH:MM.
- When summarizing a report include: employee, date, time_in-time_out, tasks_completed, work_details, pending, blockers, tomorrow_plan.
- Page: list-daily-report.php / add-daily-report.php.

IMPORTANT LISTING RULES (must match List Leads screen):
- Primary name = company_name / display_name (this is the COMPANY NAME column).
- Product = product_names (PRODUCT NAME column). Example: Digital Marketing/Advertising.
- Also show phone, status, assigned_to when available.
- lead_title is sLead_name (interest/title like "solar system") — do NOT present it as the main lead/company name.
- Format each lead like: "1. {company_name} | Product: {product_names} | Phone: {phone} | Status: {status}".

You can:
1) Answer using CRM context (date counts, reminders, follow-ups, recent leads, won projects, project tasks, daily reports, statuses, sources, users).
2) Propose creating a lead, reminder, daily report, or project task — NEVER create silently. Always propose and wait for confirmation.
3) Draft short follow-up / WhatsApp / email text.
4) Ask clarifying questions if create data is incomplete.

When proposing an action, respond with ONLY valid JSON (no markdown fences) in this exact shape:
{
  "reply": "Friendly explanation of what you will do and what you found.",
  "proposed_action": null
}

Or when ready to create:
{
  "reply": "I can create this. Please confirm.",
  "proposed_action": {
    "type": "create_lead",
    "summary": "Create lead: Name / Phone / Company",
    "payload": {
      "sLead_name": "required",
      "sPhone": "",
      "sEmail": "",
      "sCompany_name": "",
      "sContactperson": "",
      "sLead_status": "status name or id from list",
      "sLead_source": "source name or id from list",
      "sLead_priority": "High|Medium|Low or empty",
      "sLead_type": "",
      "sLocation": "",
      "sAddress": "",
      "sAssigned_to": "user id or name (default current user)",
      "sLead_owner": "user id or name (default current user)",
      "sTags": "",
      "sPreferred_communication": "",
      "sAlternate_phone": "",
      "sIndustry_type": "",
      "sDesignation": "",
      "sWebsite": ""
    }
  }
}

Or for reminder:
{
  "reply": "I can create this reminder. Please confirm.",
  "proposed_action": {
    "type": "create_reminder",
    "summary": "Reminder on YYYY-MM-DD: description",
    "payload": {
      "description": "required",
      "date": "YYYY-MM-DD",
      "iUserid": "user id or name (default current user)"
    }
  }
}

Or for daily report:
{
  "reply": "I can submit this daily report. Please confirm.",
  "proposed_action": {
    "type": "create_daily_report",
    "summary": "Daily report for YYYY-MM-DD",
    "payload": {
      "date": "YYYY-MM-DD (default today)",
      "time_in": "HH:MM 24-hour",
      "time_out": "HH:MM 24-hour",
      "tasks_planned": "",
      "tasks_completed": "",
      "work_details": "",
      "pending": "",
      "blockers": "",
      "tomorrow_plan": "",
      "remarks": ""
    }
  }
}

Or for project task:
{
  "reply": "I can add this project task. Please confirm.",
  "proposed_action": {
    "type": "create_project_task",
    "summary": "Add task on project: Company / Title",
    "payload": {
      "lead_id": "required won project lead id",
      "sTitle": "required",
      "sDescription": "",
      "sAssigned_to": "user id or name (must be project assignee/owner)",
      "sDue_date": "YYYY-MM-DD or empty",
      "sStatus": "Pending|In Progress|Done"
    }
  }
}

Rules:
- Always return JSON with "reply" string.
- proposed_action must be null unless user clearly wants to create something AND required fields are present.
- Prefer status/source names from context.
- Keep replies concise; for date questions start with the exact count and date range.
- If asked something outside CRM, briefly help then steer back.

CRM CONTEXT JSON:
{$json}
PROMPT;
}

function callGemini(string $systemPrompt, array $contents): array
{
    $url = GEMINI_API_BASE . '/' . rawurlencode(GEMINI_MODEL) . ':generateContent?key=' . urlencode(GEMINI_API_KEY);

    $body = [
        'systemInstruction' => [
            'parts' => [['text' => $systemPrompt]],
        ],
        'contents' => $contents,
        'generationConfig' => [
            'temperature' => 0.4,
            'maxOutputTokens' => 2048,
            'responseMimeType' => 'application/json',
        ],
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($body),
        CURLOPT_TIMEOUT => 60,
    ]);
    $response = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        return ['status' => 'error', 'message' => 'Could not reach Gemini: ' . $curlErr];
    }

    $data = json_decode($response, true);
    if ($httpCode >= 400) {
        $msg = $data['error']['message'] ?? ('Gemini HTTP ' . $httpCode);
        return ['status' => 'error', 'message' => $msg];
    }

    $parts = $data['candidates'][0]['content']['parts'] ?? [];
    $text = '';
    if (is_array($parts)) {
        foreach ($parts as $part) {
            if (!empty($part['text'])) {
                $text .= $part['text'];
            }
        }
    }
    if (trim($text) === '') {
        return ['status' => 'error', 'message' => 'Empty response from Gemini. Try again.'];
    }

    return ['status' => 'success', 'text' => $text];
}

function parseAgentResponse(string $text): array
{
    $clean = trim($text);
    $clean = preg_replace('/^\xEF\xBB\xBF/', '', $clean);

    if (preg_match('/```(?:json)?\s*([\s\S]*?)```/', $clean, $m)) {
        $clean = trim($m[1]);
    }

    $decoded = decodeAgentJson($clean);

    if (!is_array($decoded)) {
        // Fallback: pull "reply" string even from broken JSON
        if (preg_match('/"reply"\s*:\s*"((?:\\\\.|[^"\\\\])*)"/s', $clean, $rm)) {
            $replyOnly = stripcslashes($rm[1]);
            return [
                'reply' => $replyOnly !== '' ? $replyOnly : 'Here is what I found.',
                'proposed_action' => null,
            ];
        }
        // Last resort: hide raw JSON braces from the user
        if (strpos($clean, '"reply"') !== false || (strlen($clean) > 0 && $clean[0] === '{')) {
            return [
                'reply' => 'I had trouble formatting that answer. Please ask again.',
                'proposed_action' => null,
            ];
        }
        return [
            'reply' => $text,
            'proposed_action' => null,
        ];
    }

    $reply = trim((string)($decoded['reply'] ?? ''));
    if ($reply === '') {
        $reply = 'Here is what I found.';
    }

    $proposed = $decoded['proposed_action'] ?? null;
    if (!is_array($proposed) || empty($proposed['type'])) {
        $proposed = null;
    } else {
        $type = $proposed['type'];
        if (!in_array($type, ['create_lead', 'create_reminder'], true)) {
            $proposed = null;
        }
    }

    return [
        'reply' => $reply,
        'proposed_action' => $proposed,
    ];
}

function decodeAgentJson(string $clean): ?array
{
    $decoded = json_decode($clean, true);
    if (is_array($decoded)) {
        return $decoded;
    }

    // Trim trailing junk / extra closing braces Gemini sometimes adds
    $trimmed = rtrim($clean);
    while (substr_count($trimmed, '}') > substr_count($trimmed, '{') && substr($trimmed, -1) === '}') {
        $trimmed = rtrim(substr($trimmed, 0, -1));
        $decoded = json_decode($trimmed, true);
        if (is_array($decoded)) {
            return $decoded;
        }
    }

    // Balanced {...} extraction from first '{'
    $start = strpos($clean, '{');
    if ($start === false) {
        return null;
    }
    $depth = 0;
    $inString = false;
    $escape = false;
    $len = strlen($clean);
    for ($i = $start; $i < $len; $i++) {
        $ch = $clean[$i];
        if ($inString) {
            if ($escape) {
                $escape = false;
            } elseif ($ch === '\\') {
                $escape = true;
            } elseif ($ch === '"') {
                $inString = false;
            }
            continue;
        }
        if ($ch === '"') {
            $inString = true;
            continue;
        }
        if ($ch === '{') {
            $depth++;
        } elseif ($ch === '}') {
            $depth--;
            if ($depth === 0) {
                $slice = substr($clean, $start, $i - $start + 1);
                $decoded = json_decode($slice, true);
                return is_array($decoded) ? $decoded : null;
            }
        }
    }

    return null;
}

function resolveUserId(mysqli $link, $value, int $defaultId): int
{
    if ($value === null || $value === '') {
        return $defaultId;
    }
    if (is_numeric($value)) {
        return (int)$value;
    }
    $name = trim((string)$value);
    $stmt = $link->prepare('SELECT iUserid FROM tbluser WHERE sName = ? LIMIT 1');
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $stmt->bind_result($uid);
    if ($stmt->fetch()) {
        $stmt->close();
        return (int)$uid;
    }
    $stmt->close();
    return $defaultId;
}

function resolveStatusId(mysqli $link, $value): string
{
    if ($value === null || $value === '') {
        // default first status if any
        $res = $link->query('SELECT iStatusid FROM tblstatus ORDER BY iStatusid ASC LIMIT 1');
        $row = $res ? $res->fetch_assoc() : null;
        return $row ? (string)$row['iStatusid'] : '';
    }
    if (is_numeric($value)) {
        return (string)((int)$value);
    }
    $name = trim((string)$value);
    $stmt = $link->prepare('SELECT iStatusid FROM tblstatus WHERE sStatus = ? LIMIT 1');
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $stmt->bind_result($id);
    if ($stmt->fetch()) {
        $stmt->close();
        return (string)$id;
    }
    $stmt->close();
    // fuzzy contains
    $like = '%' . $name . '%';
    $stmt = $link->prepare('SELECT iStatusid FROM tblstatus WHERE sStatus LIKE ? LIMIT 1');
    $stmt->bind_param('s', $like);
    $stmt->execute();
    $stmt->bind_result($id);
    if ($stmt->fetch()) {
        $stmt->close();
        return (string)$id;
    }
    $stmt->close();
    return '';
}

function resolveSourceId(mysqli $link, $value): string
{
    if ($value === null || $value === '') {
        $res = $link->query('SELECT iSourceid FROM tblsources ORDER BY iSourceid ASC LIMIT 1');
        $row = $res ? $res->fetch_assoc() : null;
        return $row ? (string)$row['iSourceid'] : '';
    }
    if (is_numeric($value)) {
        return (string)((int)$value);
    }
    $name = trim((string)$value);
    $stmt = $link->prepare('SELECT iSourceid FROM tblsources WHERE sSources = ? LIMIT 1');
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $stmt->bind_result($id);
    if ($stmt->fetch()) {
        $stmt->close();
        return (string)$id;
    }
    $stmt->close();
    $like = '%' . $name . '%';
    $stmt = $link->prepare('SELECT iSourceid FROM tblsources WHERE sSources LIKE ? LIMIT 1');
    $stmt->bind_param('s', $like);
    $stmt->execute();
    $stmt->bind_result($id);
    if ($stmt->fetch()) {
        $stmt->close();
        return (string)$id;
    }
    $stmt->close();
    return '';
}

function createLeadFromAi(mysqli $link, array $p): array
{
    $userId = (int)$_SESSION['user_id'];
    $name = trim((string)($p['sLead_name'] ?? ''));
    if ($name === '') {
        return ['status' => 'error', 'message' => 'Lead name is required.'];
    }

    $sEmail = trim((string)($p['sEmail'] ?? ''));
    $sPhone = trim((string)($p['sPhone'] ?? ''));
    $sAlternate_phone = trim((string)($p['sAlternate_phone'] ?? ''));
    $sLead_source = resolveSourceId($link, $p['sLead_source'] ?? '');
    $sLead_status = resolveStatusId($link, $p['sLead_status'] ?? '');
    $sLead_priority = trim((string)($p['sLead_priority'] ?? ''));
    $sLead_type = trim((string)($p['sLead_type'] ?? ''));
    $sCompany_name = trim((string)($p['sCompany_name'] ?? ''));
    $sIndustry_type = trim((string)($p['sIndustry_type'] ?? ''));
    $sDesignation = trim((string)($p['sDesignation'] ?? ''));
    $sWebsite = trim((string)($p['sWebsite'] ?? ''));
    $sLocation = trim((string)($p['sLocation'] ?? ''));
    $sAddress = trim((string)($p['sAddress'] ?? ''));
    $sAssigned_to = (string)resolveUserId($link, $p['sAssigned_to'] ?? '', $userId);
    $sLead_owner = (string)resolveUserId($link, $p['sLead_owner'] ?? '', $userId);
    $sPreferred_communication = trim((string)($p['sPreferred_communication'] ?? ''));
    $sTags = trim((string)($p['sTags'] ?? ''));
    $sContactperson = trim((string)($p['sContactperson'] ?? ''));
    $file1 = '';
    $file2 = '';
    $file3 = '';
    $sCreated_by = $userId;

    $query = "INSERT INTO tblleads (
        sLead_name, sEmail, sPhone, sAlternate_phone, sLead_source, sLead_status,
        sLead_priority, sLead_type, sCompany_name, sIndustry_type, sDesignation,
        sWebsite, sLocation, sAddress, sAssigned_to, sLead_owner, sPreferred_communication,
        sTags, sContactperson, sFileupload, sFileupload2, sFileupload3, sCreated_by
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($link, $query);
    if (!$stmt) {
        return ['status' => 'error', 'message' => 'Database error: ' . mysqli_error($link)];
    }

    mysqli_stmt_bind_param(
        $stmt,
        'ssssssssssssssssssssssi',
        $name,
        $sEmail,
        $sPhone,
        $sAlternate_phone,
        $sLead_source,
        $sLead_status,
        $sLead_priority,
        $sLead_type,
        $sCompany_name,
        $sIndustry_type,
        $sDesignation,
        $sWebsite,
        $sLocation,
        $sAddress,
        $sAssigned_to,
        $sLead_owner,
        $sPreferred_communication,
        $sTags,
        $sContactperson,
        $file1,
        $file2,
        $file3,
        $sCreated_by
    );

    if (!mysqli_stmt_execute($stmt)) {
        $err = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        return ['status' => 'error', 'message' => 'Could not create lead: ' . $err];
    }

    $leadId = (int)mysqli_insert_id($link);
    mysqli_stmt_close($stmt);

    // Mirror company + email into Customer List
    if ($sCompany_name !== '') {
        syncLeadCompanyToCustomerFromAi($link, [
            'company_name' => $sCompany_name,
            'email' => $sEmail,
            'phone' => $sPhone,
            'contact_person' => $sContactperson,
            'industry' => $sIndustry_type,
            'address' => $sAddress,
            'tags' => $sTags,
            'source_id' => (int)$sLead_source,
            'user_id' => $userId,
        ]);
    }

    return [
        'status' => 'success',
        'message' => 'Lead created successfully (#' . $leadId . ').',
        'lead_id' => $leadId,
        'link' => 'list-lead-master.php',
    ];
}

function syncLeadCompanyToCustomerFromAi(mysqli $link, array $lead): void
{
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
    $userId = (int)($lead['user_id'] ?? 0);

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
        if ($email !== '' && trim((string)($existing['sEmail'] ?? '')) === '') {
            $upd = $link->prepare('UPDATE tblcustomer SET sEmail = ? WHERE iCustomerid = ?');
            if ($upd) {
                $upd->bind_param('si', $email, $customerId);
                $upd->execute();
                $upd->close();
            }
        }
    } else {
        $gstin = '';
        $customertype = '';
        $status = 'Active';
        $notes = 'Auto-created from lead';
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

    if (!empty($customerId) && ($contactPerson !== '' || $phone !== '' || $email !== '')) {
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

function createReminderFromAi(mysqli $link, array $p): array
{
    $userId = (int)$_SESSION['user_id'];
    $description = trim((string)($p['description'] ?? ''));
    $date = trim((string)($p['date'] ?? ''));
    $assignTo = resolveUserId($link, $p['iUserid'] ?? '', $userId);

    if ($description === '') {
        return ['status' => 'error', 'message' => 'Reminder description is required.'];
    }
    if ($date === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return ['status' => 'error', 'message' => 'Reminder date must be YYYY-MM-DD.'];
    }

    $query = 'INSERT INTO tblreminders (iUserid, sDescription, sDate, sAssigned_by) VALUES (?, ?, ?, ?)';
    $stmt = mysqli_prepare($link, $query);
    if (!$stmt) {
        return ['status' => 'error', 'message' => 'Database error: ' . mysqli_error($link)];
    }
    mysqli_stmt_bind_param($stmt, 'issi', $assignTo, $description, $date, $userId);
    if (!mysqli_stmt_execute($stmt)) {
        $err = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        return ['status' => 'error', 'message' => 'Could not create reminder: ' . $err];
    }
    $id = (int)mysqli_insert_id($link);
    mysqli_stmt_close($stmt);

    return [
        'status' => 'success',
        'message' => 'Reminder created successfully.',
        'reminder_id' => $id,
        'link' => 'list-reminders.php',
    ];
}

function ensureDailyReportTableForAi(mysqli $link): bool
{
    if (tableExists($link, 'tbldaily_report')) {
        return true;
    }
    $sql = "CREATE TABLE IF NOT EXISTS tbldaily_report (
      iReportid INT AUTO_INCREMENT PRIMARY KEY,
      iUserid INT NOT NULL,
      sDate DATE NOT NULL,
      sTimeIn VARCHAR(20) NULL,
      sTimeOut VARCHAR(20) NULL,
      sTasksPlanned TEXT NULL,
      sTasksCompleted TEXT NULL,
      sWorkDetails TEXT NULL,
      sPending TEXT NULL,
      sBlockers TEXT NULL,
      sTomorrowPlan TEXT NULL,
      sRemarks TEXT NULL,
      sCreatedTimeStamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      sModifiedTimestamp DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
      INDEX idx_user (iUserid),
      INDEX idx_date (sDate)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $ok = (bool)mysqli_query($link, $sql);
    return $ok && tableExists($link, 'tbldaily_report', true);
}

function ensureProjectTasksTableForAi(mysqli $link): bool
{
    if (tableExists($link, 'tblproject_tasks')) {
        return true;
    }
    $sql = "CREATE TABLE IF NOT EXISTS tblproject_tasks (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    return (bool)mysqli_query($link, $sql) && tableExists($link, 'tblproject_tasks', true);
}

function createDailyReportFromAi(mysqli $link, array $p): array
{
    if (!ensureDailyReportTableForAi($link)) {
        return ['status' => 'error', 'message' => 'Daily report table is not available.'];
    }

    $userId = (int)$_SESSION['user_id'];
    $date = trim((string)($p['date'] ?? ''));
    if ($date === '') {
        $date = date('Y-m-d');
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return ['status' => 'error', 'message' => 'Date must be YYYY-MM-DD.'];
    }

    $timeIn = trim((string)($p['time_in'] ?? ''));
    $timeOut = trim((string)($p['time_out'] ?? ''));
    $timePattern = '/^([01][0-9]|2[0-3]):[0-5][0-9]$/';
    if ($timeIn !== '' && !preg_match($timePattern, $timeIn)) {
        return ['status' => 'error', 'message' => 'Time In must be 24-hour HH:MM.'];
    }
    if ($timeOut !== '' && !preg_match($timePattern, $timeOut)) {
        return ['status' => 'error', 'message' => 'Time Out must be 24-hour HH:MM.'];
    }

    $tasksPlanned = trim((string)($p['tasks_planned'] ?? ''));
    $tasksCompleted = trim((string)($p['tasks_completed'] ?? ''));
    $workDetails = trim((string)($p['work_details'] ?? ''));
    $pending = trim((string)($p['pending'] ?? ''));
    $blockers = trim((string)($p['blockers'] ?? ''));
    $tomorrowPlan = trim((string)($p['tomorrow_plan'] ?? ''));
    $remarks = trim((string)($p['remarks'] ?? ''));

    if ($tasksCompleted === '' && $workDetails === '' && $tasksPlanned === '') {
        return ['status' => 'error', 'message' => 'Please include at least tasks planned, tasks completed, or work details.'];
    }

    $query = 'INSERT INTO tbldaily_report
        (iUserid, sDate, sTimeIn, sTimeOut, sTasksPlanned, sTasksCompleted, sWorkDetails, sPending, sBlockers, sTomorrowPlan, sRemarks)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
    $stmt = mysqli_prepare($link, $query);
    if (!$stmt) {
        return ['status' => 'error', 'message' => 'Database error: ' . mysqli_error($link)];
    }
    mysqli_stmt_bind_param(
        $stmt,
        'issssssssss',
        $userId,
        $date,
        $timeIn,
        $timeOut,
        $tasksPlanned,
        $tasksCompleted,
        $workDetails,
        $pending,
        $blockers,
        $tomorrowPlan,
        $remarks
    );
    if (!mysqli_stmt_execute($stmt)) {
        $err = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        return ['status' => 'error', 'message' => 'Could not save daily report: ' . $err];
    }
    $id = (int)mysqli_insert_id($link);
    mysqli_stmt_close($stmt);

    return [
        'status' => 'success',
        'message' => 'Daily report saved successfully.',
        'report_id' => $id,
        'link' => 'list-daily-report.php',
    ];
}

function createProjectTaskFromAi(mysqli $link, array $p): array
{
    if (!ensureProjectTasksTableForAi($link)) {
        return ['status' => 'error', 'message' => 'Project tasks table is not available.'];
    }

    $userId = (int)$_SESSION['user_id'];
    $isAdmin = (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'Admin');
    $leadId = (int)($p['lead_id'] ?? 0);
    $title = trim((string)($p['sTitle'] ?? $p['title'] ?? ''));
    $description = trim((string)($p['sDescription'] ?? $p['description'] ?? ''));
    $dueDate = trim((string)($p['sDue_date'] ?? $p['due_date'] ?? ''));
    $status = trim((string)($p['sStatus'] ?? $p['status'] ?? 'Pending'));
    $assignTo = resolveUserId($link, $p['sAssigned_to'] ?? ($p['assigned_to'] ?? ''), $userId);

    if ($leadId <= 0) {
        return ['status' => 'error', 'message' => 'Won project lead_id is required.'];
    }
    if ($title === '') {
        return ['status' => 'error', 'message' => 'Task title is required.'];
    }
    if (!in_array($status, ['Pending', 'In Progress', 'Done'], true)) {
        $status = 'Pending';
    }
    if ($dueDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
        return ['status' => 'error', 'message' => 'Due date must be YYYY-MM-DD.'];
    }

    $stmt = $link->prepare('SELECT iLead_id, sCompany_name, sLead_name, sAssigned_to, sLead_owner, sLead_status FROM tblleads WHERE iLead_id = ? LIMIT 1');
    if (!$stmt) {
        return ['status' => 'error', 'message' => 'Database error.'];
    }
    $stmt->bind_param('i', $leadId);
    $stmt->execute();
    $lead = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$lead) {
        return ['status' => 'error', 'message' => 'Project not found.'];
    }

    // Effective status must be Won
    $statusId = (string)($lead['sLead_status'] ?? '');
    $st = $link->prepare('SELECT sStatus FROM tblreplayleads WHERE lead_id = ? ORDER BY sCreatedTimestamp DESC LIMIT 1');
    if ($st) {
        $st->bind_param('i', $leadId);
        $st->execute();
        $row = $st->get_result()->fetch_assoc();
        $st->close();
        if ($row && $row['sStatus'] !== null && $row['sStatus'] !== '') {
            $statusId = (string)$row['sStatus'];
        }
    }
    if ($statusId !== aiWonStatusId()) {
        return ['status' => 'error', 'message' => 'Only Won projects can have tasks.'];
    }

    $assignedParts = preg_split('/\s*,\s*/', trim((string)($lead['sAssigned_to'] ?? '')), -1, PREG_SPLIT_NO_EMPTY);
    $assignedIds = [];
    foreach ($assignedParts as $part) {
        $id = (int)$part;
        if ($id > 0) {
            $assignedIds[$id] = $id;
        }
    }
    $ownerId = (int)($lead['sLead_owner'] ?? 0);
    $canAccess = $isAdmin || isset($assignedIds[$userId]) || $ownerId === $userId;
    if (!$canAccess) {
        return ['status' => 'error', 'message' => 'You are not assigned to this project.'];
    }

    $allowed = $assignedIds;
    if ($ownerId > 0) {
        $allowed[$ownerId] = $ownerId;
    }
    if (!isset($allowed[$assignTo])) {
        return ['status' => 'error', 'message' => 'Task can only be assigned to project assigned users or lead owner.'];
    }

    $dueParam = $dueDate !== '' ? $dueDate : null;
    $ins = $link->prepare('INSERT INTO tblproject_tasks (lead_id, sTitle, sDescription, sAssigned_to, sCreated_by, sStatus, sDue_date)
        VALUES (?, ?, ?, ?, ?, ?, ?)');
    if (!$ins) {
        return ['status' => 'error', 'message' => 'Database error: ' . mysqli_error($link)];
    }
    $ins->bind_param('issiiss', $leadId, $title, $description, $assignTo, $userId, $status, $dueParam);
    if (!$ins->execute()) {
        $err = $ins->error;
        $ins->close();
        return ['status' => 'error', 'message' => 'Could not save task: ' . $err];
    }
    $taskId = (int)$ins->insert_id;
    $ins->close();

    $company = $lead['sCompany_name'] ?: ($lead['sLead_name'] ?: ('#' . $leadId));
    return [
        'status' => 'success',
        'message' => 'Task added on project ' . $company . '.',
        'task_id' => $taskId,
        'link' => 'project-tasks.php?lead_id=' . $leadId,
    ];
}

