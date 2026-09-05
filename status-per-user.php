<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include 'layouts/config.php';

$view_user_id = isset($_GET['userid']) ? (int)$_GET['userid'] : (int)$_SESSION['user_id'];
$view_user_name = 'User';

$stmt = $link->prepare("SELECT sName FROM tbluser WHERE iUserid = ?");
if ($stmt) {
    $stmt->bind_param("i", $view_user_id);
    $stmt->execute();
    $stmt->bind_result($view_user_name);
    $stmt->fetch();
    $stmt->close();
}

$greeting = date('H') < 12 ? 'morning' : (date('H') < 17 ? 'afternoon' : 'evening');
?>

<head>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <title>User Dashboard</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<?php include 'layouts/body.php'; ?>

<style>
.crm-dash-stat-link { text-decoration: none; color: inherit; display: block; height: 100%; }
</style>

<!-- Begin page -->
<div id="layout-wrapper">

    <?php include 'layouts/menu.php'; ?>

    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                <div class="crm-dash-hero">
                    <div class="crm-dash-hero-content">
                        <span class="crm-dash-greeting">Good <?php echo $greeting; ?>,</span>
                        <h2><?php echo htmlspecialchars($view_user_name); ?></h2>
                        <p>CRM snapshot for this user. Track leads, follow-ups, and pipeline at a glance.</p>
                        <a href="display-users.php" class="btn btn-sm btn-outline-primary mt-2">
                            <i class="bx bx-arrow-back"></i> Back to Users
                        </a>
                    </div>
                    <div class="crm-dash-hero-meta">
                        <div class="crm-dash-date">
                            <i class="bx bx-calendar"></i>
                            <span id="dashDate"></span>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="crm-stat-card crm-stat-card--blue">
                            <div class="crm-stat-accent"></div>
                            <div class="stat-info">
                                <h6>Today's Reminders</h6>
                                <p class="stat-value" id="reminderCount">0</p>
                                <span class="stat-hint">Scheduled reminders</span>
                            </div>
                            <div class="stat-icon blue"><i class="bx bx-bell"></i></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="crm-stat-card crm-stat-card--green">
                            <div class="crm-stat-accent"></div>
                            <div class="stat-info">
                                <h6>Today's Follow Ups</h6>
                                <p class="stat-value" id="followupCount">0</p>
                                <span class="stat-hint">Leads needing follow-up</span>
                            </div>
                            <div class="stat-icon green"><i class="bx bx-time-five"></i></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="crm-stat-card crm-stat-card--purple">
                            <div class="crm-stat-accent"></div>
                            <div class="stat-info">
                                <h6>Assigned Leads</h6>
                                <p class="stat-value" id="assignedCount">0</p>
                                <span class="stat-hint">Leads assigned to this user</span>
                            </div>
                            <div class="stat-icon purple"><i class="bx bx-user-check"></i></div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-lg-5">
                        <div class="crm-chart-card crm-chart-card--tall">
                            <div class="crm-chart-card-head">
                                <div>
                                    <h6>Lead Pipeline</h6>
                                    <p>Distribution across all lead statuses</p>
                                </div>
                            </div>
                            <div class="crm-pipeline-chart-wrap">
                                <div id="leadPipelineChart"></div>
                                <div id="leadPipelineLegend" class="crm-pipeline-legend"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="crm-panel-card">
                            <div class="crm-panel-card-head">
                                <div>
                                    <h6>Lead Status Overview</h6>
                                    <p>Click any status to view matching leads</p>
                                </div>
                            </div>
                            <div class="row g-3" id="statusCardsRow">
                                <!-- Status cards appended by JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <?php include 'layouts/footer.php'; ?>
    <!-- end main content-->
    </div>
</div>
<!-- END layout-wrapper -->

<!-- Right Sidebar -->
<?php include 'layouts/right-sidebar.php'; ?>
<!-- /Right-bar -->

<!-- JAVASCRIPT -->
<?php include 'layouts/vendor-scripts.php'; ?>

<!-- apexcharts -->
<script src="assets/libs/apexcharts/apexcharts.min.js"></script>

<!-- App js -->
<script src="assets/js/app.js"></script>

</body>

