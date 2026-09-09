<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include 'layouts/config.php'; ?>

<head>
    <title>Dashboard</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<?php include 'layouts/body.php'; ?>

<div id="layout-wrapper">
    <?php include 'layouts/menu.php'; ?>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <!-- Hero -->
                <div class="crm-dash-hero-v3">
                    <div>
                        <span class="crm-dash-greeting">Good <?php echo (date('H') < 12 ? 'morning' : (date('H') < 17 ? 'afternoon' : 'evening')); ?>,</span>
                        <h2><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></h2>
                        <p>Here's your CRM snapshot for today. Track leads, follow-ups, and pipeline at a glance.</p>
                        <div class="crm-dash-hero-v3-actions">
                            <a href="add-lead-master.php" class="btn btn-primary btn-sm"><i class="bx bx-plus"></i> Add Lead</a>
                            <?php if (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'Admin') : ?>
                            <a href="list-lead-master.php" class="btn btn-primary btn-sm"><i class="bx bx-list-ul"></i> List Leads</a>
                            <?php endif; ?>
                            <a href="add-reminders.php" class="btn btn-outline-primary btn-sm"><i class="bx bx-bell"></i> Add Reminder</a>
                            <a href="list-followups_users.php" class="btn btn-outline-primary btn-sm"><i class="bx bx-time-five"></i> Follow Up</a>
                            <a href="add-reengage.php" class="btn btn-outline-primary btn-sm"><i class="bx bx-user-voice"></i> Add Re-engage</a>
                            <a href="list-daily-report.php" class="btn btn-outline-primary btn-sm"><i class="bx bx-notepad"></i> List Daily Reports</a>
                            <a href="kanban_pipeline.php" class="btn btn-outline-primary btn-sm"><i class="bx bx-columns"></i> Pipeline</a>
                            <a href="calendar.php" class="btn btn-outline-secondary btn-sm"><i class="bx bx-calendar"></i> Calendar</a>
                        </div>
                    </div>
                    <div class="crm-dash-hero-visual d-none d-lg-flex">
                        <div class="crm-hero-orb crm-hero-orb--chart">
                            <img src="assets/images/3d-chart-clean.png" alt="Growth chart">
                        </div>
                        <div class="crm-hero-orb crm-hero-orb--target">
                            <img src="assets/images/3d-target-clean.png" alt="Sales target">
                        </div>
                    </div>
                    <div class="crm-dash-date-pill">
                        <i class="bx bx-calendar"></i>
                        <span id="dashDate"></span>
                    </div>
                </div>

                <!-- Stats -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3 col-sm-6">
                        <a href="list-reminder_users.php" class="crm-dash-stat-link">
                            <div class="crm-stat-v3 crm-stat-v3--reminders">
                                <div class="crm-stat-v3-icon"><i class="bx bx-bell"></i></div>
                                <div>
                                    <p class="crm-stat-v3-num" id="reminderCount">0</p>
                                    <h6>Today's Reminders</h6>
                                    <span class="crm-stat-v3-hint">View scheduled reminders →</span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <a href="list-followups_users.php" class="crm-dash-stat-link">
                            <div class="crm-stat-v3 crm-stat-v3--followups">
                                <div class="crm-stat-v3-icon"><i class="bx bx-time-five"></i></div>
                                <div>
                                    <p class="crm-stat-v3-num" id="followupCount">0</p>
                                    <h6>Today's Follow Ups</h6>
                                    <span class="crm-stat-v3-hint">Leads needing follow-up →</span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <a href="list-reengage.php?today=1" class="crm-dash-stat-link">
                            <div class="crm-stat-v3 crm-stat-v3--reengage">
                                <div class="crm-stat-v3-icon"><i class="bx bx-user-voice"></i></div>
                                <div>
                                    <p class="crm-stat-v3-num" id="reengageCount">0</p>
                                    <h6>Today's Re-engage</h6>
                                    <span class="crm-stat-v3-hint">View today's re-engage →</span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <?php
                        $isAdminDash = (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'Admin');
                        $leadsStatHref = $isAdminDash ? 'list-lead-master.php' : 'assigned-leads.php';
                        $leadsStatTitle = $isAdminDash ? 'All Leads' : 'Assigned Leads';
                        $leadsStatHint = $isAdminDash ? 'All leads across the CRM →' : 'Leads assigned to you →';
                        ?>
                        <a href="<?php echo htmlspecialchars($leadsStatHref); ?>" class="crm-dash-stat-link">
                            <div class="crm-stat-v3 crm-stat-v3--assigned">
                                <div class="crm-stat-v3-icon"><i class="bx bx-user-check"></i></div>
                                <div>
                                    <p class="crm-stat-v3-num" id="assignedCount">0</p>
                                    <h6><?php echo htmlspecialchars($leadsStatTitle); ?></h6>
                                    <span class="crm-stat-v3-hint"><?php echo htmlspecialchars($leadsStatHint); ?></span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php if (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'Admin') : ?>
                    <div class="col-md-3 col-sm-6">
                        <a href="quotation-requests.php" class="crm-dash-stat-link">
                            <div class="crm-stat-v3 crm-stat-v3--reengage">
                                <div class="crm-stat-v3-icon"><i class="bx bx-file-find"></i></div>
                                <div>
                                    <p class="crm-stat-v3-num" id="quotationRequestCount">0</p>
                                    <h6>Quotation Requests</h6>
                                    <span class="crm-stat-v3-hint">Pending requests to review →</span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Today's Re-engage -->
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <div class="crm-panel-v3">
                            <div class="crm-panel-v3-head d-flex flex-wrap align-items-start justify-content-between gap-2">
                                <div>
                                    <h6><i class="bx bx-user-voice" style="color:#7c3aed;"></i> Today's Re-engage</h6>
                                    <p>Companies assigned for re-engagement today</p>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="add-reengage.php" class="btn btn-sm btn-primary">+ Add</a>
                                    <a href="list-reengage.php" class="btn btn-sm btn-outline-primary">View All</a>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width:50px;">#</th>
                                            <th>User</th>
                                            <th>Company</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody id="todayReengageBody">
                                        <tr><td colspan="4" class="text-center text-muted">Loading…</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lead Pipeline + Overdue Follow Ups -->
                <div class="row g-3 mb-4">
                    <div class="col-lg-6">
                        <div class="crm-panel-v3">
                            <div class="crm-panel-v3-head">
                                <h6><i class="bx bx-bar-chart-alt-2" style="color:#2563eb;"></i> Lead Pipeline</h6>
                                <p><?php echo (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'Admin') ? 'Click a stage to view matching leads' : 'Click a stage to view your matching leads'; ?></p>
                            </div>
                            <div id="leadPipelineStages" class="crm-pipeline-stage-list">
                                <div class="text-center text-muted py-4">Loading…</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="crm-panel-v3">
                            <div class="crm-panel-v3-head d-flex flex-wrap align-items-start justify-content-between gap-2">
                                <div>
                                    <h6><i class="bx bx-error-circle" style="color:#dc2626;"></i> Overdue Follow Ups</h6>
                                    <p>Follow-ups past their scheduled date — click one to open it</p>
                                </div>
                                <span class="crm-overdue-badge" id="overdueBadge" style="display:none;"></span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm crm-overdue-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Company</th>
                                            <th>Description</th>
                                            <th>Due Date</th>
                                            <th>Overdue</th>
                                            <th>Status</th>
                                            <th>User</th>
                                        </tr>
                                    </thead>
                                    <tbody id="overdueFollowupsBody">
                                        <tr><td colspan="6" class="text-center text-muted">Loading…</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lead Status Overview -->
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <div class="crm-panel-v3">
                            <div class="crm-panel-v3-head">
                                <h6><i class="bx bx-grid-alt" style="color:#059669;"></i> Lead Status Overview</h6>
                                <p>Click any status to view matching leads</p>
                            </div>
                            <div class="row g-3" id="statusCardsRow"></div>
                        </div>
                    </div>
                </div>

                <!-- Reminder calendar + boost -->
                <div class="row g-3 mb-4">
                    <div class="col-lg-8">
                        <div class="crm-panel-v3">
                            <div class="crm-panel-v3-head d-flex flex-wrap align-items-start justify-content-between gap-2">
                                <div>
                                    <h6><i class="bx bx-calendar" style="color:#f97316;"></i> Reminder Calendar</h6>
                                    <p>Reminders &amp; follow-ups — click an event for details</p>
                                </div>
                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                    <span class="crm-calendar-legend-item"><span class="crm-calendar-dot crm-calendar-dot--followup"></span> Follow-ups</span>
                                    <span class="crm-calendar-legend-item"><span class="crm-calendar-dot crm-calendar-dot--reminder"></span> Reminders</span>
                                    <a href="calendar.php" class="btn btn-sm btn-outline-primary">Full Calendar</a>
                                </div>
                            </div>
                            <div class="crm-dash-calendar-wrap">
                                <div id="dashReminderCalendar"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="crm-boost-v3">
                            <img src="assets/images/3d-chart-clean.png" alt="Boost sales">
                            <h5>Boost Your Sales</h5>
                            <p>Track, manage &amp; close more deals with your pipeline board.</p>
                            <a href="kanban_pipeline.php" class="btn btn-sm">Explore More <i class="bx bx-right-arrow-alt"></i></a>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <?php include 'layouts/footer.php'; ?>
    </div>
