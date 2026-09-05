<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include 'layouts/config.php';
include_once 'layouts/crm-access.php';
crmRequireModule('project', $link);
?>

<head>
    <title>All Projects</title>
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
        /* Custom (non Bootstrap dropdown-menu) panel: the bundled bootstrap.min.css
           forces .dropdown-menu.show { top: 100% !important } and
           .dropdown-menu-end[style] { left/right !important }, which fight any
           manual fixed-position placement — so this panel uses its own class. */
        .pm-notif-panel {
            display: none;
            background: #fff;
            border: 1px solid rgba(0, 0, 0, 0.15);
            border-radius: 8px;
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.176);
            z-index: 1050;
        }
        .pm-notif-panel.show { display: block; }
        #pmNotifList { scrollbar-width: thin; }
        #pmNotifList::-webkit-scrollbar { width: 7px; }
        #pmNotifList::-webkit-scrollbar-thumb { background: rgba(0, 0, 0, 0.25); border-radius: 4px; }
        #pmNotifList::-webkit-scrollbar-track { background: transparent; }
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
                        <span class="crm-dash-greeting">Projects</span>
                        <h2>All Projects</h2>
                        <p>Every project you're assigned to or own, at any stage. Same tasks, team, expenses and visits flow as Won Projects.</p>
                    </div>
                    <div class="crm-dash-hero-meta d-flex align-items-center gap-3">
                        <div class="crm-dash-date">
                            <i class="bx bx-trophy"></i>
                            <span id="projectCountLabel">Loading…</span>
                        </div>
                        <div class="dropdown">
                            <button type="button" class="crm-header-icon position-relative" id="pmNotifBell" aria-haspopup="true" aria-expanded="false" title="Notifications">
                                <i class="bx bx-bell"></i>
                                <span class="crm-notif-badge" id="pmNotifBadge" style="display: none;">0</span>
                            </button>
                            <div class="pm-notif-panel p-0" id="pmNotifMenu" style="width: 340px;">
                                <div class="p-2 border-bottom fw-bold small">Notifications</div>
                                <div id="pmNotifList" class="p-2 text-muted small" style="max-height: 320px; overflow-y: auto;">Loading…</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="message" class="mb-3"></div>

                <div class="crm-panel-card mb-0">
                    <div class="crm-panel-card-head">
                        <div>
                            <h6><i class="bx bx-briefcase me-1"></i> All Projects</h6>
                            <p>Every lead you're assigned to or own, regardless of status</p>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="datatable" class="table table-hover crm-leads-table mb-0">
                            <thead>
                                <tr>
                                    <th class="col-sr">Sr No.</th>
                                    <th>Company Name</th>
                                    <th>Status</th>
                                    <th>Assigned To</th>
                                    <th>Lead Owner</th>
                                    <th>Tasks</th>
                                    <th>My Tasks</th>
                                    <th class="col-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

            </div>

            <div class="modal fade" id="assignTeamModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="bx bx-user-plus me-1"></i> Assign Team</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted small mb-2" id="assignModalProjectName"></p>
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
        </div>
        <?php include 'layouts/footer.php'; ?>
    </div>
</div>

<?php include 'layouts/right-sidebar.php'; ?>
<?php include 'layouts/vendor-scripts.php'; ?>
<script src="assets/js/app.js"></script>
<script>
var IS_ADMIN = <?php echo (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'Admin') ? 'true' : 'false'; ?>;
var CURRENT_USER = <?php echo (int)($_SESSION['user_id'] ?? 0); ?>;
var assignableEmployeesCache = null;
var assignModalLeadId = null;

function canManageTeam(p) {
    return IS_ADMIN || Number(p.lead_owner_id) === Number(CURRENT_USER);
}

