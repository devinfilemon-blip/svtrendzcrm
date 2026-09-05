<?php
include 'layouts/session.php';
include 'layouts/head-main.php';
include 'layouts/config.php';
include_once 'layouts/crm-access.php';
crmRequireFinanceAdmin($link);

$id = null;
if (isset($_POST['id'])) {
    $id = (int)$_POST['id'];
}
?>

<head>
    <title><?php echo isset($id) ? 'Edit' : 'Add'; ?> P&L Entry</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .add-message { color: green; font-weight: bold; }
        .error-message { color: red; font-weight: bold; }
        .btn-primary { color: #fff; background-color: #005aa5; border-color: #005aa5; }
        .payment-hint { color: #74788d; font-size: 13px; margin-bottom: 1rem; }
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
                            <h4 class="mb-sm-0 font-size-18"><?php echo isset($id) ? 'Edit' : 'Add'; ?> P&L Entry</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="list-pnl.php">P&L Account</a></li>
                                    <li class="breadcrumb-item active"><?php echo isset($id) ? 'Edit' : 'Add'; ?> Entry</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-10">
                        <div class="card">
                            <div class="card-body">
                                <p class="payment-hint">Save income or expense details for your Profit &amp; Loss account statement.</p>
                                <div><span id="message"></span></div>

                                <form id="pnlForm" onsubmit="return false;">
                                    <input type="hidden" id="id" value="">

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                                <select class="form-select" id="sType" required>
                                                    <option value="Income">Income</option>
                                                    <option value="Expense">Expense</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                                <select class="form-select" id="sCategory" required>
                                                    <optgroup label="Income">
                                                        <option value="Sales">Sales</option>
                                                        <option value="Service">Service</option>
                                                        <option value="Commission">Commission</option>
                                                        <option value="Interest Income">Interest Income</option>
                                                        <option value="Other Income">Other Income</option>
                                                    </optgroup>
                                                    <optgroup label="Expense">
                                                        <option value="Salary">Salary</option>
                                                        <option value="Rent">Rent</option>
                                                        <option value="Marketing">Marketing</option>
                                                        <option value="Travel">Travel</option>
                                                        <option value="Utilities">Utilities</option>
                                                        <option value="Office Expense">Office Expense</option>
                                                        <option value="Purchase">Purchase</option>
                                                        <option value="Tax">Tax</option>
                                                        <option value="General Expense">General Expense</option>
                                                    </optgroup>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" id="sEntrydate" required>
                                            </div>
                                        </div>

                                        <div class="col-md-8">
                                            <div class="mb-3">
                                                <label class="form-label">Particulars / Description <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="sParticulars" placeholder="e.g. Website project payment / Office rent March" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Amount (₹) <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control" id="dAmount" min="0.01" step="0.01" value="" required>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Reference No</label>
                                                <input type="text" class="form-control" id="sReference" placeholder="Invoice / voucher / txn no">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Notes</label>
                                                <input type="text" class="form-control" id="sNotes" placeholder="Optional notes">
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <button type="button" class="btn btn-primary w-md" onclick="<?php echo isset($id) ? 'updatepnl();' : 'addpnl();'; ?>">
                                                Save Entry
                                            </button>
                                            <a href="list-pnl.php" class="btn btn-secondary w-md ms-2">Cancel</a>
                                            <a href="pnl-statement.php" class="btn btn-outline-primary w-md ms-2">View Statement</a>
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
    return {
        action: action,
        id: document.getElementById('id').value || 0,
        sType: document.getElementById('sType').value,
        sCategory: document.getElementById('sCategory').value,
        sParticulars: document.getElementById('sParticulars').value.trim(),
        sEntrydate: document.getElementById('sEntrydate').value,
        dAmount: document.getElementById('dAmount').value,
        sReference: document.getElementById('sReference').value.trim(),
        sNotes: document.getElementById('sNotes').value.trim()
    };
}

function showMsg(res) {
    var el = document.getElementById('message');
    el.innerHTML = res.message || '';
    el.className = (res.status === 'success') ? 'add-message' : 'error-message';
}

function addpnl() {
    var payload = collectPayload('addpnl');
    if (!payload.sParticulars || !payload.sEntrydate || !(parseFloat(payload.dAmount) > 0)) {
        alert('Please fill Type, Date, Particulars and Amount.');
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
            setTimeout(function () { window.location.href = 'list-pnl.php'; }, 800);
        }
    })
    .catch(function () { alert('Failed to save entry.'); });
}

function updatepnl() {
    var payload = collectPayload('updatepnl');
    if (!payload.id) {
        alert('Invalid entry.');
        return;
    }
    if (!payload.sParticulars || !payload.sEntrydate || !(parseFloat(payload.dAmount) > 0)) {
        alert('Please fill Type, Date, Particulars and Amount.');
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
            setTimeout(function () { window.location.href = 'list-pnl.php'; }, 800);
        }
    })
    .catch(function () { alert('Failed to update entry.'); });
}

function loadEntry(id) {
    fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'getpnlbyid', id: id })
    })
    .then(function (r) { return r.json(); })
    .then(function (res) {
        if (res.status !== 'success' || !res.data) {
            alert(res.message || 'Entry not found');
            return;
        }
        var d = res.data;
        document.getElementById('id').value = d.iPnLid;
        document.getElementById('sType').value = d.sType || 'Income';
        document.getElementById('sCategory').value = d.sCategory || '';
        document.getElementById('sParticulars').value = d.sParticulars || '';
        document.getElementById('sEntrydate').value = (d.sEntrydate || '').slice(0, 10);
        document.getElementById('dAmount').value = d.dAmount || '';
        document.getElementById('sReference').value = d.sReference || '';
        document.getElementById('sNotes').value = d.sNotes || '';
    });
}

$(document).ready(function () {
    var today = new Date().toISOString().slice(0, 10);
    document.getElementById('sEntrydate').value = today;
    <?php if (isset($id) && $id > 0): ?>
    loadEntry(<?php echo (int)$id; ?>);
    <?php endif; ?>
});
</script>
</body>
</html>
