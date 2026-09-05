<?php
include 'layouts/session.php';
include 'layouts/head-main.php';
include 'layouts/config.php';
include 'layouts/crm-access.php';
crmRequireAdmin($link);
?>
<head>
    <title>List Joining Letters</title>
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
                            <h4 class="mb-sm-0 font-size-18">Joining Letters</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">HR Letters</a></li>
                                    <li class="breadcrumb-item active">List Joining Letters</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12 text-end">
                        <a href="generate-joining-letter.php" class="btn btn-primary btn-sm">+ Add Joining Letter</a>
                    </div>
                </div>

                <div class="table-responsive">
                    <div><span id="message"></span></div>
                    <table id="datatable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Sr No.</th>
                                <th>Ref No.</th>
                                <th>Date</th>
                                <th>Employee</th>
                                <th>Designation</th>
                                <th>Joining Date</th>
                                <th>Manager</th>
                                <th>Print</th>
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

function loadJoiningLetters() {
    $.ajax({
        url: 'hr-letter-api.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ action: 'listjoiningletters' }),
        success: function(response) {
            var rows = '';
            if (response.status === 'success') {
                var list = response.data || [];
                if (!list.length) {
                    rows = '<tr><td colspan="10" class="text-center">No joining letters found</td></tr>';
                } else {
                    list.forEach(function(r, index) {
                        var name = escapeHtml((r.sEmployeeTitle || '') + ' ' + (r.sEmployeeName || '')).trim();
                        rows += '<tr>';
                        rows += '<td>' + (index + 1) + '</td>';
                        rows += '<td>' + escapeHtml(r.sRefNo || '') + '</td>';
                        rows += '<td>' + escapeHtml(r.sLetterDate || '') + '</td>';
                        rows += '<td>' + name + '</td>';
                        rows += '<td>' + escapeHtml(r.sDesignation || '') + '</td>';
                        rows += '<td>' + escapeHtml(r.sJoiningDate || '') + '</td>';
                        rows += '<td>' + escapeHtml(r.sReportingManager || '-') + '</td>';
                        rows += '<td><a class="btn btn-info btn-sm" target="_blank" href="print-joining-letter.php?id=' + r.iLetterid + '">Print</a></td>';
                        rows += '<td><form action="generate-joining-letter.php" method="post">' +
                            '<input type="hidden" name="id" value="' + r.iLetterid + '">' +
                            '<button type="submit" class="btn btn-success btn-sm">Edit</button></form></td>';
                        rows += '<td><button class="btn btn-danger btn-sm" onclick="deleteLetter(' + r.iLetterid + ')">Delete</button></td>';
                        rows += '</tr>';
                    });
                }
            } else {
                rows = '<tr><td colspan="10" class="text-center">No joining letters found</td></tr>';
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
            alert('Failed to load joining letters.');
        }
    });
}

function deleteLetter(id) {
    if (!id || !confirm('Delete this joining letter?')) return;
    fetch('hr-letter-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'deletehrletter', id: id })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        var el = document.getElementById('message');
        el.innerHTML = res.message || '';
        el.className = res.status === 'success' ? 'success-message' : 'error-message';
        loadJoiningLetters();
    });
}

$(document).ready(loadJoiningLetters);
</script>
</body>
</html>
