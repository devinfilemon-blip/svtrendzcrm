<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include 'layouts/config.php';


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$msg="";




if(isset($_POST['leadId'])){
    $leadId = $_POST['leadId'] ;
  
}else{
    $leadId=0;
}

?>

<head>
    <title>Add Lead</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
   <!-- Include jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


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
                            <h4 class="mb-sm-0 font-size-18">Add Lead</h4>
                            

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Lead</a></li>
                                    <li class="breadcrumb-item active">Add Lead</li>
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
                                <form id="customerform" method="post" enctype="multipart/form-data">

                                <input type="hidden" id="leadId" name="leadId" value="<?php  echo $leadId; ?>" >

                                <div class="row">
                                    <div class="col-md-6">
                                     
                                        <label for="leadname" class="form-label">Lead Name</label>
                                        <input type="text" class="form-control" id="leadname" name="leadname" value="<?php if(isset($output)) echo $output['sLead_name']; ?>" required>
                                        </div> 

                                        <div class="col-md-6">
                                    <label for="companyname" class="form-label">Company Name</label>
                                    <input type="text" class="form-control" id="companyname" name="companyname" value="<?php if(isset($output)) echo $output['sCompany_name']; ?>" required>
                                    <ul id="companyDropdown" class="list-group" style="display:none;"></ul>
                                  
                                </div>
                                       
                                  
                                        </div> 
                                        <br>
                                        <div class="row">

                                        <div class="col-md-6">
                                    <label for="industrytype" class="form-label">Industry Type</label>
                                    <input type="text" class="form-control" id="industrytype" name="industrytype" value="<?php if(isset($output)) echo $output['sIndustry_type']; ?>" required>
                                  
                                </div>

                                    <!--  -->
                                

                                   
                                    <div class="col-md-6">
                                        <label for="contactperson" class="form-label">Contact person Name</label>
                                        <input type="text" class="form-control" id="contactperson" name="contactperson" value="<?php if(isset($output)) echo $output['sContactperson']; ?>" required>
                                    </div>
                                    </div>
<br>

                                    <div class="row">
                                    <div class="col-md-6">
                                    <label for="designation" class="form-label">Designation</label>
                                    <input type="text" class="form-control" id="designation" name="designation" value="<?php if(isset($output)) echo $output['sDesignation']; ?>" required>
                                  
                                </div>

                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php if(isset($output)) echo $output['sEmail']; ?>" required>
                                        </div>
                             
                                
                                </div>
                                

                                
                                 
                                   
                           
<br>


