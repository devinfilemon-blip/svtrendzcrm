<?php
include 'layouts/session.php';
include 'layouts/head-main.php';
include 'layouts/config.php';
?>

<head>
    <title>Monthly Client Reports</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .success-message { color: green; font-weight: bold; }
        .error-message { color: red; font-weight: bold; }
        .btn-primary { color: #fff; background-color: #005aa5; border-color: #005aa5; }
        .pay-summary-card {
            border-radius: 10px;
            padding: 16px 18px;
            border: 1px solid #e8e4f3;
            background: #fff;
            height: 100%;
        }
        .pay-summary-card h6 { margin: 0 0 6px; color: #6c757d; font-size: 13px; }
        .pay-summary-card .pay-value { font-size: 22px; font-weight: 700; margin: 0; }
        .pay-summary-total .pay-value { color: #4c4585; }
        .pay-summary-received .pay-value { color: #047857; }
        .pay-summary-pending .pay-value { color: #c2410c; }
        .pay-summary-partial .pay-value { color: #1d4ed8; }
        .crm-badge-pending { background: #fff7ed; color: #c2410c; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .crm-badge-partial { background: #eff6ff; color: #1d4ed8; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .crm-badge-paid { background: #ecfdf5; color: #047857; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .crm-badge-overdue { background: #fef2f2; color: #b91c1c; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .table-responsive { overflow-x: auto; }
        .gen-box {
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            padding: 12px 14px;
            background: #f8fafc;
        }
        html.crm-dark .pay-summary-card, body.crm-dark .pay-summary-card {
            background: #111827 !important;
            border-color: rgba(255,255,255,0.1) !important;
        }
        html.crm-dark .pay-summary-card h6, body.crm-dark .pay-summary-card h6 { color: #94a3b8 !important; }
        html.crm-dark .pay-summary-total .pay-value, body.crm-dark .pay-summary-total .pay-value { color: #c4b5fd !important; }
        html.crm-dark .pay-summary-received .pay-value, body.crm-dark .pay-summary-received .pay-value { color: #4ade80 !important; }
        html.crm-dark .pay-summary-pending .pay-value, body.crm-dark .pay-summary-pending .pay-value { color: #fb923c !important; }
        html.crm-dark .pay-summary-partial .pay-value, body.crm-dark .pay-summary-partial .pay-value { color: #60a5fa !important; }
        html.crm-dark .gen-box, body.crm-dark .gen-box {
            background: #0f172a !important;
            border-color: rgba(255,255,255,0.15) !important;
        }
        html.crm-dark .crm-badge-pending, body.crm-dark .crm-badge-pending { background: rgba(249,115,22,0.18) !important; color: #fb923c !important; }
        html.crm-dark .crm-badge-partial, body.crm-dark .crm-badge-partial { background: rgba(37,99,235,0.18) !important; color: #60a5fa !important; }
        html.crm-dark .crm-badge-paid, body.crm-dark .crm-badge-paid { background: rgba(34,197,94,0.18) !important; color: #4ade80 !important; }
        html.crm-dark .crm-badge-overdue, body.crm-dark .crm-badge-overdue { background: rgba(239,68,68,0.18) !important; color: #f87171 !important; }
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
                            <h4 class="mb-sm-0 font-size-18">Client Management — Monthly Reports</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Client Management</a></li>
                                    <li class="breadcrumb-item active">Monthly Reports</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-3 g-3">
                    <div class="col-md-3">
                        <div class="pay-summary-card pay-summary-total">
                            <h6>Total Invoiced</h6>
                            <p class="pay-value" id="sumTotal">₹0.00</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="pay-summary-card pay-summary-received">
                            <h6>Received</h6>
                            <p class="pay-value" id="sumReceived">₹0.00</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="pay-summary-card pay-summary-pending">
                            <h6>Pending</h6>
                            <p class="pay-value" id="sumPending">₹0.00</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="pay-summary-card pay-summary-partial">
                            <h6>Overdue Entries</h6>
                            <p class="pay-value" id="sumOverdue">0</p>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-lg-7">
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <input type="month" id="filter_month" class="form-control form-control-sm" style="width:150px;">
                            <select id="filter_status" class="form-select form-select-sm" style="width:auto;">
                                <option value="">All Status</option>
                                <option value="Pending">Pending</option>
                                <option value="Partial">Partial</option>
                                <option value="Paid">Paid</option>
                                <option value="Overdue">Overdue</option>
                            </select>
                            <select id="filter_due" class="form-select form-select-sm" style="width:auto;">
                                <option value="">All Due</option>
                                <option value="overdue">Overdue only</option>
                                <option value="upcoming">Due in 7 days</option>
                            </select>
                            <input type="text" id="filter_client" class="form-control form-control-sm" placeholder="Search client…" style="width:160px;">
                            <button type="button" class="btn btn-sm btn-secondary" onclick="clearFilters()">Clear</button>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="gen-box d-flex flex-wrap gap-2 align-items-center justify-content-lg-end">
                            <span class="small text-muted">Generate for all active clients:</span>
                            <input type="month" id="gen_month" class="form-control form-control-sm" style="width:150px;">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="generateMonth()">Generate Month</button>
                            <a href="add-client-monthly.php" class="btn btn-primary btn-sm">+ Add Report</a>
                        </div>
                    </div>
                </div>

                <div><span id="message"></span></div>
                <div class="table-responsive">
                    <table id="datatable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Sr No.</th>
                                <th>Month</th>
                                <th>Client</th>
                                <th>Due Date</th>
                                <th>Amount</th>
                                <th>Received</th>
                                <th>Pending</th>
                                <th>Status</th>
                                <th>Report</th>
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
function checkTokenStatus() {
    $.ajax({
        url: 'check_token.php',
        method: 'GET',
        success: function(response) {
            if (response.status === 'error') {
                alert(response.message);
                window.location.href = 'auth-login.php';
            }
        },
        error: function() { window.location.href = 'auth-login.php'; }
    });
}
checkTokenStatus();

function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function formatMoney(n) {
    var v = parseFloat(n) || 0;
    return '₹' + v.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatDate(d) {
    if (!d) return '—';
    var parts = String(d).slice(0, 10).split('-');
    if (parts.length !== 3) return d;
    return parts[2] + '-' + parts[1] + '-' + parts[0];
}

function formatMonth(m) {
    if (!m || String(m).length < 7) return m || '—';
    var parts = String(m).slice(0, 7).split('-');
    var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    var mi = parseInt(parts[1], 10) - 1;
    return (months[mi] || parts[1]) + ' ' + parts[0];
}

function statusBadge(status) {
    var cls = 'crm-badge-pending';
    if (status === 'Paid') cls = 'crm-badge-paid';
    else if (status === 'Partial') cls = 'crm-badge-partial';
    else if (status === 'Overdue') cls = 'crm-badge-overdue';
    return '<span class="' + cls + '">' + escapeHtml(status || 'Pending') + '</span>';
}

function clearFilters() {
    $('#filter_status').val('');
    $('#filter_due').val('');
    $('#filter_client').val('');
    var now = new Date();
    $('#filter_month').val(now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0'));
    loadList();
}

function loadList() {
    $.ajax({
        url: 'api.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            action: 'fngetlistclientmonthly',
            status: $('#filter_status').val(),
            month: $('#filter_month').val(),
            client: $('#filter_client').val(),
            due: $('#filter_due').val()
        }),
        success: function(res) {
            if (res.status !== 'success') {
                $('#datatable tbody').html('<tr><td colspan="11">No records found</td></tr>');
                return;
            }
            var rows = res.data || [];
            var s = res.summary || {};
            $('#sumTotal').text(formatMoney(s.total_amount || 0));
            $('#sumReceived').text(formatMoney(s.total_received || 0));
            $('#sumPending').text(formatMoney(s.total_pending || 0));
            $('#sumOverdue').text(s.overdue_count || 0);

            var html = '';
            rows.forEach(function(r, i) {
                var reportPreview = (r.sReport || '').trim();
                if (reportPreview.length > 40) reportPreview = reportPreview.slice(0, 40) + '…';
                html += '<tr>'
                    + '<td>' + (i + 1) + '</td>'
                    + '<td>' + escapeHtml(formatMonth(r.sMonth)) + '</td>'
                    + '<td>' + escapeHtml(r.sClientname) + '</td>'
                    + '<td>' + escapeHtml(formatDate(r.sDuedate)) + '</td>'
                    + '<td>' + formatMoney(r.dAmount) + '</td>'
                    + '<td>' + formatMoney(r.dReceived) + '</td>'
                    + '<td>' + formatMoney(r.dPending) + '</td>'
                    + '<td>' + statusBadge(r.sStatus) + '</td>'
                    + '<td title="' + escapeHtml(r.sReport || '') + '">' + escapeHtml(reportPreview || '—') + '</td>'
                    + '<td><form action="add-client-monthly.php" method="post">'
                    + '<input type="hidden" name="id" value="' + r.iMonthlyid + '">'
                    + '<button type="submit" class="btn btn-success btn-sm">Edit</button></form></td>'
                    + '<td><button class="btn btn-danger btn-sm" onclick="deleteMonthly(' + r.iMonthlyid + ')">Delete</button></td>'
                    + '</tr>';
            });

            if ($.fn.DataTable.isDataTable('#datatable')) {
                $('#datatable').DataTable().destroy();
            }
            $('#datatable tbody').html(html || '<tr><td colspan="11">No records found</td></tr>');
            if (rows.length) $('#datatable').DataTable({ order: [] });
        }
    });
}

function deleteMonthly(id) {
    if (!confirm('Delete this monthly entry?')) return;
    $.ajax({
        url: 'api.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ action: 'deleteclientmonthly', id: id }),
        success: function(res) {
            if (res.status === 'success') {
                $('#message').html('<span class="success-message">' + res.message + '</span>');
                loadList();
            } else {
                $('#message').html('<span class="error-message">' + (res.message || 'Delete failed') + '</span>');
            }
        }
    });
}

function generateMonth() {
    var month = $('#gen_month').val();
    if (!month) {
        alert('Select a month to generate');
        return;
    }
    if (!confirm('Create monthly entries for all Active clients for ' + month + '? Existing entries will be skipped.')) return;
    $.ajax({
        url: 'api.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ action: 'generatemonthlyforclients', sMonth: month }),
        success: function(res) {
            if (res.status === 'success') {
                $('#message').html('<span class="success-message">' + res.message + '</span>');
                $('#filter_month').val(month);
                loadList();
            } else {
                $('#message').html('<span class="error-message">' + (res.message || 'Generate failed') + '</span>');
            }
        }
    });
}

$('#filter_status, #filter_due, #filter_month').on('change', loadList);
$('#filter_client').on('keyup', function(e) {
    if (e.key === 'Enter') loadList();
});

$(function() {
    var now = new Date();
    var ym = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0');
    $('#filter_month').val(ym);
    $('#gen_month').val(ym);
    loadList();
});
</script>
