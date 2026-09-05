<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include 'layouts/config.php';
$msg="";


if(isset($_POST['id'])){
    $id = $_POST['id'] ;
  
}

?>

<head>
    <title>Add Category Name</title>
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
                            <h4 class="mb-sm-0 font-size-18">Add Category Name</h4>
                            

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Category Name</a></li>
                                    <li class="breadcrumb-item active">Add Category Name</li>
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
                                <form action="add-categoryname.php" id="userForm" method="post" enctype="multipart/form-data">

                                <input type="hidden" id="id" name="id" value="">

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                        <label for="category" class="form-label">Category Name</label>
                                        <input type="text" class="form-control" id="category" name="category" value="<?php if(isset($output)) echo $output['sCategoryname']; ?>" required>
                                        </div>
                                    </div>
<div class="col-md-6">
    <div class="mb-3">
        <label for="parent_id" class="form-label">Parent Category (Optional)</label>
        <select class="form-select" id="parent_id" name="parent_id">
            <option value="">-- Select Parent Category --</option>
            <?php
            $parent_query = mysqli_query($link, "SELECT id, sCategoryname FROM tblcategoryname ORDER BY sCategoryname ASC");
            while ($row = mysqli_fetch_assoc($parent_query)) {
                $selected = (isset($output) && isset($output['parent_id']) && $output['parent_id'] == $row['id']) ? "selected" : "";
                echo "<option value='{$row['id']}' $selected>" . htmlspecialchars($row['sCategoryname']) . "</option>";
            }
            ?>
        </select>
    </div>
</div>

                                   
                               
                                    <div>
                                    <button type="button" class="btn btn-primary w-md" onclick="<?php if(isset($id)) echo "updatecategory();" ;  else echo "addcategory();" ?>">Save</button>

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

function addcategory() {
    var category = document.getElementById("category").value;
    var parent_id = document.getElementById("parent_id").value;

    if (category == "") {
        alert("Please enter all required data.");
        return false;
    }

    const data = {
        action: "addcategory",
        category: category,
        parent_id: parent_id  // added here
    };

    fetch('api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(responseData => {
        if (responseData.status === "success") {
            document.getElementById('message').innerHTML = responseData.message;
            document.getElementById('message').classList.add('add-message');  
            setTimeout(() => {
                window.location.href = 'list-category.php';
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

function updatecategory() {
    var id = <?php if(isset($id)) echo $id; else echo 0; ?>;
    var category = document.getElementById("category").value;
    var parent_id = document.getElementById("parent_id").value;

    if (id == 0 || category == "") {
        alert("Enter Required data");
        return false;
    }

    const data = {
        action: "updatecategory",
        id: id,
        category: category,
        parent_id: parent_id  // added here
    };

    fetch('api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(responseData => {
        if (responseData.status === "success") {
            document.getElementById('message').innerHTML = responseData.message;
            document.getElementById('message').classList.add('add-message');  
            setTimeout(() => {
                window.location.href = 'list-category.php';
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



function getcategorybyid() {
    var id = <?php if(isset($id)) echo $id; else echo 0; ?>;
    if (id == 0) {
        return;
    }

    var category = document.getElementById("category");
    var parent_id = document.getElementById("parent_id");

    const data = {
        action: "getcategorybyid",
        id: id
    };

    fetch('api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(responseData => {
        if (responseData.data) {
            category.value = responseData.data.sCategoryname;

            if (responseData.data.parent_id !== undefined) {
                parent_id.value = responseData.data.parent_id || "";
            }
        } else {
            alert('Error : Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

getcategorybyid();







</script>




<script src="assets/js/app.js"></script>


