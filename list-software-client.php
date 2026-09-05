<?php
include 'layouts/session.php';
include 'layouts/head-main.php';
include 'layouts/config.php';
?>

<head>
    <title>Software Clients</title>
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
        .crm-badge-active { background: #ecfdf5; color: #047857; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .crm-badge-inactive { background: #f1f5f9; color: #64748b; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .crm-badge-suspended { background: #fff7ed; color: #c2410c; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .table-responsive { overflow-x: auto; }
        html.crm-dark .pay-summary-card, body.crm-dark .pay-summary-card {
            background: #111827 !important;
            border-color: rgba(255,255,255,0.1) !important;
        }
        html.crm-dark .pay-summary-card h6, body.crm-dark .pay-summary-card h6 { color: #94a3b8 !important; }
        html.crm-dark .pay-summary-total .pay-value, body.crm-dark .pay-summary-total .pay-value { color: #c4b5fd !important; }
        html.crm-dark .pay-summary-received .pay-value, body.crm-dark .pay-summary-received .pay-value { color: #4ade80 !important; }
        html.crm-dark .pay-summary-pending .pay-value, body.crm-dark .pay-summary-pending .pay-value { color: #fb923c !important; }
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
                            <h4 class="mb-sm-0 font-size-18">Client Management — Software Clients</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Client Management</a></li>
                                    <li class="breadcrumb-item active">List Clients</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-3 g-3">
                    <div class="col-md-4">
                        <div class="pay-summary-card pay-summary-total">
                            <h6>Total Clients</h6>
                            <p class="pay-value" id="sumTotal">0</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="pay-summary-card pay-summary-received">
                            <h6>Active Clients</h6>
                            <p class="pay-value" id="sumActive">0</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="pay-summary-card pay-summary-pending">
                            <h6>Monthly Revenue (Active)</h6>
                            <p class="pay-value" id="sumRevenue">₹0.00</p>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <select id="filter_status" class="form-select form-select-sm" style="width:auto;">
                                <option value="">All Status</option>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                                <option value="Suspended">Suspended</option>
                            </select>
                            <input type="text" id="filter_search" class="form-control form-control-sm" placeholder="Search client…" style="width:180px;">
                            <button type="button" class="btn btn-sm btn-secondary" onclick="clearFilters()">Clear</button>
                        </div>
                        <a href="add-software-client.php" class="btn btn-primary btn-sm">+ Add Client</a>
                    </div>
                </div>

                <div><span id="message"></span></div>
                <div class="table-responsive">
                    <table id="datatable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Sr No.</th>
                                <th>Client</th>
                                <th>Contact</th>
                                <th>Phone</th>
                                <th>Plan</th>
                                <th>Monthly ₹</th>
                                <th>Billing Day</th>
                                <th>Status</th>
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

function statusBadge(status) {
    var cls = 'crm-badge-inactive';
    if (status === 'Active') cls = 'crm-badge-active';
    else if (status === 'Suspended') cls = 'crm-badge-suspended';
    return '<span class="' + cls + '">' + escapeHtml(status || 'Inactive') + '</span>';
}

function clearFilters() {
    $('#filter_status').val('');
    $('#filter_search').val('');
    loadList();
}

function loadList() {
    $.ajax({
        url: 'api.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            action: 'fngetlistsoftwareclient',
            status: $('#filter_status').val(),
            search: $('#filter_search').val()
        }),
        success: function(res) {
            if (res.status !== 'success') {
                $('#datatable tbody').html('<tr><td colspan="10">No clients found</td></tr>');
                return;
            }
            var rows = res.data || [];
            var s = res.summary || {};
            $('#sumTotal').text(s.total_clients || 0);
            $('#sumActive').text(s.active_clients || 0);
            $('#sumRevenue').text(formatMoney(s.monthly_revenue || 0));

            var html = '';
            rows.forEach(function(r, i) {
                html += '<tr>'
                    + '<td>' + (i + 1) + '</td>'
                    + '<td>' + escapeHtml(r.sClientname)
                    + (r.sCompanyname ? '<br><small class="text-muted">' + escapeHtml(r.sCompanyname) + '</small>' : '')
                    + '</td>'
                    + '<td>' + escapeHtml(r.sContactperson || '—') + '</td>'
                    + '<td>' + escapeHtml(r.sPhone || '—') + '</td>'
                    + '<td>' + escapeHtml(r.sPlan || '—') + '</td>'
                    + '<td>' + formatMoney(r.dMonthlyamount) + '</td>'
                    + '<td>' + escapeHtml(r.iBillingday || 1) + '</td>'
                    + '<td>' + statusBadge(r.sStatus) + '</td>'
                    + '<td><form action="add-software-client.php" method="post">'
                    + '<input type="hidden" name="id" value="' + r.iClientid + '">'
                    + '<button type="submit" class="btn btn-success btn-sm">Edit</button></form></td>'
                    + '<td><button class="btn btn-danger btn-sm" onclick="deleteClient(' + r.iClientid + ')">Delete</button></td>'
                    + '</tr>';
            });

            if ($.fn.DataTable.isDataTable('#datatable')) {
                $('#datatable').DataTable().destroy();
            }
            $('#datatable tbody').html(html || '<tr><td colspan="10">No clients found</td></tr>');
            if (rows.length) $('#datatable').DataTable({ order: [] });
        }
    });
}

function deleteClient(id) {
    if (!confirm('Delete this client? Monthly report history will be kept.')) return;
    $.ajax({
        url: 'api.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ action: 'deletesoftwareclient', id: id }),
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

$('#filter_status').on('change', loadList);
$('#filter_search').on('keyup', function(e) {
    if (e.key === 'Enter') loadList();
});
loadList();
</script>
