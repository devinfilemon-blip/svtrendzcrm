<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include 'layouts/config.php';
include_once 'layouts/crm-access.php';
crmRequireAdminOrRedirectClient($link);

$projectId = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;
$prefillName = isset($_GET['prefill_name']) ? trim((string)$_GET['prefill_name']) : '';
$prefillQuotationNo = isset($_GET['quotation_no']) ? trim((string)$_GET['quotation_no']) : '';
?>

<head>
    <title>Add Client Project</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .add-message { color: green; font-weight: bold; }
        .error-message { color: red; font-weight: bold; }
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
                            <h4 class="mb-sm-0 font-size-18"><?php echo $projectId ? 'Edit' : 'Add'; ?> Client Project</h4>
                            <div class="page-title-right">
                                <a href="list-client-project.php" class="btn btn-outline-secondary btn-sm">Back to List</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-8">
                        <div class="card">
                            <div class="card-body">
                                <div><span id="message"></span></div>
                                <form id="projectForm" onsubmit="return false;">
                                    <input type="hidden" id="iId" value="<?php echo (int)$projectId; ?>">

                                    <div class="row">
                                        <div class="col-md-8 mb-3">
                                            <label for="sProjectName" class="form-label">Project Name</label>
                                            <input type="text" class="form-control" id="sProjectName" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="iClientUserid" class="form-label">Client</label>
                                            <select class="form-control" id="iClientUserid" required>
                                                <option value="">Select Client</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="sDescription" class="form-label">Description</label>
                                        <textarea class="form-control" id="sDescription" rows="3"></textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="sStatus" class="form-label">Status</label>
                                            <select class="form-control" id="sStatus">
                                                <option value="Active">Active</option>
                                                <option value="On Hold">On Hold</option>
                                                <option value="Completed">Completed</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="dStartDate" class="form-label">Start Date</label>
                                            <input type="date" class="form-control" id="dStartDate">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="dDueDate" class="form-label">Due Date</label>
                                            <input type="date" class="form-control" id="dDueDate">
                                        </div>
                                    </div>

                                    <button type="button" class="btn btn-primary w-md" onclick="saveProject()">Save</button>
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
var PROJECT_ID = <?php echo (int)$projectId; ?>;
var PREFILL_NAME = <?php echo json_encode($prefillName); ?>;
var PREFILL_QUOTATION_NO = <?php echo json_encode($prefillQuotationNo); ?>;

function api(payload) {
    return fetch('client-project-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    }).then(function (r) { return r.json(); });
}

function loadClients(selectedId) {
    return api({ action: 'list_clients' }).then(function (res) {
        var $sel = $('#iClientUserid');
        $sel.find('option[value!=""]').remove();
        if (res.status === 'success') {
            (res.data || []).forEach(function (c) {
                var label = c.name + (c.company ? ' (' + c.company + ')' : '');
                $sel.append('<option value="' + c.id + '">' + label + '</option>');
            });
        }
        if (selectedId) $sel.val(String(selectedId));
        if (!(res.data || []).length) {
            $sel.html('<option value="">No Client accounts yet — create one in User Management first</option>');
        }
    });
}

function loadProject() {
    if (!PROJECT_ID) return;
    api({ action: 'get_client_project', project_id: PROJECT_ID }).then(function (res) {
        if (res.status !== 'success') {
            document.getElementById('message').innerHTML = '<div class="error-message">' + (res.message || 'Project not found') + '</div>';
            return;
        }
        var p = res.data;
        document.getElementById('sProjectName').value = p.sProjectName || '';
        document.getElementById('sDescription').value = p.sDescription || '';
        document.getElementById('sStatus').value = p.sStatus || 'Active';
        document.getElementById('dStartDate').value = p.dStartDate || '';
        document.getElementById('dDueDate').value = p.dDueDate || '';
        $('#iClientUserid').val(String(p.iClientUserid));
    });
}

function saveProject() {
    var name = document.getElementById('sProjectName').value.trim();
    var clientId = document.getElementById('iClientUserid').value;
    if (!name || !clientId) {
        alert('Project name and client are required.');
        return;
    }
    var payload = {
        action: PROJECT_ID ? 'update_client_project' : 'add_client_project',
        sProjectName: name,
        iClientUserid: clientId,
        sDescription: document.getElementById('sDescription').value,
        sStatus: document.getElementById('sStatus').value,
        dStartDate: document.getElementById('dStartDate').value,
        dDueDate: document.getElementById('dDueDate').value
    };
    if (PROJECT_ID) payload.iId = PROJECT_ID;

    api(payload).then(function (res) {
        var $msg = document.getElementById('message');
        $msg.innerHTML = '<span class="' + (res.status === 'success' ? 'add-message' : 'error-message') + '">' + (res.message || '') + '</span>';
        if (res.status === 'success') {
            setTimeout(function () { window.location.href = 'list-client-project.php'; }, 500);
        }
    });
}

function applyPrefill() {
    if (PROJECT_ID || (!PREFILL_NAME && !PREFILL_QUOTATION_NO)) return;
    var nameField = document.getElementById('sProjectName');
    if (PREFILL_NAME) {
        nameField.value = PREFILL_NAME + ' Project';
    }
    if (PREFILL_QUOTATION_NO) {
        document.getElementById('sDescription').value = 'Linked to quotation ' + PREFILL_QUOTATION_NO + (PREFILL_NAME ? ' (' + PREFILL_NAME + ')' : '');
    }
}

$(document).ready(function () {
    loadClients(PROJECT_ID ? null : null).then(loadProject).then(applyPrefill);
});
</script>
</html>
