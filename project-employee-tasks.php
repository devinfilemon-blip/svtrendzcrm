<?php
include 'layouts/session.php';
include 'layouts/head-main.php';
include 'layouts/config.php';
?>

<head>
    <title>Employee Tasks</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .pm-stat-row {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 20px;
        }
        .pm-stat-tile {
            flex: 1 1 160px;
            border-radius: 10px;
            padding: 14px;
            background: var(--crm-soft-bg, #f8f7fc);
        }
        .pm-stat-tile .pm-stat-value {
            font-size: 22px;
            font-weight: 700;
        }
        .pm-stat-tile .pm-stat-label {
            font-size: 12px;
            color: var(--crm-text-secondary, #6b7280);
        }
        .pm-task-row {
            cursor: pointer;
        }
        .pm-task-row:hover {
            background: var(--crm-soft-bg, #f8f7fc);
        }
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
                        <span class="crm-dash-greeting">Project Management</span>
                        <h2>Employee Tasks</h2>
                        <p>All tasks assigned to an employee across every project, with travel &amp; report details.</p>
                    </div>
                </div>

                <div id="message" class="mb-3"></div>

                <div class="crm-panel-card mb-3" id="employeeFilterCard" style="display:none;">
                    <div class="p-3 d-flex flex-wrap align-items-end gap-3">
                        <div>
                            <label class="form-label mb-1">Employee</label>
                            <select class="form-select" id="employeeSelect" style="min-width:220px;">
                                <option value="0">All Employees</option>
                            </select>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="refreshBtn"><i class="bx bx-refresh"></i> Refresh</button>
                    </div>
                </div>

                <div class="pm-stat-row">
                    <div class="pm-stat-tile">
                        <div class="pm-stat-value" id="statTotal">0</div>
                        <div class="pm-stat-label">Total Tasks</div>
                    </div>
                    <div class="pm-stat-tile">
                        <div class="pm-stat-value" id="statDone">0</div>
                        <div class="pm-stat-label">Completed</div>
                    </div>
                    <div class="pm-stat-tile">
                        <div class="pm-stat-value" id="statDistance">0 km</div>
                        <div class="pm-stat-label">Total Distance Travelled</div>
                    </div>
                    <div class="pm-stat-tile">
                        <div class="pm-stat-value" id="statPetrol">₹0</div>
                        <div class="pm-stat-label">Total Petrol Cost (₹3/km)</div>
                    </div>
                </div>

                <div class="crm-panel-card mb-0">
                    <div class="crm-panel-card-head">
                        <div>
                            <h6><i class="bx bx-list-check me-1"></i> Tasks</h6>
                            <p class="text-muted mb-0">Click a row to open its start/end location, travel cost and report.</p>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th id="colEmployee" style="display:none;">Employee</th>
                                    <th>Project</th>
                                    <th>Task</th>
                                    <th>Status</th>
                                    <th>Due Date</th>
                                    <th>Distance</th>
                                    <th>Petrol Cost</th>
                                    <th>Report</th>
                                </tr>
                            </thead>
                            <tbody id="taskTableBody">
                                <tr><td colspan="8" class="text-center text-muted">Loading…</td></tr>
                            </tbody>
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
<script>
var IS_ADMIN = <?php echo (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'Admin') ? 'true' : 'false'; ?>;

function escapeHtml(str) {
    return String(str == null ? '' : str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function showMsg(text, ok) {
    document.getElementById('message').innerHTML =
        '<div class="alert alert-' + (ok ? 'success' : 'danger') + ' alert-dismissible fade show" role="alert">' +
        escapeHtml(text) +
        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
}

function api(payload) {
    return fetch('project-api.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    }).then(function (r) { return r.json(); });
}

function statusBadge(status) {
    var cls = status === 'Done' ? 'bg-soft-success text-success' : (status === 'In Progress' ? 'bg-soft-warning text-warning' : 'bg-soft-secondary text-secondary');
    return '<span class="badge ' + cls + '">' + escapeHtml(status) + '</span>';
}

function loadEmployees() {
    if (!IS_ADMIN) return;
    api({ action: 'list_assignable_employees' }).then(function (res) {
        if (!res || res.status !== 'success') return;
        var $sel = $('#employeeSelect');
        (res.data || []).forEach(function (u) {
            $sel.append('<option value="' + u.id + '">' + escapeHtml(u.name) + (u.role ? ' (' + escapeHtml(u.role) + ')' : '') + '</option>');
        });
        document.getElementById('employeeFilterCard').style.display = '';
    });
}

function renderTasks(tasks) {
    var $body = $('#taskTableBody');
    var showEmployeeCol = IS_ADMIN && Number($('#employeeSelect').val() || 0) === 0;
    document.getElementById('colEmployee').style.display = showEmployeeCol ? '' : 'none';

    if (!tasks.length) {
        $body.html('<tr><td colspan="8" class="text-center text-muted">No tasks found</td></tr>');
        return;
    }

    var html = '';
    tasks.forEach(function (t) {
        html += '<tr class="pm-task-row" data-open="' + t.id + '">' +
            (showEmployeeCol ? '<td>' + escapeHtml(t.assigned_name || '—') + '</td>' : '') +
            '<td>' + escapeHtml(t.project_name || ('Project #' + t.lead_id)) + '</td>' +
            '<td>' + escapeHtml(t.sTitle) + '</td>' +
            '<td>' + statusBadge(t.sStatus) + '</td>' +
            '<td>' + (t.sDue_date || '—') + '</td>' +
            '<td>' + (t.distance_km != null ? t.distance_km + ' km' : '—') + '</td>' +
            '<td>' + (t.petrol_cost != null ? '₹' + t.petrol_cost : '—') + '</td>' +
            '<td>' + (t.has_report ? '<span class="badge bg-soft-success text-success"><i class="bx bx-check-double"></i> Submitted</span>' : (t.has_started ? '<span class="badge bg-soft-warning text-warning"><i class="bx bx-navigation"></i> Started</span>' : '<span class="text-muted">—</span>')) + '</td>' +
        '</tr>';
    });
    $body.html(html);
}

function loadTasks() {
    var employeeId = IS_ADMIN ? parseInt($('#employeeSelect').val() || '0', 10) : 0;
    $('#taskTableBody').html('<tr><td colspan="8" class="text-center text-muted">Loading…</td></tr>');
    api({ action: 'list_employee_tasks', employee_id: employeeId }).then(function (res) {
        if (!res || res.status !== 'success') {
            $('#taskTableBody').html('<tr><td colspan="8" class="text-center text-danger">Unable to load tasks</td></tr>');
            return;
        }
        renderTasks(res.data || []);
        var s = res.summary || {};
        document.getElementById('statTotal').textContent = s.total_tasks || 0;
        document.getElementById('statDone').textContent = s.total_done || 0;
        document.getElementById('statDistance').textContent = (s.total_distance_km || 0) + ' km';
        document.getElementById('statPetrol').textContent = '₹' + (s.total_petrol_cost || 0);
    });
}

$('#taskTableBody').on('click', '[data-open]', function () {
    window.location.href = 'project-task-detail.php?id=' + $(this).data('open');
});

$('#employeeSelect').on('change', loadTasks);
$('#refreshBtn').on('click', loadTasks);

$(document).ready(function () {
    loadEmployees();
    loadTasks();
});
</script>
</html>