</div>

<!-- Reminder / follow-up detail modal -->
<div class="modal fade" id="dashCalEventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dashCalEventTitle">Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2"><strong>Date:</strong> <span id="dashCalEventDate"></span></p>
                <p class="mb-2"><strong>Type:</strong> <span id="dashCalEventType"></span></p>
                <p class="mb-0" id="dashCalEventDescWrap"><strong>Details:</strong> <span id="dashCalEventDesc"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="list-reminder_users.php" class="btn btn-outline-primary">All Reminders</a>
                <button type="button" class="btn btn-primary" id="dashCalOpenLeadBtn" style="display:none;">Open Lead</button>
            </div>
        </div>
    </div>
</div>

<?php include 'layouts/right-sidebar.php'; ?>
<?php include 'layouts/vendor-scripts.php'; ?>
<script src="assets/libs/apexcharts/apexcharts.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script src="assets/js/app.js"></script>

<script>
function checkTokenStatus() {
    $.ajax({
        url: 'check_token.php',
        method: 'GET',
        success: function (response) {
            if (response.status === 'error') {
                alert(response.message);
                window.location.href = 'auth-login.php';
            }
        },
        error: function () {
            window.location.href = 'auth-login.php';
        }
    });
}
checkTokenStatus();

function animateCount(el, target) {
    var $el = $(el);
    target = parseInt(target, 10) || 0;
    if (target <= 0) { $el.text(0); return; }
    var start = 0;
    var steps = Math.min(target, 40);
    var stepVal = target / steps;
    var i = 0;
    var timer = setInterval(function () {
        i++;
        start = Math.round(stepVal * i);
        if (start >= target || i >= steps) {
            $el.text(target);
            clearInterval(timer);
        } else {
            $el.text(start);
        }
    }, 24);
}

