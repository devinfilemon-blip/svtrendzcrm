<?php
/**
 * Inventory module API — Products, Inward (stock-in), Outward (stock-out).
 */
session_start();
header('Content-Type: application/json');
date_default_timezone_set('Asia/Kolkata');

require_once __DIR__ . '/layouts/config.php';
require_once __DIR__ . '/layouts/crm-access.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$inputData = [];

if ($method === 'POST') {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
    if (stripos($contentType, 'application/json') !== false) {
        $raw = file_get_contents('php://input');
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $inputData = $decoded;
        }
    } else {
        $inputData = $_POST;
    }
}

$action = $inputData['action'] ?? '';

function invJson($status, $message, $data = null) {
    $out = ['status' => $status, 'message' => $message];
    if ($data !== null) {
        $out['data'] = $data;
    }
    echo json_encode($out);
    exit;
}

function invRequireAdmin() {
    if (empty($_SESSION['user_id'])) {
        invJson('error', 'Please login first.');
    }
    if (!crmIsAdmin()) {
        invJson('error', 'Access denied. Admin only.');
    }
}

function invEmptyToNull($v) {
    $v = trim((string)$v);
    return $v === '' ? null : $v;
}

function invNum($v) {
    if ($v === '' || $v === null) {
        return 0.0;
    }
    return (float)$v;
}

invRequireAdmin();

// Older installs won't have this column yet; add it on the fly so the
// outward form can optionally link an entry to a Won project.
function ensureOutwardLeadColumn($link) {
    $result = mysqli_query($link, "SHOW COLUMNS FROM tblinv_outward LIKE 'iLeadid'");
    if ($result && mysqli_num_rows($result) > 0) {
        return;
    }
    mysqli_query($link, "ALTER TABLE tblinv_outward ADD COLUMN iLeadid INT NULL AFTER iProductid, ADD INDEX idx_lead (iLeadid)");
}
ensureOutwardLeadColumn($link);

// Current stock = opening stock + total inward - total outward, computed on the fly.
define('INV_STOCK_EXPR', "(p.iOpeningStock + COALESCE(inw.qty,0) - COALESCE(outw.qty,0))");
define('INV_STOCK_JOIN', "
    LEFT JOIN (SELECT iProductid, SUM(iQty) qty FROM tblinv_inward GROUP BY iProductid) inw ON inw.iProductid = p.iProductid
    LEFT JOIN (SELECT iProductid, SUM(iQty) qty FROM tblinv_outward GROUP BY iProductid) outw ON outw.iProductid = p.iProductid
");

// ==================== PRODUCTS ====================

if ($method === 'POST' && $action === 'listproducts') {
    $sql = "SELECT p.*, " . INV_STOCK_EXPR . " AS iCurrentStock
            FROM tblinv_product p" . INV_STOCK_JOIN . "
            ORDER BY p.iProductid DESC";
    $rows = $link->query($sql)->fetch_all(MYSQLI_ASSOC);
    invJson('success', 'Products fetched', $rows);
}

if ($method === 'POST' && $action === 'listactiveproducts') {
    $sql = "SELECT p.iProductid, p.sProductCode, p.sProductName, p.sUnit, p.fPurchaseRate, p.fSaleRate,
            " . INV_STOCK_EXPR . " AS iCurrentStock
            FROM tblinv_product p" . INV_STOCK_JOIN . "
            WHERE p.sStatus = 'Active'
            ORDER BY p.sProductName ASC";
    $rows = $link->query($sql)->fetch_all(MYSQLI_ASSOC);
    invJson('success', 'Products fetched', $rows);
}

if ($method === 'POST' && $action === 'listcategories') {
    $rows = $link->query("SELECT DISTINCT sCategory FROM tblinv_product WHERE sCategory IS NOT NULL AND sCategory <> '' ORDER BY sCategory ASC")->fetch_all(MYSQLI_ASSOC);
    invJson('success', 'Categories fetched', array_column($rows, 'sCategory'));
}

if ($method === 'POST' && $action === 'getproductbyid') {
    $id = (int)($inputData['id'] ?? 0);
    if ($id <= 0) {
        invJson('error', 'Invalid product ID.');
    }
    $sql = "SELECT p.*, " . INV_STOCK_EXPR . " AS iCurrentStock
            FROM tblinv_product p" . INV_STOCK_JOIN . "
            WHERE p.iProductid = ? LIMIT 1";
    $stmt = $link->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) {
        invJson('error', 'Product not found.');
    }
    invJson('success', 'Product fetched', $row);
}

