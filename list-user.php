<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include 'layouts/config.php';
include_once 'layouts/crm-access.php';
crmRequireAdmin($link);


if(isset($_POST['userid'])){
    $userid = $_POST['userid'] ;
  
}

?>

<head>
    <title>List Users</title>
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
                            <h4 class="mb-sm-0 font-size-18">List Users</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Users</a></li>
                                    <li class="breadcrumb-item active">List Users</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end page title -->
<div class="table-responsive">
                <div><span id="message"></span></div>

                <table id="datatable" class="table table-bordered table-striped">
    <thead>
        <tr>
           <th>Sr No.</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Role</th>
            <th>Access</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>
    </thead>
    <tbody>
   
        <!-- User rows will be dynamically added here by AJAX -->
    </tbody>
</table>

       <input type="hidden" id="userid" name="userid" value="">

  

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

<script>function fngetlist() {
    $.ajax({
        url: 'api.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ action: 'fngetlist' }),
        success: function(response) {
            console.log('Raw Response:', response);

            if (response.status === 'success') {
                var users = response.data;
                var userRows = '';

                users.forEach(function(user, index) {
                    var role = user.sRole || '-';
                    var roleBadge = role === 'Admin'
                        ? `<span class="badge bg-primary">${role}</span>`
                        : (role === 'Client'
                            ? `<span class="badge bg-info">${role}</span>`
                            : `<span class="badge bg-secondary">${role}</span>`);

                    var accessLabel;
                    if (role === 'Admin') {
                        accessLabel = 'All';
                    } else if (role === 'Client') {
                        accessLabel = user.sClientCompany ? ('Client Portal — ' + user.sClientCompany) : 'Client Portal';
                    } else {
                        var accessParts = [];
                        if (parseInt(user.iAccessLead || 0, 10) === 1) accessParts.push('Lead');
                        if (parseInt(user.iAccessProject || 0, 10) === 1) accessParts.push('Project');
                        if (parseInt(user.iAccessFinance || 0, 10) === 1) accessParts.push('Finance');
                        if (accessParts.length === 0) accessParts.push('Default');
                        accessLabel = accessParts.join(', ');
                    }

                    userRows += `<tr>
                        <td>${index + 1}</td> 
                        <td>${user.sName || ''}</td>
                        <td>${user.sEmail || ''}</td>
                        <td>${user.sPhone || ''}</td>
                        <td>${roleBadge}</td>
                        <td><small>${accessLabel}</small></td>
                        <td>
                            <form action="add-user.php" method="post">
                                <input type="hidden" name="userid" value="${user.iUserid}">
                                <button type="submit" name="btnedit" value="Edit" class="btn btn-success">Edit</button>
                            </form>
                        </td>
                        <td>
                            <button class="btn btn-danger delete-btn" data-id="${user.iUserid}" onclick="deleteuser(${user.iUserid})">Delete</button>
                        </td>
                    </tr>`;
                });

                if ($.fn.DataTable.isDataTable('#datatable')) {
                    $('#datatable').DataTable().destroy();
                }

                $('#datatable tbody').html(userRows);
                $('#datatable').DataTable();

            } else {
                $('#datatable tbody').html('<tr><td colspan="8">No users found</td></tr>');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            alert('An error occurred while fetching users: ' + error);
        }
    });
}


$(document).ready(function() {
    fngetlist();
});


function deleteuser(userid) {

    console.log('User ID:', userid);  



    if (userid == 0) {
        alert('Invalid user ID.');
        return false;
    }
    var confirmDelete = confirm("Are you sure you want to delete this user?");
    
    if (confirmDelete) {
 
    const data = {
        action: "deleteuser",
        userid: userid
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
        fngetlist();
    } else {
        document.getElementById('message').innerHTML = responseData.message;
        document.getElementById('message').classList.add('error-message'); 
            fngetlist();
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