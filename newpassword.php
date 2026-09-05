<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include 'layouts/config.php';
$msg="";


if(isset($_POST['userid'])){
    $userid = $_POST['userid'] ;
  
}



?>

<head>
    <title>Add Users</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 

    <style>
.add-message {
    color: green;
    font-weight: bold;  /* Optional */
}

.error-message {
    color: red;
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
                            <h4 class="mb-sm-0 font-size-18">New Password</h4>
                            

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Users</a></li>
                                    <li class="breadcrumb-item active">New Password</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end page title -->

                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-body">
                                <!-- <h4 class="card-title mb-4">Form Grid Layout</h4> -->
                                <div><span id="message"></span></div>
                                <form action="add-user.php" id="userForm" method="post" enctype="multipart/form-data">

                                <input type="hidden" id="userid" name="userid" value="<?php echo $_SESSION['user_id']; ?>">

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                        <label for="current" class="form-label">Current password</label>
                                        <input type="password" class="form-control" id="current" name="current" value="" required>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                    <label for="newpass" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="newpass" name="newpass" value="" required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="confirm" class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control" id="confirm" name="confirm" value="" required>
                                 
                                </div>
                            </div>

                               
                                    <div>
                                    <button type="button" class="btn btn-primary w-md" onclick="changePassword();">Change Password</button>

                                    </div>
                                </form>
                            </div>
                            <!-- end card body -->
                        </div>
                        <!-- end card -->
                    </div>
                    <!-- end col -->

                    
                </div>
                <!-- end row -->

               

            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->

        <?php include 'layouts/footer.php'; ?>
    </div>
    <!-- end main content-->

</div>
</body>

</html>
<!-- END layout-wrapper -->

<!-- Right Sidebar -->
<?php include 'layouts/right-sidebar.php'; ?>
<!-- Right-bar -->
<?php include 'layouts/vendor-scripts.php'; ?>



<script src="assets/js/app.js"></script>

<script>
function changePassword() {
    var current = document.getElementById('current').value;
    var newpass = document.getElementById('newpass').value;
    var confirm = document.getElementById('confirm').value;
    var userid = document.getElementById('userid').value; 

    console.log("Current Password: " + current);
    console.log("New Password: " + newpass);
    console.log("Confirm Password: " + confirm);
    console.log("User ID: " + userid);  

    var message = document.getElementById('message');
    message.innerHTML = ""; // Clear previous message
    message.classList.remove('add-message'); // Remove any previous styles for the message class

    // Validate if the new password and confirm password match
    if (newpass !== confirm) {
        // Display message in the message div
        message.innerHTML = "New password and confirm password do not match.";
        message.style.color = "red"; // Optional: Change the color to red for error
        message.classList.add('error-message'); // Add class for styling
        return;
    }

 
    var data = {
        action: "changePassword",
        userid: userid,  // Include the userid in the data
        current: current,
        newpass: newpass,
        confirm: confirm
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
        if (responseData.status === 'success') {
            //alert('Password changed successfully');
            document.getElementById('message').innerHTML = responseData.message;
            document.getElementById('message').classList.add('add-message'); 
            document.getElementById('current').value = '';
            document.getElementById('newpass').value = '';
            document.getElementById('confirm').value = '';
        } else {
            //alert('Error: ' + responseData.message);

            document.getElementById('message').innerHTML = 'Error: ' + responseData.message;
            document.getElementById('message').classList.add('error-message'); 
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again later.');
    });
}






</script>

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


