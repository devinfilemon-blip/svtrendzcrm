<?php
include 'layouts/session.php';
include 'layouts/head-main.php';
include 'layouts/config.php';
include_once 'layouts/crm-access.php';
crmRequireModule('finance', $link);
?>

<head>
    <title>Sales Payments</title>
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
        .crm-badge-received { background: #ecfdf5; color: #047857; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .table-responsive { overflow-x: auto; }

        /* Dark mode — summary cards + badges */
        html.crm-dark .pay-summary-card,
        body.crm-dark .pay-summary-card {
            background: #111827 !important;
            border-color: rgba(255,255,255,0.1) !important;
        }
        html.crm-dark .pay-summary-card h6,
        body.crm-dark .pay-summary-card h6 {
            color: #94a3b8 !important;
        }
        html.crm-dark .pay-summary-total .pay-value,
        body.crm-dark .pay-summary-total .pay-value { color: #c4b5fd !important; }
        html.crm-dark .pay-summary-received .pay-value,
        body.crm-dark .pay-summary-received .pay-value { color: #4ade80 !important; }
        html.crm-dark .pay-summary-pending .pay-value,
        body.crm-dark .pay-summary-pending .pay-value { color: #fb923c !important; }
        html.crm-dark .pay-summary-partial .pay-value,
        body.crm-dark .pay-summary-partial .pay-value { color: #60a5fa !important; }
        html.crm-dark .crm-badge-pending,
        body.crm-dark .crm-badge-pending { background: rgba(249,115,22,0.18) !important; color: #fb923c !important; }
        html.crm-dark .crm-badge-partial,
        body.crm-dark .crm-badge-partial { background: rgba(37,99,235,0.18) !important; color: #60a5fa !important; }
        html.crm-dark .crm-badge-received,
        body.crm-dark .crm-badge-received { background: rgba(34,197,94,0.18) !important; color: #4ade80 !important; }
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
                            <h4 class="mb-sm-0 font-size-18">Sales Management — Payments</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Sales Management</a></li>
                                    <li class="breadcrumb-item active">List Payments</li>
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
                            <h6>Partial / Open Entries</h6>
                            <p class="pay-value" id="sumOpen">0</p>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <select id="filter_status" class="form-select form-select-sm" style="width:auto;">
                                <option value="">All Status</option>
                                <option value="Pending">Pending</option>
                                <option value="Partial">Partial</option>
                                <option value="Received">Received</option>
                            </select>
                            <input type="text" id="filter_client" class="form-control form-control-sm" placeholder="Search client…" style="width:180px;">
                            <button type="button" class="btn btn-sm btn-secondary" onclick="clearFilters()">Clear</button>
                        </div>
                        <a href="add-payment.php" class="btn btn-primary btn-sm">+ Add Payment</a>
                    </div>
                </div>

                <div><span id="message"></span></div>
                <div class="table-responsive">
                    <table id="datatable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Sr No.</th>
                                <th>Date</th>
                                <th>Client</th>
                                <th>Invoice No</th>
                                <th>Total</th>
                                <th>Received</th>
                                <th>Pending</th>
                                <th>Status</th>
                                <th>Mode</th>
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

function statusBadge(status) {
    var cls = 'crm-badge-pending';
    if (status === 'Received') cls = 'crm-badge-received';
    else if (status === 'Partial') cls = 'crm-badge-partial';
    return '<span class="' + cls + '">' + escapeHtml(status || 'Pending') + '</span>';
}

function updateSummary(summary) {
    summary = summary || {};
    document.getElementById('sumTotal').textContent = formatMoney(summary.total_amount || 0);
    document.getElementById('sumReceived').textContent = formatMoney(summary.total_received || 0);
    document.getElementById('sumPending').textContent = formatMoney(summary.total_pending || 0);
    document.getElementById('sumOpen').textContent = String(summary.open_count || 0);
}

function fngetlistpayment() {
    var status = document.getElementById('filter_status').value || '';
    var client = document.getElementById('filter_client').value.trim();
    $.ajax({
        url: 'api.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ action: 'fngetlistpayment', status: status, client: client }),
        success: function(response) {
            updateSummary(response.summary || {});
            if (response.status !== 'success') {
                $('#datatable tbody').html('<tr><td colspan="11" class="text-center">No payments found</td></tr>');
                return;
            }
            var rows = '';
            var list = response.data || [];
            if (!list.length) {
                rows = '<tr><td colspan="11" class="text-center">No payments found</td></tr>';
            } else {
                list.forEach(function(p, index) {
                    rows += '<tr>' +
                        '<td>' + (index + 1) + '</td>' +
                        '<td>' + escapeHtml(formatDate(p.sPaymentdate)) + '</td>' +
                        '<td>' + escapeHtml(p.sClientname || '') + '</td>' +
                        '<td>' + escapeHtml(p.sInvoiceNo || '—') + '</td>' +
                        '<td>' + formatMoney(p.dAmount) + '</td>' +
                        '<td>' + formatMoney(p.dReceived) + '</td>' +
                        '<td>' + formatMoney(p.dPending) + '</td>' +
                        '<td>' + statusBadge(p.sStatus) + '</td>' +
                        '<td>' + escapeHtml(p.sMode || '—') + '</td>' +
                        '<td><form action="add-payment.php" method="post">' +
                            '<input type="hidden" name="id" value="' + p.iPaymentid + '">' +
                            '<button type="submit" class="btn btn-success btn-sm">Edit</button></form></td>' +
                        '<td><button class="btn btn-danger btn-sm" onclick="deletepayment(' + p.iPaymentid + ')">Delete</button></td>' +
                    '</tr>';
                });
            }

            if ($.fn.DataTable.isDataTable('#datatable')) {
                $('#datatable').DataTable().destroy();
            }
            $('#datatable tbody').html(rows);
            if (list.length) {
                $('#datatable').DataTable({ order: [[1, 'desc']] });
            }
        },
        error: function() {
            alert('Failed to load payments.');
        }
    });
}

function clearFilters() {
    document.getElementById('filter_status').value = '';
    document.getElementById('filter_client').value = '';
    fngetlistpayment();
}

function deletepayment(id) {
    if (!id) return;
    if (!confirm('Delete this payment entry?')) return;
    fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'deletepayment', id: id })
    })
    .then(function (r) { return r.json(); })
    .then(function (res) {
        var el = document.getElementById('message');
        el.innerHTML = res.message || '';
        el.className = (res.status === 'success') ? 'success-message' : 'error-message';
        fngetlistpayment();
    });
}

$(document).ready(function () {
    fngetlistpayment();
    $('#filter_status').on('change', fngetlistpayment);
    var t = null;
    $('#filter_client').on('keyup', function () {
        clearTimeout(t);
        t = setTimeout(fngetlistpayment, 350);
    });
});
</script>
</body>
</html>
