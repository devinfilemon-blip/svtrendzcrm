<?php
include 'layouts/session.php';
include 'layouts/head-main.php';
include 'layouts/config.php';
$isAdmin = (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'Admin');
?>

<head>
    <title>List Re-engage</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .success-message { color: green; font-weight: bold; }
        .error-message { color: red; font-weight: bold; }
        .btn-primary { color: #fff; background-color: #005aa5; border-color: #005aa5; }
        .table-responsive { overflow-x: auto; }
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
                            <h4 class="mb-sm-0 font-size-18">Re-engage List</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Re-engage</a></li>
                                    <li class="breadcrumb-item active">List</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <input type="date" id="filter_date" class="form-control form-control-sm" style="width:160px;" title="Filter by date">
                            <button type="button" class="btn btn-sm btn-secondary" onclick="clearFilter()">Clear</button>
                        </div>
                        <a href="add-reengage.php" class="btn btn-primary btn-sm"><i class="bx bx-user-voice"></i> Add Re-engage</a>
                    </div>
                </div>

                <div><span id="message"></span></div>
                <div class="table-responsive">
                    <table id="datatable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Sr No.</th>
                                <th>Date</th>
                                <th>User</th>
                                <th>Company</th>
                                <th>Description</th>
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

function formatDate(d) {
    if (!d) return '—';
    var parts = String(d).slice(0, 10).split('-');
    if (parts.length !== 3) return d;
    return parts[2] + '-' + parts[1] + '-' + parts[0];
}

function fngetlistreengage() {
    $.ajax({
        url: 'api.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            action: 'fngetlistreengage',
            date: document.getElementById('filter_date').value || ''
        }),
        success: function(response) {
            var list = (response && response.data) ? response.data : [];
            var rows = '';
            if (!list.length) {
                rows = '<tr><td colspan="7" class="text-center">No re-engage entries found</td></tr>';
            } else {
                list.forEach(function(r, index) {
                    rows += '<tr>' +
                        '<td>' + (index + 1) + '</td>' +
                        '<td>' + escapeHtml(formatDate(r.sDate)) + '</td>' +
                        '<td>' + escapeHtml(r.user_name || '—') + '</td>' +
                        '<td>' + escapeHtml(r.sCompanyname || '—') + '</td>' +
                        '<td>' + escapeHtml(r.sDescription || '—') + '</td>' +
                        '<td><form action="add-reengage.php" method="post">' +
                            '<input type="hidden" name="id" value="' + r.iReengageid + '">' +
                            '<button type="submit" class="btn btn-success btn-sm">Edit</button></form></td>' +
                        '<td><button class="btn btn-danger btn-sm" onclick="deletereengage(' + r.iReengageid + ')">Delete</button></td>' +
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
            alert('Failed to load re-engage list.');
        }
    });
}

function clearFilter() {
    document.getElementById('filter_date').value = '';
    fngetlistreengage();
}

function deletereengage(id) {
    if (!id) return;
    if (!confirm('Delete this re-engage entry?')) return;
    fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'deletereengage', id: id })
    })
    .then(function (r) { return r.json(); })
    .then(function (res) {
        var el = document.getElementById('message');
        el.innerHTML = res.message || '';
        el.className = (res.status === 'success') ? 'success-message' : 'error-message';
        fngetlistreengage();
    });
}

$(document).ready(function () {
    var params = new URLSearchParams(window.location.search);
    if (params.get('today') === '1') {
        var today = new Date();
        var y = today.getFullYear();
        var m = String(today.getMonth() + 1).padStart(2, '0');
        var d = String(today.getDate()).padStart(2, '0');
        document.getElementById('filter_date').value = y + '-' + m + '-' + d;
    }
    fngetlistreengage();
    $('#filter_date').on('change', fngetlistreengage);
});
</script>
</body>
</html>
