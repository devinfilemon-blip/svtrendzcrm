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
                            <h4 class="mb-sm-0 font-size-18">My Profile</h4>
                            

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Users</a></li>
                                    <li class="breadcrumb-item active">My Profile</li>
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
                                        <label for="name" class="form-label">Name</label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?php if(isset($output)) echo $output['sName']; ?>" required>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php if(isset($output)) echo $output['sEmail']; ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" value="<?php if(isset($output)) echo $output['sPhone']; ?>" required>
                                    <small id="phoneError" style="color: red;"></small> <!-- Error message will show here -->
                                </div>
                            </div>

                               
                                    <div>
                                    <button type="button" class="btn btn-primary w-md" onclick="updateuser();">Update</button>
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
<!-- JAVASCRIPT -->
<script>
document.getElementById("userForm").addEventListener("submit", function(event) {
    var phone = document.getElementById("phone").value;
    var phoneError = document.getElementById("phoneError");

    // Check if phone number has exactly 10 digits
    var phonePattern = /^\d{10}$/;

    if (!phone.match(phonePattern)) {
        // Prevent form submission if validation fails
        phoneError.textContent = "Phone number must be exactly 10 digits.";
        event.preventDefault();  // Prevent form from submitting
    } else {
        phoneError.textContent = "";  // Clear any previous error message
    }
});
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

<script>




function fetchUserData() {
    var userid=document.getElementById("userid").value ;

if (userid==0){
  return;
}
var name = document.getElementById("name");
    var email = document.getElementById("email");
    var phone = document.getElementById("phone");
    

    // Convert form data to JSON
    const data = {
        "action":"fetchUserData",
        userid :userid   
    };

    console.log(data);

    // Send POST request to server
    fetch('api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())  // Parse the response JSON
    .then(data => {
        console.log('Response Data:', data); // Log the entire response
        // Check if the data is fetched successfully
        if (data.status === 'success') {
            // Populate the form fields with the fetched data
            document.getElementById("name").value = data.data.sName;
            document.getElementById("email").value = data.data.sEmail;
            document.getElementById("phone").value = data.data.sPhone;
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error fetching user data:', error);
        alert('An error occurred while fetching the user data.');
    });
}
fetchUserData();



function updateuser() {
    var userid = document.getElementById("userid").value;
    var name = document.getElementById("name").value;
    var email = document.getElementById("email").value;
    var phone = document.getElementById("phone").value;

   
    if (userid == 0 || name == "" || email == "" || phone == "") {
        alert("Please enter all required data.");
        return false;
    }

    var phonePattern = /^\d{10}$/;
    if (!phone.match(phonePattern)) {
        alert("Phone number must be exactly 10 digits.");
        return false;  // Prevent the form from being submitted
    }
    const data = {
        action: "updateMyProfile",
        userid: userid,
        name: name,
        email: email,
        phone: phone
    };

    console.log(data);

  
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
        if (responseData.status === "success") {
         
            document.getElementById('message').innerHTML = responseData.message;
            document.getElementById('message').classList.add('add-message');  // Add a success class for styling
            setTimeout(function() {
                window.location.href = 'MyProfile.php';
            }, 1500);
        } else {
          
            document.getElementById('message').innerHTML = responseData.message;
            document.getElementById('message').classList.add('error-message');  // Add an error class for styling
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}




function getuserbyid(){
    
  var userid=  <?php if(isset($userid)) echo $userid; else echo 0?>;

  if (userid==0){
    return;
  }
  
  var name = document.getElementById("name");
    var email = document.getElementById("email");
    var phone = document.getElementById("phone");
    

    // Convert form data to JSON
    const data = {
        action: "getuserbyid",
      
        userid :userid
       
    };

    // console.log(data);

    // Send POST request to server
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
        if (responseData.data) {
          
           //redirect to list
 name.value=responseData.data.sName;
 email.value=responseData.data.sEmail;
 phone.value=responseData.data.sPhone;
        } else {
            alert('Error : Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}



getuserbyid();

</script>




<script src="assets/js/app.js"></script>


