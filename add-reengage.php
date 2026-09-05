<?php
include 'layouts/session.php';
include 'layouts/head-main.php';
include 'layouts/config.php';

$id = null;
if (isset($_POST['id'])) {
    $id = (int)$_POST['id'];
}
?>

<head>
    <title><?php echo isset($id) ? 'Edit' : 'Add'; ?> Re-engage</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .add-message { color: green; font-weight: bold; }
        .error-message { color: red; font-weight: bold; }
        .btn-primary { color: #fff; background-color: #005aa5; border-color: #005aa5; }
        .hint { color: #74788d; font-size: 13px; margin-bottom: 1rem; }
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
                            <h4 class="mb-sm-0 font-size-18"><?php echo isset($id) ? 'Edit' : 'Add'; ?> Re-engage</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="list-reengage.php">Re-engage</a></li>
                                    <li class="breadcrumb-item active"><?php echo isset($id) ? 'Edit' : 'Add'; ?></li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-10">
                        <div class="card">
                            <div class="card-body">
                                <p class="hint">Assign a company from leads to a user for re-engagement. Today's entries appear on the dashboard.</p>
                                <div><span id="message"></span></div>

                                <form id="reengageForm" onsubmit="return false;">
                                    <input type="hidden" id="id" value="">

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="iUserid" class="form-label">User <span class="text-danger">*</span></label>
                                                <select class="form-select" id="iUserid" required>
                                                    <option value="">-- Select User --</option>
                                                    <?php
                                                    $uq = mysqli_query($link, "SELECT iUserid, sName FROM tbluser WHERE sIs_active = 1 ORDER BY sName ASC");
                                                    if ($uq) {
                                                        while ($row = mysqli_fetch_assoc($uq)) {
                                                            echo "<option value='" . (int)$row['iUserid'] . "'>" . htmlspecialchars($row['sName']) . "</option>";
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="iLeadid" class="form-label">Company Name (from Leads) <span class="text-danger">*</span></label>
                                                <select class="form-select" id="iLeadid" required>
                                                    <option value="">-- Select Company --</option>
                                                    <?php
                                                    $lq = mysqli_query($link, "
                                                        SELECT iLead_id, sCompany_name
                                                        FROM tblleads
                                                        WHERE TRIM(COALESCE(sCompany_name, '')) <> ''
                                                        ORDER BY sCompany_name ASC
                                                        LIMIT 3000
                                                    ");
                                                    if ($lq) {
                                                        while ($row = mysqli_fetch_assoc($lq)) {
                                                            $cid = (int)$row['iLead_id'];
                                                            $cname = htmlspecialchars($row['sCompany_name'], ENT_QUOTES);
                                                            echo "<option value='{$cid}' data-company=\"{$cname}\">{$cname}</option>";
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="sDate" class="form-label">Date <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" id="sDate" required>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="sDescription" class="form-label">Description <small class="text-muted">(optional)</small></label>
                                                <input type="text" class="form-control" id="sDescription" placeholder="Notes for re-engage">
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <button type="button" class="btn btn-primary w-md" onclick="<?php echo isset($id) ? 'updatereengage();' : 'addreengage();'; ?>">
                                                Save Re-engage
                                            </button>
                                            <a href="list-reengage.php" class="btn btn-secondary w-md ms-2">Cancel</a>
                                        </div>
                                    </div>
                                </form>
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
        error: function() { window.location.href = 'auth-login.php'; }
    });
}
checkTokenStatus();

function collectPayload(action) {
    var $opt = $('#iLeadid option:selected');
    return {
        action: action,
        id: document.getElementById('id').value || 0,
        iUserid: document.getElementById('iUserid').value,
        iLeadid: document.getElementById('iLeadid').value,
        sCompanyname: $opt.data('company') || $opt.text() || '',
        sDescription: document.getElementById('sDescription').value.trim(),
        sDate: document.getElementById('sDate').value
    };
}

function showMsg(res) {
    var el = document.getElementById('message');
    el.innerHTML = res.message || '';
    el.className = (res.status === 'success') ? 'add-message' : 'error-message';
}

function addreengage() {
    var payload = collectPayload('addreengage');
    if (!payload.iUserid || !payload.iLeadid || !payload.sDate) {
        alert('Please select User, Company and Date.');
        return;
    }
    fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(function (r) { return r.json(); })
    .then(function (res) {
        showMsg(res);
        if (res.status === 'success') {
            setTimeout(function () { window.location.href = 'list-reengage.php'; }, 700);
        }
    })
    .catch(function () { alert('Failed to save.'); });
}

function updatereengage() {
    var payload = collectPayload('updatereengage');
    if (!payload.id || !payload.iUserid || !payload.iLeadid || !payload.sDate) {
        alert('Please fill required fields.');
        return;
    }
    fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(function (r) { return r.json(); })
    .then(function (res) {
        showMsg(res);
        if (res.status === 'success') {
            setTimeout(function () { window.location.href = 'list-reengage.php'; }, 700);
        }
    })
    .catch(function () { alert('Failed to update.'); });
}

function loadEntry(id) {
    fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'getreengagebyid', id: id })
    })
    .then(function (r) { return r.json(); })
    .then(function (res) {
        if (res.status !== 'success' || !res.data) {
            alert(res.message || 'Entry not found');
            return;
        }
        var d = res.data;
        document.getElementById('id').value = d.iReengageid;
        document.getElementById('iUserid').value = d.iUserid || '';
        document.getElementById('iLeadid').value = d.iLeadid || '';
        // If lead option missing (older lead), inject option
        if (d.iLeadid && !$('#iLeadid option[value="' + d.iLeadid + '"]').length) {
            $('#iLeadid').append(
                $('<option>', {
                    value: d.iLeadid,
                    text: d.sCompanyname || ('Lead #' + d.iLeadid),
                    selected: true
                }).attr('data-company', d.sCompanyname || '')
            );
        }
        document.getElementById('sDate').value = (d.sDate || '').slice(0, 10);
        document.getElementById('sDescription').value = d.sDescription || '';
    });
}

$(document).ready(function () {
    document.getElementById('sDate').value = new Date().toISOString().slice(0, 10);
    <?php if (isset($id) && $id > 0): ?>
    loadEntry(<?php echo (int)$id; ?>);
    <?php endif; ?>
});
</script>
</body>
</html>
