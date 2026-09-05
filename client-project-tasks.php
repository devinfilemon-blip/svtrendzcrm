<?php
include 'layouts/session.php';
include 'layouts/head-main.php';
include 'layouts/config.php';
include_once 'layouts/crm-access.php';
crmRequireLoggedIn();
$crmIsAdminPage = crmIsAdmin();

$projectId = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;
if ($projectId <= 0) {
    header('Location: list-client-project.php');
    exit;
}
?>

<head>
    <title>Client Project Tasks</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .crm-cpm-task-row td { vertical-align: middle; }
        .crm-file-col { width: 70px; text-align: center; }
        .crm-file-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            padding: 0;
        }
    </style>
</head>
<?php include 'layouts/body.php'; ?>

<div id="layout-wrapper">
    <?php include 'layouts/menu.php'; ?>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                    <a href="list-client-project.php" class="btn btn-outline-secondary btn-sm"><i class="bx bx-arrow-back"></i> Back to Client Projects</a>
                </div>

                <div class="crm-dash-hero mb-4">
                    <div class="crm-dash-hero-content">
                        <span class="crm-dash-greeting">Client Project</span>
                        <h2 id="projectTitle">Loading…</h2>
                        <p id="projectMeta"></p>
                    </div>
                    <div class="crm-dash-hero-meta">
                        <span class="badge bg-soft-primary text-primary" id="taskCountLabel">0 tasks</span>
                    </div>
                </div>

                <div id="message" class="mb-3"></div>

                <div class="row">
                    <div class="col-xl-12">
                        <div class="crm-panel-card mb-0">
                            <div class="crm-panel-card-head">
                                <div><h6><i class="bx bx-task me-1"></i> Tasks</h6></div>
                                <?php if ($crmIsAdminPage) : ?>
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                                    <i class="bx bx-plus"></i> Add Task
                                </button>
                                <?php endif; ?>
                            </div>
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Assigned To</th>
                                            <th>Due</th>
                                            <th>Status</th>
                                            <th class="crm-file-col">File</th>
                                        </tr>
                                    </thead>
                                    <tbody id="taskTableBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <?php include 'layouts/footer.php'; ?>
    </div>

    <?php if ($crmIsAdminPage) : ?>
    <div class="modal fade" id="addTaskModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addTaskForm" onsubmit="return false;">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bx bx-task me-1"></i> Add Task</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="taskTitle" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="taskTitle" placeholder="e.g. Update the homepage banner" required>
                        </div>
                        <div class="mb-3">
                            <label for="taskDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="taskDescription" rows="3" placeholder="Any details that would help (optional)"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="taskDueDate" class="form-label">Date</label>
                            <input type="date" class="form-control" id="taskDueDate">
                        </div>
                        <div class="mb-3">
                            <label for="taskAttachment" class="form-label">Attachment</label>
                            <input type="file" class="form-control" id="taskAttachment" name="attachment"
                                   accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.png,.jpg,.jpeg,.gif,.zip,.txt,.csv">
                            <div class="form-text">Optional. Max 10 MB. PDF, Office docs, images, zip, txt, csv.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="addTaskSubmitBtn">Add Task</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
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
var PROJECT_ID = <?php echo (int)$projectId; ?>;
var IS_ADMIN = <?php echo $crmIsAdminPage ? 'true' : 'false'; ?>;
var CURRENT_USER = <?php echo (int)($_SESSION['user_id'] ?? 0); ?>;

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
        escapeHtml(text) + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    if (ok) setTimeout(function () { document.getElementById('message').innerHTML = ''; }, 2500);
}

