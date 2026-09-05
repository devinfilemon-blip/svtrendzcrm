<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include 'layouts/config.php';
include_once 'layouts/crm-access.php';
if (!crmIsClient()) {
    header('Location: index.php');
    exit;
}
?>

<head>
    <title>My Projects</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .crm-project-row { cursor: pointer; }
        .crm-task-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 600;
            color: var(--crm-text-secondary, #4c4585);
        }
        .crm-task-pill strong { color: var(--crm-primary, #9333ea); }
    </style>
</head>
<?php include 'layouts/body.php'; ?>

<div id="layout-wrapper">
    <?php include 'layouts/menu.php'; ?>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="crm-dash-hero mb-4">
                    <div class="crm-dash-hero-content">
                        <span class="crm-dash-greeting">Welcome,</span>
                        <h2><?php echo htmlspecialchars($_SESSION['username'] ?? 'Client'); ?></h2>
                        <p>Track the progress of your project(s) below.</p>
                    </div>
                </div>

                <div id="message" class="mb-3"></div>

                <div class="crm-panel-card mb-0">
                    <div class="table-responsive">
                        <table id="datatable" class="table table-hover crm-leads-table mb-0">
                            <thead>
                                <tr>
                                    <th>Sr No.</th>
                                    <th>Project Name</th>
                                    <th>Status</th>
                                    <th>Tasks</th>
                                    <th>Due Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
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
<script>function checkTokenStatus() {
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
</script>
<script>
function escapeHtml(str) {
    return String(str == null ? '' : str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function statusBadge(status) {
    var cls = status === 'Completed' ? 'bg-success' : (status === 'On Hold' ? 'bg-warning' : 'bg-primary');
    return '<span class="badge ' + cls + '">' + escapeHtml(status) + '</span>';
}

function openProject(id) {
    window.location.href = 'client-portal-project-view.php?project_id=' + encodeURIComponent(id);
}

function loadProjects() {
    fetch('client-project-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'list_client_projects' })
    })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if ($.fn.DataTable.isDataTable('#datatable')) {
                $('#datatable').DataTable().destroy();
            }
            var $tbody = $('#datatable tbody');
            $tbody.empty();

            if (!res || res.status !== 'success') {
                $tbody.html('<tr><td colspan="6" class="text-center text-muted">Failed to load projects</td></tr>');
                return;
            }

            var rows = res.data || [];

            if (!rows.length) {
                $tbody.html('<tr><td colspan="6" class="text-center text-muted">No projects assigned to you yet</td></tr>');
                return;
            }

            rows.forEach(function (p, idx) {
                var tasks = (p.task_done || 0) + ' / ' + (p.task_total || 0);
                $tbody.append(
                    '<tr class="crm-project-row" data-id="' + p.iId + '">' +
                        '<td>' + (idx + 1) + '</td>' +
                        '<td><strong>' + escapeHtml(p.sProjectName) + '</strong></td>' +
                        '<td>' + statusBadge(p.sStatus) + '</td>' +
                        '<td><span class="crm-task-pill"><i class="bx bx-task"></i><strong>' + escapeHtml(tasks) + '</strong> done</span></td>' +
                        '<td>' + escapeHtml(p.dDueDate || '—') + '</td>' +
                        '<td><a href="client-portal-project-view.php?project_id=' + p.iId + '" class="btn btn-sm btn-primary" onclick="event.stopPropagation()"><i class="bx bx-show"></i> View</a></td>' +
                    '</tr>'
                );
            });

            $('#datatable').DataTable({ pageLength: 25, order: [] });
        });
}

$(document).ready(function () {
    loadProjects();
    $('#datatable').on('click', 'tbody tr.crm-project-row', function () {
        var id = $(this).data('id');
        if (id) openProject(id);
    });
});
</script>
</html>
