<?php
include 'layouts/session.php';
include 'layouts/head-main.php';
include 'layouts/config.php';
include_once 'layouts/crm-access.php';
crmRequireFinanceAdmin($link);
?>

<head>
    <title>P&L Account Statement</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .btn-primary { color: #fff; background-color: #005aa5; border-color: #005aa5; }
        .pnl-summary-card {
            border-radius: 10px;
            padding: 16px 18px;
            border: 1px solid #e8e4f3;
            background: #fff;
            height: 100%;
        }
        .pnl-summary-card h6 { margin: 0 0 6px; color: #6c757d; font-size: 13px; }
        .pnl-summary-card .pay-value { font-size: 22px; font-weight: 700; margin: 0; }
        .pnl-section-title {
            font-size: 16px;
            font-weight: 700;
            margin: 1.25rem 0 0.75rem;
            padding-bottom: 6px;
            border-bottom: 2px solid #e8e4f3;
        }
        .pnl-section-title.income { color: #047857; border-color: #a7f3d0; }
        .pnl-section-title.expense { color: #c2410c; border-color: #fed7aa; }
        .pnl-net-box {
            margin-top: 1.5rem;
            padding: 18px 20px;
            border-radius: 10px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .pnl-net-box .label { font-size: 16px; font-weight: 600; color: #334155; }
        .pnl-net-box .value { font-size: 24px; font-weight: 800; }
        .pnl-period { color: #64748b; font-size: 14px; }

        /* Dark mode — summary cards */
        html.crm-dark .pnl-summary-card,
        body.crm-dark .pnl-summary-card {
            background: #111827 !important;
            border-color: rgba(255,255,255,0.1) !important;
        }
        html.crm-dark .pnl-summary-card h6,
        body.crm-dark .pnl-summary-card h6 {
            color: #94a3b8 !important;
        }
        html.crm-dark .pnl-summary-card .pay-value,
        body.crm-dark .pnl-summary-card .pay-value {
            color: #f1f5f9 !important;
        }
        html.crm-dark .pnl-net-box,
        body.crm-dark .pnl-net-box {
            background: #1a2236 !important;
            border-color: rgba(255,255,255,0.1) !important;
        }
        html.crm-dark .pnl-net-box .label,
        body.crm-dark .pnl-net-box .label {
            color: #e2e8f0 !important;
        }
        html.crm-dark .pnl-period,
        body.crm-dark .pnl-period {
            color: #94a3b8 !important;
        }
        html.crm-dark .pnl-section-title.income,
        body.crm-dark .pnl-section-title.income { color: #4ade80 !important; }
        html.crm-dark .pnl-section-title.expense,
        body.crm-dark .pnl-section-title.expense { color: #fb923c !important; }

        @media print {
            .no-print { display: none !important; }
            .main-content { margin: 0 !important; padding: 0 !important; }
            .page-content { padding: 0 !important; }
            .card { border: none !important; box-shadow: none !important; }
        }
    </style>
</head>
<?php include 'layouts/body.php'; ?>

<div id="layout-wrapper">
<?php include 'layouts/menu.php'; ?>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="row no-print">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">P&amp;L Account Statement</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="list-pnl.php">P&amp;L Account</a></li>
                                    <li class="breadcrumb-item active">Statement</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-3 no-print">
                    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <label class="mb-0">From</label>
                            <input type="date" id="filter_from" class="form-control form-control-sm" style="width:150px;">
                            <label class="mb-0">To</label>
                            <input type="date" id="filter_to" class="form-control form-control-sm" style="width:150px;">
                            <button type="button" class="btn btn-sm btn-primary" onclick="loadStatement()">Show Statement</button>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="add-pnl.php" class="btn btn-primary btn-sm">+ Add Entry</a>
                            <a href="list-pnl.php" class="btn btn-secondary btn-sm">All Entries</a>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">Print</button>
                        </div>
                    </div>
                </div>

                <div class="row mb-3 g-3">
                    <div class="col-md-4">
                        <div class="pnl-summary-card">
                            <h6>Total Income</h6>
                            <p class="pay-value" id="sumIncome" style="color:#047857;">₹0.00</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="pnl-summary-card">
                            <h6>Total Expense</h6>
                            <p class="pay-value" id="sumExpense" style="color:#c2410c;">₹0.00</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="pnl-summary-card">
                            <h6>Net Profit / Loss</h6>
                            <p class="pay-value" id="sumNet">₹0.00</p>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                            <div>
                                <h5 class="mb-1">Profit &amp; Loss Account Statement</h5>
                                <p class="pnl-period mb-0" id="periodLabel">—</p>
                            </div>
                        </div>

                        <div class="pnl-section-title income">Income</div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-2">
                                <thead>
                                    <tr>
                                        <th style="width:40%">Category</th>
                                        <th>Particulars</th>
                                        <th class="text-end" style="width:140px;">Amount (₹)</th>
                                    </tr>
                                </thead>
                                <tbody id="incomeBody">
                                    <tr><td colspan="3" class="text-center text-muted">No income entries</td></tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2" class="text-end">Total Income</th>
                                        <th class="text-end" id="footIncome">₹0.00</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="pnl-section-title expense">Expenses</div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-2">
                                <thead>
                                    <tr>
                                        <th style="width:40%">Category</th>
                                        <th>Particulars</th>
                                        <th class="text-end" style="width:140px;">Amount (₹)</th>
                                    </tr>
                                </thead>
                                <tbody id="expenseBody">
                                    <tr><td colspan="3" class="text-center text-muted">No expense entries</td></tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2" class="text-end">Total Expense</th>
                                        <th class="text-end" id="footExpense">₹0.00</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="pnl-net-box">
                            <span class="label" id="netLabel">Net Profit</span>
                            <span class="value" id="netValue">₹0.00</span>
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

function renderRows(list) {
    if (!list || !list.length) {
        return '<tr><td colspan="3" class="text-center text-muted">No entries</td></tr>';
    }
    var html = '';
    list.forEach(function (row) {
        html += '<tr>' +
            '<td>' + escapeHtml(row.sCategory || '—') + '</td>' +
            '<td>' + escapeHtml(row.sParticulars || '') +
                (row.sReference ? ' <small class="text-muted">(' + escapeHtml(row.sReference) + ')</small>' : '') +
                ' <small class="text-muted">' + escapeHtml(formatDate(row.sEntrydate)) + '</small>' +
            '</td>' +
            '<td class="text-end">' + formatMoney(row.dAmount) + '</td>' +
        '</tr>';
    });
    return html;
}

function loadStatement() {
    var from = document.getElementById('filter_from').value;
    var to = document.getElementById('filter_to').value;
    $.ajax({
        url: 'api.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ action: 'getpnlstatement', from: from, to: to }),
        success: function (res) {
            if (res.status !== 'success') {
                alert(res.message || 'Failed to load statement');
                return;
            }
            var s = res.summary || {};
            document.getElementById('sumIncome').textContent = formatMoney(s.total_income);
            document.getElementById('sumExpense').textContent = formatMoney(s.total_expense);
            document.getElementById('footIncome').textContent = formatMoney(s.total_income);
            document.getElementById('footExpense').textContent = formatMoney(s.total_expense);

            var net = parseFloat(s.net_profit) || 0;
            var netText = formatMoney(net);
            var netEl = document.getElementById('sumNet');
            netEl.textContent = netText;
            netEl.style.color = net >= 0 ? '#047857' : '#c2410c';

            document.getElementById('netLabel').textContent = net >= 0 ? 'Net Profit' : 'Net Loss';
            var netValue = document.getElementById('netValue');
            netValue.textContent = netText;
            netValue.style.color = net >= 0 ? '#047857' : '#c2410c';

            document.getElementById('periodLabel').textContent =
                'Period: ' + formatDate(res.from) + ' to ' + formatDate(res.to);

            document.getElementById('incomeBody').innerHTML = renderRows(res.income);
            document.getElementById('expenseBody').innerHTML = renderRows(res.expense);
        },
        error: function () {
            alert('Failed to load P&L statement.');
        }
    });
}

$(document).ready(function () {
    var now = new Date();
    var first = new Date(now.getFullYear(), now.getMonth(), 1);
    function ymd(d) {
        var m = String(d.getMonth() + 1).padStart(2, '0');
        var day = String(d.getDate()).padStart(2, '0');
        return d.getFullYear() + '-' + m + '-' + day;
    }
    document.getElementById('filter_from').value = ymd(first);
    document.getElementById('filter_to').value = ymd(now);
    loadStatement();
});
</script>
</body>
</html>
