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
                            <h4 class="mb-sm-0 font-size-18">Forget Password</h4>
                            

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Users</a></li>
                                    <li class="breadcrumb-item active">Forget Password</li>
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
                                <form action="add-user.php" id="forgotPasswordForm" method="post" enctype="multipart/form-data">

                                <input type="hidden" id="userid" name="userid" value="<?php echo $_SESSION['user_id']; ?>">

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                        <label for="email" class="form-label">Enter Vaild Email</label>
                                        <input type="text" class="form-control" id="email" name="email" value="" required>
                                        </div>
                                    </div>

                                  

                               
                                    <div>
                                    <button type="button" class="btn btn-primary w-md" onclick="forgetPassword();">Forget Password</button>

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
function forgetPassword() {
    var email = document.getElementById('email').value;  // Get the email entered by the user
    var data = {
        action: 'forgetPassword',
        email: email
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
    if (responseData.status === 'success') {
      //  alert('Password changed successfully');
      document.getElementById('message').innerHTML = responseData.message;
      document.getElementById('message').classList.add('add-message'); 
    } else {
       // alert('Error: ' + responseData.message);
       document.getElementById('message').innerHTML = 'Error: ' + responseData.message;
       document.getElementById('message').classList.add('error-message'); 
    }
})
.catch(error => {
    console.error('Error:', error);  // Log the error to the console
    //alert('An error occurred. Please try again later.');
   
});

}



</script>

<script>
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


