<?php
include 'layouts/session.php';
include 'layouts/head-main.php';
include 'layouts/config.php';
include_once 'layouts/crm-access.php';
crmRequireModule('finance', $link);

$id = null;
if (isset($_POST['id'])) {
    $id = (int)$_POST['id'];
}
?>

<head>
    <title><?php echo isset($id) ? 'Edit' : 'Add'; ?> Payment</title>
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
                            <h4 class="mb-sm-0 font-size-18"><?php echo isset($id) ? 'Edit' : 'Add'; ?> Client Payment</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="list-payment.php">Sales Management</a></li>
                                    <li class="breadcrumb-item active"><?php echo isset($id) ? 'Edit' : 'Add'; ?> Payment</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-10">
                        <div class="card">
                            <div class="card-body">
                                <p class="payment-hint">Track invoice amount, amount received, and pending balance for each client.</p>
                                <div><span id="message"></span></div>

                                <form id="paymentForm" onsubmit="return false;">
                                    <input type="hidden" id="id" value="">

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Client / Company <span class="text-danger">*</span></label>
                                                <select class="form-select" id="leadId" required>
                                                    <option value="">-- Select Won Client --</option>
                                                    <?php
                                                    $wonStatusId = '6';
                                                    $wonSql = "
                                                        SELECT
                                                            l.iLead_id,
                                                            l.sCompany_name,
                                                            l.sLead_name,
                                                            l.sEmail,
                                                            l.sPhone,
                                                            c.iCustomerid
                                                        FROM tblleads l
                                                        LEFT JOIN (
                                                            SELECT r1.lead_id, r1.sStatus
                                                            FROM tblreplayleads r1
                                                            INNER JOIN (
                                                                SELECT lead_id, MAX(sCreatedTimestamp) AS latest_ts
                                                                FROM tblreplayleads
                                                                GROUP BY lead_id
                                                            ) r2 ON r1.lead_id = r2.lead_id AND r1.sCreatedTimestamp = r2.latest_ts
                                                        ) r ON r.lead_id = l.iLead_id
                                                        LEFT JOIN tblcustomer c
                                                            ON LOWER(TRIM(c.sCompanyname)) = LOWER(TRIM(l.sCompany_name))
                                                        WHERE COALESCE(NULLIF(r.sStatus, ''), l.sLead_status) = ?
                                                          AND TRIM(COALESCE(l.sCompany_name, '')) <> ''
                                                        ORDER BY l.sCompany_name ASC
                                                    ";
                                                    $wonStmt = $link->prepare($wonSql);
                                                    if ($wonStmt) {
                                                        $wonStmt->bind_param('s', $wonStatusId);
                                                        $wonStmt->execute();
                                                        $wonRes = $wonStmt->get_result();
                                                        $seenCompanies = [];
                                                        while ($row = $wonRes->fetch_assoc()) {
                                                            $company = trim((string)$row['sCompany_name']);
                                                            if ($company === '') {
                                                                continue;
                                                            }
                                                            // One option per company name
                                                            $key = strtolower($company);
                                                            if (isset($seenCompanies[$key])) {
                                                                continue;
                                                            }
                                                            $seenCompanies[$key] = true;

                                                            $label = htmlspecialchars($company);
                                                            if (!empty($row['sEmail'])) {
                                                                $label .= ' (' . htmlspecialchars($row['sEmail']) . ')';
                                                            }
                                                            $customerId = (int)($row['iCustomerid'] ?? 0);
                                                            echo "<option value='" . (int)$row['iLead_id'] . "'"
                                                                . " data-name=\"" . htmlspecialchars($company, ENT_QUOTES) . "\""
                                                                . " data-customerid=\"" . $customerId . "\">"
                                                                . $label
                                                                . "</option>";
                                                        }
                                                        $wonStmt->close();
                                                    }
                                                    ?>
                                                </select>
                                                <small class="text-muted">Only Won clients are listed</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Client Name</label>
                                                <input type="text" class="form-control" id="sClientname" placeholder="Auto-filled from Won client" readonly>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Invoice / Bill No</label>
                                                <input type="text" class="form-control" id="sInvoiceNo" placeholder="e.g. INV-001">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" id="sPaymentdate" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Payment Mode</label>
                                                <select class="form-select" id="sMode">
                                                    <option value="">-- Select --</option>
                                                    <option value="Cash">Cash</option>
                                                    <option value="NEFT">NEFT</option>
                                                    <option value="RTGS">RTGS</option>
                                                    <option value="UPI">UPI</option>
                                                    <option value="Cheque">Cheque</option>
                                                    <option value="Card">Card</option>
                                                    <option value="Other">Other</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Total Amount (₹) <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control" id="dAmount" min="0" step="0.01" value="0" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Amount Received (₹)</label>
                                                <input type="number" class="form-control" id="dReceived" min="0" step="0.01" value="0">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Pending (₹)</label>
                                                <input type="number" class="form-control" id="dPending" min="0" step="0.01" value="0" readonly>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Status</label>
                                                <select class="form-select" id="sStatus">
                                                    <option value="Pending">Pending</option>
                                                    <option value="Partial">Partial</option>
                                                    <option value="Received">Received</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Reference No</label>
                                                <input type="text" class="form-control" id="sReference" placeholder="Txn / cheque no">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Notes</label>
                                                <input type="text" class="form-control" id="sNotes" placeholder="Optional notes">
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <button type="button" class="btn btn-primary w-md" onclick="<?php echo isset($id) ? 'updatepayment();' : 'addpayment();'; ?>">
                                                Save Payment
                                            </button>
                                            <a href="list-payment.php" class="btn btn-secondary w-md ms-2">Cancel</a>
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

