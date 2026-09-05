<?php
include 'layouts/session.php';
include 'layouts/head-main.php';
include 'layouts/config.php';
include 'layouts/crm-access.php';
crmRequireAdmin($link);

require 'vendor/autoload.php';
require 'includes/excel-helper.php';

$importSuccess = '';
$importError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['excelFile'])) {
        try {
            $spreadsheet = crmLoadSpreadsheetFromUpload($_FILES['excelFile']);
            $rawRows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
            $rows = [];
            foreach ($rawRows as $row) {
                $rows[] = array_values($row);
            }

            if (count($rows) < 2) {
                $importError = 'Excel file is empty or has no data rows.';
            } else {
                $header = array_map(function ($col) {
                    return strtolower(trim(crmExcelCell($col)));
                }, $rows[0]);
                $colMap = [];

                foreach ($header as $index => $key) {
                    if (strpos($key, 'product code') !== false || strpos($key, 'part no') !== false || $key === 'code') $colMap['code'] = $index;
                    elseif (strpos($key, 'product name') !== false || strpos($key, 'description') !== false || $key === 'name') $colMap['name'] = $index;
                    elseif (strpos($key, 'category') !== false || strpos($key, 'brand') !== false) $colMap['category'] = $index;
                    elseif (strpos($key, 'hsn') !== false) $colMap['hsn'] = $index;
                    elseif ($key === 'unit' || strpos($key, 'unit') === 0) $colMap['unit'] = $index;
                    elseif (strpos($key, 'purchase rate') !== false) $colMap['purchase_rate'] = $index;
                    elseif (strpos($key, 'sale rate') !== false) $colMap['sale_rate'] = $index;
                    elseif (strpos($key, 'opening stock') !== false) $colMap['opening_stock'] = $index;
                    elseif (strpos($key, 'reorder') !== false) $colMap['reorder_level'] = $index;
                    elseif (strpos($key, 'status') !== false) $colMap['status'] = $index;
                }

                if (!isset($colMap['name'])) {
                    $importError = 'Required column "Product Name" not found in Excel header.';
                } else {
                    $imported = 0;
                    $updated = 0;
                    $errors = [];
                    $createdBy = (int)$_SESSION['user_id'];

                    $getCol = function ($key, $rowIndex) use ($colMap, $rows) {
                        if (!isset($colMap[$key])) return '';
                        return crmExcelCell($rows[$rowIndex][$colMap[$key]] ?? '');
                    };

                    $findStmt = mysqli_prepare($link, 'SELECT iProductid FROM tblinv_product WHERE (sProductCode = ? AND sProductCode <> \'\') OR (sProductName = ? AND (sCategory <=> ?)) LIMIT 1');
                    $insStmt = mysqli_prepare($link, "INSERT INTO tblinv_product
                        (sProductCode, sProductName, sCategory, sHsnCode, sUnit, fPurchaseRate, fSaleRate, iOpeningStock, iReorderLevel, sStatus, iCreatedBy)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    // Opening stock is intentionally excluded from updates: current stock is always
                    // derived from opening + inward - outward, so overwriting it here on a re-import
                    // would silently shift the stock baseline for products that already have movement history.
                    $updStmt = mysqli_prepare($link, "UPDATE tblinv_product SET
                        sProductCode = ?, sProductName = ?, sCategory = ?, sHsnCode = ?, sUnit = ?,
                        fPurchaseRate = ?, fSaleRate = ?, iReorderLevel = ?, sStatus = ?, sModifiedTimestamp = NOW()
                        WHERE iProductid = ?");

                    for ($i = 1; $i < count($rows); $i++) {
                        $name = $getCol('name', $i);
                        if ($name === '') continue;

                        $code = $getCol('code', $i);
                        $category = $getCol('category', $i) ?: null;
                        $hsn = $getCol('hsn', $i) ?: null;
                        $unit = $getCol('unit', $i) ?: 'Nos';
                        $purchaseRate = (float)($getCol('purchase_rate', $i) ?: 0);
                        $saleRate = (float)($getCol('sale_rate', $i) ?: 0);
                        $opening = (int)($getCol('opening_stock', $i) ?: 0);
                        $reorder = (int)($getCol('reorder_level', $i) ?: 0);
                        $status = $getCol('status', $i) ?: 'Active';
                        if (!in_array($status, ['Active', 'Inactive'], true)) {
                            $status = 'Active';
                        }
                        $codeForMatch = $code !== '' ? $code : null;

                        mysqli_stmt_bind_param($findStmt, 'sss', $code, $name, $category);
                        mysqli_stmt_execute($findStmt);
                        $existing = mysqli_stmt_get_result($findStmt)->fetch_assoc();

                        if ($existing) {
                            $id = (int)$existing['iProductid'];
                            mysqli_stmt_bind_param(
                                $updStmt, 'sssssddisi',
                                $codeForMatch, $name, $category, $hsn, $unit,
                                $purchaseRate, $saleRate, $reorder, $status, $id
                            );
                            if (mysqli_stmt_execute($updStmt)) {
                                $updated++;
                            } else {
                                $errors[] = 'Row ' . ($i + 1) . ': ' . mysqli_stmt_error($updStmt);
                            }
                        } else {
                            mysqli_stmt_bind_param(
                                $insStmt, 'sssssddiisi',
                                $codeForMatch, $name, $category, $hsn, $unit,
                                $purchaseRate, $saleRate, $opening, $reorder, $status, $createdBy
                            );
                            if (mysqli_stmt_execute($insStmt)) {
                                $imported++;
                            } else {
                                $errors[] = 'Row ' . ($i + 1) . ': ' . mysqli_stmt_error($insStmt);
                            }
                        }
                    }

                    $importSuccess = "Import complete! $imported product(s) added, $updated updated.";
                    if (!empty($errors)) {
                        $importSuccess .= ' Some rows failed: ' . implode('; ', array_slice($errors, 0, 5));
                    }
                }
            }
        } catch (Throwable $e) {
            $importError = $e->getMessage();
        }
    } else {
        $importError = 'No file uploaded.';
    }
}
?>
<head>
    <title>Import Products</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<?php include 'layouts/body.php'; ?>
<div id="layout-wrapper">
    <?php include 'layouts/menu.php'; ?>
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Import Products from Excel</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="inv-list-product.php">Inventory</a></li>
                                    <li class="breadcrumb-item active">Import</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($importSuccess)) : ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($importSuccess); ?></div>
                <?php endif; ?>
                <?php if (!empty($importError)) : ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($importError); ?></div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-3"><strong>Upload Excel File</strong></h5>
                                <p class="text-muted">Upload a <strong>.xlsx</strong> file with a header row. Required column: <strong>Product Name</strong>. A product matching an existing <strong>Product Code</strong>, or the same <strong>Product Name + Category</strong>, will be updated instead of duplicated.</p>
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <input type="file" name="excelFile" class="form-control" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="submit" class="btn btn-primary"><i class="bx bx-upload"></i> Import Products</button>
                                        <a href="inv-list-product.php" class="btn btn-secondary">Back to List</a>
                                        <a href="inv-download-product-template.php" class="btn btn-outline-primary"><i class="bx bx-download"></i> Download Template</a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <h6 class="mb-2"><strong>Supported columns</strong></h6>
                                <p class="text-muted mb-0">Product Code, Product Name, Category, HSN Code, Unit, Purchase Rate, Sale Rate, Opening Stock, Reorder Level, Status</p>
                                <p class="text-muted mt-2 mb-0"><small>Status accepts <strong>Active</strong> or <strong>Inactive</strong> (defaults to Active). Opening Stock is only used when a product is first created — it won't overwrite live stock on an update, since current stock is always calculated from Inward/Outward history.</small></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include 'layouts/footer.php'; ?>
    </div>
</div>
<?php include 'layouts/vendor-scripts.php'; ?>
<script src="assets/js/app.js"></script>
</body>
</html>
