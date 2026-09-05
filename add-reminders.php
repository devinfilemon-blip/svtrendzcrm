<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include 'layouts/config.php';
$msg="";


if(isset($_POST['id'])){
    $id = $_POST['id'] ;
  
}

?>

<head>
    <title>Add Reminder</title>
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
                            <h4 class="mb-sm-0 font-size-18">Add Reminder</h4>
                            

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Reminder</a></li>
                                    <li class="breadcrumb-item active">Add Reminder</li>
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
                                <form action="add-reminders.php" id="userForm" method="post" enctype="multipart/form-data">

                                <input type="hidden" id="id" name="id" value="">

                                <div class="row">
                                  

                                   <div class="col-md-6">
    <div class="mb-3">
        <label for="iUserid" class="form-label">User</label>
       <select class="form-select" id="iUserid" name="iUserid" required>

          <option value="">-- Select User --</option>

            <?php
            $parent_query = mysqli_query($link, "SELECT * from tbluser");
            while ($row = mysqli_fetch_assoc($parent_query)) {
                $selected = (isset($output) && isset($output['iUserid']) && $output['iUserid'] == $row['iUserid']) ? "selected" : "";
                echo "<option value='{$row['iUserid']}' $selected>" . htmlspecialchars($row['sName']) . "</option>";
            }
            ?>
        </select>
    </div>
</div>

  <div class="col-md-6">
                                        <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <input type="text" class="form-control" id="description" name="description" value="<?php if(isset($output)) echo $output['sDescription']; ?>" required>
                                        </div>
                                    </div>

                                      <div class="col-md-6">
                                        <div class="mb-3">
                                        <label for="date" class="form-label">Date</label>
                                        <input type="date" class="form-control" id="date" name="date" value="<?php if(isset($output)) echo $output['sDate']; ?>" required>
                                        </div>
                                    </div>
                               
                                    <div>
                                    <button type="button" class="btn btn-primary w-md" onclick="<?php if(isset($id)) echo "updatereminder();" ;  else echo "addreminder();" ?>">Save</button>

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


function addreminder() {
    var description = document.getElementById("description").value;
    var date = document.getElementById("date").value;
    var iUserid = document.getElementById("iUserid").value;  // Add this

    if (description == "") {
        alert("Please enter all required data.");
        return false;
    }

    const data = {
        action: "addreminder",
        iUserid:iUserid,
        description: description,
        date: date  // Add this
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
        // Check if response status is success
        if (responseData.status === "success") {
           document.getElementById('message').innerHTML = responseData.message;
           document.getElementById('message').classList.add('add-message');
           if (typeof window.refreshCrmNotifBadge === 'function') {
               window.refreshCrmNotifBadge();
           }
           setTimeout(function() {
            window.location.href = 'list-reminders.php';
        }, 500);
        } else {
            //alert('Error: ' + responseData.message);  // Display error message

            document.getElementById('message').innerHTML = responseData.message;
            document.getElementById('message').classList.add('error-message'); 
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}


function updatereminder() {
    var id = <?php if(isset($id)) echo $id; else echo 0?>;
     var description = document.getElementById("description").value;
    var date = document.getElementById("date").value;
    var iUserid = document.getElementById("iUserid").value;  // Add this

  

    const data = {
        action: "updatereminder",
        id: id,
        iUserid:iUserid,
        description: description,
        date: date  // Add this
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
    .then(response => response.json())
  
    .then(responseData => {
        console.log(responseData);
        // Check if response status is success
        if (responseData.status === "success") {
        //    alert(responseData.message);  // Display success message
        document.getElementById('message').innerHTML = responseData.message;
        document.getElementById('message').classList.add('add-message');  
        setTimeout(function() {
            window.location.href = 'list-reminders.php';  // Redirect to list of users
        }, 500);
        } else {
           // alert('Error: ' + responseData.message);  // Display error message

           document.getElementById('message').innerHTML = responseData.message;
           document.getElementById('message').classList.add('error-message'); 
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}



function getreminderbyid() {
    var id = <?php if(isset($id)) echo $id; else echo 0 ?>;
    if (id == 0) return;

    const data = {
        action: "getreminderbyid",
        id: id
    };

    fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(responseData => {
        if (responseData.data) {
                 document.getElementById("date").value = responseData.data.sDate;
            document.getElementById("description").value = responseData.data.sDescription;
            if (responseData.data.iUserid !== null) {
                document.getElementById("iUserid").value = responseData.data.iUserid;
            } else {
                document.getElementById("iUserid").value = "";
            }
        } else {
            alert('Error: Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}



getreminderbyid();



</script>




<script src="assets/js/app.js"></script>