function calcPendingAndStatus() {
    var amount = parseFloat(document.getElementById('dAmount').value) || 0;
    var received = parseFloat(document.getElementById('dReceived').value) || 0;
    if (received < 0) received = 0;
    if (received > amount) received = amount;
    document.getElementById('dReceived').value = received.toFixed(2);
    var pending = Math.max(0, amount - received);
    document.getElementById('dPending').value = pending.toFixed(2);

    var statusEl = document.getElementById('sStatus');
    if (amount <= 0) {
        statusEl.value = 'Pending';
    } else if (pending <= 0) {
        statusEl.value = 'Received';
    } else if (received > 0) {
        statusEl.value = 'Partial';
    } else {
        statusEl.value = 'Pending';
    }
}

$('#dAmount, #dReceived').on('input change', calcPendingAndStatus);

$('#leadId').on('change', function () {
    var $opt = $(this).find('option:selected');
    var name = $opt.data('name') || '';
    document.getElementById('sClientname').value = name;
});

function collectData(action) {
    var $opt = $('#leadId').find('option:selected');
    return {
        action: action,
        leadId: document.getElementById('leadId').value || 0,
        iCustomerid: $opt.data('customerid') || 0,
        sClientname: document.getElementById('sClientname').value.trim(),
        sInvoiceNo: document.getElementById('sInvoiceNo').value.trim(),
        sPaymentdate: document.getElementById('sPaymentdate').value,
        dAmount: document.getElementById('dAmount').value,
        dReceived: document.getElementById('dReceived').value,
        dPending: document.getElementById('dPending').value,
        sStatus: document.getElementById('sStatus').value,
        sMode: document.getElementById('sMode').value,
        sReference: document.getElementById('sReference').value.trim(),
        sNotes: document.getElementById('sNotes').value.trim()
    };
}

function showMessage(msg, ok) {
    var el = document.getElementById('message');
    el.innerHTML = msg;
    el.className = ok ? 'add-message' : 'error-message';
}

function addpayment() {
    calcPendingAndStatus();
    var data = collectData('addpayment');
    if (!data.leadId || !data.sClientname || !data.sPaymentdate) {
        alert('Please select a Won client and payment date.');
        return;
    }
    if (parseFloat(data.dAmount) < 0) {
        alert('Total amount cannot be negative.');
        return;
    }
    fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(function (r) { return r.json(); })
    .then(function (res) {
        if (res.status === 'success') {
            showMessage(res.message, true);
            setTimeout(function () { window.location.href = 'list-payment.php'; }, 500);
        } else {
            showMessage(res.message || 'Failed to save.', false);
        }
    })
    .catch(function (e) { console.error(e); });
}

function updatepayment() {
    calcPendingAndStatus();
    var id = <?php echo isset($id) ? (int)$id : 0; ?>;
    var data = collectData('updatepayment');
    data.id = id;
    if (!id || !data.leadId || !data.sClientname || !data.sPaymentdate) {
        alert('Please select a Won client and required fields.');
        return;
    }
    fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(function (r) { return r.json(); })
    .then(function (res) {
        if (res.status === 'success') {
            showMessage(res.message, true);
            setTimeout(function () { window.location.href = 'list-payment.php'; }, 500);
        } else {
            showMessage(res.message || 'Failed to update.', false);
        }
    })
    .catch(function (e) { console.error(e); });
}

function getpaymentbyid() {
    var id = <?php echo isset($id) ? (int)$id : 0; ?>;
    if (!id) {
        document.getElementById('sPaymentdate').value = new Date().toISOString().slice(0, 10);
        calcPendingAndStatus();
        return;
    }
    fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'getpaymentbyid', id: id })
    })
    .then(function (r) { return r.json(); })
    .then(function (res) {
        if (!res.data) {
            alert(res.message || 'Payment not found');
            window.location.href = 'list-payment.php';
            return;
        }
        var d = res.data;
        var leadId = d.leadId || '';
        if (leadId && $('#leadId option[value="' + leadId + '"]').length) {
            document.getElementById('leadId').value = String(leadId);
        } else if (d.sClientname) {
            // Fallback: match by company name if lead id missing/old
            var matched = false;
            $('#leadId option').each(function () {
                if (!matched && String($(this).data('name') || '').toLowerCase() === String(d.sClientname).toLowerCase()) {
                    document.getElementById('leadId').value = $(this).val();
                    matched = true;
                }
            });
        }
        document.getElementById('sClientname').value = d.sClientname || ($('#leadId option:selected').data('name') || '');
        document.getElementById('sInvoiceNo').value = d.sInvoiceNo || '';
        document.getElementById('sPaymentdate').value = d.sPaymentdate || '';
        document.getElementById('dAmount').value = d.dAmount || 0;
        document.getElementById('dReceived').value = d.dReceived || 0;
        document.getElementById('dPending').value = d.dPending || 0;
        document.getElementById('sStatus').value = d.sStatus || 'Pending';
        document.getElementById('sMode').value = d.sMode || '';
        document.getElementById('sReference').value = d.sReference || '';
        document.getElementById('sNotes').value = d.sNotes || '';
        calcPendingAndStatus();
    });
}

getpaymentbyid();
</script>
</html>
