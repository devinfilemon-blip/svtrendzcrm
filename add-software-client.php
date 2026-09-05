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
    <title><?php echo isset($id) ? 'Edit' : 'Add'; ?> Software Client</title>
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
                            <h4 class="mb-sm-0 font-size-18"><?php echo isset($id) ? 'Edit' : 'Add'; ?> Software Client</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="list-software-client.php">Client Management</a></li>
                                    <li class="breadcrumb-item active"><?php echo isset($id) ? 'Edit' : 'Add'; ?> Client</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-10">
                        <div class="card">
                            <div class="card-body">
                                <p class="client-hint">Add clients who purchased your software. Set monthly fee and billing day for due-date tracking.</p>
                                <div><span id="message"></span></div>

                                <form id="clientForm" onsubmit="return false;">
                                    <input type="hidden" id="id" value="">

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Client Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="sClientname" placeholder="Client / shop name" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Company Name</label>
                                                <input type="text" class="form-control" id="sCompanyname" placeholder="Registered company (optional)">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Contact Person</label>
                                                <input type="text" class="form-control" id="sContactperson" placeholder="Primary contact">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Phone</label>
                                                <input type="text" class="form-control" id="sPhone" placeholder="Mobile / landline">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" id="sEmail" placeholder="email@example.com">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Plan / Package</label>
                                                <input type="text" class="form-control" id="sPlan" placeholder="e.g. Basic, Pro, Enterprise">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Monthly Amount (₹) <span class="text-danger">*</span></label>
                                                <input type="number" step="0.01" min="0" class="form-control" id="dMonthlyamount" value="0" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Billing Day (1–28) <span class="text-danger">*</span></label>
                                                <input type="number" min="1" max="28" class="form-control" id="iBillingday" value="1" required>
                                                <small class="text-muted">Due date each month</small>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Start Date</label>
                                                <input type="date" class="form-control" id="sStartdate">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Status</label>
                                                <select class="form-select" id="sStatus">
                                                    <option value="Active">Active</option>
                                                    <option value="Inactive">Inactive</option>
                                                    <option value="Suspended">Suspended</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Address</label>
                                                <input type="text" class="form-control" id="sAddress" placeholder="City / address">
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="mb-3">
                                                <label class="form-label">Notes</label>
                                                <textarea class="form-control" id="sNotes" rows="3" placeholder="Agreement notes, support terms, etc."></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="button" class="btn btn-primary w-md" id="btnSave" onclick="saveClient()">Save</button>
                                    <a href="list-software-client.php" class="btn btn-secondary w-md ms-1">Back to List</a>
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

function saveClient() {
    var name = $.trim($('#sClientname').val());
    if (!name) {
        showMsg('Client name is required', false);
        return;
    }
    var payload = {
        action: editId ? 'updatesoftwareclient' : 'addsoftwareclient',
        sClientname: name,
        sCompanyname: $.trim($('#sCompanyname').val()),
        sContactperson: $.trim($('#sContactperson').val()),
        sEmail: $.trim($('#sEmail').val()),
        sPhone: $.trim($('#sPhone').val()),
        sPlan: $.trim($('#sPlan').val()),
        dMonthlyamount: parseFloat($('#dMonthlyamount').val()) || 0,
        iBillingday: parseInt($('#iBillingday').val(), 10) || 1,
        sStartdate: $('#sStartdate').val() || '',
        sStatus: $('#sStatus').val(),
        sAddress: $.trim($('#sAddress').val()),
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
                    setTimeout(function() { window.location.href = 'list-software-client.php'; }, 800);
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

function loadClient(id) {
    $.ajax({
        url: 'api.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ action: 'getsoftwareclientbyid', id: id }),
        success: function(res) {
            if (res.status !== 'success' || !res.data) {
                showMsg(res.message || 'Client not found', false);
                return;
            }
            var d = res.data;
            $('#id').val(d.iClientid);
            $('#sClientname').val(d.sClientname || '');
            $('#sCompanyname').val(d.sCompanyname || '');
            $('#sContactperson').val(d.sContactperson || '');
            $('#sEmail').val(d.sEmail || '');
            $('#sPhone').val(d.sPhone || '');
            $('#sPlan').val(d.sPlan || '');
            $('#dMonthlyamount').val(d.dMonthlyamount || 0);
            $('#iBillingday').val(d.iBillingday || 1);
            $('#sStartdate').val(d.sStartdate ? String(d.sStartdate).slice(0, 10) : '');
            $('#sStatus').val(d.sStatus || 'Active');
            $('#sAddress').val(d.sAddress || '');
            $('#sNotes').val(d.sNotes || '');
        }
    });
}

$(function() {
    if (!editId) {
        $('#sStartdate').val(new Date().toISOString().slice(0, 10));
    } else {
        loadClient(editId);
    }
});
</script>