<script>
function checkTokenStatus() {
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
$(function () {
    var userid = <?php echo json_encode($view_user_id); ?>;
    var useridParam = userid ? '?userid=' + userid : '';

    var now = new Date();
    var options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    $('#dashDate').text(now.toLocaleDateString('en-US', options));

    /* 1. Load reminder / follow-up / assigned counts */
    $.getJSON('dashboard_data-user.php' + useridParam).done(function(res) {
        if (res.status === 'success') {
            $('#reminderCount').text(res.reminders);
            $('#followupCount').text(res.followups);
            $('#assignedCount').text(res.assigned);
        }
    });

    /* 2. Build status cards + chart */
    const $row = $('#statusCardsRow').empty();
    const statusThemes = ['blue', 'green', 'orange', 'purple', 'teal', 'rose', 'indigo', 'amber'];
    const statusIcons = ['bx-time', 'bx-check-circle', 'bx-loader-circle', 'bx-x-circle', 'bx-star', 'bx-flag', 'bx-target-lock', 'bx-trending-up'];
    let pipelineChart = null;

    function renderPipelineLegend(items, colors) {
        const $legend = $('#leadPipelineLegend').empty();
        if (!items.length) return;

        items.forEach(function(item, index) {
            const color = colors[index % colors.length];
            const count = parseInt(item.count, 10) || 0;
            $legend.append(
                '<span class="crm-pipeline-legend-item" title="' + $('<div>').text(item.status).html() + '">' +
                    '<span class="crm-pipeline-legend-dot" style="background:' + color + '"></span>' +
                    '<span class="crm-pipeline-legend-label">' + $('<div>').text(item.status).html() + '</span>' +
                    '<strong class="crm-pipeline-legend-count">' + count + '</strong>' +
                '</span>'
            );
        });
    }

    function renderPipelineChart(items) {
        if (!items.length || typeof ApexCharts === 'undefined') return;

        const labels = items.map(i => i.status);
        const series = items.map(i => parseInt(i.count, 10) || 0);
        const colors = ['#9333ea', '#7c3aed', '#a855f7', '#5b21b6', '#059669', '#d97706', '#c026d3', '#1e1b4b'];
        const chartColors = colors.slice(0, items.length);
        const total = series.reduce(function(a, b) { return a + b; }, 0);

        renderPipelineLegend(items, chartColors);

        const options = {
            chart: {
                type: 'donut',
                height: 260,
                fontFamily: 'Inter, sans-serif',
                toolbar: { show: false }
            },
            series: total > 0 ? series : [1],
            labels: total > 0 ? labels : ['No leads'],
            colors: total > 0 ? chartColors : ['#e8e4f3'],
            legend: { show: false },
            plotOptions: {
                pie: {
                    donut: {
                        size: '68%',
                        labels: {
                            show: true,
                            name: { show: total > 0 },
                            value: { show: total > 0, fontSize: '22px', fontWeight: 700, color: '#1e1b4b' },
                            total: {
                                show: true,
                                showAlways: true,
                                label: 'Total Leads',
                                fontSize: '13px',
                                color: '#8b85a8',
                                formatter: function () {
                                    return total;
                                }
                            }
                        }
                    }
                }
            },
            states: {
                hover: { filter: { type: total > 0 ? 'lighten' : 'none' } },
                active: { filter: { type: total > 0 ? 'darken' : 'none' } }
            },
            dataLabels: { enabled: false },
            stroke: { width: 2, colors: ['#fff'] },
            tooltip: {
                enabled: total > 0,
                theme: 'light',
                y: { formatter: function(val) { return val + ' leads'; } }
            }
        };

        if (pipelineChart) {
            pipelineChart.destroy();
        }
        pipelineChart = new ApexCharts(document.querySelector('#leadPipelineChart'), options);
        pipelineChart.render();
    }

    $.getJSON('lead_status_summary-user.php' + useridParam)
        .done(function(res) {
            if (res.status !== 'success') { console.error(res.message); return; }

            renderPipelineChart(res.data);

            res.data.forEach(function(item, index) {
                const theme = statusThemes[index % statusThemes.length];
                const icon = statusIcons[index % statusIcons.length];

                $row.append(`
                    <div class="col-sm-6 col-xl-4">
                        <div class="crm-status-card crm-status-card--${theme}"
                             onclick="location.href='list-leads-by-status-user.php?status=${item.status_id}&userid=${userid}'">
                            <div class="crm-status-card-top">
                                <span class="crm-status-icon"><i class="bx ${icon}"></i></span>
                                <span class="crm-status-count">${item.count}</span>
                            </div>
                            <div class="crm-status-label">${item.status}</div>
                        </div>
                    </div>
                `);
            });
        })
        .fail(function(jqxhr, textStatus, errorThrown) {
            console.error('AJAX error →', textStatus, errorThrown);
            console.error('Raw response →', jqxhr.responseText);
        });
});
</script>

</html>
