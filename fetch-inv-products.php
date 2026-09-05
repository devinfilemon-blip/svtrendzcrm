<?php
ob_start();
include 'layouts/config.php';
ob_end_clean();

header('Content-Type: application/json; charset=utf-8');

$search = '';
if (isset($_POST['searchTerm'])) {
    $search = trim((string) $_POST['searchTerm']);
} elseif (isset($_GET['searchTerm'])) {
    $search = trim((string) $_GET['searchTerm']);
} elseif (isset($_GET['q'])) {
    $search = trim((string) $_GET['q']);
}

$data = [];

try {
    if (!isset($link) || !($link instanceof mysqli)) {
        throw new Exception('Database connection failed');
    }

    $nameFilter = "sProductName IS NOT NULL AND TRIM(sProductName) <> '' AND sStatus = 'Active'";
    $searchEscaped = mysqli_real_escape_string($link, $search);

    if ($search === '') {
        $sql = "SELECT iProductid, sProductName, sProductCode, sHsnCode, sUnit FROM tblinv_product WHERE {$nameFilter} ORDER BY sProductName ASC LIMIT 100";
    } else {
        $sql = "SELECT iProductid, sProductName, sProductCode, sHsnCode, sUnit FROM tblinv_product WHERE {$nameFilter}
                AND (sProductName LIKE '%{$searchEscaped}%' OR sProductCode LIKE '%{$searchEscaped}%')
                ORDER BY sProductName ASC LIMIT 100";
    }

    $result = mysqli_query($link, $sql);
    if ($result === false) {
        throw new Exception(mysqli_error($link));
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $name = trim((string) $row['sProductName']);
        if ($name === '') {
            continue;
        }
        $code = trim((string) ($row['sProductCode'] ?? ''));
        $data[] = [
            'id'   => (int) $row['iProductid'],
            'text' => $code !== '' ? "{$name} ({$code})" : $name,
            'hsn'  => trim((string) ($row['sHsnCode'] ?? '')),
            'unit' => trim((string) ($row['sUnit'] ?? '')),
        ];
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error'   => true,
        'message' => $e->getMessage(),
    ]);
    exit;
}

echo json_encode($data, JSON_UNESCAPED_UNICODE);
