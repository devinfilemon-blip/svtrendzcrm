<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include 'layouts/config.php';
include_once 'layouts/crm-access.php';
crmRequireAdmin($link);

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
.btn-primary {
    color: #fff;
    background-color: #005aa5;
    border-color: #005aa5;
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
                            <h4 class="mb-sm-0 font-size-18">Add User</h4>
                            

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Users</a></li>
                                    <li class="breadcrumb-item active">Add User</li>
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

                                <input type="hidden" id="userid" name="userid" value="">


                                <div class="row">
                                    <div class="col-md-6">
                                            <label for="usercode" class="form-label">User Code</label>
                                            <input type="text" class="form-control" id="usercode" name="usercode" value="<?php if(isset($output)) echo $output['sUserCode']; ?>" required>
                                        </div>
                                    
                                    <div class="col-md-6">
                                            <label for="name" class="form-label">Name</label>
                                            <input type="text" class="form-control" id="name" name="name" value="<?php if(isset($output)) echo $output['sName']; ?>" required>
                                        
                                             </div>
                                    </div>
                                     <br>
                                        <div class="row">
                                    <div class="col-md-6">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" value="<?php if(isset($output)) echo $output['sEmail']; ?>" required>
                                        </div>
                                   
                               
                                    
                                  
                                    <div class="col-md-6">
                              
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" value="<?php if(isset($output)) echo $output['sPhone']; ?>" required>
                                    <small id="phoneError" style="color: red;"></small> <!-- Error message will show here -->
                                </div>
                            
                            </div>
                             <br>
<div class="row">
                             <div class="col-md-6">
                                    
                                        <label for="department" class="form-label">Department</label>
                                        <select class="form-control" id="department" name="department" required">
                                            

                                            <option value="">Select Department</option>
                                            <?php
                                                $stmt = $link->prepare('select * from tbldepartment');
                                                $stmt->execute();
                                                $result = $stmt->get_result();
                                                while($row = $result->fetch_assoc()){
                                            ?>               
                                                <option value="<?php echo $row['iDepid']; ?>" <?php if(isset($output) && $output['iDepid'] == $row['iDepid']) echo "selected"; ?>><?php echo $row['sDepartment']; ?></option>
                                            <?php
                                                }
                                            ?>
                                            </select>
                                        </div>
           <div class="col-md-6">
                            
    <label for="Role" class="form-label">Role</label>
    <select class="form-control" id="Role" name="Role" required onchange="toggleAccessModules()">
        <option value="User" <?php if(isset($output) && $output['sRole'] == 'User') echo 'selected'; ?>>User</option>
        <option value="Admin" <?php if(isset($output) && $output['sRole'] == 'Admin') echo 'selected'; ?>>Admin</option>
        <option value="Client" <?php if(isset($output) && $output['sRole'] == 'Client') echo 'selected'; ?>>Client</option>
    </select>
</div>
                                    </div>


 <br>

<div class="row" id="accessModulesRow">
    <div class="col-12">
        <label class="form-label">Access Modules <small class="text-muted">(for User role — Admin gets all)</small></label>
        <div class="border rounded p-3 bg-light">
            <p class="mb-2 text-muted small">Default for User: Reminder, Re-engage, Daily Report (own records only).</p>
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="accessLead" name="accessLead" value="1">
                <label class="form-check-label" for="accessLead">Lead Management <span class="text-muted">(includes List Quotation only)</span></label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="accessProject" name="accessProject" value="1">
                <label class="form-check-label" for="accessProject">Project Management</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="accessFinance" name="accessFinance" value="1">
                <label class="form-check-label" for="accessFinance">Finance <span class="text-muted">(full Sales Management)</span></label>
            </div>
        </div>
    </div>
</div>
<br>

<div class="row" id="clientCompanyRow" style="display:none;">
    <div class="col-md-6">
        <label for="clientCompany" class="form-label">Client / Company Name</label>
        <input type="text" class="form-control" id="clientCompany" name="clientCompany" value="<?php if(isset($output)) echo htmlspecialchars($output['sClientCompany'] ?? ''); ?>">
        <small class="text-muted">Shown in Client Project Management when assigning projects to this client.</small>
    </div>
</div>
<br>

                       
                          
                                    <div>
                                    <button type="button" class="btn btn-primary w-md" onclick="<?php if(isset($userid)) echo "updateuser();" ;  else echo "adduser();" ?>">Save</button>

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

function toggleAccessModules() {
    var role = document.getElementById("Role").value;
    var row = document.getElementById("accessModulesRow");
    var clientRow = document.getElementById("clientCompanyRow");
    if (!row) return;
    if (role === "Admin") {
        row.style.display = "none";
        document.getElementById("accessLead").checked = true;
        document.getElementById("accessProject").checked = true;
        document.getElementById("accessFinance").checked = true;
    } else if (role === "Client") {
        row.style.display = "none";
        document.getElementById("accessLead").checked = false;
        document.getElementById("accessProject").checked = false;
        document.getElementById("accessFinance").checked = false;
    } else {
        row.style.display = "";
    }
    if (clientRow) {
        clientRow.style.display = (role === "Client") ? "" : "none";
    }
}

function getAccessFlags() {
    var role = document.getElementById("Role").value;
    if (role === "Admin") {
        return { accessLead: 1, accessProject: 1, accessFinance: 1 };
    }
    if (role === "Client") {
        return { accessLead: 0, accessProject: 0, accessFinance: 0 };
    }
    return {
        accessLead: document.getElementById("accessLead").checked ? 1 : 0,
        accessProject: document.getElementById("accessProject").checked ? 1 : 0,
        accessFinance: document.getElementById("accessFinance").checked ? 1 : 0
    };
}

function adduser() {
    var usercode = document.getElementById("usercode").value;
    var name = document.getElementById("name").value;
    var email = document.getElementById("email").value;
    var phone = document.getElementById("phone").value;
    var department = document.getElementById("department").value;
    var Role = document.getElementById("Role").value;
    // Validate if required fields are empty
    if (usercode == "" || name == "" || email == "" || phone == "" || department=="" || Role=="" ) {
        alert("Please enter all required data.");
        return false;
    }

    // Validate phone number: check if it has exactly 10 digits
    var phonePattern = /^\d{10}$/;
    if (!phone.match(phonePattern)) {
        alert("Phone number must be exactly 10 digits.");
        return false;  // Prevent the form from being submitted
    }

    var flags = getAccessFlags();
    // Convert form data to JSON
    const data = {
        action: "adduser",
        usercode: usercode,
        name: name,
        email: email,
        phone: phone,
        department:department,
        Role:Role,
        accessLead: flags.accessLead,
        accessProject: flags.accessProject,
        accessFinance: flags.accessFinance,
        clientCompany: document.getElementById("clientCompany").value
    };

    // Send POST request to the server
    fetch('api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('HTTP error ' + response.status);
        }
        return response.json();
    })
    .then(responseData => {
        // Check if response status is success
        if (responseData.status === "success") {
           document.getElementById('message').innerHTML = responseData.message;
           document.getElementById('message').classList.add('add-message');  
           setTimeout(function() {
            window.location.href = 'list-user.php';  // Redirect to list of users
        }, 500);
        } else {
            document.getElementById('message').innerHTML = responseData.message;
            document.getElementById('message').classList.add('error-message'); 
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}






function updateuser(){
    var userid=  <?php if(isset($userid)) echo $userid; else echo 0?>;
    var usercode = document.getElementById("usercode").value;
    var name = document.getElementById("name").value;
    var email = document.getElementById("email").value;
    var phone = document.getElementById("phone").value;
    var department = document.getElementById("department").value;
    var Role = document.getElementById("Role").value;

    if(userid==0||usercode == "" || name == "" || email == "" || phone == "" || department=="" ){
        alert("Enter Required data");
        return false;
    }

    var phonePattern = /^\d{10}$/;
    if (!phone.match(phonePattern)) {
        alert("Phone number must be exactly 10 digits.");
        return false;  // Prevent the form from being submitted
    }
    var flags = getAccessFlags();
    // Convert form data to JSON
    const data = {
        action: "updateuser",
        userid:userid,
        usercode: usercode,
        name: name,
        email: email,
        phone: phone,
        department:department,
        Role:Role,
        accessLead: flags.accessLead,
        accessProject: flags.accessProject,
        accessFinance: flags.accessFinance,
        clientCompany: document.getElementById("clientCompany").value
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
    .then(async response => {
        const text = await response.text();
        let responseData;
        try {
            responseData = JSON.parse(text);
        } catch (e) {
            throw new Error(text ? text.substring(0, 200) : ('Server error HTTP ' + response.status));
        }
        return responseData;
    })
    .then(responseData => {
        console.log(responseData);
        if (responseData.status === "success") {
        document.getElementById('message').innerHTML = responseData.message;
        document.getElementById('message').classList.add('add-message');  
        setTimeout(function() {
            window.location.href = 'list-user.php';
        }, 500);
        } else {
           document.getElementById('message').innerHTML = responseData.message || 'Update failed.';
           document.getElementById('message').classList.add('error-message'); 
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('message').innerHTML = error.message || 'Update failed.';
        document.getElementById('message').classList.add('error-message');
    });
}






function getuserbyid(){
    var userid=  <?php if(isset($userid)) echo $userid; else echo 0?>;
    if (userid==0){
        return;
    }
    var usercode = document.getElementById("usercode");
    var name = document.getElementById("name");
    var email = document.getElementById("email");
    var phone = document.getElementById("phone");
    var department = document.getElementById("department");
    var Role = document.getElementById("Role");

    // Convert form data to JSON
    const data = {
        action: "getuserbyid",
        userid :userid  
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
        if (responseData.data) {
            usercode.value = responseData.data.sUserCode || '';
            name.value = responseData.data.sName;
            email.value = responseData.data.sEmail;
            phone.value = responseData.data.sPhone;
            department.value = responseData.data.iDepid;
            Role.value = responseData.data.sRole;
            document.getElementById("clientCompany").value = responseData.data.sClientCompany || '';
            document.getElementById("accessLead").checked = parseInt(responseData.data.iAccessLead || 0, 10) === 1;
            document.getElementById("accessProject").checked = parseInt(responseData.data.iAccessProject || 0, 10) === 1;
            document.getElementById("accessFinance").checked = parseInt(responseData.data.iAccessFinance || 0, 10) === 1;
            toggleAccessModules();
        } else {
            alert('Error : Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}



getuserbyid();
toggleAccessModules();

</script>




<script src="assets/js/app.js"></script>


