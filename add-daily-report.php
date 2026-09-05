<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include 'layouts/config.php';
$msg = "";
$id = null;
if (isset($_POST['id'])) {
    $id = $_POST['id'];
}
$employeeName = isset($_SESSION['username']) ? $_SESSION['username'] : '';
?>

<head>
    <title><?php echo isset($id) ? 'Edit' : 'Add'; ?> Daily Report</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        .add-message { color: green; font-weight: bold; }
        .error-message { color: red; font-weight: bold; }
        .btn-primary {
            color: #fff;
            background-color: #005aa5;
            border-color: #005aa5;
        }
        .report-hint {
            color: #74788d;
            font-size: 13px;
            margin-bottom: 1rem;
        }
        .report-instructions {
            background: #f8f9fa;
            border-left: 3px solid #005aa5;
            padding: 12px 16px;
            margin-top: 1.5rem;
            font-size: 13px;
        }
        .report-instructions h6 {
            color: #005aa5;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .report-instructions ul {
            margin-bottom: 0;
            padding-left: 18px;
        }
        .report-instructions li { margin-bottom: 4px; }
    </style>
</head>
<?php include 'layouts/body.php'; ?>
<div id="layout-wrapper">

    <?php include 'layouts/menu.php'; ?>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18"><?php echo isset($id) ? 'Edit' : 'Add'; ?> Daily Report</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="list-daily-report.php">Daily Report</a></li>
                                    <li class="breadcrumb-item active"><?php echo isset($id) ? 'Edit' : 'Add'; ?> Daily Report</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-body">
                                <p class="report-hint">One format for all departments — Sales, Development, Design, Marketing</p>
                                <div><span id="message"></span></div>
                                <form id="reportForm" method="post" onsubmit="return false;">
                                    <input type="hidden" id="id" name="id" value="">

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Employee</label>
                                                <input type="text" class="form-control" id="employee_name" value="<?php echo htmlspecialchars($employeeName); ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" id="date" name="date" required>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="mb-3">
                                                <label for="time_in" class="form-label">Time In <small class="text-muted">(24 hrs)</small></label>
                                                <input type="text" class="form-control" id="time_in" name="time_in"
                                                       placeholder="e.g. 10:00" maxlength="5"
                                                       pattern="^([01][0-9]|2[0-3]):[0-5][0-9]$"
                                                       title="Use 24-hour format HH:MM (e.g. 10:00, 19:00)">
                                                <div class="form-check mt-2">
                                                    <input class="form-check-input" type="checkbox" id="time_in_check" onchange="toggleTimeCheck('in')">
                                                    <label class="form-check-label" for="time_in_check">Check (10:00)</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="mb-3">
                                                <label for="time_out" class="form-label">Time Out <small class="text-muted">(24 hrs)</small></label>
                                                <input type="text" class="form-control" id="time_out" name="time_out"
                                                       placeholder="e.g. 18:00" maxlength="5"
                                                       pattern="^([01][0-9]|2[0-3]):[0-5][0-9]$"
                                                       title="Use 24-hour format HH:MM (e.g. 10:00, 19:00)">
                                                <div class="form-check mt-2">
                                                    <input class="form-check-input" type="checkbox" id="time_out_check" onchange="toggleTimeCheck('out')">
                                                    <label class="form-check-label" for="time_out_check">Check (18:00)</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="tasks_planned" class="form-label">Tasks Planned Today</label>
                                                <textarea class="form-control" id="tasks_planned" name="tasks_planned" rows="3" placeholder="What did you plan to work on today?"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="tasks_completed" class="form-label">Tasks Completed</label>
                                                <textarea class="form-control" id="tasks_completed" name="tasks_completed" rows="3" placeholder="What did you complete today?"></textarea>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="work_details" class="form-label">Work Details</label>
                                                <textarea class="form-control" id="work_details" name="work_details" rows="3" placeholder="Client / business / project name + what was done"></textarea>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="pending" class="form-label">Pending / Carried Forward</label>
                                                <textarea class="form-control" id="pending" name="pending" rows="3" placeholder="Tasks still pending or carried to next day"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="blockers" class="form-label">Blockers / Support Needed</label>
                                                <textarea class="form-control" id="blockers" name="blockers" rows="3" placeholder="Anything blocking your work or support you need"></textarea>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="tomorrow_plan" class="form-label">Tomorrow's Plan</label>
                                                <textarea class="form-control" id="tomorrow_plan" name="tomorrow_plan" rows="3" placeholder="What will you work on tomorrow?"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="remarks" class="form-label">Remarks</label>
                                                <textarea class="form-control" id="remarks" name="remarks" rows="3" placeholder="Leads visited, hours logged, links, or anything extra"></textarea>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <button type="button" class="btn btn-primary w-md" onclick="<?php echo isset($id) ? 'updatedailyreport();' : 'adddailyreport();'; ?>">
                                                Save Report
                                            </button>
                                            <a href="list-daily-report.php" class="btn btn-secondary w-md ms-2">Cancel</a>
                                        </div>
                                    </div>
                                </form>

                                <div class="report-instructions">
                                    <h6>Instructions</h6>
                                    <ul>
                                        <li>Fill and submit this report every day by 6:00 PM.</li>
                                        <li>“Work Details” covers whatever applies to your role — businesses visited (sales), tasks/modules worked on (dev/design), or campaigns run (marketing).</li>
                                        <li>Team lead / Admin can view all reports in the list.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <?php include 'layouts/footer.php'; ?>
    </div>