<div class="row">
                            <div class="col-md-6">
                                      
                                      <label for="phone" class="form-label">Phone Number</label>
                                      <input type="text" class="form-control" id="phone" name="phone" value="<?php if(isset($output)) echo $output['sPhone']; ?>" required>
                              </div>

                              <div class="col-md-6">
                            <label for="alterphone" class="form-label">Alternate Phone Number</label>
                            <input type="text" class="form-control" id="alterphone" name="alterphone" value="<?php if(isset($output)) echo $output['sAlternate_phone']; ?>" required>
                        </div>
                            <!--  -->

                                    </div>
                                    <br>

                                    <div class="row">
                                    <div class="col-md-6">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control" id="address" name="address" required><?php if(isset($output)) echo $output['sAddress']; ?></textarea>
                                    </div>

                                    <div class="col-md-6">
                                    <label for="location" class="form-label">Location</label>
                                    <input type="text" class="form-control" id="location" name="location" value="<?php if(isset($output)) echo $output['sLocation']; ?>" required>
                                  
                                </div>

    </div>
    <br>
                                <div class="row">
                                <div class="col-md-6">
                                        <label for="sources" class="form-label">Lead Source</label>
                                        <select class="form-control" id="sources" name="sources" required">
                                             <option value="">Select Sources</option>
                                            <?php
                                                $stmt1 = $link->prepare('select * from tblsources');
                                                $stmt1->execute();
                                                $result1 = $stmt1->get_result();
                                                while($row1 = $result1->fetch_assoc()){
                                            ?>               
                                                <option value="<?php echo $row1['iSourceid']; ?>" <?php if(isset($output) && $output['sLead_source'] == $row1['iSourceid']) echo "selected"; ?>><?php echo $row1['sSources']; ?></option>
                                            <?php
                                                }
                                            ?>
                                            </select>
                                        </div>
                                    
                                
                                    <div class="col-md-6">
                                    <label for="statuslead" class="form-label">Lead Status</label>
                                    <select class="form-control" id="statuslead" name="statuslead" required">
                                             <option value="">Select Status</option>
                                            <?php
                                                $stmt1 = $link->prepare('select * from tblstatus');
                                                $stmt1->execute();
                                                $result1 = $stmt1->get_result();
                                                while($row1 = $result1->fetch_assoc()){
                                            ?>               
                                                <option value="<?php echo $row1['iStatusid']; ?>" <?php if(isset($output) && $output['sLead_status'] == $row1['iStatusid']) echo "selected"; ?>><?php echo $row1['sStatus']; ?></option>
                                            <?php
                                                }
                                            ?>
                                            </select>
                                        </div>
                                    </div>
                                  
                                 
                                 <br>

                                 <div class="row">
                                    <div class="col-md-6">
                                            <label for="priority" class="form-label">Lead Priority</label>
                                            <select class="form-control" id="priority" name="priority" required">
                                            

                                            <option value="">Select Priority</option>
                                            <?php
                                                $stmt1 = $link->prepare('select * from tblpriority');
                                                $stmt1->execute();
                                                $result1 = $stmt1->get_result();
                                                while($row1 = $result1->fetch_assoc()){
                                            ?>               
                                                <option value="<?php echo $row1['id']; ?>" <?php if(isset($output) && $output['sLead_priority'] == $row1['id']) echo "selected"; ?>><?php echo $row1['sPrioritylevel']; ?></option>
                                            <?php
                                                }
                                            ?>
                                            </select>
                                        </div>
                                       
                                        <div class="col-md-6">
                                    <label for="text" class="form-label">Lead Type</label>
                                    <input type="text" class="form-control" id="leadtype" name="leadtype" value="<?php if(isset($output)) echo $output['sLead_type']; ?>" required>
                                </div>
                                </div>
                               
                               
                                       
<br>
                              
                               
                                <div class="row">
                                <div class="col-md-6">
                                    <label for="assignedto" class="form-label">Assigned to</label>
                                    <select class="form-control" id="assignedto" name="assignedto" required">
                                             <option value="">Select Assigned to</option>
                                            <?php
                                                $stmt1 = $link->prepare('select * from tbluser');
                                                $stmt1->execute();
                                                $result1 = $stmt1->get_result();
                                                while($row1 = $result1->fetch_assoc()){
                                            ?>               
                                                <option value="<?php echo $row1['iUserid']; ?>" <?php if(isset($output) && $output['sAssigned_to'] == $row1['iUserid']) echo "selected"; ?>><?php echo $row1['sName']; ?></option>
                                            <?php
                                                }
                                            ?>
                                            </select>
                                  
                                </div>
 
                        <div class="col-md-6">
                    <label for="leadowner" class="form-label">Lead Owner</label>
                    <select class="form-control" id="leadowner" name="leadowner" >
                                             <option value="">Select Lead Owner</option>
                                            <?php
                                                $stmt1 = $link->prepare('select * from tbluser');
                                                $stmt1->execute();
                                                $result1 = $stmt1->get_result();
                                                while($row1 = $result1->fetch_assoc()){
                                            ?>               
                                                <option value="<?php echo $row1['iUserid']; ?>" <?php if(isset($output) && $output['sAssigned_to'] == $row1['iUserid']) echo "selected"; ?>><?php echo $row1['sName']; ?></option>
                                            <?php
                                                }
                                            ?>
                                            </select>
  
