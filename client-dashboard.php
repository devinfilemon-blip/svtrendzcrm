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
    <title>Dashboard</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .crm-task-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 600;
            color: var(--crm-text-secondary, #4c4585);
        }
        .crm-task-pill strong { color: var(--crm-primary, #9333ea); }
        .crm-recent-project-row { cursor: pointer; }
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
                        <p>Here's a snapshot of how your project(s) are progressing.</p>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-3 col-sm-6">
                        <div class="crm-stat-v3 crm-stat-v3--total">
                            <div class="crm-stat-v3-icon"><i class="bx bx-briefcase"></i></div>
                            <div>
                                <p class="crm-stat-v3-num" id="statTotalProjects">0</p>
                                <h6>Total Projects</h6>
                                <span class="crm-stat-v3-hint">All projects with us</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="crm-stat-v3 crm-stat-v3--completed">
                            <div class="crm-stat-v3-icon"><i class="bx bx-check-circle"></i></div>
                            <div>
                                <p class="crm-stat-v3-num" id="statCompletedProjects">0</p>
                                <h6>Completed</h6>
                                <span class="crm-stat-v3-hint">Delivered successfully</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="crm-stat-v3 crm-stat-v3--active">
                            <div class="crm-stat-v3-icon"><i class="bx bx-loader-circle"></i></div>
                            <div>
                                <p class="crm-stat-v3-num" id="statActiveProjects">0</p>
                                <h6>In Progress</h6>
                                <span class="crm-stat-v3-hint">Currently being worked on</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="crm-stat-v3 crm-stat-v3--onhold">
                            <div class="crm-stat-v3-icon"><i class="bx bx-pause-circle"></i></div>
                            <div>
                                <p class="crm-stat-v3-num" id="statOnHoldProjects">0</p>
                                <h6>On Hold</h6>
                                <span class="crm-stat-v3-hint">Temporarily paused</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <div class="crm-client-progress-card">
                            <div class="crm-client-progress-head">
                                <h6>Project Completion</h6>
                                <span id="statCompletionPct">0%</span>
                            </div>
                            <div class="crm-client-progress-track">
                                <div class="crm-client-progress-fill" id="statCompletionFill" style="width: 0%;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-lg-5">
                        <div class="crm-panel-v3">
                            <div class="crm-panel-v3-head">
                                <h6><i class="bx bx-pie-chart-alt-2" style="color:#4f46e5;"></i> Project Status</h6>
                                <p>Breakdown of all your projects</p>
                            </div>
                            <div class="crm-pipeline-chart-wrap">
                                <div id="projectStatusChart"></div>
                                <div id="projectStatusLegend" class="crm-pipeline-legend"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="crm-panel-card mb-0 h-100">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h5 class="mb-0">Recent Projects</h5>
                                <a href="client-portal-projects.php" class="btn btn-sm btn-outline-primary">
                                    View All <i class="bx bx-right-arrow-alt"></i>
                                </a>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover crm-leads-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Project Name</th>
                                            <th>Status</th>
                                            <th>Tasks</th>
                                            <th>Due Date</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="recentProjectsBody"></tbody>
                                </table>
                            </div>
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
<script src="assets/libs/apexcharts/apexcharts.min.js"></script>
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

function updateProjectStats(rows) {
    var total = rows.length;
    var completed = 0, active = 0, onHold = 0;

    rows.forEach(function (p) {
        if (p.sStatus === 'Completed') completed++;
        else if (p.sStatus === 'On Hold') onHold++;
        else active++; // 'Active' and any other/legacy status counts as in-progress
    });

    var pct = total > 0 ? Math.round((completed / total) * 100) : 0;

    $('#statTotalProjects').text(total);
    $('#statCompletedProjects').text(completed);
    $('#statActiveProjects').text(active);
    $('#statOnHoldProjects').text(onHold);
    $('#statCompletionPct').text(pct + '%');
    $('#statCompletionFill').css('width', pct + '%');
}

var projectStatusChart = null;
// Fixed category order + colors, validated colorblind-safe (dataviz palette check).
var STATUS_ORDER = ['Completed', 'In Progress', 'On Hold'];
var STATUS_COLORS = { 'Completed': '#16a34a', 'In Progress': '#2563eb', 'On Hold': '#d97706' };

function getChartThemeColors() {
    var isDark = document.body.classList.contains('crm-dark');
    return {
        valueColor: isDark ? '#f8fafc' : '#0f172a',
        labelColor: isDark ? '#94a3b8' : '#64748b',
        strokeColor: isDark ? '#111827' : '#ffffff',
        emptyColor: isDark ? '#1e2d3d' : '#dbe4f5',
        tooltipTheme: isDark ? 'dark' : 'light'
    };
}

function renderStatusLegend(counts) {
    var $legend = $('#projectStatusLegend').empty();
    STATUS_ORDER.forEach(function (label) {
        $legend.append(
            '<span class="crm-pipeline-legend-item">' +
                '<span class="crm-pipeline-legend-dot" style="background:' + STATUS_COLORS[label] + '"></span>' +
                '<span class="crm-pipeline-legend-label">' + escapeHtml(label) + '</span>' +
                '<strong class="crm-pipeline-legend-count">' + (counts[label] || 0) + '</strong>' +
            '</span>'
        );
    });
}

function renderStatusChart(rows) {
    if (typeof ApexCharts === 'undefined') return;

    var counts = { 'Completed': 0, 'In Progress': 0, 'On Hold': 0 };
    rows.forEach(function (p) {
        if (p.sStatus === 'Completed') counts['Completed']++;
        else if (p.sStatus === 'On Hold') counts['On Hold']++;
        else counts['In Progress']++; // 'Active' and any other/legacy status
    });

    var total = rows.length;
    var series = STATUS_ORDER.map(function (label) { return counts[label]; });
    var colors = STATUS_ORDER.map(function (label) { return STATUS_COLORS[label]; });
    var theme = getChartThemeColors();

    renderStatusLegend(counts);

    var options = {
        chart: {
            type: 'donut',
            height: 260,
            fontFamily: 'Inter, sans-serif',
            toolbar: { show: false },
            background: 'transparent',
            animations: { enabled: true, easing: 'easeinout', speed: 450 }
        },
        series: total > 0 ? series : [1],
        labels: total > 0 ? STATUS_ORDER : ['No projects'],
        colors: total > 0 ? colors : [theme.emptyColor],
        legend: { show: false },
        states: {
            active: { filter: { type: 'none' } },
            hover: { filter: { type: 'lighten', value: 0.08 } }
        },
        plotOptions: {
            pie: {
                expandOnClick: false,
                donut: {
                    size: '68%',
                    labels: {
                        show: true,
                        name: {
                            show: total > 0,
                            fontSize: '13px',
                            color: theme.labelColor,
                            formatter: function (val) { return val; }
                        },
                        value: {
                            show: true,
                            fontSize: '28px',
                            fontWeight: 800,
                            color: theme.valueColor,
                            formatter: function (val) { return val; }
                        },
                        total: {
                            show: true,
                            showAlways: true,
                            label: 'Total Projects',
                            fontSize: '13px',
                            fontWeight: 600,
                            color: theme.labelColor,
                            formatter: function () { return String(total); }
                        }
                    }
                }
            }
        },
        dataLabels: { enabled: false },
        stroke: { width: 3, colors: [theme.strokeColor] },
        tooltip: {
            enabled: total > 0,
            theme: theme.tooltipTheme,
            y: { formatter: function (val) { return val + ' project' + (val === 1 ? '' : 's'); } }
        }
    };

    var el = document.querySelector('#projectStatusChart');
    if (!el) return;

    if (projectStatusChart) {
        try { projectStatusChart.destroy(); } catch (e) {}
        projectStatusChart = null;
        el.innerHTML = '';
    }
    projectStatusChart = new ApexCharts(el, options);
    projectStatusChart.render();
}

function renderRecentProjects(rows) {
    var $tbody = $('#recentProjectsBody');
    $tbody.empty();

    if (!rows.length) {
        $tbody.html('<tr><td colspan="5" class="text-center text-muted">No projects assigned to you yet</td></tr>');
        return;
    }

    rows.slice(0, 5).forEach(function (p) {
        var tasks = (p.task_done || 0) + ' / ' + (p.task_total || 0);
        $tbody.append(
            '<tr class="crm-recent-project-row" data-id="' + p.iId + '">' +
                '<td><strong>' + escapeHtml(p.sProjectName) + '</strong></td>' +
                '<td>' + statusBadge(p.sStatus) + '</td>' +
                '<td><span class="crm-task-pill"><i class="bx bx-task"></i><strong>' + escapeHtml(tasks) + '</strong> done</span></td>' +
                '<td>' + escapeHtml(p.dDueDate || '—') + '</td>' +
                '<td><a href="client-portal-project-view.php?project_id=' + p.iId + '" class="btn btn-sm btn-primary" onclick="event.stopPropagation()"><i class="bx bx-show"></i> View</a></td>' +
            '</tr>'
        );
    });
}

function loadDashboard() {
    fetch('client-project-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'list_client_projects' })
    })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (!res || res.status !== 'success') {
                updateProjectStats([]);
                renderStatusChart([]);
                renderRecentProjects([]);
                return;
            }
            var rows = res.data || [];
            updateProjectStats(rows);
            renderStatusChart(rows);
            renderRecentProjects(rows);
        });
}

$(document).ready(function () {
    loadDashboard();
    $('#recentProjectsBody').on('click', 'tr.crm-recent-project-row', function () {
        var id = $(this).data('id');
        if (id) openProject(id);
    });
});
</script>
</html>
