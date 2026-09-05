<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include 'layouts/config.php';
include_once 'layouts/crm-access.php';
crmRequireModule('lead', $link);
$isAdmin = crmCanSeeAllLeads($link);
if (isset($_POST['id'])) {
    $id = $_POST['id'];
}
?>

<head>
    <title>List Follow Up</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .success-message {
            color: green;
            font-weight: bold;
        }

        .error-message {
            color: green;
            font-weight: bold;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
    </style>
</head>
<?php include 'layouts/body.php'; ?>

<!-- Begin page -->
<div id="layout-wrapper">

<?php include 'layouts/menu.php'; ?>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">
                                <?php echo $isAdmin ? 'List Follow Up (All Users)' : 'List Follow Up'; ?>
                            </h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Follow Up</a></li>
                                    <li class="breadcrumb-item active">List Follow Up</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="datatable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Sr no</th>
                                <th>Description</th>
                                <th>Follow-up Date</th>
                                <th>Company Name</th>
                                <th>Status</th>
                                <?php if ($isAdmin) : ?>
                                    <th>User</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>

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
var isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;

function checkTokenStatus() {
    $.ajax({
        url: 'check_token.php',
        method: 'GET',
        success: function(response) {
            if (response.status === 'error') {
                alert(response.message);
                window.location.href = 'auth-login.php';
            } else {
                console.log(response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error checking token:', error);
            window.location.href = 'auth-login.php';
        }
    });
}

checkTokenStatus();

function triggerReplay(leadId) {
    var form = document.createElement('form');
    form.method = 'post';
    form.action = 'lead-replay.php';

    var input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'leadId';
    input.value = leadId;

    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}

function renderFollowups(followups) {
    var followupRows = '';
    var colCount = isAdmin ? 6 : 5;

    followups.forEach(function(item, index) {
        var desc = item.sDescription || '';
        var date = item.sFollowupdate || '';
        var company = item.sCompany_name || '';
        var status = item.status_name || '';
        var user = item.user_name || '';
        followupRows += `<tr>
            <td>${index + 1}</td>
            <td onclick="triggerReplay(${item.lead_id})" style="cursor:pointer; color:blue; text-decoration:underline;">${desc}</td>
            <td onclick="triggerReplay(${item.lead_id})" style="cursor:pointer; color:blue; text-decoration:underline;">${date}</td>
            <td onclick="triggerReplay(${item.lead_id})" style="cursor:pointer; color:blue; text-decoration:underline;">${company}</td>
            <td onclick="triggerReplay(${item.lead_id})" style="cursor:pointer; color:blue; text-decoration:underline;">${status}</td>
            ${isAdmin ? `<td>${user}</td>` : ''}
        </tr>`;
    });

    if ($.fn.DataTable.isDataTable('#datatable')) {
        $('#datatable').DataTable().destroy();
    }

    $('#datatable tbody').html(followupRows || `<tr><td colspan="${colCount}">No follow-ups found</td></tr>`);
    if (followups.length) {
        $('#datatable').DataTable({
            order: [[2, 'desc']]
        });
    }
}

function fngetFollowups() {
    var action = isAdmin ? 'getAllFollowups' : 'getTodaysFollowups';
    $.ajax({
        url: 'api.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ action: action }),
        success: function(response) {
            if (response.status === 'success') {
                renderFollowups(response.data || []);
            } else {
                renderFollowups([]);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX error:', error);
        }
    });
}

$(document).ready(function() {
    fngetFollowups();
});
</script>

</body>
</html>
