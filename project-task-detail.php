<?php
include 'layouts/session.php';
include 'layouts/head-main.php';
include 'layouts/config.php';

$taskId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($taskId <= 0) {
    header('Location: project-management.php');
    exit;
}
?>

<head>
    <title>Task Detail</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .pm-detail-card {
            border: 1px solid var(--crm-border, #e5e7eb);
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 16px;
        }
        .pm-detail-card h6 {
            margin-bottom: 12px;
        }
        .pm-loc-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        @media (max-width: 767.98px) {
            .pm-loc-grid { grid-template-columns: 1fr; }
        }
        .pm-loc-box {
            border: 1px dashed var(--crm-border, #d1d5db);
            border-radius: 8px;
            padding: 12px;
        }
        .pm-loc-box.filled {
            border-style: solid;
        }
        .pm-loc-box .pm-loc-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: var(--crm-text-secondary, #6b7280);
            font-weight: 700;
            margin-bottom: 6px;
        }
        .pm-loc-map {
            width: 100%;
            height: 160px;
            border: 0;
            border-radius: 6px;
            margin-top: 8px;
        }
        .pm-travel-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 16px;
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
        .pm-report-text {
            white-space: pre-wrap;
            line-height: 1.5;
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
                    <a href="#" id="backToProjectBtn" class="btn btn-outline-secondary btn-sm"><i class="bx bx-arrow-back"></i> Back to Project</a>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="refreshBtn"><i class="bx bx-refresh"></i> Refresh</button>
                </div>

                <div id="message" class="mb-3"></div>

                <div class="crm-dash-hero mb-4">
                    <div class="crm-dash-hero-content">
                        <span class="crm-dash-greeting" id="projectMeta">Loading…</span>
                        <h2 id="taskTitle">Loading…</h2>
                        <p id="taskMeta"></p>
                    </div>
                    <div class="crm-dash-hero-meta d-flex align-items-center gap-2">
                        <span class="badge bg-soft-primary text-primary" id="taskStatusBadge">—</span>
                    </div>
                </div>

                <div class="pm-detail-card">
                    <h6><i class="bx bx-map-alt me-1"></i> Description</h6>
                    <div id="taskDescription" class="text-muted">—</div>
                </div>

                <div class="pm-detail-card">
                    <h6><i class="bx bx-navigation me-1"></i> Location &amp; Travel</h6>

                    <div class="pm-loc-grid">
                        <div class="pm-loc-box" id="startLocBox">
                            <div class="pm-loc-label">Start Location</div>
                            <div id="startLocContent" class="text-muted">Not started yet</div>
                        </div>
                        <div class="pm-loc-box" id="endLocBox">
                            <div class="pm-loc-label">End Location</div>
                            <div id="endLocContent" class="text-muted">Not completed yet</div>
                        </div>
                    </div>

                    <div class="pm-travel-stats" id="travelStats" style="display:none;">
                        <div class="pm-stat-tile">
                            <div class="pm-stat-value" id="statDistance">0 km</div>
                            <div class="pm-stat-label">Distance Travelled</div>
                        </div>
                        <div class="pm-stat-tile">
                            <div class="pm-stat-value" id="statPetrol">₹0</div>
                            <div class="pm-stat-label" id="statPetrolLabel">Petrol Cost (₹3/km)</div>
                        </div>
                    </div>

                    <div class="text-muted small mt-3" id="matchDateNote"></div>
                </div>

                <div class="pm-detail-card">
                    <h6><i class="bx bx-file me-1"></i> Visit / Meeting Report</h6>
                    <div id="reportDisplay" class="pm-report-text text-muted">No report submitted yet.</div>
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
var TASK_ID = <?php echo (int)$taskId; ?>;
var currentTask = null;

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

function api(payload) {
    return fetch('project-api.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    }).then(function (r) { return r.json(); });
}

function formatDateTime(dt) {
    if (!dt) return '';
    var d = new Date(String(dt).replace(' ', 'T'));
    if (isNaN(d.getTime())) return dt;
    return d.toLocaleString();
}

function osmEmbedUrl(lat, lng) {
    var delta = 0.005;
    var bbox = (lng - delta) + ',' + (lat - delta) + ',' + (lng + delta) + ',' + (lat + delta);
    return 'https://www.openstreetmap.org/export/embed.html?bbox=' + bbox + '&marker=' + lat + ',' + lng;
}

function renderLocBox(contentEl, boxEl, lat, lng, time, emptyText) {
    if (lat == null || lng == null) {
        boxEl.classList.remove('filled');
        contentEl.innerHTML = '<span class="text-muted">' + escapeHtml(emptyText) + '</span>';
        return;
    }
    boxEl.classList.add('filled');
    var mapsLink = 'https://www.google.com/maps?q=' + lat + ',' + lng;
    contentEl.innerHTML =
        '<div>' + escapeHtml(formatDateTime(time)) + '</div>' +
        '<div class="text-muted small">' + lat.toFixed(6) + ', ' + lng.toFixed(6) + '</div>' +
        '<a href="' + mapsLink + '" target="_blank" class="small"><i class="bx bx-map"></i> Open in Google Maps</a>' +
        '<iframe class="pm-loc-map" src="' + osmEmbedUrl(lat, lng) + '" loading="lazy"></iframe>';
}

function formatDateOnly(d) {
    if (!d) return '';
    var parts = String(d).slice(0, 10).split('-');
    return parts.length === 3 ? (parts[2] + '-' + parts[1] + '-' + parts[0]) : d;
}

function loadTask() {
    return api({ action: 'get_project_task', id: TASK_ID }).then(function (res) {
        if (!res || res.status !== 'success') {
            showMsg((res && res.message) || 'Task not found', false);
            setTimeout(function () { window.location.href = 'project-management.php'; }, 1200);
            return;
        }
        var t = res.data;
        currentTask = t;

        document.getElementById('backToProjectBtn').href = 'project-tasks.php?lead_id=' + t.lead_id;
        document.getElementById('projectMeta').textContent = t.company_name || t.lead_name || ('Project #' + t.lead_id);
        document.getElementById('taskTitle').textContent = t.sTitle;
        document.getElementById('taskMeta').textContent =
            'Assigned to: ' + (t.assigned_name || '—') + '  ·  Created by: ' + (t.created_by_name || '—') +
            (t.sDue_date ? ('  ·  Due: ' + t.sDue_date) : '');
        document.getElementById('taskDescription').textContent = t.sDescription || 'No description provided';

        var statusEl = document.getElementById('taskStatusBadge');
        statusEl.textContent = t.sStatus;
        statusEl.className = 'badge ' + (t.sStatus === 'Done' ? 'bg-soft-success text-success' : (t.sStatus === 'In Progress' ? 'bg-soft-warning text-warning' : 'bg-soft-secondary text-secondary'));

        renderLocBox(document.getElementById('startLocContent'), document.getElementById('startLocBox'), t.start_latitude, t.start_longitude, t.start_time, 'Not started yet');
        renderLocBox(document.getElementById('endLocContent'), document.getElementById('endLocBox'), t.end_latitude, t.end_longitude, t.end_time, 'Not completed yet');

        if (t.distance_km != null) {
            document.getElementById('travelStats').style.display = '';
            document.getElementById('statDistance').textContent = t.distance_km + ' km';
            document.getElementById('statPetrol').textContent = '₹' + t.petrol_cost;
            document.getElementById('statPetrolLabel').textContent = 'Petrol Cost (₹' + t.petrol_rate_per_km + '/km)';
        } else {
            document.getElementById('travelStats').style.display = 'none';
        }
        document.getElementById('matchDateNote').textContent = t.match_date
            ? ('Location & report shown are matched by date: ' + formatDateOnly(t.match_date))
            : '';

        var reportLines = [];
        if (t.visit_type) {
            reportLines.push(t.visit_type + (t.visit_location ? ' — ' + t.visit_location : ''));
        }
        if (t.report) {
            reportLines.push(t.report);
        }
        if (reportLines.length) {
            document.getElementById('reportDisplay').textContent = reportLines.join('\n');
            document.getElementById('reportDisplay').classList.remove('text-muted');
        } else {
            document.getElementById('reportDisplay').textContent = 'No report submitted yet.';
            document.getElementById('reportDisplay').classList.add('text-muted');
        }
    });
}

document.getElementById('refreshBtn').addEventListener('click', loadTask);

$(document).ready(loadTask);
</script>
</html>