function escapeHtml(str) {
    return String(str == null ? '' : str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function openProject(leadId) {
    window.location.href = 'project-tasks.php?lead_id=' + encodeURIComponent(leadId);
}

function loadProjects() {
    fetch('project-api.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'list_all_projects' })
    })
        .then(function (r) {
            return r.text().then(function (body) {
                try { return JSON.parse(body); }
                catch (e) {
                    throw new Error(body ? body.replace(/<[^>]+>/g, ' ').trim().slice(0, 160) : ('HTTP ' + r.status));
                }
            });
        })
        .then(function (res) {
            if ($.fn.DataTable.isDataTable('#datatable')) {
                $('#datatable').DataTable().destroy();
            }
            var $tbody = $('#datatable tbody');
            $tbody.empty();

            if (!res || res.status !== 'success') {
                document.getElementById('projectCountLabel').textContent = 'Error';
                document.getElementById('message').innerHTML = '<div class="error-message">' +
                    escapeHtml((res && res.message) || 'Failed to load projects') + '</div>';
                $tbody.html('<tr><td colspan="8" class="text-center text-muted">No projects found</td></tr>');
                return;
            }

            var rows = res.data || [];
            document.getElementById('projectCountLabel').textContent = rows.length + (rows.length === 1 ? ' project' : ' projects');

            if (!rows.length) {
                $tbody.html('<tr><td colspan="8" class="text-center text-muted">No projects assigned to you yet</td></tr>');
                return;
            }

            rows.forEach(function (p, idx) {
                var company = p.sCompany_name || p.sLead_name || ('Project #' + p.iLead_id);
                var tasks = (p.task_done || 0) + ' / ' + (p.task_total || 0);
                $tbody.append(
                    '<tr class="crm-project-row" data-lead-id="' + p.iLead_id + '">' +
                        '<td class="col-sr">' + (idx + 1) + '</td>' +
                        '<td><strong>' + escapeHtml(company) + '</strong></td>' +
                        '<td>' + escapeHtml(p.status_name || '—') + '</td>' +
                        '<td>' + escapeHtml(p.assigned_to_name || '—') + '</td>' +
                        '<td>' + escapeHtml(p.lead_owner_name || '—') + '</td>' +
                        '<td><span class="crm-task-pill"><i class="bx bx-task"></i><strong>' + escapeHtml(tasks) + '</strong> done</span></td>' +
                        '<td>' + (p.my_tasks || 0) + '</td>' +
                        '<td class="col-actions">' +
                            '<a href="project-tasks.php?lead_id=' + p.iLead_id + '" class="btn btn-sm btn-primary" onclick="event.stopPropagation()"><i class="bx bx-list-check"></i> Tasks</a> ' +
                            (canManageTeam(p) ? '<button type="button" class="btn btn-sm btn-outline-primary" data-assign-btn data-lead-id="' + p.iLead_id + '" data-assigned="' + escapeHtml((p.assigned_ids || []).join(',')) + '" data-name="' + escapeHtml(p.sCompany_name || p.sLead_name || ('Project #' + p.iLead_id)) + '"><i class="bx bx-user-plus"></i> Assign</button>' : '') +
                        '</td>' +
                    '</tr>'
                );
            });

            $('#datatable').DataTable({
                pageLength: 25,
                order: [],
                language: {
                    search: 'Search:',
                    lengthMenu: 'Show _MENU_ entries',
                    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                    zeroRecords: 'No matching projects found'
                }
            });
        })
        .catch(function (err) {
            document.getElementById('projectCountLabel').textContent = 'Error';
            document.getElementById('message').innerHTML = '<div class="error-message">Failed to load projects' +
                (err && err.message ? ': ' + err.message : '') + '</div>';
        });
}

function loadAssignableEmployees() {
    if (assignableEmployeesCache) {
        return Promise.resolve(assignableEmployeesCache);
    }
    return fetch('project-api.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'list_assignable_employees' })
    })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            assignableEmployeesCache = (res && res.status === 'success') ? res.data : [];
            return assignableEmployeesCache;
        });
}

function openAssignModal(leadId, assignedIds, name) {
    assignModalLeadId = leadId;
    document.getElementById('assignModalProjectName').textContent = name;
    var $list = $('#assignEmployeeList');
    $list.html('<div class="text-muted small">Loading employees…</div>');
    var modal = new bootstrap.Modal(document.getElementById('assignTeamModal'));
    modal.show();

    loadAssignableEmployees().then(function (users) {
        if (!users.length) {
            $list.html('<div class="text-muted small">No employees found</div>');
            return;
        }
        var html = '';
        users.forEach(function (u) {
            var checked = assignedIds.indexOf(u.id) !== -1 ? 'checked' : '';
            html += '<div class="form-check">' +
                '<input class="form-check-input" type="checkbox" value="' + u.id + '" id="assignEmp' + u.id + '" ' + checked + '>' +
                '<label class="form-check-label" for="assignEmp' + u.id + '">' + escapeHtml(u.name) + (u.role ? ' <span class="text-muted small">(' + escapeHtml(u.role) + ')</span>' : '') + '</label>' +
            '</div>';
        });
        $list.html(html);
    });
}

