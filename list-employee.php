<?php
include 'layouts/session.php';
include 'layouts/head-main.php';
include 'layouts/config.php';
include 'layouts/crm-access.php';
crmRequireAdmin($link);
?>
<head>
    <title>Employee List</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .success-message { color: green; font-weight: bold; }
        .error-message { color: red; font-weight: bold; }
        .btn-primary { color: #fff; background-color: #005aa5; border-color: #005aa5; }
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
                            <h4 class="mb-sm-0 font-size-18">Employee List</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">HRM</a></li>
                                    <li class="breadcrumb-item active">Employee List</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12 text-end">
                        <a href="add-employee.php" class="btn btn-primary btn-sm">+ Add Employee</a>
                    </div>
                </div>

                <div class="table-responsive">
                    <div><span id="message"></span></div>
                    <table id="datatable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Sr No.</th>
                                <th>Employee Code</th>
                                <th>Full Name</th>
                                <th>Father's Name</th>
                                <th>Gender</th>
                                <th>Blood Group</th>
                                <th>Marital Status</th>
                                <th>Nominee</th>
                                <th>Edit</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
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
function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    return String(text)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

function loadEmployees() {
    $.ajax({
        url: 'hrm-api.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ action: 'listemployees' }),
        success: function(response) {
            var rows = '';
            if (response.status === 'success') {
                var list = response.data || [];
                if (!list.length) {
                    rows = '<tr><td colspan="10" class="text-center">No employees found</td></tr>';
                } else {
                    list.forEach(function(r, index) {
                        rows += '<tr>';
                        rows += '<td>' + (index + 1) + '</td>';
                        rows += '<td>' + escapeHtml(r.sEmployeeCode || '-') + '</td>';
                        rows += '<td>' + escapeHtml(r.sFullName || '') + '</td>';
                        rows += '<td>' + escapeHtml(r.sFatherName || '-') + '</td>';
                        rows += '<td>' + escapeHtml(r.sGender || '-') + '</td>';
                        rows += '<td>' + escapeHtml(r.sBloodGroup || '-') + '</td>';
                        rows += '<td>' + escapeHtml(r.sMaritalStatus || '-') + '</td>';
                        rows += '<td>' + escapeHtml(r.sNomineeName || '-') + '</td>';
                        rows += '<td><a class="btn btn-success btn-sm" href="add-employee.php?id=' + r.iEmployeeid + '">Edit</a></td>';
                        rows += '<td><button class="btn btn-danger btn-sm" onclick="deleteEmployee(' + r.iEmployeeid + ')">Delete</button></td>';
                        rows += '</tr>';
                    });
                }
            } else {
                rows = '<tr><td colspan="10" class="text-center">No employees found</td></tr>';
            }
            if ($.fn.DataTable.isDataTable('#datatable')) {
                $('#datatable').DataTable().destroy();
            }
            $('#datatable tbody').html(rows);
            if (response.status === 'success' && (response.data || []).length) {
                $('#datatable').DataTable({ order: [[0, 'asc']] });
            }
        },
        error: function() {
            alert('Failed to load employees.');
        }
    });
}

function deleteEmployee(id) {
    if (!id || !confirm('Delete this employee? This cannot be undone.')) return;
    fetch('hrm-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'deleteemployee', id: id })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        var el = document.getElementById('message');
        el.innerHTML = res.message || '';
        el.className = res.status === 'success' ? 'success-message' : 'error-message';
        loadEmployees();
    });
}

$(document).ready(loadEmployees);
</script>
</body>
</html>
