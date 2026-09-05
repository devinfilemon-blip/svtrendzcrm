<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include 'layouts/config.php'; ?>

<head>
    <title>User Dashboard</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
    .ud-hero {
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 22px;
        padding: 26px 30px;
        border-radius: 18px;
        background: linear-gradient(135deg, #ffffff 0%, #eef2ff 50%, #dbeafe 100%);
        border: 1px solid #dbe4f5;
        box-shadow: 0 8px 24px rgba(15,23,42,.06);
        animation: crmDashFadeUp .55s ease both;
    }
    body.crm-dark .ud-hero {
        background: linear-gradient(135deg, #111827 0%, #1e3a8a 60%, #0f172a 100%);
        border-color: rgba(255,255,255,.08);
    }
    .ud-hero h2 { font-size: 26px; font-weight: 800; color: #0f172a; margin: 0 0 6px; }
    body.crm-dark .ud-hero h2 { color: #fff !important; }
    .ud-hero p { margin: 0; color: #64748b; font-size: 13px; }
    body.crm-dark .ud-hero p { color: rgba(255,255,255,.7) !important; }
    .ud-hero img {
        width: 110px; height: 110px; object-fit: contain;
        animation: crmFloat 3.5s ease-in-out infinite;
    }
    .ud-stat-row { display: flex; gap: 14px; flex-wrap: wrap; margin-bottom: 20px; }
    .ud-stat-pill {
        flex: 1 1 160px;
        display: flex; align-items: center; gap: 12px;
        padding: 16px 18px;
        border-radius: 14px;
        background: #fff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 14px rgba(15,23,42,.04);
        animation: crmDashFadeUp .55s ease both;
    }
    body.crm-dark .ud-stat-pill { background: #111827; border-color: rgba(255,255,255,.07); }
    .ud-stat-icon {
        width: 44px; height: 44px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center; font-size: 22px;
    }
    .ud-stat-icon--navy { background: #eff6ff; color: #1d4ed8; }
    .ud-stat-icon--green { background: #ecfdf5; color: #059669; }
    body.crm-dark .ud-stat-icon--navy { background: rgba(37,99,235,.18); color: #60a5fa; }
    body.crm-dark .ud-stat-icon--green { background: rgba(34,197,94,.15); color: #4ade80; }
    .ud-stat-num { font-size: 24px; font-weight: 800; line-height: 1; color: #0f172a; }
    body.crm-dark .ud-stat-num { color: #f1f5f9; }
    .ud-stat-label { font-size: 12px; color: #94a3b8; font-weight: 500; }
    .ud-user-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
        gap: 16px;
        margin-bottom: 22px;
    }
    .ud-user-card {
        display: block;
        text-align: center;
        text-decoration: none !important;
        color: inherit !important;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 20px 14px;
        transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
        animation: crmDashFadeUp .5s ease both;
    }
    body.crm-dark .ud-user-card { background: #111827; border-color: rgba(255,255,255,.07); }
    .ud-user-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 28px rgba(30,58,138,.14);
        border-color: #2563eb;
    }
    .ud-avatar {
        width: 56px; height: 56px; border-radius: 50%;
        margin: 0 auto 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px; font-weight: 700; color: #fff;
        background: linear-gradient(135deg, #1e3a8a, #2563eb);
        position: relative;
    }
    .ud-avatar::after {
        content: '';
        position: absolute; inset: -4px;
        border-radius: 50%;
        border: 2px dashed rgba(37,99,235,.35);
        animation: spin 12s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    .ud-user-name { font-size: 14px; font-weight: 700; margin-bottom: 10px; color: #0f172a; }
    body.crm-dark .ud-user-name { color: #f1f5f9; }
    .ud-view-btn {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 999px;
        background: linear-gradient(135deg, #1e3a8a, #2563eb);
        color: #fff !important;
        font-size: 12px;
        font-weight: 600;
    }
    .ud-chart-wrap {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 20px 22px;
        box-shadow: 0 4px 16px rgba(15,23,42,.04);
        margin-bottom: 20px;
    }
    body.crm-dark .ud-chart-wrap { background: #111827; border-color: rgba(255,255,255,.07); }
    .ud-chart-wrap h6 { font-size: 15px; font-weight: 700; margin: 0 0 4px; color: #0f172a; }
    body.crm-dark .ud-chart-wrap h6 { color: #f1f5f9 !important; }
    .ud-chart-wrap p { font-size: 12px; color: #94a3b8; margin: 0 0 12px; }
    </style>
</head>

<?php include 'layouts/body.php'; ?>

<div id="layout-wrapper">
    <?php include 'layouts/menu.php'; ?>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="ud-hero">
                    <div>
                        <h2><i class="bx bx-group me-2"></i>User Dashboard</h2>
                        <p>Overview of all sales users — click any card to see their leads</p>
                    </div>
                    <img src="assets/images/3d-target-clean.png" class="d-none d-md-block" alt="Target">
                </div>

                <div class="ud-stat-row">
                    <div class="ud-stat-pill">
                        <div class="ud-stat-icon ud-stat-icon--navy"><i class="bx bx-group"></i></div>
                        <div>
                            <div class="ud-stat-num" id="udTotalUsers">—</div>
                            <div class="ud-stat-label">Total Users</div>
                        </div>
                    </div>
                    <div class="ud-stat-pill">
                        <div class="ud-stat-icon ud-stat-icon--green"><i class="bx bx-user-check"></i></div>
                        <div>
                            <div class="ud-stat-num" id="udActiveUsers">—</div>
                            <div class="ud-stat-label">Active Users</div>
                        </div>
                    </div>
                </div>

                <div class="ud-user-grid" id="udUserGrid">
                    <div style="grid-column:1/-1;text-align:center;padding:28px;color:#94a3b8;">
                        <i class="bx bx-loader-alt bx-spin" style="font-size:24px;"></i> Loading users…
                    </div>
                </div>

                <div class="ud-chart-wrap">
                    <h6><i class="bx bx-bar-chart-alt-2 me-2" style="color:#2563eb;"></i>Users Overview</h6>
                    <p>Users list activity snapshot</p>
                    <div id="udBarChart" style="min-height:220px;"></div>
                </div>

                <span id="message"></span>
            </div>
        </div>
        <?php include 'layouts/footer.php'; ?>
    </div>
</div>

<?php include 'layouts/right-sidebar.php'; ?>
<?php include 'layouts/vendor-scripts.php'; ?>
<script src="assets/libs/apexcharts/apexcharts.min.js"></script>
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

var colorsPool = ['#2563eb','#f97316','#059669','#ef4444','#0ea5e9','#eab308','#7c3aed','#14b8a6'];
var udBarChartInst = null;

function escapeHtml(str) {
    return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function initUdBarChart(labels, data) {
    if (typeof ApexCharts === 'undefined') return;
    var isDark = document.body.classList.contains('crm-dark');
    var options = {
        chart: { type: 'bar', height: 220, toolbar: { show: false }, background: 'transparent',
            animations: { enabled: true, easing: 'easeinout', speed: 700 } },
        series: [{ name: 'Users', data: data }],
        xaxis: { categories: labels, labels: { style: { colors: isDark ? '#94a3b8' : '#64748b', fontSize: '11px' } } },
        yaxis: { labels: { style: { colors: isDark ? '#94a3b8' : '#64748b' } }, tickAmount: 4, min: 0 },
        colors: ['#2563eb'],
        plotOptions: { bar: { borderRadius: 7, columnWidth: '48%' } },
        dataLabels: { enabled: false },
        grid: { borderColor: isDark ? 'rgba(255,255,255,0.06)' : '#e2e8f0', strokeDashArray: 4 },
        fill: { type: 'gradient', gradient: { type: 'vertical', gradientToColors: ['#60a5fa'], stops: [0, 100] } },
        tooltip: { theme: isDark ? 'dark' : 'light' }
    };
    if (udBarChartInst) udBarChartInst.destroy();
    udBarChartInst = new ApexCharts(document.querySelector('#udBarChart'), options);
    udBarChartInst.render();
}

function fngetlist() {
    $.ajax({
        url: 'api.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ action: 'fngetlist' }),
        success: function (response) {
            if (response.status !== 'success') {
                $('#udUserGrid').html('<div style="grid-column:1/-1;text-align:center;color:#94a3b8;">No users found.</div>');
                return;
            }
            var users = response.data || [];
            var $grid = $('#udUserGrid').empty();
            $('#udTotalUsers').text(users.length);
            $('#udActiveUsers').text(users.length);

            if (!users.length) {
                $grid.html('<div style="grid-column:1/-1;text-align:center;color:#94a3b8;">No users found.</div>');
                return;
            }

            var chartLabels = [], chartData = [];
            users.forEach(function (user, index) {
                var initials = (user.sName || 'U').substring(0, 1).toUpperCase();
                var color = colorsPool[index % colorsPool.length];
                chartLabels.push(user.sName || ('User ' + (index + 1)));
                chartData.push(1);
                $grid.append(
                    '<a href="status-per-user.php?userid=' + user.iUserid + '" class="ud-user-card" style="animation-delay:' + (index * 0.05) + 's">' +
                        '<div class="ud-avatar" style="background:linear-gradient(135deg,' + color + ',' + color + 'cc)">' + initials + '</div>' +
                        '<div class="ud-user-name">' + escapeHtml(user.sName) + '</div>' +
                        '<span class="ud-view-btn">View Leads</span>' +
                    '</a>'
                );
            });
            initUdBarChart(chartLabels, chartData);
        },
        error: function () {
            $('#udUserGrid').html('<div style="grid-column:1/-1;text-align:center;color:#ef4444;">Error loading users.</div>');
        }
    });
}

$(document).ready(fngetlist);
</script>
</body>
</html>
