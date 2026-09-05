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
    <title><?php echo $isEdit ? 'Edit' : 'Add'; ?> Employee</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .btn-primary { color: #fff; background-color: #005aa5; border-color: #005aa5; }
        .add-message { color: green; font-weight: bold; }
        .error-message { color: red; font-weight: bold; }
        .hrm-form .form-label {
            text-transform: uppercase;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .3px;
            color: #495057;
        }
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
                            <h4 class="mb-sm-0 font-size-18"><?php echo $isEdit ? 'Edit' : 'Add'; ?> Employee</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="list-employee.php">HRM</a></li>
                                    <li class="breadcrumb-item active"><?php echo $isEdit ? 'Edit' : 'Add'; ?> Employee</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-body">
                                <div><span id="message"></span></div>
                                <form id="employeeForm" class="hrm-form" method="post" onsubmit="return false;">
                                    <input type="hidden" id="id" name="id" value="<?php echo $isEdit ? (int)$id : ''; ?>">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="full_name">Full Name *</label>
                                                <input type="text" class="form-control" id="full_name" name="full_name" placeholder="Full name" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="employee_code">Employee Code</label>
                                                <input type="text" class="form-control" id="employee_code" name="employee_code" placeholder="EMP-001">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="father_name">Father's Name</label>
                                                <input type="text" class="form-control" id="father_name" name="father_name" placeholder="Father's name">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="dob">Date of Birth</label>
                                                <input type="date" class="form-control" id="dob" name="dob">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="age">Age</label>
                                                <input type="number" class="form-control" id="age" name="age" placeholder="Age" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="birth_location">Birth Location</label>
                                                <input type="text" class="form-control" id="birth_location" name="birth_location" placeholder="City / village of birth">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="gender">Gender</label>
                                                <select class="form-select" id="gender" name="gender">
                                                    <option value="">Select...</option>
                                                    <option value="Male">Male</option>
                                                    <option value="Female">Female</option>
                                                    <option value="Other">Other</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="blood_group">Blood Group</label>
                                                <input type="text" class="form-control" id="blood_group" name="blood_group" placeholder="B+">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="marital_status">Marital Status</label>
                                                <select class="form-select" id="marital_status" name="marital_status">
                                                    <option value="">Select...</option>
                                                    <option value="Single">Single</option>
                                                    <option value="Married">Married</option>
                                                    <option value="Widowed">Widowed</option>
                                                    <option value="Divorced">Divorced</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="nominee_name">Nominee Name</label>
                                                <input type="text" class="form-control" id="nominee_name" name="nominee_name" placeholder="Nominee full name">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="nominee_relation">Nominee Relation</label>
                                                <input type="text" class="form-control" id="nominee_relation" name="nominee_relation" placeholder="e.g. Wife, Father">
                                            </div>
                                        </div>
                                    </div>

                                    <button type="button" class="btn btn-primary w-md" onclick="saveEmployee()"><?php echo $isEdit ? 'Update' : 'Save'; ?></button>
                                    <a href="list-employee.php" class="btn btn-secondary w-md ms-2">List</a>
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

function saveEmployee() {
    var fullName = document.getElementById('full_name').value.trim();
    if (!fullName) { alert('Full name is required.'); return; }

    var data = {
        action: editId > 0 ? 'updateemployee' : 'saveemployee',
        id: editId,
        full_name: fullName,
        employee_code: document.getElementById('employee_code').value.trim(),
        father_name: document.getElementById('father_name').value.trim(),
        dob: document.getElementById('dob').value,
        age: document.getElementById('age').value,
        birth_location: document.getElementById('birth_location').value.trim(),
        gender: document.getElementById('gender').value,
        blood_group: document.getElementById('blood_group').value.trim(),
        marital_status: document.getElementById('marital_status').value,
        nominee_name: document.getElementById('nominee_name').value.trim(),
        nominee_relation: document.getElementById('nominee_relation').value.trim()
    };

    fetch('hrm-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        showMessage(res.message || '', res.status === 'success');
        if (res.status === 'success') {
            setTimeout(function() { window.location.href = 'list-employee.php'; }, 700);
        }
    })
    .catch(function() { showMessage('Save failed.', false); });
}

function fillEmployeeForm(d) {
    document.getElementById('full_name').value = d.sFullName || '';
    document.getElementById('employee_code').value = d.sEmployeeCode || '';
    document.getElementById('father_name').value = d.sFatherName || '';
    document.getElementById('dob').value = d.dDob || '';
    document.getElementById('age').value = d.iAge || '';
    document.getElementById('birth_location').value = d.sBirthLocation || '';
    document.getElementById('gender').value = d.sGender || '';
    document.getElementById('blood_group').value = d.sBloodGroup || '';
    document.getElementById('marital_status').value = d.sMaritalStatus || '';
    document.getElementById('nominee_name').value = d.sNomineeName || '';
    document.getElementById('nominee_relation').value = d.sNomineeRelation || '';
}

if (editId > 0) {
    fetch('hrm-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'getemployeebyid', id: editId })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.status === 'success' && res.data) {
            fillEmployeeForm(res.data);
        } else {
            showMessage(res.message || 'Failed to load employee.', false);
        }
    });
}
</script>
</body>
</html>
