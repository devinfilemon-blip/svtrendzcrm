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

    $nameFilter = "sProductname IS NOT NULL AND TRIM(sProductname) <> ''";
    $searchEscaped = mysqli_real_escape_string($link, $search);

    if ($search === '') {
        $sql = "SELECT iProductid, sProductname FROM tblproduct WHERE {$nameFilter} ORDER BY sProductname ASC LIMIT 100";
    } else {
        $sql = "SELECT iProductid, sProductname FROM tblproduct WHERE {$nameFilter} AND sProductname LIKE '%{$searchEscaped}%' ORDER BY sProductname ASC LIMIT 100";
    }

    $result = mysqli_query($link, $sql);
    if ($result === false) {
        throw new Exception(mysqli_error($link));
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $name = trim((string) $row['sProductname']);
        if ($name === '') {
            continue;
        }
        $data[] = [
            'id'   => (int) $row['iProductid'],
            'text' => $name,
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