$('#assignTeamSaveBtn').on('click', function () {
    if (!assignModalLeadId) return;
    var ids = [];
    $('#assignEmployeeList input:checked').each(function () { ids.push(parseInt(this.value, 10)); });
    if (!ids.length) {
        showAssignError('Select at least one employee');
        return;
    }
    fetch('project-api.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'assign_project_employees', lead_id: assignModalLeadId, user_ids: ids })
    })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res && res.status === 'success') {
                bootstrap.Modal.getInstance(document.getElementById('assignTeamModal')).hide();
                loadProjects();
            } else {
                showAssignError((res && res.message) || 'Failed to update team');
            }
        })
        .catch(function () { showAssignError('Network error while updating team'); });
});

function showAssignError(text) {
    document.getElementById('message').innerHTML = '<div class="alert alert-danger alert-dismissible fade show">' +
        escapeHtml(text) + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
}

function loadNotifications() {
    fetch('project-api.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'list_notifications' })
    })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            var $badge = $('#pmNotifBadge');
            var $list = $('#pmNotifList');
            if (!res || res.status !== 'success') {
                $list.html('<div class="text-danger small">Failed to load</div>');
                return;
            }
            var unread = res.unread || 0;
            if (unread > 0) {
                $badge.text(unread).show();
            } else {
                $badge.hide();
            }
            var items = res.data || [];
            if (!items.length) {
                $list.html('<div class="text-muted small">No notifications yet</div>');
                return;
            }
            var html = '';
            items.forEach(function (n) {
                html += '<div class="p-2 border-bottom ' + (n.iIsRead ? '' : 'bg-soft-primary') + '">' +
                    '<div class="small">' + escapeHtml(n.sMessage) + '</div>' +
                    '<div class="text-muted" style="font-size:11px;">' + escapeHtml(n.dCreatedAt || '') + '</div>' +
                '</div>';
            });
            $list.html(html);
        })
        .catch(function () {
            $('#pmNotifList').html('<div class="text-danger small">Network error</div>');
        });
}

$('#pmNotifBell').on('click', function () {
    fetch('project-api.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'mark_notifications_read' })
    }).then(function () {
        setTimeout(loadNotifications, 400);
    });
});

// The hero banner uses overflow:hidden to contain its decorative background blob,
// which clips a Bootstrap-positioned dropdown-menu. Position this one manually,
// fixed to the viewport, so it always renders below the bell regardless of that clip.
(function () {
    var bell = document.getElementById('pmNotifBell');
    var menu = document.getElementById('pmNotifMenu');

    function positionNotifMenu() {
        var rect = bell.getBoundingClientRect();
        var menuWidth = menu.offsetWidth || 340;
        var left = Math.min(rect.right - menuWidth, window.innerWidth - menuWidth - 12);
        left = Math.max(left, 12);
        menu.style.position = 'fixed';
        menu.style.top = (rect.bottom + 8) + 'px';
        menu.style.left = left + 'px';
        menu.style.right = 'auto';
        menu.style.margin = '0';
    }

    bell.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = menu.classList.contains('show');
        if (isOpen) {
            menu.classList.remove('show');
            bell.setAttribute('aria-expanded', 'false');
        } else {
            positionNotifMenu();
            menu.classList.add('show');
            bell.setAttribute('aria-expanded', 'true');
        }
    });

    document.addEventListener('click', function (e) {
        if (menu.classList.contains('show') && !menu.contains(e.target) && !bell.contains(e.target)) {
            menu.classList.remove('show');
            bell.setAttribute('aria-expanded', 'false');
        }
    });

    window.addEventListener('resize', function () {
        if (menu.classList.contains('show')) positionNotifMenu();
    });
})();

$(document).ready(function () {
    loadProjects();
    loadNotifications();
    $('#datatable').on('click', 'tbody tr.crm-project-row', function (e) {
        if ($(e.target).closest('a, button', this).length) return;
        var id = $(this).data('lead-id');
        if (id) openProject(id);
    });
    $('#datatable').on('click', '[data-assign-btn]', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var leadId = parseInt($(this).data('lead-id'), 10);
        var assignedRaw = String($(this).data('assigned') || '');
        var assignedIds = assignedRaw ? assignedRaw.split(',').map(function (v) { return parseInt(v, 10); }) : [];
        var name = $(this).data('name');
        openAssignModal(leadId, assignedIds, name);
    });
});
</script>
</html>
