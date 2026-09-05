<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include 'layouts/config.php';
if(isset($_POST['id'])){
    $id = $_POST['id'] ;
  
}

?>

<head>
    <title>List Task type</title>
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
                            <h4 class="mb-sm-0 font-size-18">List Task type</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Task type</a></li>
                                    <li class="breadcrumb-item active">List Task type</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end page title -->

                <div><span id="message"></span></div>

                <table id="datatable" class="table table-bordered table-striped">
    <thead>
        <tr>
           <th>Sr No.</th>
            <th>Task type</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>
    </thead>
    <tbody>
   
        <!-- User rows will be dynamically added here by AJAX -->
    </tbody>
</table>

       <input type="hidden" id="id" name="id" value="">

  

   
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
function fngetlisttask() {
    $.ajax({
        url: 'api.php',
        method: 'POST',
        contentType: 'application/json', // Set content type to JSON
        data: JSON.stringify({
            action: 'fngetlisttask'
        }),
        success: function(response) {
            console.log('Raw Response:', response);  // Log the response to check if it's an object

            if (response.status === 'success') {
                var statuses = response.data;
                var statusRows = '';

                statuses.forEach(function(status, index) {
                    statusRows += `<tr>
                                   <td>${index + 1}</td> 
                                   <td>${status.sTasktype}</td>
                                   <td>
                                       <form action="add-tasktype.php" method="post">
                                           <input type="hidden" name="id" value="${status.id}">
                                           <button type="submit" name="btnedit" value="Edit" class="btn btn-success">Edit</button>
                                       </form>
                                   </td>
                                   <td>
                                       <button class="btn btn-danger delete-btn" data-id="${status.id}" onclick="deletetask(${status.id})">Delete</button>
                                   </td>
                               </tr>`;
                });

                // Destroy existing DataTable if initialized
                if ($.fn.DataTable.isDataTable('#datatable')) {
                    $('#datatable').DataTable().destroy();
                }

                $('#datatable tbody').html(statusRows);

                // Re-initialize DataTable
                $('#datatable').DataTable();
            } else {
                $('#datatable tbody').html('<tr><td colspan="4">No statuses found</td></tr>');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            alert('An error occurred while fetching statuses: ' + error);
        }
    });
}

$(document).ready(function() {
    fngetlisttask();
});




function deletetask(id) {

console.log(' ID:', id);  



if (id == 0) {
    alert('Invalid user ID.');
    return false;
}
var confirmDelete = confirm("Are you sure you want to delete this user?");

if (confirmDelete) {

const data = {
    action: "deletetask",
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
    fngetlisttask();
} else {
    document.getElementById('message').innerHTML = responseData.message;
    document.getElementById('message').classList.add('error-message'); 
    fngetlisttask();
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