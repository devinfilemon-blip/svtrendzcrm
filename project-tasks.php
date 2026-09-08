<?php
include 'layouts/session.php';
include 'layouts/head-main.php';
include 'layouts/config.php';

$leadId = isset($_GET['lead_id']) ? (int)$_GET['lead_id'] : 0;
if ($leadId <= 0) {
    header('Location: project-management.php');
    exit;
}
?>

<head>
    <title>Project Tasks</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <link href="assets/css/crm-kanban.css?v=3" rel="stylesheet" type="text/css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .crm-task-kanban-layout {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: flex-start;
        }
        .crm-task-kanban-form {
            flex: 0 0 300px;
            max-width: 100%;
        }
        .crm-task-kanban-board-area {
            flex: 1 1 520px;
            min-width: 0;
        }
        .crm-task-kanban-board-area .crm-kanban-board-wrap {
            margin: 0;
        }
        .crm-task-kanban-board-area .crm-kanban-column {
            flex-basis: 260px;
            min-width: 240px;
            max-width: 280px;
        }
        .crm-task-card-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 8px;
            gap: 6px;
        }
        .crm-task-card-actions .btn {
            padding: 2px 8px;
        }
        .crm-kanban-card[data-locked="1"] {
            cursor: default;
            opacity: 0.92;
        }
        .crm-task-due-overdue {
            color: #dc2626;
            font-weight: 600;
        }
        @media (max-width: 991.98px) {
            .crm-task-kanban-form {
                flex: 1 1 100%;
            }
        }
        .pm-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 16px;
            border-bottom: 1px solid var(--crm-border, #e5e7eb);
        }
        .pm-tab-btn {
            border: none;
            background: transparent;
            padding: 10px 16px;
            font-weight: 600;
            font-size: 13px;
            color: var(--crm-text-secondary, #6b7280);
            border-bottom: 2px solid transparent;
            cursor: pointer;
        }
        .pm-tab-btn.active {
            color: var(--crm-primary, #9333ea);
            border-bottom-color: var(--crm-primary, #9333ea);
        }
        .pm-tab-panel { display: none; }
        .pm-tab-panel.active { display: block; }
        .pm-photo-thumb {
            width: 84px;
            height: 84px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid var(--crm-border, #e5e7eb);
            cursor: pointer;
        }
        .pm-visit-card, .pm-expense-summary {
            border: 1px solid var(--crm-border, #e5e7eb);
            border-radius: 10px;
            padding: 14px;
            margin-bottom: 12px;
        }
        .crm-approved-expense-badge {
            font-size: 20px;
            font-weight: 700;
            color: #dc2626;
            background: rgba(220, 38, 38, 0.1);
            padding: 6px 14px;
            border-radius: 8px;
        }
    </style>
</head>
<?php include 'layouts/body.php'; ?>
<script>document.body.classList.add('crm-kanban-page');</script>

<div id="layout-wrapper">
    <?php include 'layouts/menu.php'; ?>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                    <a href="project-management.php" class="btn btn-outline-secondary btn-sm"><i class="bx bx-arrow-back"></i> Back to Projects</a>
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="mineOnly">
                            <label class="form-check-label" for="mineOnly">Show only my tasks</label>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="refreshTasksBtn">
                            <i class="bx bx-refresh"></i> Refresh
                        </button>
                    </div>
                </div>

                <div class="crm-dash-hero mb-4">
                    <div class="crm-dash-hero-content">
                        <span class="crm-dash-greeting">Won Project</span>
                        <h2 id="projectTitle">Loading…</h2>
                        <p id="projectMeta">Drag task cards between columns to update status.</p>
                    </div>
                    <div class="crm-dash-hero-meta d-flex align-items-center gap-2">
                        <span class="crm-approved-expense-badge" id="approvedExpenseLabel">₹0 approved</span>
                        <span class="badge bg-soft-primary text-primary" id="taskCountLabel">0 tasks</span>
                    </div>
                </div>

                <div id="message" class="mb-3"></div>

                <div class="pm-tabs">
                    <button type="button" class="pm-tab-btn active" data-tab="tasks">Tasks</button>
                    <button type="button" class="pm-tab-btn" data-tab="team">Team</button>
                    <button type="button" class="pm-tab-btn" data-tab="entries">Expenses / Visits / Meetings</button>
                </div>

                <div class="pm-tab-panel active" id="pmTabTasks">
                <p class="crm-kanban-hint"><i class="bx bx-move"></i> Drag cards across Pending → In Progress → Done · Long-press on mobile</p>

                <div class="crm-task-kanban-layout">
                    <div class="crm-task-kanban-form">
                        <div class="crm-panel-card mb-0">
                            <div class="crm-panel-card-head">
                                <div>
                                    <h6><i class="bx bx-plus-circle me-1"></i> Add Task</h6>
                                    <p>Assign to project users only</p>
                                </div>
                            </div>
                            <div class="p-3">
                                <form id="taskForm">
                                    <div class="mb-3">
                                        <label class="form-label">Task Title</label>
                                        <input type="text" class="form-control" id="sTitle" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea class="form-control" id="sDescription" rows="3"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Assigned To</label>
                                        <select class="form-select" id="sAssigned_to" required></select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Due Date</label>
                                        <input type="date" class="form-control" id="sDue_date">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select class="form-select" id="sStatus">
                                            <option value="Pending">Pending</option>
                                            <option value="In Progress">In Progress</option>
                                            <option value="Done">Done</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100"><i class="bx bx-save"></i> Save Task</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="crm-task-kanban-board-area">
                        <div class="crm-kanban-board-wrap">
                            <div class="crm-kanban-board" id="taskKanbanBoard">
                                <div class="crm-kanban-loading">
                                    <i class="bx bx-loader-alt bx-spin"></i> Loading tasks…
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>

                <div class="pm-tab-panel" id="pmTabTeam">
                    <div class="crm-panel-card mb-0">
                        <div class="crm-panel-card-head d-flex justify-content-between align-items-center">
                            <div>
                                <h6><i class="bx bx-group me-1"></i> Project Team</h6>
                                <p>Employees assigned to this project</p>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="manageTeamBtn" style="display:none;"><i class="bx bx-user-plus"></i> Manage Team</button>
                        </div>
                        <div class="p-3" id="teamList">Loading…</div>
                    </div>
                </div>

                <div class="pm-tab-panel" id="pmTabEntries">
                    <div class="row g-3">
                        <div class="col-lg-4">
                            <div class="crm-panel-card mb-0">
                                <div class="crm-panel-card-head">
                                    <div>
                                        <h6><i class="bx bx-plus-circle me-1"></i> Add Entry</h6>
                                        <p>Log an expense, site visit or meeting</p>
                                    </div>
                                </div>
                                <div class="p-3">
                                    <form id="entryForm">
                                        <div class="mb-3">
                                            <label class="form-label">Entry Type</label>
                                            <select class="form-select" id="entryType" required>
                                                <option value="Expense">Expense</option>
                                                <option value="Visit">Site Visit</option>
                                                <option value="Meeting">Meeting</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Date</label>
                                            <input type="date" class="form-control" id="entryDate" required>
                                        </div>

                                        <div id="entryExpenseFields">
                                            <div class="mb-3">
                                                <label class="form-label">Category</label>
                                                <select class="form-select" id="expCategory">
                                                    <option value="Travel">Travel</option>
                                                    <option value="Food">Food</option>
                                                    <option value="Hotel">Hotel</option>
                                                    <option value="Other">Other</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Amount</label>
                                                <input type="number" step="0.01" min="0.01" class="form-control" id="expAmount">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Description</label>
                                                <textarea class="form-control" id="expDescription" rows="2"></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Receipt (optional)</label>
                                                <input type="file" class="form-control" id="expReceipt" accept=".pdf,.png,.jpg,.jpeg">
                                            </div>
                                        </div>

                                        <div id="entryVisitFields" style="display:none;">
                                            <div class="mb-3">
                                                <label class="form-label">Location</label>
                                                <input type="text" class="form-control" id="visitLocation">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Notes</label>
                                                <textarea class="form-control" id="visitNotes" rows="3"></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Photos (up to 6)</label>
                                                <input type="file" class="form-control" id="visitPhotos" accept="image/*" multiple>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-primary w-100"><i class="bx bx-save"></i> Submit</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-8 d-flex flex-column gap-3">
                            <div class="crm-panel-card mb-0">
                                <div class="crm-panel-card-head">
                                    <div>
                                        <h6><i class="bx bx-wallet me-1"></i> Expenses</h6>
                                        <p id="expenseSummary" class="text-muted"></p>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Category</th>
                                                <th>Amount</th>
                                                <th>By</th>
                                                <th>Status</th>
                                                <th>Receipt</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="expenseTableBody">
                                            <tr><td colspan="7" class="text-center text-muted">Loading…</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="crm-panel-card mb-0">
                                <div class="crm-panel-card-head">
                                    <div>
                                        <h6><i class="bx bx-map-pin me-1"></i> Visits &amp; Meetings</h6>
                                    </div>
                                </div>
                                <div class="p-3" id="visitList">Loading…</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <?php include 'layouts/footer.php'; ?>
    </div>
</div>

<div class="modal fade" id="assignTeamModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bx bx-user-plus me-1"></i> Manage Team</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="assignEmployeeList" class="d-flex flex-column gap-2" style="max-height: 320px; overflow-y: auto;">
                    <div class="text-muted small">Loading employees…</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="assignTeamSaveBtn">Save</button>
            </div>
        </div>
    </div>
</div>

<?php include 'layouts/right-sidebar.php'; ?>
<?php include 'layouts/vendor-scripts.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="assets/js/app.js"></script>
<script>
var LEAD_ID = <?php echo (int)$leadId; ?>;
var CURRENT_USER = <?php echo (int)($_SESSION['user_id'] ?? 0); ?>;
var IS_ADMIN = <?php echo (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'Admin') ? 'true' : 'false'; ?>;
var STATUSES = ['Pending', 'In Progress', 'Done'];
var STATUS_THEME = { 'Pending': 2, 'In Progress': 3, 'Done': 1 };
var sortableInstances = [];
var allTasks = [];
var canManageBoard = false;
var canManageTeamFlag = false;
var currentProject = null;
var assignableEmployeesCache = null;
var loadedTabs = {};

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
    if (ok) {
        setTimeout(function () { document.getElementById('message').innerHTML = ''; }, 2500);
    }
}

function formatDate(d) {
    if (!d) return '';
    var parts = String(d).slice(0, 10).split('-');
    if (parts.length !== 3) return d;
    return parts[2] + '-' + parts[1] + '-' + parts[0];
}

function isOverdue(d, status) {
    if (!d || status === 'Done') return false;
    var today = new Date();
    today.setHours(0, 0, 0, 0);
    var due = new Date(String(d).slice(0, 10) + 'T00:00:00');
    return due < today;
}

function canMoveTask(task) {
    if (IS_ADMIN || canManageBoard) return true;
    return Number(task.sAssigned_to) === Number(CURRENT_USER);
}

function api(payload) {
    return fetch('project-api.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    }).then(function (r) { return r.json(); });
}

function destroySortables() {
    sortableInstances.forEach(function (s) {
        if (s && s.destroy) s.destroy();
    });
    sortableInstances = [];
}

function updateCounts() {
    document.querySelectorAll('.crm-kanban-column').forEach(function (col) {
        var count = col.querySelectorAll('.crm-kanban-card').length;
        var badge = col.querySelector('.crm-kanban-column-count');
        if (badge) badge.textContent = count;
    });
    var total = document.querySelectorAll('#taskKanbanBoard .crm-kanban-card').length;
    document.getElementById('taskCountLabel').textContent = total + (total === 1 ? ' task' : ' tasks');
}

function initSortables() {
    destroySortables();
    var isTouch = window.matchMedia('(hover: none), (max-width: 767.98px)').matches;
    document.querySelectorAll('.crm-kanban-column-body').forEach(function (col) {
        var instance = Sortable.create(col, {
            group: 'project-tasks',
            animation: 180,
            ghostClass: 'crm-kanban-card-ghost',
            dragClass: 'crm-kanban-card-drag',
            delay: isTouch ? 180 : 0,
            delayOnTouchOnly: true,
            touchStartThreshold: 5,
            filter: '.crm-task-card-actions, .crm-task-card-actions *',
            preventOnFilter: true,
            onMove: function (evt) {
                var locked = evt.dragged.getAttribute('data-locked') === '1';
                return !locked;
            },
            onStart: function () {
                window.__taskKanbanDragging = true;
            },
            onEnd: function (evt) {
                setTimeout(function () { window.__taskKanbanDragging = false; }, 100);

                var taskId = evt.item.getAttribute('data-task-id');
                var newStatus = evt.to.getAttribute('data-status');
                var oldStatus = evt.from.getAttribute('data-status');

                if (!taskId || !newStatus || newStatus === oldStatus) {
                    updateCounts();
                    return;
                }

                updateCounts();

                api({ action: 'update_project_task', id: parseInt(taskId, 10), sStatus: newStatus }).then(function (res) {
                    if (res && res.status === 'success') {
                        showMsg(res.message || 'Task status updated', true);
                    } else {
                        showMsg((res && res.message) || 'Failed to update status', false);
                        loadTasks();
                    }
                }).catch(function () {
                    showMsg('Network error while updating status', false);
                    loadTasks();
                });
            }
        });
        sortableInstances.push(instance);
    });
}

function renderTaskCard(t) {
    var locked = !canMoveTask(t);
    var dueHtml = '';
    if (t.sDue_date) {
        var overdue = isOverdue(t.sDue_date, t.sStatus);
        dueHtml = '<span class="' + (overdue ? 'crm-task-due-overdue' : '') + '"><i class="bx bx-calendar"></i> ' +
            escapeHtml(formatDate(t.sDue_date)) + (overdue ? ' (overdue)' : '') + '</span>';
    }

    var deleteBtn = '';
    if (IS_ADMIN || Number(t.sCreated_by) === Number(CURRENT_USER) || canManageBoard) {
        deleteBtn = '<button type="button" class="btn btn-sm btn-outline-danger" data-task-del="' + t.id + '" title="Delete">' +
            '<i class="bx bx-trash"></i></button>';
    }
    var viewBtn = '<button type="button" class="btn btn-sm btn-outline-primary" data-task-view="' + t.id + '" title="View location, travel cost &amp; report">' +
        '<i class="bx bx-show"></i></button>';
    var trackBadge = '';
    if (t.has_report) {
        trackBadge = '<span class="badge bg-soft-success text-success" title="Report submitted"><i class="bx bx-check-double"></i></span>';
    } else if (t.has_started) {
        trackBadge = '<span class="badge bg-soft-warning text-warning" title="Started"><i class="bx bx-navigation"></i></span>';
    }

    return '<div class="crm-kanban-card" data-task-id="' + t.id + '" data-locked="' + (locked ? '1' : '0') + '">' +
        '<div class="crm-kanban-card-title">' + escapeHtml(t.sTitle) + ' ' + trackBadge + '</div>' +
        (t.sDescription ? '<div class="crm-kanban-card-sub">' + escapeHtml(t.sDescription) + '</div>' : '') +
        '<div class="crm-kanban-card-meta">' +
            (t.assigned_name ? '<span><i class="bx bx-user"></i> ' + escapeHtml(t.assigned_name) + '</span>' : '') +
            dueHtml +
        '</div>' +
        (t.created_by_name ? '<div class="crm-kanban-card-assignee"><i class="bx bx-edit-alt"></i> By ' + escapeHtml(t.created_by_name) + '</div>' : '') +
        '<div class="crm-task-card-actions">' + viewBtn + deleteBtn + '</div>' +
    '</div>';
}

function renderKanban(tasks) {
    var grouped = { 'Pending': [], 'In Progress': [], 'Done': [] };
    (tasks || []).forEach(function (t) {
        var status = STATUSES.indexOf(t.sStatus) !== -1 ? t.sStatus : 'Pending';
        grouped[status].push(t);
    });

    var html = '';
    STATUSES.forEach(function (status) {
        var theme = STATUS_THEME[status] != null ? STATUS_THEME[status] : 0;
        var list = grouped[status] || [];
        html += '<div class="crm-kanban-column" data-status="' + escapeHtml(status) + '">' +
            '<div class="crm-kanban-column-head crm-kanban-column-head--' + theme + '">' +
                '<span class="crm-kanban-column-title">' + escapeHtml(status) + '</span>' +
                '<span class="crm-kanban-column-count">' + list.length + '</span>' +
            '</div>' +
            '<div class="crm-kanban-column-body" data-status="' + escapeHtml(status) + '">';

        list.forEach(function (t) {
            html += renderTaskCard(t);
        });

        html += '</div></div>';
    });

    $('#taskKanbanBoard').html(html);
    document.getElementById('taskCountLabel').textContent =
        (tasks || []).length + ((tasks || []).length === 1 ? ' task' : ' tasks');
    initSortables();
}

function loadProject() {
    return api({ action: 'get_project', lead_id: LEAD_ID }).then(function (res) {
        if (!res || res.status !== 'success') {
            showMsg((res && res.message) || 'Project not found', false);
            setTimeout(function () { window.location.href = 'project-management.php'; }, 1200);
            return null;
        }
        var p = res.data;
        canManageBoard = true; // project access already validated by API
        canManageTeamFlag = !!p.can_manage_team;
        currentProject = p;
        document.getElementById('projectTitle').textContent = p.sCompany_name || p.sLead_name || ('Project #' + p.iLead_id);
        document.getElementById('projectMeta').textContent =
            'Assigned To: ' + (p.assigned_to_name || '—') +
            '  |  Lead Owner: ' + (p.lead_owner_name || '—') +
            '  ·  Drag cards to change status';
        if (canManageTeamFlag) {
            document.getElementById('manageTeamBtn').style.display = '';
        }

        var $sel = $('#sAssigned_to');
        $sel.empty();
        (p.assignable_users || []).forEach(function (u) {
            $sel.append('<option value="' + u.id + '">' + escapeHtml(u.name) + '</option>');
        });
        if (!$sel.children().length) {
            $sel.append('<option value="">No assignable users</option>');
        } else if ($sel.find('option[value="' + CURRENT_USER + '"]').length) {
            $sel.val(String(CURRENT_USER));
        }
        return p;
    });
}

function loadTasks() {
    var mineOnly = document.getElementById('mineOnly').checked;
    return api({ action: 'list_project_tasks', lead_id: LEAD_ID, mine_only: mineOnly ? 1 : 0 }).then(function (res) {
        if (!res || res.status !== 'success') {
            $('#taskKanbanBoard').html('<div class="crm-kanban-empty">Unable to load tasks</div>');
            document.getElementById('taskCountLabel').textContent = '0 tasks';
            return;
        }
        allTasks = res.data || [];
        renderKanban(allTasks);
    });
}

$('#taskForm').on('submit', function (e) {
    e.preventDefault();
    api({
        action: 'add_project_task',
        lead_id: LEAD_ID,
        sTitle: $('#sTitle').val(),
        sDescription: $('#sDescription').val(),
        sAssigned_to: $('#sAssigned_to').val(),
        sDue_date: $('#sDue_date').val(),
        sStatus: $('#sStatus').val()
    }).then(function (res) {
        if (res && res.status === 'success') {
            showMsg(res.message || 'Task added', true);
            $('#taskForm')[0].reset();
            if ($('#sAssigned_to').find('option[value="' + CURRENT_USER + '"]').length) {
                $('#sAssigned_to').val(String(CURRENT_USER));
            }
            loadTasks();
        } else {
            showMsg((res && res.message) || 'Failed to add task', false);
        }
    });
});

$('#mineOnly').on('change', loadTasks);
$('#refreshTasksBtn').on('click', loadTasks);

$('#taskKanbanBoard').on('click', '[data-task-view]', function (e) {
    e.preventDefault();
    e.stopPropagation();
    var id = $(this).data('task-view');
    window.location.href = 'project-task-detail.php?id=' + id;
});

$('#taskKanbanBoard').on('click', '[data-task-del]', function (e) {
    e.preventDefault();
    e.stopPropagation();
    var id = $(this).data('task-del');
    if (!confirm('Delete this task?')) return;
    api({ action: 'delete_project_task', id: id }).then(function (res) {
        showMsg((res && res.message) || 'Delete failed', !!(res && res.status === 'success'));
        if (res && res.status === 'success') loadTasks();
    });
});

/* ===================== Tabs ===================== */
function showTab(name) {
    $('.pm-tab-btn').removeClass('active');
    $('.pm-tab-btn[data-tab="' + name + '"]').addClass('active');
    $('.pm-tab-panel').removeClass('active');
    $('#pmTab' + name.charAt(0).toUpperCase() + name.slice(1)).addClass('active');

    if (!loadedTabs[name]) {
        loadedTabs[name] = true;
        if (name === 'team') renderTeam();
        if (name === 'entries') { loadExpenses(); loadVisits(); }
    }
}

$('.pm-tab-btn').on('click', function () {
    showTab($(this).data('tab'));
});

/* ===================== Team ===================== */
function loadAssignableEmployees() {
    if (assignableEmployeesCache) return Promise.resolve(assignableEmployeesCache);
    return api({ action: 'list_assignable_employees' }).then(function (res) {
        assignableEmployeesCache = (res && res.status === 'success') ? res.data : [];
        return assignableEmployeesCache;
    });
}

function renderTeam() {
    var $list = $('#teamList');
    if (!currentProject) { $list.html('<div class="text-muted small">Unable to load team</div>'); return; }
    var html = '<div class="mb-2"><strong>Lead Owner:</strong> ' + escapeHtml(currentProject.lead_owner_name || '—') + '</div>';
    html += '<div><strong>Assigned Employees:</strong></div>';
    var names = (currentProject.assigned_to_name || '').split(',').map(function (s) { return s.trim(); }).filter(Boolean);
    if (!names.length) {
        html += '<div class="text-muted small mt-1">No employees assigned yet</div>';
    } else {
        html += '<div class="d-flex flex-wrap gap-2 mt-1">' + names.map(function (n) {
            return '<span class="badge bg-soft-primary text-primary"><i class="bx bx-user"></i> ' + escapeHtml(n) + '</span>';
        }).join('') + '</div>';
    }
    $list.html(html);
}

function openAssignModal() {
    if (!currentProject) return;
    var $list = $('#assignEmployeeList');
    $list.html('<div class="text-muted small">Loading employees…</div>');
    var modal = new bootstrap.Modal(document.getElementById('assignTeamModal'));
    modal.show();

    loadAssignableEmployees().then(function (users) {
        if (!users.length) {
            $list.html('<div class="text-muted small">No employees found</div>');
            return;
        }
        var assignedIds = (currentProject.assigned_ids || []).map(Number);
        var html = '';
        users.forEach(function (u) {
            var checked = assignedIds.indexOf(Number(u.id)) !== -1 ? 'checked' : '';
            html += '<div class="form-check">' +
                '<input class="form-check-input" type="checkbox" value="' + u.id + '" id="assignEmp' + u.id + '" ' + checked + '>' +
                '<label class="form-check-label" for="assignEmp' + u.id + '">' + escapeHtml(u.name) + (u.role ? ' <span class="text-muted small">(' + escapeHtml(u.role) + ')</span>' : '') + '</label>' +
            '</div>';
        });
        $list.html(html);
    });
}

$('#manageTeamBtn').on('click', openAssignModal);

$('#assignTeamSaveBtn').on('click', function () {
    var ids = [];
    $('#assignEmployeeList input:checked').each(function () { ids.push(parseInt(this.value, 10)); });
    if (!ids.length) {
        showMsg('Select at least one employee', false);
        return;
    }
    api({ action: 'assign_project_employees', lead_id: LEAD_ID, user_ids: ids }).then(function (res) {
        if (res && res.status === 'success') {
            bootstrap.Modal.getInstance(document.getElementById('assignTeamModal')).hide();
            showMsg(res.message || 'Team updated', true);
            loadProject().then(renderTeam);
        } else {
            showMsg((res && res.message) || 'Failed to update team', false);
        }
    });
});

/* ===================== Expenses ===================== */
function expenseStatusBadge(status) {
    var cls = status === 'Approved' ? 'bg-soft-success text-success' : (status === 'Rejected' ? 'bg-soft-danger text-danger' : 'bg-soft-warning text-warning');
    return '<span class="badge ' + cls + '">' + escapeHtml(status) + '</span>';
}

function loadExpenses() {
    api({ action: 'list_project_expenses', lead_id: LEAD_ID }).then(function (res) {
        var $body = $('#expenseTableBody');
        if (!res || res.status !== 'success') {
            $body.html('<tr><td colspan="7" class="text-center text-danger">Unable to load expenses</td></tr>');
            return;
        }
        var rows = res.data || [];
        if (!rows.length) {
            $body.html('<tr><td colspan="7" class="text-center text-muted">No expenses yet</td></tr>');
        } else {
            var html = '';
            rows.forEach(function (e) {
                var canReview = canManageTeamFlag && e.sStatus === 'Pending';
                var isOwnPending = Number(e.iUserid) === Number(CURRENT_USER) && e.sStatus === 'Pending';
                var canDelete = canManageTeamFlag || isOwnPending;
                var actions = '';
                if (canReview) {
                    actions += '<button type="button" class="btn btn-sm btn-outline-success me-1" data-exp-status="' + e.id + '" data-status="Approved" title="Approve"><i class="bx bx-check"></i></button>' +
                        '<button type="button" class="btn btn-sm btn-outline-danger me-1" data-exp-status="' + e.id + '" data-status="Rejected" title="Reject"><i class="bx bx-x"></i></button>';
                }
                if (canDelete) {
                    actions += '<button type="button" class="btn btn-sm btn-outline-secondary" data-exp-del="' + e.id + '" title="Delete"><i class="bx bx-trash"></i></button>';
                }
                html += '<tr>' +
                    '<td>' + escapeHtml(formatDate(e.sExpenseDate)) + '</td>' +
                    '<td>' + escapeHtml(e.sCategory) + '</td>' +
                    '<td>' + Number(e.dAmount).toFixed(2) + '</td>' +
                    '<td>' + escapeHtml(e.submitted_by_name || '—') + '</td>' +
                    '<td>' + expenseStatusBadge(e.sStatus) + '</td>' +
                    '<td>' + (e.has_receipt ? '<a href="download-project-expense-receipt.php?id=' + e.id + '" target="_blank"><i class="bx bx-file"></i></a>' : '—') + '</td>' +
                    '<td>' + actions + '</td>' +
                '</tr>';
            });
            $body.html(html);
        }

        var totalApproved = 0, totalPending = 0;
        rows.forEach(function (e) {
            if (e.sStatus === 'Approved') totalApproved += Number(e.dAmount);
            if (e.sStatus === 'Pending') totalPending += Number(e.dAmount);
        });
        document.getElementById('expenseSummary').textContent =
            'Approved: ' + totalApproved.toFixed(2) + '  ·  Pending: ' + totalPending.toFixed(2);
        document.getElementById('approvedExpenseLabel').textContent =
            '₹' + totalApproved.toFixed(2) + ' approved';
    });
}

$('#expenseTableBody').on('click', '[data-exp-status]', function () {
    var id = $(this).data('exp-status');
    var status = $(this).data('status');
    api({ action: 'update_project_expense_status', id: id, sStatus: status }).then(function (res) {
        showMsg((res && res.message) || 'Update failed', !!(res && res.status === 'success'));
        if (res && res.status === 'success') loadExpenses();
    });
});

$('#expenseTableBody').on('click', '[data-exp-del]', function () {
    var id = $(this).data('exp-del');
    if (!confirm('Delete this expense?')) return;
    api({ action: 'delete_project_expense', id: id }).then(function (res) {
        showMsg((res && res.message) || 'Delete failed', !!(res && res.status === 'success'));
        if (res && res.status === 'success') loadExpenses();
    });
});

/* ===================== Visits & Meetings ===================== */
function renderVisitCard(v) {
    var photosHtml = '';
    (v.photos || []).forEach(function (pid) {
        photosHtml += '<a href="view-project-visit-photo.php?id=' + pid + '" target="_blank">' +
            '<img class="pm-photo-thumb" src="view-project-visit-photo.php?id=' + pid + '" alt="Photo"></a> ';
    });
    var canDelete = canManageTeamFlag || Number(v.iUserid) === Number(CURRENT_USER);
    return '<div class="pm-visit-card">' +
        '<div class="d-flex justify-content-between align-items-start">' +
            '<div>' +
                '<span class="badge bg-soft-primary text-primary">' + escapeHtml(v.sVisitType === 'Meeting' ? 'Meeting' : 'Site Visit') + '</span> ' +
                '<strong>' + escapeHtml(formatDate(v.sVisitDate)) + '</strong>' +
                (v.sLocation ? ' <span class="text-muted">· ' + escapeHtml(v.sLocation) + '</span>' : '') +
            '</div>' +
            (canDelete ? '<button type="button" class="btn btn-sm btn-outline-danger" data-visit-del="' + v.id + '"><i class="bx bx-trash"></i></button>' : '') +
        '</div>' +
        (v.sNotes ? '<div class="mt-2">' + escapeHtml(v.sNotes) + '</div>' : '') +
        (photosHtml ? '<div class="mt-2 d-flex flex-wrap gap-2">' + photosHtml + '</div>' : '') +
        '<div class="text-muted mt-2" style="font-size:12px;">Logged by ' + escapeHtml(v.created_by_name || '—') + '</div>' +
    '</div>';
}

function loadVisits() {
    api({ action: 'list_project_visits', lead_id: LEAD_ID }).then(function (res) {
        var $list = $('#visitList');
        if (!res || res.status !== 'success') {
            $list.html('<div class="text-danger">Unable to load visits</div>');
            return;
        }
        var rows = res.data || [];
        if (!rows.length) {
            $list.html('<div class="text-muted">No visits or meetings logged yet</div>');
            return;
        }
        $list.html(rows.map(renderVisitCard).join(''));
    });
}

/* ===================== Unified Entry Form (Expense / Visit / Meeting) ===================== */
function syncEntryFields() {
    var isExpense = $('#entryType').val() === 'Expense';
    $('#entryExpenseFields').toggle(isExpense);
    $('#entryVisitFields').toggle(!isExpense);
    $('#expCategory, #expAmount').prop('required', isExpense);
}
$('#entryType').on('change', syncEntryFields);
syncEntryFields();

function getCurrentLocation() {
    return new Promise(function (resolve) {
        if (!navigator.geolocation) { resolve(null); return; }
        navigator.geolocation.getCurrentPosition(
            function (pos) { resolve({ lat: pos.coords.latitude, lng: pos.coords.longitude }); },
            function () { resolve(null); },
            { enableHighAccuracy: true, timeout: 6000, maximumAge: 60000 }
        );
    });
}

$('#entryForm').on('submit', function (e) {
    e.preventDefault();
    var type = $('#entryType').val();
    var photosInput = document.getElementById('visitPhotos');

    if (type !== 'Expense' && photosInput.files.length > 6) {
        showMsg('You can upload up to 6 photos per entry', false);
        return;
    }

    getCurrentLocation().then(function (loc) {
        var fd = new FormData();
        fd.append('action', 'add_project_entry');
        fd.append('lead_id', LEAD_ID);
        fd.append('entryType', type);
        fd.append('sDate', $('#entryDate').val());

        if (type === 'Expense') {
            fd.append('sCategory', $('#expCategory').val());
            fd.append('dAmount', $('#expAmount').val());
            fd.append('sDescription', $('#expDescription').val());
            var receipt = document.getElementById('expReceipt').files[0];
            if (receipt) fd.append('receipt', receipt);
        } else {
            fd.append('sLocation', $('#visitLocation').val());
            fd.append('sNotes', $('#visitNotes').val());
            for (var i = 0; i < photosInput.files.length; i++) {
                fd.append('photos[]', photosInput.files[i]);
            }
        }

        if (loc) {
            fd.append('endLatitude', loc.lat);
            fd.append('endLongitude', loc.lng);
        }

        fetch('project-api.php', { method: 'POST', credentials: 'same-origin', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res && res.status === 'success') {
                    showMsg(res.message || 'Entry saved', true);
                    $('#entryForm')[0].reset();
                    syncEntryFields();
                    loadExpenses();
                    loadVisits();
                } else {
                    showMsg((res && res.message) || 'Failed to save entry', false);
                }
            })
            .catch(function () { showMsg('Network error while saving entry', false); });
    });
});

$('#visitList').on('click', '[data-visit-del]', function () {
    var id = $(this).data('visit-del');
    if (!confirm('Delete this visit/meeting entry?')) return;
    api({ action: 'delete_project_visit', id: id }).then(function (res) {
        showMsg((res && res.message) || 'Delete failed', !!(res && res.status === 'success'));
        if (res && res.status === 'success') loadVisits();
    });
});

$(document).ready(function () {
    loadProject().then(loadTasks);
    loadedTabs.entries = true;
    loadExpenses();
    loadVisits();
});
</script>
</html>
