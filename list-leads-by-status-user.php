<?php
include 'layouts/session.php';
include 'layouts/head-main.php';
include 'layouts/config.php';

$status = isset($_GET['status']) ? $_GET['status'] : '';

?>

<head>
    
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <title>Dashboard</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</head>

<?php include 'layouts/body.php'; ?>
<style>
a:hover .card {
    box-shadow: 0 6px 24px rgba(0, 0, 0, 0.15);
    transform: scale(1.02);
}

   .card {
    background-color: #ffffff;
    border: 1px solid #ddd;
    border-radius: 10px;
    transition: 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    text-align: center;
    padding: 10px 0px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

/* On hover */
.card:hover {
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

/* Card header text */
.card-title {
    font-size: 18px;
    font-weight: 500;
    color: #333;
    margin-bottom: 10px;
}

/* Card number/value text */
.card-text {
    font-size: 28px;
    font-weight: bold;
    color: #0080cd;
    margin: 0;
}

/* Specific section card height */
.card.large-card {
    min-height: 150px;
}
/* .card-body{
     margin : 0px;
       } */
</style>
<!-- Begin page -->
<div id="layout-wrapper">

    <?php include 'layouts/menu.php'; ?>



    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <div class="main-content" >

        <div class="page-content">
            <div class="container-fluid">
                
                    <div class="container" >
<br>
<br>
               <h4 id="status-heading">Leads with Status:</h4>

<br>
<br>
                <div class="table-responsive">
                    <table id="datatable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Sr No.</th>
                                <th>Company Name</th>
                                <th>Product</th>
                                <th>Amount</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- AJAX filled -->
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

         </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
    
    <!-- ============================================================== -->

        <?php include 'layouts/footer.php'; ?>
    <!-- end main content-->
    </div>
</div>
<!-- END layout-wrapper -->

<!-- Right Sidebar -->
<?php include 'layouts/right-sidebar.php'; ?>
<!-- /Right-bar -->

<!-- JAVASCRIPT -->
<?php include 'layouts/vendor-scripts.php'; ?>

<!-- apexcharts -->
<script src="assets/libs/apexcharts/apexcharts.min.js"></script>
<!-- <script src="assets/js/pages/dashboard.init.js"></script> -->

<!-- App js -->


<script src="assets/js/app.js"></script>

<script>
// Extract userid and status from URL
function getUrlParam(name) {
    const url = new URL(window.location.href);
    return url.searchParams.get(name);
}
const userid = getUrlParam('userid');
const status = getUrlParam('status');

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

</script>

<script>
function triggerReplay(leadId) {
    var form = document.createElement('form');
    form.method = 'post';
    form.action = 'lead-replay.php';
    form.target = '_blank';

    var input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'leadId';
    input.value = leadId;

    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function loadLeadsByStatus() {
        $.ajax({
                url: 'api.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                        action: 'leads_by_status_user',
                        status: status,
                        userid: userid
                }),

        success: function(response) {
            if (response.status === 'success') {
                let rows = '';
                response.data.forEach((lead, index) => {
                    let totalAmount = 0;
                    let products = [];

                    lead.products.forEach(p => {
                        totalAmount += parseFloat(p.total_amount) || 0;
                        if(p.sProductname) products.push(p.sProductname);
                    });

                    rows += `<tr>
                        <td>${index + 1}</td>
                        <td onclick="triggerReplay(${lead.iLead_id})" style="cursor:pointer;color:blue;text-decoration:underline;">${lead.sCompany_name || lead.sLead_name || ''}</td>
                        <td onclick="triggerReplay(${lead.iLead_id})" style="cursor:pointer;color:blue;text-decoration:underline;">${products.join('<br>')}</td>
                        <td onclick="triggerReplay(${lead.iLead_id})" style="cursor:pointer;color:blue;text-decoration:underline;">${totalAmount.toFixed(2)}</td>
                        <td onclick="triggerReplay(${lead.iLead_id})" style="cursor:pointer;color:blue;text-decoration:underline;"> ${formatDate(lead.sCreated_date)}</td>
                    </tr>`;
                });

                if ($.fn.DataTable.isDataTable('#datatable')) {
                    $('#datatable').DataTable().destroy();
                }
                $('#datatable tbody').html(rows);
                $('#datatable').DataTable({
                    paging: true,
                    searching: true,
                    ordering: true,
                    order: [[0, 'asc']],
                    responsive: true,
                    language: {
                        search: "Filter records:",
                        lengthMenu: "Show _MENU_ leads per page",
                        info: "Showing _START_ to _END_ of _TOTAL_ leads"
                    }
                });
            } else {
                $('#datatable tbody').html('<tr><td colspan="5">No leads found for this status.</td></tr>');
            }
        },
        error: function(xhr, status, error) {
            alert('Error loading leads: ' + error);
        }
    });
}

function formatDate(dateString) {
    const date = new Date(dateString);
    if (isNaN(date)) return dateString;  // fallback if invalid

    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');  // Months 0-based
    const year = date.getFullYear();

    return `${day}-${month}-${year}`;
}

$(document).ready(function() {
    loadLeadsByStatus();
    // Update status heading with AJAX
    $.ajax({
        url: 'api.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            action: 'leads_by_status_user',
            status: status,
            userid: userid
        }),
        success: function(response) {
            if (response.status_name) {
                $('#status-heading').text('Leads with Status: ' + response.status_name);
            }
            console.log(response.status_name); // works
            // You can add more logic here if needed
        },
        error: function(xhr, status, error) {
            alert('Error loading leads: ' + error);
        }
    });
});


</script>

</body>
</html>
