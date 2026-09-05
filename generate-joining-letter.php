<?php
include 'layouts/session.php';
include 'layouts/head-main.php';
include 'layouts/config.php';
include 'layouts/crm-access.php';
crmRequireAdmin($link);

$id = null;
if (isset($_POST['id'])) {
    $id = (int)$_POST['id'];
} elseif (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
}
$isEdit = ($id > 0);
?>
<head>
    <title><?php echo $isEdit ? 'Edit' : 'Add'; ?> Joining Letter</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .btn-primary { color: #fff; background-color: #005aa5; border-color: #005aa5; }
        .letter-hint { font-size: 12px; color: #6c757d; }
        .add-message { color: green; font-weight: bold; }
        .error-message { color: red; font-weight: bold; }
        #sigPreview { max-height: 70px; margin-top: 8px; display: none; }
    </style>
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
                            <h4 class="mb-sm-0 font-size-18"><?php echo $isEdit ? 'Edit' : 'Add'; ?> Joining Letter</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="list-joining-letter.php">Joining Letters</a></li>
                                    <li class="breadcrumb-item active"><?php echo $isEdit ? 'Edit' : 'Add'; ?></li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-body">
                                <p class="letter-hint mb-3">Save the letter, then use Print from the list or Preview below.</p>
                                <div><span id="message"></span></div>
                                <form id="joiningForm" method="post" enctype="multipart/form-data" onsubmit="return false;">
                                    <input type="hidden" id="id" name="id" value="<?php echo $isEdit ? (int)$id : ''; ?>">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label" for="ref_no">Ref No.</label>
                                                <input type="text" class="form-control" id="ref_no" name="ref_no" value="02" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label" for="letter_date">Date</label>
                                                <input type="date" class="form-control" id="letter_date" name="letter_date" value="<?php echo date('Y-m-d'); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label" for="employee_title">Title</label>
                                                <select class="form-select" id="employee_title" name="employee_title">
                                                    <option value="Mr.">Mr.</option>
                                                    <option value="Ms." selected>Ms.</option>
                                                    <option value="Mrs.">Mrs.</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="employee_name">Employee Name</label>
                                                <input type="text" class="form-control" id="employee_name" name="employee_name" placeholder="e.g. Nandini" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="designation">Designation</label>
                                                <input type="text" class="form-control" id="designation" name="designation" value="SAP Basis Consultant" required>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label" for="employee_address">Employee Address (To block)</label>
                                                <textarea class="form-control" id="employee_address" name="employee_address" rows="2" placeholder="Kabnur, Tal- Hatkanangale, Dist- Kolhapur, 416115"></textarea>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label" for="joining_date">Joining / Report Date</label>
                                                <input type="date" class="form-control" id="joining_date" name="joining_date" value="<?php echo date('Y-m-d'); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label" for="joining_time">Report Time</label>
                                                <input type="text" class="form-control" id="joining_time" name="joining_time" value="10 AM" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label" for="office_location">Office Location</label>
                                                <input type="text" class="form-control" id="office_location" name="office_location" value="Ichalkaranji" required>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="reporting_manager">Reporting Manager</label>
                                                <input type="text" class="form-control" id="reporting_manager" name="reporting_manager" value="Mr. Sopan Kekade" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="signature">Authorized Signature (optional)</label>
                                                <input type="file" class="form-control" id="signature" name="signature" accept="image/png,image/jpeg,image/jpg,image/webp,image/gif">
                                                <div class="letter-hint mt-1">PNG/JPG preferred. Leave empty to keep existing / default.</div>
                                                <img id="sigPreview" alt="Current signature">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="company_phone">Footer Phone</label>
                                                <input type="text" class="form-control" id="company_phone" name="company_phone" value="9579801138">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label" for="company_address">Footer Address</label>
                                                <textarea class="form-control" id="company_address" name="company_address" rows="2">21/1945, beside National High School Road, Jawaharnagar, Ichalkaranji, Jawaharnagar, Maharashtra 416115</textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="button" class="btn btn-primary w-md" onclick="saveJoiningLetter()"><?php echo $isEdit ? 'Update' : 'Save'; ?></button>
                                    <button type="button" class="btn btn-success w-md ms-2" onclick="previewJoiningLetter()">Preview / Print</button>
                                    <a href="list-joining-letter.php" class="btn btn-secondary w-md ms-2">List</a>
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
<?php include 'layouts/right-sidebar.php'; ?>
<?php include 'layouts/vendor-scripts.php'; ?>
<script src="assets/js/app.js"></script>
<script>
var editId = <?php echo $isEdit ? (int)$id : 0; ?>;

function showMessage(msg, ok) {
    var el = document.getElementById('message');
    el.innerHTML = msg || '';
    el.className = ok ? 'add-message' : 'error-message';
}

function buildFormData(action) {
    var fd = new FormData(document.getElementById('joiningForm'));
    fd.set('action', action);
    if (editId > 0) fd.set('id', editId);
    return fd;
}

function saveJoiningLetter() {
    var name = document.getElementById('employee_name').value.trim();
    if (!name) { alert('Employee name is required.'); return; }
    var action = editId > 0 ? 'updatejoiningletter' : 'savejoiningletter';
    fetch('hr-letter-api.php', { method: 'POST', body: buildFormData(action) })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            showMessage(res.message || '', res.status === 'success');
            if (res.status === 'success') {
                if (res.data && res.data.id) {
                    editId = parseInt(res.data.id, 10) || editId;
                    document.getElementById('id').value = editId;
                }
                setTimeout(function() { window.location.href = 'list-joining-letter.php'; }, 800);
            }
        })
        .catch(function() { showMessage('Save failed.', false); });
}

