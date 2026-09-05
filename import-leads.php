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
                    if (strpos($key, 'company') !== false) $colMap['company'] = $index;
                    elseif (strpos($key, 'contact person') !== false || $key === 'contact') $colMap['contact'] = $index;
                    elseif (strpos($key, 'lead name') !== false) $colMap['leadname'] = $index;
                    elseif ($key === 'email' || strpos($key, 'email') !== false) $colMap['email'] = $index;
                    elseif (strpos($key, 'alternate') !== false) $colMap['altphone'] = $index;
                    elseif ($key === 'phone' || strpos($key, 'phone') !== false) {
                        if (!isset($colMap['phone'])) $colMap['phone'] = $index;
                    }
                    elseif (strpos($key, 'lead type') !== false) $colMap['leadtype'] = $index;
                    elseif (strpos($key, 'industry') !== false) $colMap['industry'] = $index;
                    elseif (strpos($key, 'location') !== false) $colMap['location'] = $index;
                    elseif (strpos($key, 'address') !== false) $colMap['address'] = $index;
                    elseif (strpos($key, 'lead source') !== false) $colMap['source'] = $index;
                    elseif (strpos($key, 'lead status') !== false || $key === 'status') $colMap['status'] = $index;
                    elseif (strpos($key, 'priority') !== false) $colMap['priority'] = $index;
                    elseif (strpos($key, 'assigned') !== false) $colMap['assigned'] = $index;
                    elseif (strpos($key, 'owner') !== false) $colMap['owner'] = $index;
                    elseif (strpos($key, 'tag') !== false) $colMap['tags'] = $index;
                }

                if (!isset($colMap['company'])) {
                    $importError = 'Required column "Company Name" not found in Excel header.';
                } else {
                    $imported = 0;
                    $errors = [];
                    $createdBy = (int)$_SESSION['user_id'];

                    $getCol = function ($key, $rowIndex) use ($colMap, $rows) {
                        if (!isset($colMap[$key])) return '';
                        return crmExcelCell($rows[$rowIndex][$colMap[$key]] ?? '');
                    };

                    $resolveUserId = function ($value) use ($link) {
                        if ($value === '') return '';
                        if (is_numeric($value)) return (string)(int)$value;
                        $stmt = mysqli_prepare($link, 'SELECT iUserid FROM tbluser WHERE sName = ? LIMIT 1');
                        mysqli_stmt_bind_param($stmt, 's', $value);
                        mysqli_stmt_execute($stmt);
                        $res = mysqli_stmt_get_result($stmt);
                        $row = mysqli_fetch_assoc($res);
                        mysqli_stmt_close($stmt);
                        return $row ? (string)$row['iUserid'] : '';
                    };

                    $resolveStatusId = function ($value) use ($link) {
                        if ($value === '') return '';
                        if (is_numeric($value)) return (string)(int)$value;
                        $stmt = mysqli_prepare($link, 'SELECT iStatusid FROM tblstatus WHERE sStatus = ? LIMIT 1');
                        mysqli_stmt_bind_param($stmt, 's', $value);
                        mysqli_stmt_execute($stmt);
                        $res = mysqli_stmt_get_result($stmt);
                        $row = mysqli_fetch_assoc($res);
                        mysqli_stmt_close($stmt);
                        return $row ? (string)$row['iStatusid'] : '';
                    };

                    $resolveSourceId = function ($value) use ($link) {
                        if ($value === '') return '';
                        if (is_numeric($value)) return (string)(int)$value;
                        $stmt = mysqli_prepare($link, 'SELECT iSourceid FROM tblsources WHERE sSources = ? LIMIT 1');
                        mysqli_stmt_bind_param($stmt, 's', $value);
                        mysqli_stmt_execute($stmt);
                        $res = mysqli_stmt_get_result($stmt);
                        $row = mysqli_fetch_assoc($res);
                        mysqli_stmt_close($stmt);
                        return $row ? (string)$row['iSourceid'] : '';
                    };

                    $resolvePriorityId = function ($value) use ($link) {
                        if ($value === '') return '';
                        if (is_numeric($value)) return (string)(int)$value;
                        $stmt = mysqli_prepare($link, 'SELECT id FROM tblpriority WHERE sPrioritylevel = ? LIMIT 1');
                        mysqli_stmt_bind_param($stmt, 's', $value);
                        mysqli_stmt_execute($stmt);
                        $res = mysqli_stmt_get_result($stmt);
                        $row = mysqli_fetch_assoc($res);
                        mysqli_stmt_close($stmt);
                        return $row ? (string)$row['id'] : '';
                    };

                    for ($i = 1; $i < count($rows); $i++) {
                        $company    = $getCol('company', $i);
                        if ($company === '') continue;

                        $contact    = $getCol('contact', $i);
                        $leadName   = $getCol('leadname', $i) ?: $contact;
                        $email      = $getCol('email', $i);
                        $phone      = $getCol('phone', $i);
                        $altPhone   = $getCol('altphone', $i);
                        $leadType   = $getCol('leadtype', $i);
                        $industry   = $getCol('industry', $i);
                        $location   = $getCol('location', $i);
                        $address    = $getCol('address', $i);
                        $tags       = $getCol('tags', $i);
                        $source     = $resolveSourceId($getCol('source', $i));
                        $status     = $resolveStatusId($getCol('status', $i));
                        $priority   = $resolvePriorityId($getCol('priority', $i));
                        $assigned   = $resolveUserId($getCol('assigned', $i));
                        $owner      = $resolveUserId($getCol('owner', $i)) ?: (string)$createdBy;

                        $designation = '';
                        $website = '';
                        $preferred = '';
                        $file1 = '';
                        $file2 = '';
                        $file3 = '';

                        $query = "INSERT INTO tblleads (
                            sLead_name, sEmail, sPhone, sAlternate_phone, sLead_source, sLead_status,
                            sLead_priority, sLead_type, sCompany_name, sIndustry_type, sDesignation,
                            sWebsite, sLocation, sAddress, sAssigned_to, sLead_owner, sPreferred_communication,
                            sTags, sContactperson, sFileupload, sFileupload2, sFileupload3, sCreated_by
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                        $stmt = mysqli_prepare($link, $query);
                        if (!$stmt) {
                            $errors[] = 'Row ' . ($i + 1) . ': ' . mysqli_error($link);
                            continue;
                        }

                        mysqli_stmt_bind_param(
                            $stmt,
                            'ssssssssssssssssssssssi',
                            $leadName, $email, $phone, $altPhone, $source, $status, $priority, $leadType,
                            $company, $industry, $designation, $website, $location, $address, $assigned, $owner,
                            $preferred, $tags, $contact, $file1, $file2, $file3, $createdBy
                        );

                        if (mysqli_stmt_execute($stmt)) {
                            $imported++;
                        } else {
                            $errors[] = 'Row ' . ($i + 1) . ': ' . mysqli_stmt_error($stmt);
                        }
                        mysqli_stmt_close($stmt);
                    }

                    $importSuccess = "Import complete! $imported lead(s) added.";
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
    <title>Import Leads — <?php echo APP_NAME; ?></title>
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
                            <h4 class="mb-sm-0 font-size-18">Import Leads from Excel</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="list-lead-master.php">Leads</a></li>
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
                                <p class="text-muted">Upload <strong>.xlsx</strong> file with a header row. Required column: <strong>Company Name</strong>.</p>
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <input type="file" name="excelFile" class="form-control" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="submit" class="btn btn-primary"><i class="bx bx-upload"></i> Import Leads</button>
                                        <a href="list-lead-master.php" class="btn btn-secondary">Back to List</a>
                                        <a href="download-lead-template.php" class="btn btn-outline-primary"><i class="bx bx-download"></i> Download Template</a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <h6 class="mb-2"><strong>Supported columns</strong></h6>
                                <p class="text-muted mb-0">Company Name, Contact Person, Lead Name, Email, Phone, Alternate Phone, Lead Type, Industry, Location, Address, Lead Source, Lead Status, Priority, Assigned To, Lead Owner, Tags</p>
                                <p class="text-muted mt-2 mb-0"><small>Status, Source, Priority, and User fields accept either <strong>ID</strong> or <strong>name</strong> from your master data.</small></p>
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