$(function () {
    var now = new Date();
    $('#dashDate').text(now.toLocaleDateString('en-US', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
    }));

    $.getJSON('dashboard_data.php').done(function (res) {
        if (res.status === 'success') {
            animateCount('#reminderCount', res.reminders);
            animateCount('#followupCount', res.followups);
            animateCount('#assignedCount', res.assigned);
            animateCount('#reengageCount', res.reengage || 0);
        }
    });

    if (document.getElementById('quotationRequestCount')) {
        fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'list_quotation_requests' })
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res.status !== 'success') return;
            var pending = (res.data || []).filter(function (r) { return r.status === 'Pending'; }).length;
            animateCount('#quotationRequestCount', pending);
        });
    }

    function escapeDashHtml(text) {
        if (text === null || text === undefined) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'gettodaysreengage' })
    })
    .then(function (r) { return r.json(); })
    .then(function (res) {
        var $body = $('#todayReengageBody');
        var list = (res && res.data) ? res.data : [];
        if (!list.length) {
            $body.html('<tr><td colspan="4" class="text-center text-muted">No re-engage entries for today</td></tr>');
            return;
        }
        var rows = '';
        list.forEach(function (item, index) {
            rows += '<tr>' +
                '<td>' + (index + 1) + '</td>' +
                '<td>' + escapeDashHtml(item.user_name || '—') + '</td>' +
                '<td>' + escapeDashHtml(item.sCompanyname || '—') + '</td>' +
                '<td>' + escapeDashHtml(item.sDescription || '—') + '</td>' +
            '</tr>';
        });
        $body.html(rows);
    })
    .catch(function () {
        $('#todayReengageBody').html('<tr><td colspan="4" class="text-center text-muted">Unable to load today\'s re-engage</td></tr>');
    });

    var $row = $('#statusCardsRow').empty();
    var statusThemes = ['blue', 'green', 'orange', 'purple', 'teal', 'rose', 'indigo', 'amber'];
    var statusIcons = ['bx-time', 'bx-check-circle', 'bx-loader-circle', 'bx-x-circle', 'bx-star', 'bx-flag', 'bx-target-lock', 'bx-trending-up'];

    function openLeadsByStatus(statusId) {
        if (!statusId && statusId !== 0) return;
        window.location.href = 'list-leads-by-status.php?status=' + encodeURIComponent(statusId);
    }

    function renderPipelineStages(items) {
        var $wrap = $('#leadPipelineStages').empty();
        items = items || [];
        if (!items.length) {
            $wrap.html('<div class="text-center text-muted py-4">No pipeline data</div>');
            return;
        }
        var counts = items.map(function (i) { return parseInt(i.count, 10) || 0; });
        var maxCount = Math.max.apply(null, counts.concat([1]));
        items.forEach(function (item, index) {
            var count = counts[index];
            var pct = maxCount > 0 ? Math.round((count / maxCount) * 100) : 0;
            var label = $('<div>').text(item.status).html();
            $wrap.append(
                '<div class="crm-pipeline-stage" role="button" tabindex="0" data-status-id="' + item.status_id + '" style="animation-delay:' + (0.04 * index) + 's">' +
                    '<div class="crm-pipeline-stage-top">' +
                        '<span class="crm-pipeline-stage-label">' + label + '</span>' +
                        '<span class="crm-pipeline-stage-count">' + count + '</span>' +
                    '</div>' +
                    '<div class="crm-pipeline-stage-track"><div class="crm-pipeline-stage-fill" style="width:' + pct + '%"></div></div>' +
                '</div>'
            );
        });
        $wrap.off('click.pipelineStage keypress.pipelineStage')
            .on('click.pipelineStage', '.crm-pipeline-stage', function () {
                openLeadsByStatus($(this).data('status-id'));
            })
            .on('keypress.pipelineStage', '.crm-pipeline-stage', function (e) {
                if (e.which === 13) openLeadsByStatus($(this).data('status-id'));
            });
    }

    function dashFormatDateDMY(dateStr) {
        if (!dateStr) return '—';
        var parts = String(dateStr).slice(0, 10).split('-');
        if (parts.length !== 3) return dateStr;
        return parts[2] + '-' + parts[1] + '-' + parts[0];
    }

    function fetchOverdueFollowups() {
        $.getJSON('overdue-followups.php').done(function (res) {
            var $body = $('#overdueFollowupsBody');
            var $badge = $('#overdueBadge');
            if (res.status !== 'success') {
                $badge.hide();
                $body.html('<tr><td colspan="6" class="text-center text-muted">Unable to load overdue follow-ups</td></tr>');
                return;
            }
            var list = res.data || [];
            if (!list.length) {
                $badge.hide();
                $body.html('<tr><td colspan="6" class="text-center text-muted">No overdue follow-ups</td></tr>');
                return;
            }
            $badge.text(list.length + ' overdue').show();
            var rows = '';
            list.forEach(function (item) {
                rows += '<tr class="crm-overdue-row" data-lead-id="' + item.lead_id + '">' +
                    '<td>' + escapeDashHtml(item.company) + '</td>' +
                    '<td>' + escapeDashHtml(item.description) + '</td>' +
                    '<td>' + dashFormatDateDMY(item.due_date) + '</td>' +
                    '<td><span class="crm-overdue-days">' + item.days_overdue + 'd</span></td>' +
                    '<td>' + escapeDashHtml(item.status) + '</td>' +
                    '<td>' + escapeDashHtml(item.user) + '</td>' +
                '</tr>';
            });
            $body.html(rows);
        }).fail(function () {
            $('#overdueBadge').hide();
            $('#overdueFollowupsBody').html('<tr><td colspan="6" class="text-center text-muted">Unable to load overdue follow-ups</td></tr>');
        });
    }

    $('#overdueFollowupsBody').on('click', 'tr.crm-overdue-row', function () {
        var leadId = $(this).data('lead-id');
        if (!leadId) return;
        var form = document.createElement('form');
        form.method = 'post';
        form.action = 'lead-replay.php';
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'leadId';
        input.value = leadId;
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    });

    fetchOverdueFollowups();

    window.addEventListener('crm-theme-changed', function () {
        setTimeout(function () {
            try {
                if (window.dashReminderCalendarInstance && typeof window.dashReminderCalendarInstance.updateSize === 'function') {
                    window.dashReminderCalendarInstance.updateSize();
                }
            } catch (e) {}
        }, 40);
    });

    $.getJSON('lead_status_summary.php').done(function (res) {
        if (res.status !== 'success') return;
        renderPipelineStages(res.data || []);
        (res.data || []).forEach(function (item, index) {
            var theme = statusThemes[index % statusThemes.length];
            var icon = statusIcons[index % statusIcons.length];
            $row.append(
                '<div class="col-sm-6 col-xl-4" style="animation-delay:' + (0.05 * index) + 's">' +
                    '<div class="crm-status-card crm-status-card--' + theme + '" onclick="location.href=\'list-leads-by-status.php?status=' + item.status_id + '\'">' +
                        '<div class="crm-status-card-top">' +
                            '<span class="crm-status-icon"><i class="bx ' + icon + '"></i></span>' +
                            '<span class="crm-status-count">' + item.count + '</span>' +
                        '</div>' +
                        '<div class="crm-status-label">' + $('<div>').text(item.status).html() + '</div>' +
                    '</div>' +
                '</div>'
            );
        });
    });

    /* Reminder calendar (replaces follow-up chart) */
    var dashCalEl = document.getElementById('dashReminderCalendar');
    if (dashCalEl && typeof FullCalendar !== 'undefined') {
        var dashModalEl = document.getElementById('dashCalEventModal');
        var dashModal = dashModalEl ? new bootstrap.Modal(dashModalEl) : null;
        var dashPendingLeadId = null;

        function dashFormatDate(dateStr) {
            if (!dateStr) return '—';
            var parts = String(dateStr).slice(0, 10).split('-');
            if (parts.length !== 3) return dateStr;
            return parts[2] + '-' + parts[1] + '-' + parts[0];
        }

        function dashOpenLead(leadId) {
            var form = document.createElement('form');
            form.method = 'post';
            form.action = 'add-lead-master.php';
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'leadId';
            input.value = leadId;
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }

        var dashCalendar = new FullCalendar.Calendar(dashCalEl, {
            initialView: 'dayGridMonth',
            height: 420,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,listWeek'
            },
            buttonText: { today: 'Today', month: 'Month', list: 'List' },
            dayMaxEvents: 3,
            moreLinkClick: 'popover',
            events: function (info, successCallback, failureCallback) {
                $.ajax({
                    url: 'api.php',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        action: 'calendar_events',
                        start: info.startStr.slice(0, 10),
                        end: info.endStr.slice(0, 10)
                    }),
                    success: function (res) {
                        if (res.status === 'success') {
                            successCallback(res.data || []);
                        } else {
                            failureCallback(new Error(res.message || 'Failed'));
                        }
                    },
                    error: function () {
                        failureCallback(new Error('Network error'));
                    }
                });
            },
            eventClick: function (info) {
                var props = info.event.extendedProps || {};
                var type = props.type || 'event';
                document.getElementById('dashCalEventTitle').textContent = info.event.title;
                document.getElementById('dashCalEventDate').textContent = dashFormatDate(info.event.startStr);
                document.getElementById('dashCalEventType').textContent = type === 'followup' ? 'Follow-up' : 'Reminder';

                var desc = props.description || '';
                var descWrap = document.getElementById('dashCalEventDescWrap');
                if (desc) {
                    descWrap.style.display = '';
                    document.getElementById('dashCalEventDesc').textContent = desc;
                } else {
                    descWrap.style.display = 'none';
                }

                var leadBtn = document.getElementById('dashCalOpenLeadBtn');
                dashPendingLeadId = props.leadId || null;
                if (leadBtn) leadBtn.style.display = dashPendingLeadId ? '' : 'none';
                if (dashModal) dashModal.show();
            }
        });
        dashCalendar.render();
        window.dashReminderCalendarInstance = dashCalendar;

        var openLeadBtn = document.getElementById('dashCalOpenLeadBtn');
        if (openLeadBtn) {
            openLeadBtn.addEventListener('click', function () {
                if (dashPendingLeadId) dashOpenLead(dashPendingLeadId);
            });
        }
    }
});
</script>
</body>
</html>
