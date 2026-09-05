<?php
include 'layouts/session.php';
include 'layouts/head-main.php';
include 'layouts/config.php';
include_once 'layouts/crm-access.php';
crmRequireFinanceAdmin($link);
?>

<head>
    <title>P&L Entries</title>
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
        .pay-summary-total .pay-value { color: #047857; }
        .pay-summary-received .pay-value { color: #c2410c; }
        .pay-summary-pending .pay-value { color: #1d4ed8; }
        .pay-summary-partial .pay-value { color: #4c4585; }
        .crm-badge-income { background: #ecfdf5; color: #047857; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .crm-badge-expense { background: #fff7ed; color: #c2410c; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .table-responsive { overflow-x: auto; }

        /* Dark mode — summary cards + type badges */
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
        body.crm-dark .pay-summary-total .pay-value { color: #4ade80 !important; }
        html.crm-dark .pay-summary-received .pay-value,
        body.crm-dark .pay-summary-received .pay-value { color: #fb923c !important; }
        html.crm-dark .pay-summary-pending .pay-value,
        body.crm-dark .pay-summary-pending .pay-value { color: #60a5fa !important; }
        html.crm-dark .pay-summary-partial .pay-value,
        body.crm-dark .pay-summary-partial .pay-value { color: #c4b5fd !important; }
        html.crm-dark .crm-badge-income,
        body.crm-dark .crm-badge-income {
            background: rgba(34,197,94,0.18) !important;
            color: #4ade80 !important;
        }
        html.crm-dark .crm-badge-expense,
        body.crm-dark .crm-badge-expense {
            background: rgba(249,115,22,0.18) !important;
            color: #fb923c !important;
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
                            <h4 class="mb-sm-0 font-size-18">P&amp;L Account — Entries</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Accounts</a></li>
                                    <li class="breadcrumb-item active">List P&amp;L</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-3 g-3">
                    <div class="col-md-3">
                        <div class="pay-summary-card pay-summary-total">
                            <h6>Total Income</h6>
                            <p class="pay-value" id="sumIncome">₹0.00</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="pay-summary-card pay-summary-received">
                            <h6>Total Expense</h6>
                            <p class="pay-value" id="sumExpense">₹0.00</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="pay-summary-card pay-summary-pending">
                            <h6>Net Profit / Loss</h6>
                            <p class="pay-value" id="sumNet">₹0.00</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="pay-summary-card pay-summary-partial">
                            <h6>Entries</h6>
                            <p class="pay-value" id="sumCount">0</p>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <select id="filter_type" class="form-select form-select-sm" style="width:auto;">
                                <option value="">All Types</option>
                                <option value="Income">Income</option>
                                <option value="Expense">Expense</option>
                            </select>
                            <input type="date" id="filter_from" class="form-control form-control-sm" style="width:150px;" title="From date">
                            <input type="date" id="filter_to" class="form-control form-control-sm" style="width:150px;" title="To date">
                            <input type="text" id="filter_search" class="form-control form-control-sm" placeholder="Search…" style="width:160px;">
                            <button type="button" class="btn btn-sm btn-secondary" onclick="clearFilters()">Clear</button>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="pnl-statement.php" class="btn btn-outline-primary btn-sm">P&amp;L Statement</a>
                            <a href="add-pnl.php" class="btn btn-primary btn-sm">+ Add Entry</a>
                        </div>
                    </div>
                </div>

                <div><span id="message"></span></div>
                <div class="table-responsive">
                    <table id="datatable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Sr No.</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Category</th>
                                <th>Particulars</th>
                                <th>Amount</th>
                                <th>Reference</th>
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

function typeBadge(type) {
    if (type === 'Income') return '<span class="crm-badge-income">Income</span>';
    return '<span class="crm-badge-expense">Expense</span>';
}

function updateSummary(summary) {
    summary = summary || {};
    document.getElementById('sumIncome').textContent = formatMoney(summary.total_income || 0);
    document.getElementById('sumExpense').textContent = formatMoney(summary.total_expense || 0);
    var net = parseFloat(summary.net_profit) || 0;
    var netEl = document.getElementById('sumNet');
    netEl.textContent = formatMoney(net);
    netEl.style.color = net >= 0 ? '#047857' : '#c2410c';
    document.getElementById('sumCount').textContent = String(summary.count || 0);
}

function fngetlistpnl() {
    $.ajax({
        url: 'api.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            action: 'fngetlistpnl',
            type: document.getElementById('filter_type').value || '',
            from: document.getElementById('filter_from').value || '',
            to: document.getElementById('filter_to').value || '',
            search: document.getElementById('filter_search').value.trim()
        }),
        success: function(response) {
            updateSummary(response.summary || {});
            if (response.status !== 'success') {
                $('#datatable tbody').html('<tr><td colspan="9" class="text-center">No entries found</td></tr>');
                return;
            }
            var rows = '';
            var list = response.data || [];
            if (!list.length) {
                rows = '<tr><td colspan="9" class="text-center">No entries found</td></tr>';
            } else {
                list.forEach(function(p, index) {
                    rows += '<tr>' +
                        '<td>' + (index + 1) + '</td>' +
                        '<td>' + escapeHtml(formatDate(p.sEntrydate)) + '</td>' +
                        '<td>' + typeBadge(p.sType) + '</td>' +
                        '<td>' + escapeHtml(p.sCategory || '—') + '</td>' +
                        '<td>' + escapeHtml(p.sParticulars || '') + '</td>' +
                        '<td>' + formatMoney(p.dAmount) + '</td>' +
                        '<td>' + escapeHtml(p.sReference || '—') + '</td>' +
                        '<td><form action="add-pnl.php" method="post">' +
                            '<input type="hidden" name="id" value="' + p.iPnLid + '">' +
                            '<button type="submit" class="btn btn-success btn-sm">Edit</button></form></td>' +
                        '<td><button class="btn btn-danger btn-sm" onclick="deletepnl(' + p.iPnLid + ')">Delete</button></td>' +
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
            alert('Failed to load P&L entries.');
        }
    });
}

function clearFilters() {
    document.getElementById('filter_type').value = '';
    document.getElementById('filter_from').value = '';
    document.getElementById('filter_to').value = '';
    document.getElementById('filter_search').value = '';
    fngetlistpnl();
}

function deletepnl(id) {
    if (!id) return;
    if (!confirm('Delete this P&L entry?')) return;
    fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'deletepnl', id: id })
    })
    .then(function (r) { return r.json(); })
    .then(function (res) {
        var el = document.getElementById('message');
        el.innerHTML = res.message || '';
        el.className = (res.status === 'success') ? 'success-message' : 'error-message';
        fngetlistpnl();
    });
}

$(document).ready(function () {
    fngetlistpnl();
    $('#filter_type, #filter_from, #filter_to').on('change', fngetlistpnl);
    var t = null;
    $('#filter_search').on('keyup', function () {
        clearTimeout(t);
        t = setTimeout(fngetlistpnl, 350);
    });
});
</script>
</body>
</html>
