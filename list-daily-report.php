<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include 'layouts/config.php';
$isAdmin = (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'Admin');
?>

<head>
    <title>List Daily Report</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .success-message { color: green; font-weight: bold; }
        .error-message { color: red; font-weight: bold; }
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .work-preview {
            max-width: 220px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .btn-primary {
            color: #fff;
            background-color: #005aa5;
            border-color: #005aa5;
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
                            <h4 class="mb-sm-0 font-size-18">Daily Reports</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Daily Report</a></li>
                                    <li class="breadcrumb-item active">List Daily Report</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex gap-2 align-items-center">
                            <label for="filter_date" class="mb-0">Filter by date:</label>
                            <input type="date" id="filter_date" class="form-control form-control-sm" style="width:auto;">
                            <button type="button" class="btn btn-sm btn-secondary" onclick="clearFilter()">Clear</button>
                        </div>
                        <a href="add-daily-report.php" class="btn btn-primary btn-sm">+ Add Daily Report</a>
                    </div>
                </div>

                <div class="table-responsive">
                    <div><span id="message"></span></div>
                    <table id="datatable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Sr No.</th>
                                <?php if ($isAdmin): ?><th>Employee</th><?php endif; ?>
                                <th>Date</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                                <th>Tasks Completed</th>
                                <th>Work Details</th>
                                <th>Pending</th>
                                <th>View / Edit</th>
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
var isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;

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
        error: function() {
            window.location.href = 'auth-login.php';
        }
    });
}
checkTokenStatus();

function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function previewText(text) {
    var t = text || '';
    if (t.length > 60) t = t.substring(0, 60) + '...';
    return escapeHtml(t);
}

function fngetlistdailyreport() {
    var filterDate = document.getElementById('filter_date').value || '';
    $.ajax({
        url: 'api.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ action: 'fngetlistdailyreport', date: filterDate }),
        success: function(response) {
            if (response.status === 'success') {
                var reports = response.data || [];
                var rows = '';
                var colCount = isAdmin ? 10 : 9;

                if (!reports.length) {
                    rows = '<tr><td colspan="' + colCount + '" class="text-center">No daily reports found</td></tr>';
                } else {
                    reports.forEach(function(r, index) {
                        rows += '<tr>';
                        rows += '<td>' + (index + 1) + '</td>';
                        if (isAdmin) {
                            rows += '<td>' + escapeHtml(r.sName || '-') + '</td>';
                        }
                        rows += '<td>' + escapeHtml(r.sDate || '') + '</td>';
                        rows += '<td>' + escapeHtml(r.sTimeIn || '-') +
                            (parseInt(r.iTimeInChecked || 0, 10) === 1 ? ' <span class="text-success" title="Checked">✓</span>' : '') +
                            '</td>';
                        rows += '<td>' + escapeHtml(r.sTimeOut || '-') +
                            (parseInt(r.iTimeOutChecked || 0, 10) === 1 ? ' <span class="text-success" title="Checked">✓</span>' : '') +
                            '</td>';
                        rows += '<td><div class="work-preview" title="' + escapeHtml(r.sTasksCompleted || '') + '">' + previewText(r.sTasksCompleted) + '</div></td>';
                        rows += '<td><div class="work-preview" title="' + escapeHtml(r.sWorkDetails || '') + '">' + previewText(r.sWorkDetails) + '</div></td>';
                        rows += '<td><div class="work-preview" title="' + escapeHtml(r.sPending || '') + '">' + previewText(r.sPending) + '</div></td>';
                        rows += '<td><form action="add-daily-report.php" method="post">' +
                            '<input type="hidden" name="id" value="' + r.iReportid + '">' +
                            '<button type="submit" class="btn btn-success btn-sm">Edit</button></form></td>';
                        rows += '<td><button class="btn btn-danger btn-sm" onclick="deletedailyreport(' + r.iReportid + ')">Delete</button></td>';
                        rows += '</tr>';
                    });
                }

                if ($.fn.DataTable.isDataTable('#datatable')) {
                    $('#datatable').DataTable().destroy();
                }
                $('#datatable tbody').html(rows);
                if (reports.length) {
                    $('#datatable').DataTable({ order: [[isAdmin ? 2 : 1, 'desc']] });
                }
            } else {
                $('#datatable tbody').html('<tr><td colspan="10" class="text-center">No daily reports found</td></tr>');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            alert('An error occurred while fetching reports.');
        }
    });
}

function clearFilter() {
    document.getElementById('filter_date').value = '';
    fngetlistdailyreport();
}

function deletedailyreport(id) {
    if (!id) {
        alert('Invalid report ID.');
        return false;
    }
    if (!confirm('Are you sure you want to delete this daily report?')) {
        return false;
    }
    fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'deletedailyreport', id: id })
    })
    .then(function(r) { return r.json(); })
    .then(function(responseData) {
        var el = document.getElementById('message');
        el.innerHTML = responseData.message || '';
        el.className = (responseData.status === 'success') ? 'success-message' : 'error-message';
        fngetlistdailyreport();
    })
    .catch(function(error) { console.error('Error:', error); });
}

$(document).ready(function() {
    fngetlistdailyreport();
    $('#filter_date').on('change', fngetlistdailyreport);
});
</script>
</body>
</html>
