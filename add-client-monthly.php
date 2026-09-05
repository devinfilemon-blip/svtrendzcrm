<?php
include 'layouts/session.php';
include 'layouts/head-main.php';
include 'layouts/config.php';

$id = null;
if (isset($_POST['id'])) {
    $id = (int)$_POST['id'];
}

// Ensure tables exist (first visit before install SQL)
mysqli_query($link, "CREATE TABLE IF NOT EXISTS tblsoftware_client (
  iClientid INT AUTO_INCREMENT PRIMARY KEY,
  sClientname VARCHAR(255) NOT NULL,
  sCompanyname VARCHAR(255) NULL,
  sContactperson VARCHAR(255) NULL,
  sEmail VARCHAR(150) NULL,
  sPhone VARCHAR(50) NULL,
  sPlan VARCHAR(100) NULL,
  dMonthlyamount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  iBillingday INT NOT NULL DEFAULT 1,
  sStartdate DATE NULL,
  sStatus VARCHAR(20) NOT NULL DEFAULT 'Active',
  sAddress TEXT NULL,
  sNotes TEXT NULL,
  iUserid INT NULL,
  sCreatedTimeStamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  sModifiedTimestamp DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_status (sStatus),
  INDEX idx_name (sClientname)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$clients = [];
$cres = mysqli_query($link, "SELECT iClientid, sClientname, sCompanyname, dMonthlyamount, iBillingday, sStatus
    FROM tblsoftware_client ORDER BY sClientname ASC");
if ($cres) {
    while ($row = mysqli_fetch_assoc($cres)) {
        $clients[] = $row;
    }
}
?>

<head>
    <title><?php echo isset($id) ? 'Edit' : 'Add'; ?> Monthly Report</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .add-message { color: green; font-weight: bold; }
        .error-message { color: red; font-weight: bold; }
        .btn-primary { color: #fff; background-color: #005aa5; border-color: #005aa5; }
        .client-hint { color: #74788d; font-size: 13px; margin-bottom: 1rem; }
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
                            <h4 class="mb-sm-0 font-size-18"><?php echo isset($id) ? 'Edit' : 'Add'; ?> Monthly Client Report</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="list-client-monthly.php">Client Management</a></li>
                                    <li class="breadcrumb-item active"><?php echo isset($id) ? 'Edit' : 'Add'; ?> Monthly Report</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-10">
                        <div class="card">
                            <div class="card-body">
                                <p class="client-hint">Record monthly billing, payment, due date, and service notes for each software client.</p>
                                <div><span id="message"></span></div>

                                <form id="monthlyForm" onsubmit="return false;">
                                    <input type="hidden" id="id" value="">

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Software Client <span class="text-danger">*</span></label>
                                                <select class="form-select" id="iClientid" required>
                                                    <option value="">-- Select Client --</option>
                                                    <?php foreach ($clients as $c) :
                                                        $label = htmlspecialchars($c['sClientname']);
                                                        if (!empty($c['sCompanyname'])) {
                                                            $label .= ' (' . htmlspecialchars($c['sCompanyname']) . ')';
                                                        }
                                                        $label .= ' — ₹' . number_format((float)$c['dMonthlyamount'], 2);
                                                        if (($c['sStatus'] ?? '') !== 'Active') {
                                                            $label .= ' [' . htmlspecialchars($c['sStatus']) . ']';
                                                        }
                                                        ?>
                                                        <option value="<?php echo (int)$c['iClientid']; ?>"
                                                            data-amount="<?php echo htmlspecialchars((string)$c['dMonthlyamount']); ?>"
                                                            data-billingday="<?php echo (int)$c['iBillingday']; ?>">
                                                            <?php echo $label; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <?php if (empty($clients)) : ?>
                                                    <small class="text-danger">No clients yet. <a href="add-software-client.php">Add a client</a> first.</small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">Billing Month <span class="text-danger">*</span></label>
                                                <input type="month" class="form-control" id="sMonth" required>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">Due Date <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" id="sDuedate" required>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">Invoice Amount (₹)</label>
                                                <input type="number" step="0.01" min="0" class="form-control" id="dAmount" value="0">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">Amount Received (₹)</label>
                                                <input type="number" step="0.01" min="0" class="form-control" id="dReceived" value="0">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">Paid Date</label>
                                                <input type="date" class="form-control" id="sPaiddate">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">Payment Mode</label>
                                                <select class="form-select" id="sMode">
                                                    <option value="">-- Select --</option>
                                                    <option value="Cash">Cash</option>
                                                    <option value="NEFT">NEFT</option>
                                                    <option value="UPI">UPI</option>
                                                    <option value="Cheque">Cheque</option>
                                                    <option value="Card">Card</option>
                                                    <option value="Other">Other</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Invoice / Bill No</label>
                                                <input type="text" class="form-control" id="sInvoiceNo" placeholder="e.g. INV-2026-01">
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="mb-3">
                                                <label class="form-label">Monthly Report / Work Done</label>
                                                <textarea class="form-control" id="sReport" rows="3" placeholder="What was delivered / supported this month…"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="mb-3">
                                                <label class="form-label">Notes</label>
                                                <textarea class="form-control" id="sNotes" rows="2" placeholder="Payment remarks, follow-up, etc."></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="button" class="btn btn-primary w-md" id="btnSave" onclick="saveMonthly()">Save</button>
                                    <a href="list-client-monthly.php" class="btn btn-secondary w-md ms-1">Back to List</a>
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
var editId = <?php echo isset($id) ? (int)$id : 'null'; ?>;

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

function showMsg(text, ok) {
    $('#message').html('<span class="' + (ok ? 'add-message' : 'error-message') + '">' + text + '</span>');
}

function calcDueDate(month, billingDay) {
    if (!month) return '';
    var parts = month.split('-');
    if (parts.length !== 2) return '';
    var y = parseInt(parts[0], 10);
    var m = parseInt(parts[1], 10);
    var day = Math.max(1, Math.min(28, parseInt(billingDay, 10) || 1));
    var last = new Date(y, m, 0).getDate();
    if (day > last) day = last;
    return y + '-' + String(m).padStart(2, '0') + '-' + String(day).padStart(2, '0');
}

function onClientOrMonthChange() {
    var opt = $('#iClientid option:selected');
    var amount = parseFloat(opt.data('amount')) || 0;
    var billingDay = parseInt(opt.data('billingday'), 10) || 1;
    var month = $('#sMonth').val();
    if (!editId || !$('#dAmount').data('touched')) {
        if (opt.val()) $('#dAmount').val(amount.toFixed(2));
    }
    if (month && opt.val()) {
        $('#sDuedate').val(calcDueDate(month, billingDay));
    }
}

$('#iClientid, #sMonth').on('change', onClientOrMonthChange);
$('#dAmount').on('input', function() { $(this).data('touched', true); });

function saveMonthly() {
    var clientId = parseInt($('#iClientid').val(), 10) || 0;
    var month = $('#sMonth').val();
    var due = $('#sDuedate').val();
    if (!clientId) {
        showMsg('Please select a client', false);
        return;
    }
    if (!month) {
        showMsg('Billing month is required', false);
        return;
    }
    if (!due) {
        showMsg('Due date is required', false);
        return;
    }
    var payload = {
        action: editId ? 'updateclientmonthly' : 'addclientmonthly',
        iClientid: clientId,
        sMonth: month,
        sDuedate: due,
        dAmount: parseFloat($('#dAmount').val()) || 0,
        dReceived: parseFloat($('#dReceived').val()) || 0,
        sPaiddate: $('#sPaiddate').val() || '',
        sMode: $('#sMode').val() || '',
        sInvoiceNo: $.trim($('#sInvoiceNo').val()),
        sReport: $.trim($('#sReport').val()),
        sNotes: $.trim($('#sNotes').val())
    };
    if (editId) payload.id = editId;

    $('#btnSave').prop('disabled', true);
    $.ajax({
        url: 'api.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(payload),
        success: function(res) {
            $('#btnSave').prop('disabled', false);
            if (res.status === 'success') {
                showMsg(res.message, true);
                if (!editId) {
                    setTimeout(function() { window.location.href = 'list-client-monthly.php'; }, 800);
                }
            } else {
                showMsg(res.message || 'Save failed', false);
            }
        },
        error: function() {
            $('#btnSave').prop('disabled', false);
            showMsg('Network error', false);
        }
    });
}

function loadMonthly(id) {
    $.ajax({
        url: 'api.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ action: 'getclientmonthlybyid', id: id }),
        success: function(res) {
            if (res.status !== 'success' || !res.data) {
                showMsg(res.message || 'Entry not found', false);
                return;
            }
            var d = res.data;
            $('#id').val(d.iMonthlyid);
            $('#iClientid').val(d.iClientid);
            $('#sMonth').val(d.sMonth || '');
            $('#sDuedate').val(d.sDuedate ? String(d.sDuedate).slice(0, 10) : '');
            $('#dAmount').val(d.dAmount || 0).data('touched', true);
            $('#dReceived').val(d.dReceived || 0);
            $('#sPaiddate').val(d.sPaiddate ? String(d.sPaiddate).slice(0, 10) : '');
            $('#sMode').val(d.sMode || '');
            $('#sInvoiceNo').val(d.sInvoiceNo || '');
            $('#sReport').val(d.sReport || '');
            $('#sNotes').val(d.sNotes || '');
        }
    });
}

$(function() {
    if (!editId) {
        var now = new Date();
        $('#sMonth').val(now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0'));
    } else {
        loadMonthly(editId);
    }
});
</script>