</div>
</body>
</html>

<?php include 'layouts/right-sidebar.php'; ?>
<?php include 'layouts/vendor-scripts.php'; ?>

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

function isValidTime24(value) {
    if (!value) return true; // optional
    return /^([01][0-9]|2[0-3]):[0-5][0-9]$/.test(value);
}

function toggleTimeCheck(which) {
    if (which === 'in') {
        var checked = document.getElementById('time_in_check').checked;
        if (checked) {
            document.getElementById('time_in').value = '10:00';
        }
    } else if (which === 'out') {
        var checked = document.getElementById('time_out_check').checked;
        if (checked) {
            document.getElementById('time_out').value = '18:00';
        }
    }
}

function collectReportData(action) {
    return {
        action: action,
        date: document.getElementById('date').value,
        time_in: document.getElementById('time_in').value.trim(),
        time_out: document.getElementById('time_out').value.trim(),
        time_in_check: document.getElementById('time_in_check').checked ? 1 : 0,
        time_out_check: document.getElementById('time_out_check').checked ? 1 : 0,
        tasks_planned: document.getElementById('tasks_planned').value,
        tasks_completed: document.getElementById('tasks_completed').value,
        work_details: document.getElementById('work_details').value,
        pending: document.getElementById('pending').value,
        blockers: document.getElementById('blockers').value,
        tomorrow_plan: document.getElementById('tomorrow_plan').value,
        remarks: document.getElementById('remarks').value
    };
}

function showMessage(msg, isSuccess) {
    var el = document.getElementById('message');
    el.innerHTML = msg;
    el.className = isSuccess ? 'add-message' : 'error-message';
}

function adddailyreport() {
    var data = collectReportData('adddailyreport');
    if (!data.date) {
        alert('Please select the date.');
        return false;
    }
    if (!isValidTime24(data.time_in) || !isValidTime24(data.time_out)) {
        alert('Please enter Time In / Time Out in 24-hour format (HH:MM), e.g. 10:00 or 19:00.');
        return false;
    }
    fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(function(r) { return r.json(); })
    .then(function(responseData) {
        if (responseData.status === 'success') {
            showMessage(responseData.message, true);
            setTimeout(function() { window.location.href = 'list-daily-report.php'; }, 500);
        } else {
            showMessage(responseData.message || 'Failed to save report.', false);
        }
    })
    .catch(function(error) { console.error('Error:', error); });
}

function updatedailyreport() {
    var id = <?php echo isset($id) ? (int)$id : 0; ?>;
    var data = collectReportData('updatedailyreport');
    data.id = id;
    if (!id || !data.date) {
        alert('Enter required data.');
        return false;
    }
    if (!isValidTime24(data.time_in) || !isValidTime24(data.time_out)) {
        alert('Please enter Time In / Time Out in 24-hour format (HH:MM), e.g. 10:00 or 19:00.');
        return false;
    }
    fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(function(r) { return r.json(); })
    .then(function(responseData) {
        if (responseData.status === 'success') {
            showMessage(responseData.message, true);
            setTimeout(function() { window.location.href = 'list-daily-report.php'; }, 500);
        } else {
            showMessage(responseData.message || 'Failed to update report.', false);
        }
    })
    .catch(function(error) { console.error('Error:', error); });
}

function getdailyreportbyid() {
    var id = <?php echo isset($id) ? (int)$id : 0; ?>;
    if (!id) {
        // Default date to today for new reports
        document.getElementById('date').value = new Date().toISOString().slice(0, 10);
        return;
    }
    fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'getdailyreportbyid', id: id })
    })
    .then(function(r) { return r.json(); })
    .then(function(responseData) {
        if (responseData.data) {
            var d = responseData.data;
            function toHHMM(t) {
                if (!t) return '';
                // Keep only HH:MM for 24-hour display
                var m = String(t).match(/^(\d{1,2}):(\d{2})/);
                if (!m) return t;
                return ('0' + m[1]).slice(-2) + ':' + m[2];
            }
            document.getElementById('date').value = d.sDate || '';
            document.getElementById('employee_name').value = d.sName || '';
            document.getElementById('time_in').value = toHHMM(d.sTimeIn);
            document.getElementById('time_out').value = toHHMM(d.sTimeOut);
            document.getElementById('time_in_check').checked = parseInt(d.iTimeInChecked || 0, 10) === 1;
            document.getElementById('time_out_check').checked = parseInt(d.iTimeOutChecked || 0, 10) === 1;
            document.getElementById('tasks_planned').value = d.sTasksPlanned || '';
            document.getElementById('tasks_completed').value = d.sTasksCompleted || '';
            document.getElementById('work_details').value = d.sWorkDetails || '';
            document.getElementById('pending').value = d.sPending || '';
            document.getElementById('blockers').value = d.sBlockers || '';
            document.getElementById('tomorrow_plan').value = d.sTomorrowPlan || '';
            document.getElementById('remarks').value = d.sRemarks || '';
        } else {
            alert(responseData.message || 'Report not found.');
            window.location.href = 'list-daily-report.php';
        }
    })
    .catch(function(error) { console.error('Error:', error); });
}

getdailyreportbyid();
</script>
<script src="assets/js/app.js"></script>
