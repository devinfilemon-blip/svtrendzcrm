<?php
include 'layouts/session.php';
include 'layouts/head-main.php';
include 'layouts/config.php';
include 'layouts/crm-access.php';
crmRequireAdmin($link);
?>
<head>
    <title>Payroll List</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .success-message { color: green; font-weight: bold; }
        .error-message { color: red; font-weight: bold; }
        .btn-primary { color: #fff; background-color: #005aa5; border-color: #005aa5; }
        .badge-paid { background-color: #34c38f; }
        .badge-pending { background-color: #f1b44c; }
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
                            <h4 class="mb-sm-0 font-size-18">Payroll List</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">HRM</a></li>
                                    <li class="breadcrumb-item active">Payroll List</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12 d-flex flex-wrap gap-2 justify-content-end align-items-center">
                        <input type="month" class="form-control form-control-sm" id="filterMonth" style="width: 160px;">
                        <select class="form-select form-select-sm" id="filterStatus" style="width: 150px;">
                            <option value="">All Status</option>
                            <option value="Pending">Pending</option>
                            <option value="Paid">Paid</option>
                        </select>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="loadPayroll()">Filter</button>
                        <a href="payroll.php" class="btn btn-primary btn-sm">Payroll</a>
                    </div>
                </div>

                <div class="table-responsive">
                    <div><span id="message"></span></div>
                    <table id="datatable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Sr No.</th>
                                <th>Employee</th>
                                <th>Pay Month</th>
                                <th>Gross Salary</th>
                                <th>Deductions</th>
                                <th>Net Salary</th>
                                <th>Status</th>
                                <th>Paid Date</th>
                                <th>Actions</th>
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

function fmtMoney(n) {
    n = parseFloat(n) || 0;
    return '₹' + n.toLocaleString('en-IN', { maximumFractionDigits: 2 });
}

function loadPayroll() {
    var month = document.getElementById('filterMonth').value;
    var status = document.getElementById('filterStatus').value;
    $.ajax({
        url: 'hrm-api.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ action: 'listpayroll', month: month, status: status }),
        success: function(response) {
            var rows = '';
            if (response.status === 'success') {
                var list = response.data || [];
                if (!list.length) {
                    rows = '<tr><td colspan="9" class="text-center">No payroll records found</td></tr>';
                } else {
                    list.forEach(function(r, index) {
                        var name = escapeHtml(r.sFullName || '') + (r.sEmployeeCode ? ' (' + escapeHtml(r.sEmployeeCode) + ')' : '');
                        var badgeClass = r.sStatus === 'Paid' ? 'badge-paid' : 'badge-pending';
                        rows += '<tr>';
                        rows += '<td>' + (index + 1) + '</td>';
                        rows += '<td>' + name + '</td>';
                        rows += '<td>' + escapeHtml(r.sPayMonth || '') + '</td>';
                        rows += '<td>' + fmtMoney(r.fGrossSalary) + '</td>';
                        rows += '<td>' + fmtMoney(r.fTotalDeductions) + '</td>';
                        rows += '<td>' + fmtMoney(r.fNetSalary) + '</td>';
                        rows += '<td><span class="badge ' + badgeClass + '">' + escapeHtml(r.sStatus || '') + '</span></td>';
                        rows += '<td>' + escapeHtml(r.dPaidDate || '-') + '</td>';
                        rows += '<td>';
                        if (r.sStatus !== 'Paid') {
                            rows += '<button class="btn btn-success btn-sm me-1" onclick="markPaid(' + r.iPayrollid + ')">Mark Paid</button>';
                        }
                        rows += '<button class="btn btn-danger btn-sm" onclick="deletePayroll(' + r.iPayrollid + ')">Delete</button>';
                        rows += '</td>';
                        rows += '</tr>';
                    });
                }
            } else {
                rows = '<tr><td colspan="9" class="text-center">No payroll records found</td></tr>';
            }
            if ($.fn.DataTable.isDataTable('#datatable')) {
                $('#datatable').DataTable().destroy();
            }
            $('#datatable tbody').html(rows);
            if (response.status === 'success' && (response.data || []).length) {
                $('#datatable').DataTable({ order: [[2, 'desc']] });
            }
        },
        error: function() {
            alert('Failed to load payroll records.');
        }
    });
}

function markPaid(id) {
    if (!id || !confirm('Mark this payslip as paid?')) return;
    fetch('hrm-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'markpayrollpaid', id: id })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        var el = document.getElementById('message');
        el.innerHTML = res.message || '';
        el.className = res.status === 'success' ? 'success-message' : 'error-message';
        loadPayroll();
    });
}

function deletePayroll(id) {
    if (!id || !confirm('Delete this payslip?')) return;
    fetch('hrm-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'deletepayroll', id: id })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        var el = document.getElementById('message');
        el.innerHTML = res.message || '';
        el.className = res.status === 'success' ? 'success-message' : 'error-message';
        loadPayroll();
    });
}

$(document).ready(function() {
    loadPayroll();
});
</script>
</body>
</html>