</div>
</div>
<br>
                            <div class="row">
                               
                               
                               <div class="col-md-6">
                           <label for="website" class="form-label">Website</label>
                           <input type="text" class="form-control" id="website" name="website" value="<?php if(isset($output)) echo $output['sWebsite']; ?>" >
                         </div>

                     <div class="col-md-6">
                   <label for="tags" class="form-label">Tags</label>
                   <input type="text" class="form-control" id="tags" name="tags" value="<?php if(isset($output)) echo $output['sTags']; ?>" >
                   </div>
                       </div>
                   
<br>

<div class="row">


        <div class="col-md-6">
    <label for="preferredcommunication" class="form-label">Preferred Communication</label>
    <select class="form-control" id="preferredcommunication" name="preferredcommunication" required">
                                             <option value="">Select Communication</option>
                                            <?php
                                                $stmt1 = $link->prepare('select * from tblcommunication');
                                                $stmt1->execute();
                                                $result1 = $stmt1->get_result();
                                                while($row1 = $result1->fetch_assoc()){
                                            ?>               
                                                <option value="<?php echo $row1['id']; ?>" <?php if(isset($output) && $output['sPreferred_communication'] == $row1['id']) echo "selected"; ?>><?php echo $row1['sCommunication']; ?></option>
                                            <?php
                                                }
                                            ?>
                                            </select>
  
</div>
<div class="col-md-6">
    <label for="fileUpload" class="form-label">File Upload 1</label>
    <input type="file" class="form-control" id="fileUpload" name="fileUpload">
    <div id="viewFile1" class="mt-2"></div> <!-- View Link Here -->
</div>
</div>


    <br>

    <!-- File Upload 2 -->
   <div class="row">


      <!-- File Upload 2 -->
<div class="col-md-6">
    <label for="fileUpload2" class="form-label">File Upload 2</label>
    <input type="file" class="form-control" id="fileUpload2" name="fileUpload2">
    <div id="viewFile2" class="mt-2"></div>
</div>

<!-- File Upload 3 -->
<div class="col-md-6">
    <label for="fileUpload3" class="form-label">File Upload 3</label>
    <input type="file" class="form-control" id="fileUpload3" name="fileUpload3">
    <div id="viewFile3" class="mt-2"></div>
</div>
    
    </div>
    <br>


<!-- Place this button wherever you want to trigger the product modal -->


<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="productModalLabel">Add Product</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div id="message"></div>
        <form id="productForm">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="product" class="form-label">Product Name</label>
              <input type="text" class="form-control" id="product" name="product" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="parent_id" class="form-label">Parent Category</label>
              <select class="form-select" id="parent_id" name="parent_id">
                <option value="">-- Select Parent Category --</option>
                <?php
                $parent_query = mysqli_query($link, "SELECT id, sCategoryname FROM tblcategoryname ORDER BY sCategoryname ASC");
                while ($row = mysqli_fetch_assoc($parent_query)) {
                    echo "<option value='{$row['id']}'>" . htmlspecialchars($row['sCategoryname']) . "</option>";
                }
                ?>
              </select>
            </div>
          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="addproduct()">Save</button>
      </div>

    </div>
  </div>
</div>

<br>





<br>



