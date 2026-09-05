<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include 'layouts/config.php';
include_once 'layouts/crm-access.php';
crmRequireModule('lead', $link);
if(isset($_POST['id'])){
    $id = $_POST['id'] ;
  
}

?>

<head>
    <title>List Leads</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 
    <style>
        .success-message {
    color: green;
    font-weight: bold;  /* Optional */
}

.error-message {
    color: green;
    font-weight: bold;  /* Optional */
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

    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->

   
    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">List Leads</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Lead</a></li>
                                    <li class="breadcrumb-item active">List Leads</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end page title -->

                <div><span id="message"></span></div>

             <form id="filterForm" class="row g-3 mb-3">
                    <div class="col-md-2">
                        <label for="periodFilter" class="form-label">Show Leads</label>
                        <select class="form-control" id="periodFilter" name="periodFilter">
                            <option value="">All</option>
                            <option value="today">Today</option>
                            <option value="yesterday">Yesterday</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="fromDate" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="fromDate" name="fromDate">
                    </div>
                    <div class="col-md-2">
                        <label for="toDate" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="toDate" name="toDate">
                    </div>
                    <div class="col-md-3">
                        <label for="assignedTo" class="form-label">Assigned To</label>
                        <select class="form-control" id="assignedTo" name="assignedTo">
                            <option value="">All</option>
                            <?php
                            $res = mysqli_query($link, "SELECT iUserid, sName FROM tbluser WHERE sIs_active = 1");
                            while ($row = mysqli_fetch_assoc($res)) {
                                echo '<option value="' . $row['iUserid'] . '">' . $row['sName'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" class="btn btn-primary" onclick="fngetlistlead()">Filter</button>
                    </div>
                </form>

                <div class="crm-import-export-bar d-flex flex-wrap gap-2 mb-3">
                    <a href="import-leads.php" class="btn btn-outline-primary">
                        <i class="bx bx-upload"></i> Import Excel
                    </a>
                    <button type="button" class="btn btn-outline-success" onclick="exportLeads()">
                        <i class="bx bx-download"></i> Export Excel
                    </button>
                </div>

                <div class="table-responsive">
                    <table id="datatable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Sr no.</th>
                                <th>Company Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                 <th>Product Name</th>
                               
                                <th>Assigned To</th>
                                 <th>View</th>
                                <th>Edit</th>
                                <th>Delete</th>
                                <th>Tax Invoice</th>
                                <th>Sales Order</th>

                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

       <input type="hidden" id="id" name="id" value="">

  
</div>
   
            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->

      
        <?php include 'layouts/footer.php'; ?>
    </div>
    <!-- end main content-->
</div>
<!-- END layout-wrapper -->

<!-- Right Sidebar -->
<?php include 'layouts/right-sidebar.php'; ?>
<!-- Right-bar -->

<!-- JAVASCRIPT -->
<?php include 'layouts/vendor-scripts.php'; ?>

<!-- apexcharts -->
<script src="assets/libs/apexcharts/apexcharts.min.js"></script>
<!-- <script src="assets/js/pages/dashboard.init.js"></script> -->

<!-- App js -->
<script src="assets/js/app.js"></script>
<script>function checkTokenStatus() {
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
</script>

<script>

    function viewLead(id) {
    // Redirect to the lead-replay.php page with the lead ID as a query parameter
    window.location.href = `lead-replay.php?id=${id}`;
}

function formatLocalDate(d) {
    var y = d.getFullYear();
    var m = String(d.getMonth() + 1).padStart(2, '0');
    var day = String(d.getDate()).padStart(2, '0');
    return y + '-' + m + '-' + day;
}

function applyPeriodFilter() {
    var period = document.getElementById('periodFilter').value;
    var fromEl = document.getElementById('fromDate');
    var toEl = document.getElementById('toDate');

    if (!period) {
        return;
    }

    var today = new Date();
    today.setHours(0, 0, 0, 0);
    var from = new Date(today);
    var to = new Date(today);

    if (period === 'today') {
        // from/to = today
    } else if (period === 'yesterday') {
        from.setDate(from.getDate() - 1);
        to.setDate(to.getDate() - 1);
    } else if (period === 'weekly') {
        from.setDate(from.getDate() - 6);
    } else if (period === 'monthly') {
        from = new Date(today.getFullYear(), today.getMonth(), 1);
    }

    fromEl.value = formatLocalDate(from);
    toEl.value = formatLocalDate(to);
}

function exportLeads() {
    applyPeriodFilter();
    const fromDate = document.getElementById('fromDate').value;
    const toDate = document.getElementById('toDate').value;
    const assignedTo = document.getElementById('assignedTo').value;
    const params = new URLSearchParams();
    if (fromDate) params.append('fromDate', fromDate);
    if (toDate) params.append('toDate', toDate);
    if (assignedTo) params.append('assignedTo', assignedTo);
    window.open('export-leads.php?' + params.toString(), '_blank');
}

function fngetlistlead() {
    applyPeriodFilter();
    const fromDate = document.getElementById('fromDate').value;
    const toDate = document.getElementById('toDate').value;
    const assignedTo = document.getElementById('assignedTo').value;

    $.ajax({
        url: 'api.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            action: 'fngetlistlead',
            fromDate: fromDate,
            toDate: toDate,
            assignedTo: assignedTo
        }),
        success: function(response) {
            if (response.status === 'success') {
                let leadRows = '';
                response.data.forEach(function(lead, index) {
                    leadRows += `<tr>
                        <td>${index + 1}</td>
                        <td>${lead.sCompany_name || '-'}</td>
                        <td>${lead.sEmail}</td>
                        <td>${lead.sPhone}</td>
                     
                        <td>${lead.product_names || '-'}</td>
                        <td>${lead.assigned_to_name || '-'}</td>
                           <td>
    <form method="post" action="lead-replay.php" target="_blank">
        <input type="hidden" name="leadId" value="${lead.iLead_id}">
        <button type="submit" class="btn btn-info">View</button>
    </form>
</td>
                        <td>
                            <form action="add-lead-master.php" method="post">
                                <input type="hidden" name="leadId" value="${lead.iLead_id}">
                                <button type="submit" name="btnedit" class="btn btn-success">Edit</button>
                            </form>
                        </td>
                        <td>
                            <button class="btn btn-danger" onclick="deletelead(${lead.iLead_id})">Delete</button>
                        </td>
                      <td>
   <a href="add-quotation.php?leadId=${lead.iLead_id}" class="btn btn-success"> Tax Invoice</a>
</td>
                 <td>
   <a href="add-sales-order.php?leadId=${lead.iLead_id}" class="btn btn-warning"> Sales Order</a>
</td>


                    </tr>`;
                });
                $('#datatable').DataTable().clear().destroy();
                $('#datatable tbody').html(leadRows);
                $('#datatable').DataTable();
            } else {
                $('#datatable tbody').html('<tr><td colspan="11">No leads found</td></tr>');
            }
        },
        error: function(xhr) {
            alert('Error fetching leads.');
        }
    });
}

$(document).ready(function() {
    $('#periodFilter').on('change', function () {
        if (!this.value) {
            document.getElementById('fromDate').value = '';
            document.getElementById('toDate').value = '';
        }
        fngetlistlead();
    });
    $('#fromDate, #toDate').on('change', function () {
        document.getElementById('periodFilter').value = '';
    });
    fngetlistlead();
});



function deletelead(id) {

console.log(' ID:', id);  



if (id == 0) {
    alert('Invalid user ID.');
    return false;
}
var confirmDelete = confirm("Are you sure you want to delete this user?");

if (confirmDelete) {

const data = {
    action: "deletelead",
    id: id
};

fetch('api.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
})
.then(response => response.json())
.then(responseData => {
    console.log(responseData);
    if (responseData.success) {

        document.getElementById('message').innerHTML = responseData.message;
    document.getElementById('message').classList.add('success-message');  // Apply green color
    fngetlistlead();
} else {
    document.getElementById('message').innerHTML = responseData.message;
    document.getElementById('message').classList.add('error-message'); 
    fngetlistlead();
    }
})
.catch(error => {
    console.error('Error:', error);
});
}
}




</script>
<!-- apexcharts -->


</body>

</html>