if ($method === 'POST' && in_array($action, ['saveproduct', 'updateproduct'], true)) {
    $name = trim($inputData['product_name'] ?? '');
    if ($name === '') {
        invJson('error', 'Product name is required.');
    }
    $code = invEmptyToNull($inputData['product_code'] ?? '');
    $category = invEmptyToNull($inputData['category'] ?? '');
    $hsn = invEmptyToNull($inputData['hsn_code'] ?? '');
    $unit = trim($inputData['unit'] ?? '') !== '' ? trim($inputData['unit']) : 'Nos';
    $purchaseRate = invNum($inputData['purchase_rate'] ?? 0);
    $saleRate = invNum($inputData['sale_rate'] ?? 0);
    $opening = (int)($inputData['opening_stock'] ?? 0);
    $reorder = (int)($inputData['reorder_level'] ?? 0);
    $userId = (int)($_SESSION['user_id'] ?? 0);

    if ($action === 'updateproduct') {
        $id = (int)($inputData['id'] ?? 0);
        if ($id <= 0) {
            invJson('error', 'Invalid product ID.');
        }
        $sql = "UPDATE tblinv_product SET
            sProductCode = ?, sProductName = ?, sCategory = ?, sHsnCode = ?, sUnit = ?,
            fPurchaseRate = ?, fSaleRate = ?, iOpeningStock = ?, iReorderLevel = ?, sModifiedTimestamp = NOW()
            WHERE iProductid = ?";
        $stmt = $link->prepare($sql);
        $stmt->bind_param('sssssddiii', $code, $name, $category, $hsn, $unit, $purchaseRate, $saleRate, $opening, $reorder, $id);
        if (!$stmt->execute()) {
            $err = $stmt->error;
            $stmt->close();
            invJson('error', 'Update failed: ' . $err);
        }
        $stmt->close();
        invJson('success', 'Product updated successfully.', ['id' => $id]);
    }

    $sql = "INSERT INTO tblinv_product
        (sProductCode, sProductName, sCategory, sHsnCode, sUnit, fPurchaseRate, fSaleRate, iOpeningStock, iReorderLevel, iCreatedBy)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $link->prepare($sql);
    $stmt->bind_param('sssssddiii', $code, $name, $category, $hsn, $unit, $purchaseRate, $saleRate, $opening, $reorder, $userId);
    if (!$stmt->execute()) {
        $err = $stmt->error;
        $stmt->close();
        invJson('error', 'Save failed: ' . $err);
    }
    $newId = (int)$stmt->insert_id;
    $stmt->close();
    invJson('success', 'Product saved successfully.', ['id' => $newId]);
}

