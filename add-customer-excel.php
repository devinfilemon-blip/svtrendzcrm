<?php
include 'layouts/session.php';
include 'layouts/head-main.php';
include 'layouts/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth-login.php');
    exit;
}

require 'vendor/autoload.php';
require 'includes/excel-helper.php';

$importSuccess = '';
$importError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['excelFile'])) {
        try {
            $spreadsheet = crmLoadSpreadsheetFromUpload($_FILES['excelFile']);
            $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

            // Normalize rows to indexed arrays starting at 0
            $normalizedRows = [];
            foreach ($rows as $row) {
                $normalizedRows[] = array_values($row);
            }
            $rows = $normalizedRows;

            if (count($rows) < 2) {
                $importError = 'Excel file appears empty or missing data rows.';
            } else {
                $header = array_map(function ($col) {
                    return strtolower(trim(crmExcelCell($col)));
                }, $rows[0]);

                $colMap = [];
                foreach ($header as $index => $colNameLower) {
                    if (strpos($colNameLower, 'company') !== false && strpos($colNameLower, 'email') === false) {
                        $colMap['company'] = $index;
                    } elseif (strpos($colNameLower, 'company email') !== false) {
                        $colMap['email_company'] = $index;
                    } elseif (strpos($colNameLower, 'contact email') !== false) {
                        $colMap['email_contact'] = $index;
                    } elseif (strpos($colNameLower, 'email') !== false && !isset($colMap['email_company'])) {
                        $colMap['email_company'] = $index;
                    } elseif (strpos($colNameLower, 'customer type') !== false) {
                        $colMap['customertype'] = $index;
                    } elseif (strpos($colNameLower, 'industry') !== false) {
                        $colMap['industrytype'] = $index;
                    } elseif (strpos($colNameLower, 'user id') !== false || $colNameLower === 'manager') {
                        $colMap['userid'] = $index;
                    } elseif (strpos($colNameLower, 'source id') !== false) {
                        $colMap['sourceid'] = $index;
                    } elseif (strpos($colNameLower, 'tag') !== false) {
                        $colMap['tags'] = $index;
                    } elseif (strpos($colNameLower, 'status') !== false) {
                        $colMap['status'] = $index;
                    } elseif (strpos($colNameLower, 'note') !== false) {
                        $colMap['notes'] = $index;
                    } elseif (strpos($colNameLower, 'contact name') !== false) {
                        $colMap['contactname'] = $index;
                    } elseif (strpos($colNameLower, 'referred contact') !== false) {
                        $colMap['referredcontact'] = $index;
                    } elseif (strpos($colNameLower, 'phone') !== false || strpos($colNameLower, 'contact phone') !== false) {
                        if (!isset($colMap['phone'])) {
                            $colMap['phone'] = $index;
                        }
                    } elseif (strpos($colNameLower, 'department') !== false) {
                        $colMap['department'] = $index;
                    }
                }

                if (!isset($colMap['company'])) {
                    $importError = 'Required column "Company Name" not found in Excel header.';
                } elseif (!isset($colMap['contactname'])) {
                    $importError = 'Required column "Contact Name" not found in Excel header.';
                } else {
                    $imported = 0;
                    $errors = [];

                    for ($i = 1; $i < count($rows); $i++) {
                        $data = $rows[$i];
                        $getCol = function ($key) use ($data, $colMap) {
                            if (!isset($colMap[$key])) {
                                return '';
                            }
                            return crmExcelCell($data[$colMap[$key]] ?? '');
                        };

                        $companyname = mysqli_real_escape_string($link, $getCol('company'));
                        if ($companyname === '') {
                            continue;
                        }

                        $email_company   = mysqli_real_escape_string($link, $getCol('email_company'));
                        $customertype    = mysqli_real_escape_string($link, $getCol('customertype'));
                        $industrytype    = mysqli_real_escape_string($link, $getCol('industrytype'));
                        $useridVal       = $getCol('userid');
                        $userid          = is_numeric($useridVal) ? (int)$useridVal : 0;
                        $sourceid        = is_numeric($getCol('sourceid')) ? (int)$getCol('sourceid') : 0;
                        $tags            = mysqli_real_escape_string($link, $getCol('tags'));
                        $status          = mysqli_real_escape_string($link, $getCol('status'));
                        $notes           = mysqli_real_escape_string($link, $getCol('notes'));
                        $contactname     = mysqli_real_escape_string($link, $getCol('contactname'));
                        $referredcontact = is_numeric($getCol('referredcontact')) ? (int)$getCol('referredcontact') : 0;
                        $email_contact   = mysqli_real_escape_string($link, $getCol('email_contact'));
                        $phone           = mysqli_real_escape_string($link, $getCol('phone'));
                        $department      = mysqli_real_escape_string($link, $getCol('department'));

                        if ($contactname === '') {
                            $errors[] = 'Row ' . ($i + 1) . ': Contact Name is empty.';
                            continue;
                        }

                        $sql1 = "INSERT INTO tblcustomer (
                            sCompanyname, sEmail, sBillingaddress, sShippingaddress,
                            sCustomertype, sIndustrytype, iUserid, iSourceid,
                            sTags, sStatus, sNotes, sCreateddate, sReferred
                        ) VALUES (
                            '$companyname', '$email_company', '', '',
                            '$customertype', '$industrytype', $userid, $sourceid,
                            '$tags', '$status', '$notes', NOW(), $referredcontact
                        )";

                        if (mysqli_query($link, $sql1)) {
                            $customerId = mysqli_insert_id($link);
                            $sql2 = "INSERT INTO tblcontact (
                                iCustomerid, sContactname, iContactid, sEmail, sPhone, sDepartment, sCreatedTimestamp
                            ) VALUES (
                                $customerId, '$contactname', $referredcontact, '$email_contact', '$phone', '$department', NOW()
                            )";

                            if (!mysqli_query($link, $sql2)) {
                                $errors[] = 'Row ' . ($i + 1) . ': Contact insert failed.';
                            } else {
                                $imported++;
                            }
                        } else {
                            $errors[] = 'Row ' . ($i + 1) . ': Customer insert failed.';
                        }
                    }

                    $importSuccess = "Import complete! $imported customer(s) added.";
                    if (!empty($errors)) {
                        $importSuccess .= ' Errors: ' . implode('; ', array_slice($errors, 0, 5));
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
    <title>Import Customers — <?php echo APP_NAME; ?></title>
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
                            <h4 class="mb-sm-0 font-size-18">Import Customers from Excel</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="list-customer.php">Customers</a></li>
                                    <li class="breadcrumb-item active">Import</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($importSuccess)): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($importSuccess); ?></div>
                <?php endif; ?>
                <?php if (!empty($importError)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($importError); ?></div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-3"><strong>Upload Excel File</strong></h5>
                                <p class="text-muted">Upload <strong>.xlsx</strong> Excel file only. Required columns: <strong>Company Name</strong> and <strong>Contact Name</strong>.</p>
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <input type="file" name="excelFile" class="form-control" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="submit" class="btn btn-primary"><i class="bx bx-upload"></i> Import Customers</button>
                                        <a href="list-customer.php" class="btn btn-secondary">Back to List</a>
                                        <a href="download-customer-template.php" class="btn btn-outline-primary"><i class="bx bx-download"></i> Download Template</a>
                                    </div>
                                </form>
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