function previewJoiningLetter() {
    var id = editId || parseInt(document.getElementById('id').value || '0', 10);
    if (id > 0) {
        window.open('print-joining-letter.php?id=' + id, '_blank');
        return;
    }
    fetch('hr-letter-api.php', { method: 'POST', body: buildFormData('savejoiningletter') })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.status === 'success' && res.data && res.data.id) {
                editId = parseInt(res.data.id, 10);
                document.getElementById('id').value = editId;
                window.open('print-joining-letter.php?id=' + editId, '_blank');
            } else {
                showMessage(res.message || 'Unable to preview.', false);
            }
        })
        .catch(function() { showMessage('Preview failed.', false); });
}

function fillJoiningForm(d) {
    document.getElementById('ref_no').value = d.sRefNo || '';
    document.getElementById('letter_date').value = d.sLetterDate || '';
    document.getElementById('employee_title').value = d.sEmployeeTitle || 'Ms.';
    document.getElementById('employee_name').value = d.sEmployeeName || '';
    document.getElementById('designation').value = d.sDesignation || '';
    document.getElementById('employee_address').value = d.sEmployeeAddress || '';
    document.getElementById('joining_date').value = d.sJoiningDate || '';
    document.getElementById('joining_time').value = d.sJoiningTime || '10 AM';
    document.getElementById('office_location').value = d.sOfficeLocation || '';
    document.getElementById('reporting_manager').value = d.sReportingManager || '';
    document.getElementById('company_phone').value = d.sCompanyPhone || '9579801138';
    document.getElementById('company_address').value = d.sCompanyAddress || '';
    if (d.sSignaturePath) {
        var img = document.getElementById('sigPreview');
        img.src = d.sSignaturePath;
        img.style.display = 'block';
    }
}

if (editId > 0) {
    fetch('hr-letter-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'gethrletterbyid', id: editId })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.status === 'success' && res.data) {
            fillJoiningForm(res.data);
        } else {
            showMessage(res.message || 'Failed to load letter.', false);
        }
    });
}
</script>
</body>
</html>
