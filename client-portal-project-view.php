<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include 'layouts/config.php';
include_once 'layouts/crm-access.php';
if (!crmIsClient()) {
    header('Location: index.php');
    exit;
}

$projectId = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;
if ($projectId <= 0) {
    header('Location: client-portal-projects.php');
    exit;
}
?>

<head>
    <title>Project Details</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        #taskTableBody td { vertical-align: middle; }
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
                    <a href="client-portal-projects.php" class="btn btn-outline-secondary btn-sm"><i class="bx bx-arrow-back"></i> Back to My Projects</a>
                </div>

                <div class="crm-dash-hero mb-4">
                    <div class="crm-dash-hero-content">
                        <span class="crm-dash-greeting">Project</span>
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
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                                    <i class="bx bx-plus"></i> Add Task
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Due</th>
                                            <th>Status</th>
                                            <th class="crm-file-col">File</th>
                                            <th></th>
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

    <div class="modal fade" id="addTaskModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addTaskForm" onsubmit="return false;">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bx bx-task me-1"></i> Add Task</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small">This will be added to the task list for our team to pick up.</p>
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

    <div class="modal fade" id="editTaskModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editTaskForm" onsubmit="return false;">
                    <input type="hidden" id="editTaskId">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bx bx-edit me-1"></i> Edit Task</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editTaskTitle" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editTaskTitle" required>
                        </div>
                        <div class="mb-3">
                            <label for="editTaskDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="editTaskDescription" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editTaskDueDate" class="form-label">Date</label>
                            <input type="date" class="form-control" id="editTaskDueDate">
                        </div>
                        <div class="mb-3">
                            <label for="editTaskAttachment" class="form-label">Attachment</label>
                            <div id="editTaskCurrentAttachment" class="small mb-2"></div>
                            <input type="file" class="form-control" id="editTaskAttachment" name="attachment"
                                   accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.png,.jpg,.jpeg,.gif,.zip,.txt,.csv">
                            <div class="form-text">Optional. Choosing a file replaces the current attachment. Max 10 MB.</div>
                            <div class="form-check mt-2" id="editTaskRemoveAttachmentWrap" style="display:none;">
                                <input class="form-check-input" type="checkbox" id="editTaskRemoveAttachment">
                                <label class="form-check-label" for="editTaskRemoveAttachment">Remove current attachment</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="editTaskSubmitBtn">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
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
var PROJECT_ID = <?php echo (int)$projectId; ?>;
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
            setTimeout(function () { window.location.href = 'client-portal-projects.php'; }, 1200);
            return;
        }
        var p = res.data;
        document.getElementById('projectTitle').textContent = p.sProjectName;
        document.getElementById('projectMeta').textContent = 'Status: ' + p.sStatus + (p.dDueDate ? ('  ·  Due: ' + p.dDueDate) : '');
    });
}

function statusBadge(status) {
    var cls = status === 'Done' ? 'bg-success' : (status === 'In Progress' ? 'bg-warning' : 'bg-secondary');
    return '<span class="badge ' + cls + '">' + escapeHtml(status) + '</span>';
}

var TASKS_BY_ID = {};

