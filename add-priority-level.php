<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include 'layouts/config.php';
$msg="";


if(isset($_POST['id'])){
    $id = $_POST['id'] ;
  
}

?>

<head>
    <title>Add  Priority level</title>
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
                            <h4 class="mb-sm-0 font-size-18">Add  Priority level</h4>
                            

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);"> Priority level</a></li>
                                    <li class="breadcrumb-item active">Add  Priority level</li>
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
                                <form action="add-pripority-level.php" id="userForm" method="post" enctype="multipart/form-data">

                                <input type="hidden" id="id" name="id" value="">

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                        <label for="priority" class="form-label">Priority levels</label>
                                        <input type="text" class="form-control" id="priority" name="priority" value="<?php if(isset($output)) echo $output['sPriority']; ?>" required>
                                        </div>
                                    </div>

                                   
                               
                                    <div>
                                    <button type="button" class="btn btn-primary w-md" onclick="<?php if(isset($id)) echo "updatepriority();" ;  else echo "addpriority();" ?>">Save</button>

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


function addpriority() {
    var priority = document.getElementById("priority").value;

    if (priority == "") {
        alert("Please enter all required data.");
        return false;
    }

    const data = {
        action: "addpriority",
        priority: priority
      
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
           // alert(responseData.message);  // Display success message
           document.getElementById('message').innerHTML = responseData.message;
           document.getElementById('message').classList.add('add-message');  
           setTimeout(function() {
            window.location.href = 'list-priority-level.php';  // Redirect to list of users
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


function updatepriority(){
    var id=  <?php if(isset($id)) echo $id; else echo 0?>;
    var priority = document.getElementById("priority").value;
   
    

    if(id==0||priority == ""){
        alert("Enter Required data");
        return false;
    }
    // Convert form data to JSON
    const data = {
        action: "updatepriority",
        id:id,
        priority: priority,
      
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
            window.location.href = 'list-priority-level.php';  // Redirect to list of users
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





function getprioritybyid(){
    
  var id=  <?php if(isset($id)) echo $id; else echo 0?>;

  if (id==0){
    return;
  }
  
  var contact = document.getElementById("contact");
 
   
    const data = {
        action: "getprioritybyid",
      
        id :id
       
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
        if (responseData.data) {
          
           //redirect to list
           priority.value=responseData.data.sPrioritylevel;

        } else {
            alert('Error : Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}



getprioritybyid();






</script>




<script src="assets/js/app.js"></script>