function api(payload) {
    return fetch('client-project-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    }).then(function (r) { return r.json(); });
}

function loadProject() {
    return api({ action: 'get_client_project', project_id: PROJECT_ID }).then(function (res) {
        if (res.status !== 'success') {
            showMsg(res.message || 'Project not found', false);
            setTimeout(function () { window.location.href = 'list-client-project.php'; }, 1200);
            return;
        }
        var p = res.data;
        document.getElementById('projectTitle').textContent = p.sProjectName;
        document.getElementById('projectMeta').textContent = 'Client: ' + (p.client_name || '—') + '  ·  Status: ' + p.sStatus;
    });
}

function renderTasks(tasks) {
    var $body = $('#taskTableBody');
    $body.empty();
    document.getElementById('taskCountLabel').textContent = tasks.length + (tasks.length === 1 ? ' task' : ' tasks');

    if (!tasks.length) {
        $body.html('<tr><td colspan="5" class="text-center text-muted">No tasks yet</td></tr>');
        return;
    }
    tasks.forEach(function (t) {
        var statusCell;
        if (IS_ADMIN) {
            var statusOpts = ['Pending', 'In Progress', 'Done'].map(function (s) {
                return '<option value="' + s + '"' + (s === t.sStatus ? ' selected' : '') + '>' + s + '</option>';
            }).join('');
            statusCell = '<select class="form-select form-select-sm crm-task-status" data-task-id="' + t.id + '">' + statusOpts + '</select>';
        } else {
            var cls = t.sStatus === 'Done' ? 'bg-success' : (t.sStatus === 'In Progress' ? 'bg-warning' : 'bg-secondary');
            statusCell = '<span class="badge ' + cls + '">' + escapeHtml(t.sStatus) + '</span>';
        }
        var fileCell = t.hasAttachment
            ? '<a href="download-client-task-attachment.php?id=' + t.id + '" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary crm-file-btn" title="' + escapeHtml(t.sAttachmentName || 'Download attachment') + '"><i class="bx bx-download"></i></a>'
            : '<span class="text-muted">—</span>';
        $body.append(
            '<tr class="crm-cpm-task-row">' +
                '<td><strong>' + escapeHtml(t.sTitle) + '</strong>' + (t.sDescription ? '<div class="text-muted small">' + escapeHtml(t.sDescription) + '</div>' : '') + '</td>' +
                '<td>' + escapeHtml(t.assigned_name || '—') + '</td>' +
                '<td>' + escapeHtml(t.sDue_date || '—') + '</td>' +
                '<td>' + statusCell + '</td>' +
                '<td class="crm-file-col">' + fileCell + '</td>' +
            '</tr>'
        );
    });
}

function loadTasks() {
    return api({ action: 'list_client_project_tasks', project_id: PROJECT_ID }).then(function (res) {
        renderTasks(res.status === 'success' ? (res.data || []) : []);
    });
}

$('#taskTableBody').on('change', '.crm-task-status', function () {
    var id = $(this).data('task-id');
    var status = $(this).val();
    api({ action: 'update_client_project_task', id: id, sStatus: status }).then(function (res) {
        showMsg(res.message || '', res.status === 'success');
        loadTasks();
    });
});

$('#addTaskForm').on('submit', function () {
    var title = $('#taskTitle').val().trim();
    if (!title) return;

    var $btn = $('#addTaskSubmitBtn');
    $btn.prop('disabled', true).text('Adding…');

    var formData = new FormData();
    formData.append('action', 'add_client_project_task');
    formData.append('project_id', PROJECT_ID);
    formData.append('sTitle', title);
    formData.append('sDescription', $('#taskDescription').val().trim());
    formData.append('sDue_date', $('#taskDueDate').val());
    var fileInput = document.getElementById('taskAttachment');
    if (fileInput && fileInput.files && fileInput.files[0]) {
        formData.append('attachment', fileInput.files[0]);
    }

    fetch('client-project-api.php', { method: 'POST', body: formData })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            $btn.prop('disabled', false).text('Add Task');
            showMsg(res.message || '', res.status === 'success');
            if (res.status === 'success') {
                $('#addTaskForm')[0].reset();
                var modalEl = document.getElementById('addTaskModal');
                var modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modal.hide();
                loadTasks();
            }
        }).catch(function () {
            $btn.prop('disabled', false).text('Add Task');
            showMsg('Network error while adding task.', false);
        });
});

$(document).ready(function () {
    loadProject();
    loadTasks();
});
</script>
</html>
