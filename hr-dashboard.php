<?php
include 'layouts/session.php';
include 'layouts/head-main.php';
include 'layouts/config.php';
include 'layouts/crm-access.php';
crmRequireAdmin($link);
?>
<head>
    <title>HR Dashboard</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .btn-primary { color: #fff; background-color: #005aa5; border-color: #005aa5; }
        .hrm-stat-icon { color: #fff; }
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
                            <h4 class="mb-sm-0 font-size-18">HR Dashboard</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">HRM</a></li>
                                    <li class="breadcrumb-item active">HR Dashboard</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" id="statCards">
                    <div class="col-xl-3 col-md-6">
                        <div class="card mini-stats-wid">
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="flex-grow-1">
                                        <p class="text-muted fw-medium">Total Employees</p>
                                        <h4 class="mb-0" id="statTotalEmployees">0</h4>
                                    </div>
                                    <div class="flex-shrink-0 align-self-center">
                                        <div class="avatar-sm rounded-circle bg-primary align-self-center mini-stat-icon">
                                            <span class="avatar-title rounded-circle bg-primary">
                                                <i class="bx bx-group font-size-24 hrm-stat-icon"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card mini-stats-wid">
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="flex-grow-1">
                                        <p class="text-muted fw-medium">Active Employees</p>
                                        <h4 class="mb-0" id="statActiveEmployees">0</h4>
                                    </div>
                                    <div class="flex-shrink-0 align-self-center">
                                        <div class="avatar-sm rounded-circle bg-success align-self-center mini-stat-icon">
                                            <span class="avatar-title rounded-circle bg-success">
                                                <i class="bx bx-user-check font-size-24 hrm-stat-icon"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card mini-stats-wid">
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="flex-grow-1">
                                        <p class="text-muted fw-medium">Salary Structures</p>
                                        <h4 class="mb-0" id="statStructures">0</h4>
                                    </div>
                                    <div class="flex-shrink-0 align-self-center">
                                        <div class="avatar-sm rounded-circle bg-info align-self-center mini-stat-icon">
                                            <span class="avatar-title rounded-circle bg-info">
                                                <i class="bx bx-rupee font-size-24 hrm-stat-icon"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card mini-stats-wid">
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="flex-grow-1">
                                        <p class="text-muted fw-medium" id="statMonthLabel">This Month Payroll</p>
                                        <h4 class="mb-0" id="statMonthNet">₹0</h4>
                                    </div>
                                    <div class="flex-shrink-0 align-self-center">
                                        <div class="avatar-sm rounded-circle bg-warning align-self-center mini-stat-icon">
                                            <span class="avatar-title rounded-circle bg-warning">
                                                <i class="bx bx-wallet font-size-24 hrm-stat-icon"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-5">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Employees by Gender</h5>
                                <table class="table table-borderless mb-0" id="genderTable">
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-3">This Month Payroll Status</h5>
                                <p class="mb-1">Payslips generated: <strong id="statMonthPayslips">0</strong></p>
                                <p class="mb-0">Pending payslips: <strong id="statMonthPending">0</strong></p>
                                <a href="list-payroll.php" class="btn btn-primary btn-sm mt-3">View Payroll List</a>
                                <a href="payroll.php" class="btn btn-secondary btn-sm mt-3 ms-2">Manage Payroll</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-7">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title mb-0">Recently Added Employees</h5>
                                    <a href="add-employee.php" class="btn btn-primary btn-sm">+ Add Employee</a>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th>Employee Code</th>
                                                <th>Full Name</th>
                                                <th>Gender</th>
                                                <th>Added On</th>
                                            </tr>
                                        </thead>
                                        <tbody id="recentEmployeesBody"></tbody>
                                    </table>
                                </div>
                                <a href="list-employee.php" class="btn btn-secondary btn-sm mt-3">View All Employees</a>
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
function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    return String(text)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

function fmtMoney(n) {
    n = parseFloat(n) || 0;
    return '₹' + n.toLocaleString('en-IN', { maximumFractionDigits: 2 });
}

function loadDashboard() {
    fetch('hrm-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'hrdashboardstats' })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.status !== 'success') { return; }
        var d = res.data;
        document.getElementById('statTotalEmployees').textContent = d.total_employees;
        document.getElementById('statActiveEmployees').textContent = d.active_employees;
        document.getElementById('statStructures').textContent = d.structure_count;
        document.getElementById('statMonthNet').textContent = fmtMoney(d.current_month_net);
        document.getElementById('statMonthLabel').textContent = 'Payroll (' + d.current_month + ')';
        document.getElementById('statMonthPayslips').textContent = d.current_month_payslips;
        document.getElementById('statMonthPending').textContent = d.current_month_pending;

        var genderRows = '';
        (d.gender_breakdown || []).forEach(function(g) {
            genderRows += '<tr><td>' + escapeHtml(g.g) + '</td><td class="text-end">' + g.c + '</td></tr>';
        });
        if (!genderRows) genderRows = '<tr><td colspan="2" class="text-center">No data</td></tr>';
        document.querySelector('#genderTable tbody').innerHTML = genderRows;

        var recentRows = '';
        (d.recent_employees || []).forEach(function(e) {
            recentRows += '<tr>' +
                '<td>' + escapeHtml(e.sEmployeeCode || '-') + '</td>' +
                '<td>' + escapeHtml(e.sFullName || '') + '</td>' +
                '<td>' + escapeHtml(e.sGender || '-') + '</td>' +
                '<td>' + escapeHtml((e.sCreatedTimeStamp || '').split(' ')[0]) + '</td>' +
                '</tr>';
        });
        if (!recentRows) recentRows = '<tr><td colspan="4" class="text-center">No employees yet</td></tr>';
        document.getElementById('recentEmployeesBody').innerHTML = recentRows;
    });
}

$(document).ready(loadDashboard);
</script>
</body>
</html>
