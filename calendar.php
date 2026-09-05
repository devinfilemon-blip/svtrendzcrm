<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include 'layouts/config.php'; ?>

<head>
    <title>Calendar — <?php echo APP_NAME; ?></title>
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

                <div class="crm-dash-hero mb-4">
                    <div class="crm-dash-hero-content">
                        <span class="crm-dash-greeting">Schedule</span>
                        <h2>Calendar</h2>
                        <p>Follow-ups and reminders in one view. Click an event for details.</p>
                    </div>
                    <div class="crm-dash-hero-meta d-flex flex-wrap gap-2">
                        <span class="crm-calendar-legend-item"><span class="crm-calendar-dot crm-calendar-dot--followup"></span> Follow-ups</span>
                        <span class="crm-calendar-legend-item"><span class="crm-calendar-dot crm-calendar-dot--reminder"></span> Reminders</span>
                    </div>
                </div>

                <div class="crm-panel-card">
                    <div class="crm-panel-card-body crm-calendar-wrap">
                        <div id="crmCalendar"></div>
                    </div>
                </div>

            </div>
        </div>
        <?php include 'layouts/footer.php'; ?>
    </div>
</div>

<!-- Event detail modal -->
<div class="modal fade" id="calendarEventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="calendarEventTitle">Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2"><strong>Date:</strong> <span id="calendarEventDate"></span></p>
                <p class="mb-2"><strong>Type:</strong> <span id="calendarEventType"></span></p>
                <p class="mb-0" id="calendarEventDescWrap"><strong>Details:</strong> <span id="calendarEventDesc"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="calendarOpenLeadBtn" style="display:none;">Open Lead</button>
            </div>
        </div>
    </div>
</div>

<?php include 'layouts/right-sidebar.php'; ?>
<?php include 'layouts/vendor-scripts.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script src="assets/js/app.js"></script>

<script>
(function () {
    'use strict';

    var calendarEl = document.getElementById('crmCalendar');
    var modalEl = document.getElementById('calendarEventModal');
    var modal = modalEl ? new bootstrap.Modal(modalEl) : null;
    var pendingLeadId = null;

    function formatDate(dateStr) {
        if (!dateStr) return '—';
        var parts = dateStr.split('-');
        if (parts.length !== 3) return dateStr;
        return parts[2] + '-' + parts[1] + '-' + parts[0];
    }

    function openLead(leadId) {
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

    if (!calendarEl || typeof FullCalendar === 'undefined') return;

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 'auto',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listMonth'
        },
        buttonText: {
            today: 'Today',
            month: 'Month',
            week: 'Week',
            list: 'List'
        },
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
                        failureCallback(new Error(res.message || 'Failed to load events'));
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

            document.getElementById('calendarEventTitle').textContent = info.event.title;
            document.getElementById('calendarEventDate').textContent = formatDate(info.event.startStr.slice(0, 10));
            document.getElementById('calendarEventType').textContent = type === 'followup' ? 'Follow-up' : 'Reminder';

            var desc = props.description || '';
            var descWrap = document.getElementById('calendarEventDescWrap');
            if (desc) {
                descWrap.style.display = '';
                document.getElementById('calendarEventDesc').textContent = desc;
            } else {
                descWrap.style.display = 'none';
            }

            var leadBtn = document.getElementById('calendarOpenLeadBtn');
            pendingLeadId = props.leadId || null;
            if (pendingLeadId && leadBtn) {
                leadBtn.style.display = '';
            } else if (leadBtn) {
                leadBtn.style.display = 'none';
            }

            if (modal) modal.show();
        }
    });

    calendar.render();

    document.getElementById('calendarOpenLeadBtn').addEventListener('click', function () {
        if (pendingLeadId) {
            openLead(pendingLeadId);
        }
    });
})();
</script>

</body>
</html>