<div class="col-md-12">
                                         
                                         <table id="" class="table table-bordered table-striped">            
                                             <thead>
                                               <tr>
                                               <th> <center>Product Name</center></th>
                                               <th> <center>Quantity</center></th>
                                               <th> <center>Rate</center></th>
                                            
                                             </tr>    
                                             
                                             </thead> 
                                             <tbody id="tablevalue">
                                               <?php 
                                               
                                               if(isset($output_values) && $output_values !=[]) {
                                               foreach($output_values as $item) {
                                                   
                                                   
                                                   ?>
                                             <tr>
                                         <td>
<select name="product[]" class="form-select product-select" required style="width:100%;">

    <?php if(isset($item['sProductname'])) { 
        $product_id = $item['sProductname'];
        $stmt = $link->prepare("SELECT sProductname FROM tblproduct WHERE iProductid = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        ?>
        <option value="<?= $product_id ?>" selected><?= $res['sProductname'] ?></option>
    <?php } ?>
  </select>
</td>

                                               <td>
                                                   <input type="text" class="form-control" id="quantity"  name="quantity[]" value="<?php if(isset($item)) echo $item['sQuantity'];?>" required>
                                               </td> 
                                               <td>
                                               <input type="text" class="form-control" id="rate" name="rate[]" 
                                                value="<?php if(isset($item)) echo $item['sRate']; ?>" 
                                              required>
                                               </td>
                                             
                                              
                                              
                                             </tr>
               
                                             <?php } 
                                     }else
                                     {

                                             
                                             ?>
               
                                            <tr> 
                                        
                                            <td>
                                        <select name="product[]" class="form-select product-select" required style="width:100%;">

                                                            <option value="">Select</option>
                                                            <?php $stmt = $link->prepare("SELECT * FROM tblproduct ORDER BY iProductid"); $stmt->execute(); $result = $stmt->get_result(); while($row = $result->fetch_assoc()) { ?>
                                                            <option value="<?php echo $row['iProductid']; ?>"><?php echo $row['sProductname']; ?></option> 
                                                            <?php } ?></select>
                                            </td>
                                            <td>
                                            <input type="text" class="form-control"   name="quantity[]"  value="" required>
                                            </td>
                                            <td>
                                            <input type="text" class="form-control" name="rate[]"  required>
                                            </td>
                                           

                                       </tr>

                                       <?php 
                                       
                                    }                 
                                             ?>
               
                                             </tbody>
               
                                           </table>
               
                             <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#productModal">Click here to Add Product</a>


                                           <button type='button' onclick='addreadings();'  class="btn btn-primary" style="float: right;">Add more</button>
                                           <br> 
               
               
               

                             </div>

  
                              <br>
                              <br>
                              <br>
                                    <center><div>
                                    <button type="button" class="btn btn-primary w-md" onclick="<?php if(isset($leadId)&&$leadId!=0) echo "updatelead();" ;  else echo "addlead();" ?>">Save</button>

                                    </div></center>

                                    
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


function addreadings(){

var table=document.getElementById("tablevalue");

html="";

var html = '<tr>' +

'<td>' +
'<select name="product[]" class="form-select product-select" required style="width:100%;">' +
                '<option value="">Select</option>' +
                '<?php $stmt = $link->prepare("SELECT * FROM tblproduct ORDER BY iProductid"); $stmt->execute(); $result = $stmt->get_result(); while($row = $result->fetch_assoc()) { ?>' +
                '<option value="<?php echo $row['iProductid']; ?>"><?php echo $row['sProductname']; ?></option>' +
                '<?php } ?></select>' +
'</td>' +
'<td>' +
'<input type="text" class="form-control"   name="quantity[]"  value="" required>' +
'</td>' +
'<td>' +
'<input type="text" class="form-control" name="rate[]"  required>' +
'</td>' +
'</tr>';

var newRow = table.insertRow();
newRow.innerHTML = html;
}



</script>
<script>
function addlead() {
    // Get form values
    var leadname = document.getElementById("leadname").value;
    var email = document.getElementById("email").value;
    var phone = document.getElementById("phone").value;
    var alterphone = document.getElementById("alterphone").value;
    var sources = document.getElementById("sources").value;
    var statuslead = document.getElementById("statuslead").value;
    var priority = document.getElementById("priority").value;
    var leadtype = document.getElementById("leadtype").value;
    var companyname = document.getElementById("companyname").value;
    var industrytype = document.getElementById("industrytype").value;
    var designation = document.getElementById("designation").value;
    var website = document.getElementById("website").value;
    var location = document.getElementById("location").value;
    var address = document.getElementById("address").value;
    var assignedto = document.getElementById("assignedto").value;
    var leadowner = document.getElementById("leadowner").value;
    var preferredcommunication = document.getElementById("preferredcommunication").value;
    var tags = document.getElementById("tags").value;
    var contactperson = document.getElementById("contactperson").value;

    // Validate required fields
    if (companyname.trim() === "" || contactperson.trim() === "" || phone.trim() === "" || leadname.trim() === "") {
        alert("Please fill in all customer information.");
        return false;
    }

    // Prepare FormData
    const formData = new FormData();

    formData.append("action", "addlead");
    formData.append("sLead_name", leadname);
    formData.append("sEmail", email);
    formData.append("sPhone", phone);
    formData.append("sAlternate_phone", alterphone);
    formData.append("sLead_source", sources);
    formData.append("sLead_status", statuslead);
    formData.append("sLead_priority", priority);
    formData.append("sLead_type", leadtype);
    formData.append("sCompany_name", companyname);
    formData.append("sIndustry_type", industrytype);
    formData.append("sDesignation", designation);
    formData.append("sWebsite", website);
    formData.append("sLocation", location);
    formData.append("sAddress", address);
    formData.append("sAssigned_to", assignedto);
    formData.append("sLead_owner", leadowner);
    formData.append("sPreferred_communication", preferredcommunication);
    formData.append("sTags", tags);
    formData.append("sContactperson", contactperson);

    // Append files if present
    var fileInput = document.getElementById("fileUpload");
    if (fileInput && fileInput.files.length > 0) {
        formData.append("fileUpload", fileInput.files[0]);
    }
    var fileInput2 = document.getElementById("fileUpload2");
    if (fileInput2 && fileInput2.files.length > 0) {
        formData.append("fileUpload2", fileInput2.files[0]);
    }
    var fileInput3 = document.getElementById("fileUpload3");
    if (fileInput3 && fileInput3.files.length > 0) {
        formData.append("fileUpload3", fileInput3.files[0]);
    }

    // Collect product table data
    const products = [];
    const quantities = [];
    const rates = [];

    const rows = document.querySelectorAll('#tablevalue tr');
    rows.forEach(function(row) {
        var product = row.querySelector('select[name="product[]"]');
        var quantity = row.querySelector('input[name="quantity[]"]');
        var rate = row.querySelector('input[name="rate[]"]');

        if (product && quantity && rate) {
            products.push(product.value);
            quantities.push(quantity.value);
            rates.push(rate.value);
        }
    });

    // Send arrays as JSON strings
    formData.append('products', JSON.stringify(products));
    formData.append('quantities', JSON.stringify(quantities));
    formData.append('rates', JSON.stringify(rates));

    // Send POST request
    fetch('api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(responseData => {
        const messageEl = document.getElementById('message');
        if (responseData.status === "success") {
            messageEl.innerHTML = responseData.message;
            messageEl.classList.remove('error-message');
            messageEl.classList.add('add-message');
            setTimeout(() => window.location.href = 'list-lead-master.php', 500);
        } else {
            messageEl.innerHTML = responseData.message;
            messageEl.classList.remove('add-message');
            messageEl.classList.add('error-message');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        const messageEl = document.getElementById('message');
        messageEl.innerHTML = "An error occurred while adding the customer.";
        messageEl.classList.remove('add-message');
        messageEl.classList.add('error-message');
    });
}




function updatelead() {
    var leadId = document.getElementById("leadId").value;
    var leadname = document.getElementById("leadname").value;
    var email = document.getElementById("email").value;
    var phone = document.getElementById("phone").value;
    var alterphone = document.getElementById("alterphone").value;
    var sources = document.getElementById("sources").value;
    var statuslead = document.getElementById("statuslead").value;
    var priority = document.getElementById("priority").value;
    var leadtype = document.getElementById("leadtype").value;
    var companyname = document.getElementById("companyname").value;
    var industrytype = document.getElementById("industrytype").value;
    var designation = document.getElementById("designation").value;
    var website = document.getElementById("website").value;
    var location = document.getElementById("location").value;
    var address = document.getElementById("address").value;
    var assignedto = document.getElementById("assignedto").value;
    var leadowner = document.getElementById("leadowner").value;
    var preferredcommunication = document.getElementById("preferredcommunication").value;
    var tags = document.getElementById("tags").value;
    var contactperson = document.getElementById("contactperson").value;

    var products = [];
    var quantities = [];
    var rates = [];

    var rows = document.querySelectorAll('#tablevalue tr');
    rows.forEach(function(row) {
        var product = row.querySelector('select[name="product[]"]');
        var quantity = row.querySelector('input[name="quantity[]"]');
        var rate = row.querySelector('input[name="rate[]"]');
        if (product && quantity && rate) {
            products.push(product.value);
            quantities.push(quantity.value);
            rates.push(rate.value);
        }
    });

    if (companyname.trim() === "" || contactperson.trim() === "" || phone.trim() === "" || leadname.trim() === "") {
        alert("Please fill in all customer information.");
        return false;
    }

    // Prepare FormData instead of JSON
    var formData = new FormData();
    formData.append('action', 'updatelead');
    formData.append('leadId', leadId);
    formData.append('sLead_name', leadname);
    formData.append('sEmail', email);
    formData.append('sPhone', phone);
    formData.append('sAlternate_phone', alterphone);
    formData.append('sLead_source', sources);
    formData.append('sLead_status', statuslead);
    formData.append('sLead_priority', priority);
    formData.append('sLead_type', leadtype);
    formData.append('sCompany_name', companyname);
    formData.append('sIndustry_type', industrytype);
    formData.append('sDesignation', designation);
    formData.append('sWebsite', website);
    formData.append('sLocation', location);
    formData.append('sAddress', address);
    formData.append('sAssigned_to', assignedto);
    formData.append('sLead_owner', leadowner);
    formData.append('sPreferred_communication', preferredcommunication);
    formData.append('sTags', tags);
    formData.append('sContactperson', contactperson);

    // Append products, quantities, rates as JSON strings
    formData.append('products', JSON.stringify(products));
    formData.append('quantities', JSON.stringify(quantities));
    formData.append('rates', JSON.stringify(rates));

    // Append files if selected
    var file1 = document.getElementById('fileUpload').files[0];
    var file2 = document.getElementById('fileUpload2').files[0];
    var file3 = document.getElementById('fileUpload3').files[0];

    if (file1) formData.append('fileUpload', file1);
    if (file2) formData.append('fileUpload2', file2);
    if (file3) formData.append('fileUpload3', file3);

    fetch('api.php', {
        method: 'POST',
        body: formData // no JSON.stringify here, no Content-Type header
    })
    .then(response => response.json())
    .then(responseData => {
        if (responseData.status === "success") {
            document.getElementById('message').innerHTML = responseData.message;
            document.getElementById('message').classList.add('add-message');
            setTimeout(() => {
                window.location.href = 'list-lead-master.php';
            }, 500);
        } else {
            document.getElementById('message').innerHTML = responseData.message;
            document.getElementById('message').classList.add('error-message');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('message').innerHTML = "An error occurred while updating the lead.";
        document.getElementById('message').classList.add('error-message');
    });
}

function formatDate(dateStr) {
    // Check if the date is valid
    const date = new Date(dateStr);
    if (isNaN(date)) {
        return '';  // Return an empty string if the date is invalid
    }
    
    // Format the date to ISO string or a specific format
    return date.toISOString().split('T')[0];  // You can change the format as needed
}


function getleadbyid() {
    var leadId = document.getElementById("leadId").value;

    if (leadId == 0) {
        return;
    }

    // Form fields to be populated
    var leadname = document.getElementById("leadname");
    var email = document.getElementById("email");
    var phone = document.getElementById("phone");
    var alterphone = document.getElementById("alterphone");
    var sources = document.getElementById("sources");
    var statuslead = document.getElementById("statuslead");
    var priority = document.getElementById("priority");
    var leadtype = document.getElementById("leadtype");
    var companyname = document.getElementById("companyname");
    var industrytype = document.getElementById("industrytype");
    var designation = document.getElementById("designation");
    var website = document.getElementById("website");
    var location = document.getElementById("location");
    var address = document.getElementById("address");
    var assignedto = document.getElementById("assignedto");
    var leadowner = document.getElementById("leadowner");
    
    var preferredcommunication = document.getElementById("preferredcommunication");
    var contactperson = document.getElementById("contactperson");

    var tags = document.getElementById("tags");



    // Prepare data to send to the backend
    const data = {
        action: "getleadbyid", 
        leadId: leadId        
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
    console.log('API Response:', responseData); // Log the response to the console
    if (responseData.status === "success" && responseData.lead) {
        const lead = responseData.lead;

        // Populate the form fields with the retrieved lead data
        leadname.value = lead.sLead_name || '';
        email.value = lead.sEmail || '';
        phone.value = lead.sPhone || '';
        alterphone.value = lead.sAlternate_phone || '';
        sources.value = lead.sLead_source || '';
        statuslead.value = lead.sLead_status || '';
        priority.value = lead.sLead_priority || '';
        leadtype.value = lead.sLead_type || '';
        companyname.value = lead.sCompany_name || '';
        industrytype.value = lead.sIndustry_type || '';
        designation.value = lead.sDesignation || '';
        website.value = lead.sWebsite || '';
        location.value = lead.sLocation || '';
        address.value = lead.sAddress || '';
        assignedto.value = lead.sAssigned_to || '';
        leadowner.value = lead.sLead_owner || '';
        contactperson.value = lead.sContactperson || ''; // Correctly assigning value here
        preferredcommunication.value = lead.sPreferred_communication || '';
        tags.value = lead.sTags || '';


   const fileLinks = responseData.files || {};

function getFullFilePath(filename) {
    // If it already contains 'uploads/', don’t add it again
    return filename.startsWith('uploads/') ? filename : 'uploads/' + filename;
}

if (fileLinks.file1) {
    document.getElementById("viewFile1").innerHTML = `<a href="${getFullFilePath(fileLinks.file1)}" target="_blank">View File 1</a>`;
}
if (fileLinks.file2) {
    document.getElementById("viewFile2").innerHTML = `<a href="${getFullFilePath(fileLinks.file2)}" target="_blank">View File 2</a>`;
}
if (fileLinks.file3) {
    document.getElementById("viewFile3").innerHTML = `<a href="${getFullFilePath(fileLinks.file3)}" target="_blank">View File 3</a>`;
}

        // Process products
        const products = responseData.products;
        const tableBody = document.getElementById('tablevalue');
        tableBody.innerHTML = ''; // Clear any existing rows

        products.forEach(product => {
            const row = document.createElement('tr');

            row.innerHTML = `
                <td style=" color: white;">
                    <select name="product[]" class="form-select" disabled>
                        <option value="">Select</option>
                        ${responseData.allProducts.map(prod => {
                            const selected = prod.iProductid == product.sProductname ? 'selected' : '';
                            return `
                                <option value="${prod.iProductid}" ${selected}>
                                    ${prod.sProductname}
                                </option>
                            `;
                        }).join('')}
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control" name="quantity[]" value="${product.sQuantity}" readonly>
                </td>
                <td>
                    <input type="text" class="form-control" name="rate[]" value="${product.sRate}" readonly>
                </td>
            `;

            tableBody.appendChild(row);
        });
    }
})
.catch(error => {
    console.error('Error:', error);
});

}
getleadbyid();

// function getContactOptions(selectedContactId) {
//     let optionsHTML = '';
    
 
//     const contactTypes = [
//         { id: 1, name: 'Manager' },
//         { id: 2, name: 'Support' },
//         { id: 3, name: 'Sales' }
//     ];

//     contactTypes.forEach(contactType => {
//         optionsHTML += `<option value="${contactType.id}" ${contactType.id === selectedContactId ? 'selected' : ''}>${contactType.name}</option>`;
//     });

//     return optionsHTML;
// }





</script>
<script>
    // Function to handle user input and search for matching companies
    document.getElementById('companyname').addEventListener('input', function() {
        const query = this.value.trim();

        // Only proceed if there's input
        if (query.length > 0) {
            fetch('api.php?query=' + encodeURIComponent(query))
                .then(response => response.json())
                .then(data => {
                    const dropdown = document.getElementById('companyDropdown');
                    dropdown.innerHTML = ''; // Clear previous results

                    if (data.status === 'success' && data.data.length > 0) {
                        // Display matching companies in the dropdown
                        data.data.forEach(company => {
                            const li = document.createElement('li');
                            li.classList.add('list-group-item');
                            li.textContent = company.sCompanyname;
                            li.onclick = function() {
                                document.getElementById('companyname').value = company.sCompanyname;
                                dropdown.style.display = 'none'; // Hide dropdown after selection
                            };
                            dropdown.appendChild(li);
                        });
                        dropdown.style.display = 'block';
                    } else {
                        // If no matches, hide the dropdown
                        dropdown.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error fetching companies:', error);
                    const dropdown = document.getElementById('companyDropdown');
                    dropdown.style.display = 'none';
                });
        } else {
            document.getElementById('companyDropdown').style.display = 'none';
        }
    });

    // Optional: Close the dropdown when clicking outside of it
    document.addEventListener('click', function(event) {
        if (!document.getElementById('companyDropdown').contains(event.target) && event.target !== document.getElementById('companyname')) {
            document.getElementById('companyDropdown').style.display = 'none';
        }
    });


   function addproduct() {
    var productName = document.getElementById("product").value.trim();
    var parent_id = document.getElementById("parent_id").value;

    if (productName === "") {
        alert("Please enter the product name.");
        return;
    }

    const data = {
        action: "addproduct",
        product: productName,
        parent_id: parent_id
    };

    fetch('api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(responseData => {
    if (responseData.status === "success") {
        // document.getElementById('message').innerHTML = `<div class="text-success">${responseData.message}</div>`;

        // Here — after success, add the new product option to all dropdowns
        const newProductId = responseData.productId; // You need this from your API response
        const newProductName = document.getElementById("product").value.trim();

        // Create a new option element
        const option = document.createElement('option');
        option.value = newProductId;
        option.textContent = newProductName;

        // Add to modal dropdown (#product)
        const modalSelect = document.getElementById('product');
        if (modalSelect) {
            modalSelect.appendChild(option.cloneNode(true));
        }

        // Add to all table product dropdowns (select[name="product[]"])
        document.querySelectorAll('select[name="product[]"]').forEach(select => {
            select.appendChild(option.cloneNode(true));
        });

        // Then hide modal, clear inputs, etc
        setTimeout(() => {
            const modalEl = document.getElementById('productModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) {
                modal.hide();
            }
            document.getElementById("product").value = '';
            document.getElementById("parent_id").value = '';
            setTimeout(() => {
                document.getElementById('message').innerHTML = '';
            }, 3000);
        }, 800);

    } else {
        // Handle error messages here if needed
    }
})

   }


</script>
<script>
$(document).ready(function () {
 $('.product-select').select2({
  placeholder: 'Select Product',
  tags: true,
  ajax: {
    url: 'fetch-products.php',
    type: 'POST',
    dataType: 'json',
    delay: 250,
    data: function (params) {
      return {
        searchTerm: params.term
      };
    },
    processResults: function (data) {
      return {
        results: data
      };
    },
    cache: true
  }
});

});

</script>



<script src="assets/js/app.js"></script>