if ($method === 'POST' && $action === 'deleteproduct') {
    $id = (int)($inputData['id'] ?? 0);
    if ($id <= 0) {
        invJson('error', 'Invalid product ID.');
    }
    $check = $link->prepare("SELECT
        (SELECT COUNT(*) FROM tblinv_inward WHERE iProductid = ?) +
        (SELECT COUNT(*) FROM tblinv_outward WHERE iProductid = ?) AS c");
    $check->bind_param('ii', $id, $id);
    $check->execute();
    $row = $check->get_result()->fetch_assoc();
    $check->close();
    if ((int)$row['c'] > 0) {
        invJson('error', 'Cannot delete: this product has inward/outward entries. Set it to Inactive instead.');
    }
    $stmt = $link->prepare('DELETE FROM tblinv_product WHERE iProductid = ?');
    $stmt->bind_param('i', $id);
    if (!$stmt->execute()) {
        $err = $stmt->error;
        $stmt->close();
        invJson('error', 'Delete failed: ' . $err);
    }
    $stmt->close();
    invJson('success', 'Product deleted successfully.');
}

// ==================== INWARD ====================

if ($method === 'POST' && $action === 'listinward') {
    $sql = "SELECT i.*, p.sProductName, p.sProductCode, p.sUnit
            FROM tblinv_inward i
            JOIN tblinv_product p ON p.iProductid = i.iProductid
            ORDER BY i.dDate DESC, i.iInwardid DESC";
    $rows = $link->query($sql)->fetch_all(MYSQLI_ASSOC);
    invJson('success', 'Inward entries fetched', $rows);
}

if ($method === 'POST' && $action === 'getinwardbyid') {
    $id = (int)($inputData['id'] ?? 0);
    if ($id <= 0) {
        invJson('error', 'Invalid inward ID.');
    }
    $stmt = $link->prepare('SELECT * FROM tblinv_inward WHERE iInwardid = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) {
        invJson('error', 'Inward entry not found.');
    }
    invJson('success', 'Inward entry fetched', $row);
}

if ($method === 'POST' && in_array($action, ['saveinward', 'updateinward'], true)) {
    $productId = (int)($inputData['product_id'] ?? 0);
    $date = invEmptyToNull($inputData['date'] ?? '');
    $qty = (int)($inputData['qty'] ?? 0);
    $rate = invNum($inputData['rate'] ?? 0);
    $supplier = invEmptyToNull($inputData['supplier'] ?? '');
    $invoiceNo = invEmptyToNull($inputData['invoice_no'] ?? '');
    $remarks = invEmptyToNull($inputData['remarks'] ?? '');
    $amount = round($qty * $rate, 2);
    $userId = (int)($_SESSION['user_id'] ?? 0);

    if ($productId <= 0) {
        invJson('error', 'Please select a product.');
    }
    if (!$date) {
        invJson('error', 'Date is required.');
    }
    if ($qty <= 0) {
        invJson('error', 'Quantity must be greater than zero.');
    }

    if ($action === 'updateinward') {
        $id = (int)($inputData['id'] ?? 0);
        if ($id <= 0) {
            invJson('error', 'Invalid inward ID.');
        }
        $sql = "UPDATE tblinv_inward SET
            iProductid = ?, dDate = ?, iQty = ?, fRate = ?, fAmount = ?, sSupplier = ?, sInvoiceNo = ?, sRemarks = ?, sModifiedTimestamp = NOW()
            WHERE iInwardid = ?";
        $stmt = $link->prepare($sql);
        $stmt->bind_param('isiddsssi', $productId, $date, $qty, $rate, $amount, $supplier, $invoiceNo, $remarks, $id);
        if (!$stmt->execute()) {
            $err = $stmt->error;
            $stmt->close();
            invJson('error', 'Update failed: ' . $err);
        }
        $stmt->close();
        invJson('success', 'Inward entry updated successfully.', ['id' => $id]);
    }

    $sql = "INSERT INTO tblinv_inward (iProductid, dDate, iQty, fRate, fAmount, sSupplier, sInvoiceNo, sRemarks, iCreatedBy)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $link->prepare($sql);
    $stmt->bind_param('isiddsssi', $productId, $date, $qty, $rate, $amount, $supplier, $invoiceNo, $remarks, $userId);
    if (!$stmt->execute()) {
        $err = $stmt->error;
        $stmt->close();
        invJson('error', 'Save failed: ' . $err);
    }
    $newId = (int)$stmt->insert_id;
    $stmt->close();
    invJson('success', 'Inward entry saved successfully.', ['id' => $newId]);
}

if ($method === 'POST' && $action === 'deleteinward') {
    $id = (int)($inputData['id'] ?? 0);
    if ($id <= 0) {
        invJson('error', 'Invalid inward ID.');
    }
    $stmt = $link->prepare('DELETE FROM tblinv_inward WHERE iInwardid = ?');
    $stmt->bind_param('i', $id);
    if (!$stmt->execute()) {
        $err = $stmt->error;
        $stmt->close();
        invJson('error', 'Delete failed: ' . $err);
    }
    $stmt->close();
    invJson('success', 'Inward entry deleted successfully.');
}

// ==================== OUTWARD ====================

if ($method === 'POST' && $action === 'listwonprojects') {
    $sql = "
        SELECT l.iLead_id, l.sCompany_name, l.sLead_name
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
        WHERE COALESCE(NULLIF(r.sStatus, ''), l.sLead_status) = '6'
        ORDER BY l.sCompany_name ASC, l.sLead_name ASC
    ";
    $rows = $link->query($sql)->fetch_all(MYSQLI_ASSOC);
    $projects = [];
    foreach ($rows as $row) {
        $projects[] = [
            'iLead_id' => (int)$row['iLead_id'],
            'sCompany_name' => $row['sCompany_name'] !== '' && $row['sCompany_name'] !== null ? $row['sCompany_name'] : $row['sLead_name'],
        ];
    }
    invJson('success', 'Won projects fetched', $projects);
}

if ($method === 'POST' && $action === 'listoutward') {
    $sql = "SELECT o.*, p.sProductName, p.sProductCode, p.sUnit, l.sCompany_name, l.sLead_name
            FROM tblinv_outward o
            JOIN tblinv_product p ON p.iProductid = o.iProductid
            LEFT JOIN tblleads l ON l.iLead_id = o.iLeadid
            ORDER BY o.dDate DESC, o.iOutwardid DESC";
    $rows = $link->query($sql)->fetch_all(MYSQLI_ASSOC);
    foreach ($rows as &$row) {
        $row['sProjectCompany'] = $row['sCompany_name'] ?: $row['sLead_name'];
    }
    unset($row);
    invJson('success', 'Outward entries fetched', $rows);
}

if ($method === 'POST' && $action === 'getoutwardbyid') {
    $id = (int)($inputData['id'] ?? 0);
    if ($id <= 0) {
        invJson('error', 'Invalid outward ID.');
    }
    $stmt = $link->prepare('SELECT * FROM tblinv_outward WHERE iOutwardid = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) {
        invJson('error', 'Outward entry not found.');
    }
    invJson('success', 'Outward entry fetched', $row);
}

if ($method === 'POST' && in_array($action, ['saveoutward', 'updateoutward'], true)) {
    $productId = (int)($inputData['product_id'] ?? 0);
    $date = invEmptyToNull($inputData['date'] ?? '');
    $qty = (int)($inputData['qty'] ?? 0);
    $leadId = (int)($inputData['lead_id'] ?? 0);
    $issuedTo = invEmptyToNull($inputData['issued_to'] ?? '');
    $purpose = trim($inputData['purpose'] ?? '') !== '' ? trim($inputData['purpose']) : 'Sale';
    $remarks = invEmptyToNull($inputData['remarks'] ?? '');
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $leadIdParam = $leadId > 0 ? $leadId : null;

    if ($productId <= 0) {
        invJson('error', 'Please select a product.');
    }
    if (!$date) {
        invJson('error', 'Date is required.');
    }
    if ($qty <= 0) {
        invJson('error', 'Quantity must be greater than zero.');
    }

    if ($action === 'updateoutward') {
        $id = (int)($inputData['id'] ?? 0);
        if ($id <= 0) {
            invJson('error', 'Invalid outward ID.');
        }
        $sql = "UPDATE tblinv_outward SET
            iProductid = ?, dDate = ?, iQty = ?, iLeadid = ?, sIssuedTo = ?, sPurpose = ?, sRemarks = ?, sModifiedTimestamp = NOW()
            WHERE iOutwardid = ?";
        $stmt = $link->prepare($sql);
        $stmt->bind_param('isiisssi', $productId, $date, $qty, $leadIdParam, $issuedTo, $purpose, $remarks, $id);
        if (!$stmt->execute()) {
            $err = $stmt->error;
            $stmt->close();
            invJson('error', 'Update failed: ' . $err);
        }
        $stmt->close();
        invJson('success', 'Outward entry updated successfully.', ['id' => $id]);
    }

    $sql = "INSERT INTO tblinv_outward (iProductid, dDate, iQty, iLeadid, sIssuedTo, sPurpose, sRemarks, iCreatedBy)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $link->prepare($sql);
    $stmt->bind_param('isiisssi', $productId, $date, $qty, $leadIdParam, $issuedTo, $purpose, $remarks, $userId);
    if (!$stmt->execute()) {
        $err = $stmt->error;
        $stmt->close();
        invJson('error', 'Save failed: ' . $err);
    }
    $newId = (int)$stmt->insert_id;
    $stmt->close();
    invJson('success', 'Outward entry saved successfully.', ['id' => $newId]);
}

if ($method === 'POST' && $action === 'deleteoutward') {
    $id = (int)($inputData['id'] ?? 0);
    if ($id <= 0) {
        invJson('error', 'Invalid outward ID.');
    }
    $stmt = $link->prepare('DELETE FROM tblinv_outward WHERE iOutwardid = ?');
    $stmt->bind_param('i', $id);
    if (!$stmt->execute()) {
        $err = $stmt->error;
        $stmt->close();
        invJson('error', 'Delete failed: ' . $err);
    }
    $stmt->close();
    invJson('success', 'Outward entry deleted successfully.');
}

invJson('error', 'Invalid action.');