function loadTasks() {
    return api({ action: 'list_client_project_tasks', project_id: PROJECT_ID }).then(function (res) {
        var tasks = res.status === 'success' ? (res.data || []) : [];
        document.getElementById('taskCountLabel').textContent = tasks.length + (tasks.length === 1 ? ' task' : ' tasks');
        var $body = $('#taskTableBody');
        $body.empty();
        TASKS_BY_ID = {};

        if (!tasks.length) {
            $body.html('<tr><td colspan="5" class="text-center text-muted">No tasks yet</td></tr>');
            return;
        }
        tasks.forEach(function (t) {
            TASKS_BY_ID[t.id] = t;
            var isOwn = Number(t.sCreated_by) === Number(CURRENT_USER);
            var actionsCell = isOwn
                ? '<button type="button" class="btn btn-sm btn-outline-secondary me-1" data-task-edit="' + t.id + '" title="Edit task"><i class="bx bx-edit"></i></button>' +
                  '<button type="button" class="btn btn-sm btn-outline-danger" data-task-del="' + t.id + '" title="Delete task"><i class="bx bx-trash"></i></button>'
                : '';
            var fileCell = t.hasAttachment
                ? '<a href="download-client-task-attachment.php?id=' + t.id + '" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary crm-file-btn" title="' + escapeHtml(t.sAttachmentName || 'Download attachment') + '"><i class="bx bx-download"></i></a>'
                : '<span class="text-muted">—</span>';
            $body.append(
                '<tr>' +
                    '<td><strong>' + escapeHtml(t.sTitle) + '</strong>' + (t.sDescription ? '<div class="text-muted small">' + escapeHtml(t.sDescription) + '</div>' : '') + '</td>' +
                    '<td>' + escapeHtml(t.sDue_date || '—') + '</td>' +
                    '<td>' + statusBadge(t.sStatus) + '</td>' +
                    '<td class="crm-file-col">' + fileCell + '</td>' +
                    '<td class="text-end">' + actionsCell + '</td>' +
                '</tr>'
            );
        });
    });
}

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

$('#taskTableBody').on('click', '[data-task-edit]', function () {
    var id = $(this).data('task-edit');
    var t = TASKS_BY_ID[id];
    if (!t) return;
    $('#editTaskId').val(t.id);
    $('#editTaskTitle').val(t.sTitle);
    $('#editTaskDescription').val(t.sDescription || '');
    $('#editTaskDueDate').val(t.sDue_date || '');
    $('#editTaskAttachment').val('');
    $('#editTaskRemoveAttachment').prop('checked', false);
    if (t.hasAttachment) {
        $('#editTaskCurrentAttachment').html(
            '<i class="bx bx-paperclip"></i> Current: <a href="download-client-task-attachment.php?id=' + t.id + '" target="_blank" rel="noopener">' +
            escapeHtml(t.sAttachmentName || 'Attachment') + '</a>'
        );
        $('#editTaskRemoveAttachmentWrap').show();
    } else {
        $('#editTaskCurrentAttachment').html('<span class="text-muted">No attachment</span>');
        $('#editTaskRemoveAttachmentWrap').hide();
    }
    var modalEl = document.getElementById('editTaskModal');
    var modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    modal.show();
});

$('#editTaskForm').on('submit', function () {
    var title = $('#editTaskTitle').val().trim();
    if (!title) return;

    var $btn = $('#editTaskSubmitBtn');
    $btn.prop('disabled', true).text('Saving…');

    var formData = new FormData();
    formData.append('action', 'update_client_project_task');
    formData.append('id', $('#editTaskId').val());
    formData.append('sTitle', title);
    formData.append('sDescription', $('#editTaskDescription').val().trim());
    formData.append('sDue_date', $('#editTaskDueDate').val());
    if ($('#editTaskRemoveAttachment').is(':checked')) {
        formData.append('remove_attachment', '1');
    }
    var fileInput = document.getElementById('editTaskAttachment');
    if (fileInput && fileInput.files && fileInput.files[0]) {
        formData.append('attachment', fileInput.files[0]);
    }

    fetch('client-project-api.php', { method: 'POST', body: formData })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            $btn.prop('disabled', false).text('Save Changes');
            showMsg(res.message || '', res.status === 'success');
            if (res.status === 'success') {
                var modalEl = document.getElementById('editTaskModal');
                var modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modal.hide();
                loadTasks();
            }
        }).catch(function () {
            $btn.prop('disabled', false).text('Save Changes');
            showMsg('Network error while saving task.', false);
        });
});

$('#taskTableBody').on('click', '[data-task-del]', function () {
    var id = $(this).data('task-del');
    if (!confirm('Delete this task?')) return;
    api({ action: 'delete_client_project_task', id: id }).then(function (res) {
        showMsg(res.message || '', res.status === 'success');
        if (res.status === 'success') { loadTasks(); }
    });
});

$(document).ready(function () {
    loadProject();
    loadTasks();
});
</script>
</html>
