<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include 'layouts/config.php';
include_once 'layouts/crm-access.php';
crmRequireModule('lead', $link);


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
  
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="assets/css/crm-dark.css?v=20250729e" rel="stylesheet" type="text/css" />

<!-- jQuery (Required for Select2) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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
<input type="hidden" id="screatedby" value="<?php echo $_SESSION['user_id']; ?>">

                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="leadname" class="form-label">Subject</label>
                                        <input type="text" class="form-control" id="leadname" name="leadname" value="<?php if(isset($output)) echo $output['sLead_name']; ?>">
                                    </div>

                                    <div class="col-md-6">
                                        <label for="companyname" class="form-label">Company Name</label>
                                        <input type="text" class="form-control" id="companyname" name="companyname" value="<?php if(isset($output)) echo $output['sCompany_name']; ?>">
                                        <ul id="companyDropdown" class="list-group" style="display:none;"></ul>
                                    </div>
                                </div>
                                        <br>
                                        <div class="row">

                                    <input type="hidden" id="industrytype" name="industrytype" value="<?php if(isset($output)) echo $output['sIndustry_type']; ?>">

                                    <!--  -->
                                

                                   
                                    <div class="col-md-12">
                                        <label for="contactperson" class="form-label">Contact person Name</label>
                                        <input type="text" class="form-control" id="contactperson" name="contactperson" value="<?php if(isset($output)) echo $output['sContactperson']; ?>">
                                    </div>
                                    </div>
<br>

                                    <div class="row">
                                    <div class="col-md-6">
                                    <label for="designation" class="form-label">Designation</label>
                                    <input type="text" class="form-control" id="designation" name="designation" value="<?php if(isset($output)) echo $output['sDesignation']; ?>">
                                  
                                </div>

                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php if(isset($output)) echo $output['sEmail']; ?>">
                                        </div>
                             
                                
                                </div>
                                

                                
                                 
                                   
                           
<br>


<div class="row">
                            <div class="col-md-6">
                                      
                                      <label for="phone" class="form-label">Phone Number</label>
                                      <input type="text" class="form-control" id="phone" name="phone" value="<?php if(isset($output)) echo $output['sPhone']; ?>">
                              </div>

                              <div class="col-md-6">
                            <label for="alterphone" class="form-label">Alternate Phone Number</label>
                            <input type="text" class="form-control" id="alterphone" name="alterphone" value="<?php if(isset($output)) echo $output['sAlternate_phone']; ?>">
                        </div>
                            <!--  -->

                                    </div>
                                    <br>

                                    <div class="row">
                                    <div class="col-md-12">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control" id="address" name="address"><?php if(isset($output)) echo $output['sAddress']; ?></textarea>
                                    </div>

                                    <input type="hidden" id="location" name="location" value="<?php if(isset($output)) echo $output['sLocation']; ?>">

    </div>
    <br>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="sources" class="form-label">Lead Source</label>
                                        <select class="form-control" id="sources" name="sources" onchange="toggleReferenceName()">
                                            <option value="">Select Source</option>
                                            <?php
                                                $stmt1 = $link->prepare('SELECT * FROM tblsources');
                                                $stmt1->execute();
                                                $result1 = $stmt1->get_result();
                                                while ($row1 = $result1->fetch_assoc()) {
                                                    $isReferenceSource = stripos($row1['sSources'], 'reference') !== false;
                                            ?>
                                                <option value="<?php echo $row1['iSourceid']; ?>" <?php echo $isReferenceSource ? 'data-reference="1"' : ''; ?> <?php if (isset($output) && $output['sLead_source'] == $row1['iSourceid']) echo 'selected'; ?>><?php echo htmlspecialchars($row1['sSources']); ?></option>
                                            <?php
                                                }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6" id="referenceNameWrap" style="display:none;">
                                        <label for="referenceName" class="form-label">Reference Person Name</label>
                                        <input type="text" class="form-control" id="referenceName" name="referenceName" placeholder="Name of the person who referred this lead" value="<?php if(isset($output)) echo htmlspecialchars($output['sReferenceName'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="statuslead" class="form-label">Lead Status</label>
                                        <select class="form-control" id="statuslead" name="statuslead">
                                            <option value="">Select Status</option>
                                            <?php
                                                $stmt1 = $link->prepare('SELECT * FROM tblstatus');
                                                $stmt1->execute();
                                                $result1 = $stmt1->get_result();
                                                while ($row1 = $result1->fetch_assoc()) {
                                            ?>
                                                <option value="<?php echo $row1['iStatusid']; ?>" <?php if (isset($output) && $output['sLead_status'] == $row1['iStatusid']) echo 'selected'; ?>><?php echo htmlspecialchars($row1['sStatus']); ?></option>
                                            <?php
                                                }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                  
                                 
                                 <br>

                                 <div class="row">
                                    <input type="hidden" id="priority" name="priority" value="<?php if(isset($output)) echo $output['sLead_priority']; ?>">
                                       
                                        <div class="col-md-12">
                                    <label for="text" class="form-label">Description</label>
                                    <input type="text" class="form-control" id="leadtype" name="leadtype" value="<?php if(isset($output)) echo $output['sLead_type']; ?>">
                                </div>
                                </div>
                               
                               
                                       
<br>
                              <!-- <?php
    echo "DEBUG: Assigned to = " . ($output['sAssigned_to'] ?? 'Not set');
?> -->

                               
                                <div class="row">
                              <div class="col-md-6">
    <label for="assignedto" class="form-label">Assigned to</label>
    <select class="form-control" id="assignedto" name="assignedto[]" multiple="multiple">
        <?php
            $assignedSelected = [];
            if (isset($output['sAssigned_to']) && $output['sAssigned_to'] !== '' && $output['sAssigned_to'] !== null) {
                $assignedSelected = array_filter(array_map('trim', explode(',', (string)$output['sAssigned_to'])));
            }
            $stmt1 = $link->prepare('SELECT * FROM tbluser');
            $stmt1->execute();
            $result1 = $stmt1->get_result();
            while ($row1 = $result1->fetch_assoc()) {
                $uid = (string)$row1['iUserid'];
                $selected = in_array($uid, $assignedSelected, true) ? 'selected' : '';
                echo "<option value='{$uid}' {$selected}>" . htmlspecialchars($row1['sName']) . "</option>";
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
                                                <option value="<?php echo $row1['iUserid']; ?>" <?php if(isset($output) && (string)$output['sLead_owner'] === (string)$row1['iUserid']) echo "selected"; ?>><?php echo htmlspecialchars($row1['sName']); ?></option>
                                            <?php
                                                }
                                            ?>
                                            </select>
  
</div>
</div>
<br>
<input type="hidden" id="website" name="website" value="<?php if(isset($output)) echo $output['sWebsite']; ?>">
<input type="hidden" id="tags" name="tags" value="<?php if(isset($output)) echo $output['sTags']; ?>">

<div class="row">


        <div class="col-md-6">
    <label for="preferredcommunication" class="form-label">Preferred Communication</label>
    <select class="form-control" id="preferredcommunication" name="preferredcommunication"">
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
<div class="col-md-12">
    <label for="fileUpload2" class="form-label">File Upload 2</label>
    <input type="file" class="form-control" id="fileUpload2" name="fileUpload2">
    <div id="viewFile2" class="mt-2"></div>
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
              <input type="text" class="form-control" id="product" name="product">
            </div>
            <div class="col-md-6 mb-3">
              <label for="parent_id" class="form-label">Category</label>
              <input type="text" class="form-control" id="parent_id" name="parent_id" placeholder="e.g. KPT Pneumato">
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
    <div style="overflow-x: auto; width: 100%;">
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
<select name="product[]" class="form-select product-select" style="width:100%;">

    <?php if(isset($item['sProductname'])) {
        $product_id = $item['sProductname'];
        $stmt = $link->prepare("SELECT sProductName, sProductCode FROM tblinv_product WHERE iProductid = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        if ($res) {
            $optLabel = $res['sProductCode'] ? $res['sProductName'] . ' (' . $res['sProductCode'] . ')' : $res['sProductName'];
        ?>
        <option value="<?= $product_id ?>" selected><?= htmlspecialchars($optLabel, ENT_QUOTES) ?></option>
    <?php } } ?>
  </select>
</td>

                                               <td>
                                                   <input type="text" class="form-control" id="quantity"  name="quantity[]" value="<?php if(isset($item)) echo $item['sQuantity'];?>">
                                               </td> 
                                               <td>
                                               <input type="text" class="form-control" id="rate" name="rate[]" 
                                                value="<?php if(isset($item)) echo $item['sRate']; ?>" 
                                             >
                                               </td>
                                             
                                              
                                              
                                             </tr>
               
                                             <?php } 
                                     }else
                                     {

                                             
                                             ?>
               
                                            <tr> 
                                        
                                           <td>
    <select name="product[]" class="form-select product-select" style="width:100%;">
        <option value="">Select Product</option>
    </select>
    <!-- Options are populated on demand by Select2's AJAX search (fetch-inv-products.php) —
         the inventory catalog is large, so it isn't pre-rendered here. -->
</td>

                                            <td>
                                            <input type="text" class="form-control"   name="quantity[]"  value="">
                                            </td>
                                            <td>
                                            <input type="text" class="form-control" name="rate[]" >
                                            </td>
                                           

                                       </tr>

                                       <?php 
                                       
                                    }                 
                                             ?>
               
                                             </tbody>
               
        </table>
    </div>
               
                             <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#productModal">Click here to Add Product</a>


                                          <button type="button" onclick="addreadings();" class="btn btn-primary" style="float: right;">Add more</button>

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
// Options are populated on demand by Select2's AJAX search (fetch-inv-products.php) —
// the inventory catalog is large, so it isn't pre-rendered here.
var productOptions = `<option value="">Select</option>`;
</script>

<script>
function addreadings() {
    var table = document.getElementById("tablevalue");

    var html = '<tr>' +
        '<td>' +
        '<select name="product[]" class="form-select product-select" style="width:100%;">' +
        productOptions +
        '</select>' +
        '</td>' +
        '<td>' +
        '<input type="text" class="form-control" name="quantity[]">' +
        '</td>' +
        '<td>' +
        '<input type="text" class="form-control" name="rate[]">' +
        '</td>' +
        '</tr>';

    var newRow = table.insertRow();
    newRow.innerHTML = html;

    // Re-initialize Select2 for the new product-select
    $(newRow).find('.product-select').select2({
        placeholder: 'Select Product',
        tags: true,
        ajax: {
            url: 'fetch-inv-products.php',
            type: 'GET',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    searchTerm: params.term || ''
                };
            },
            processResults: function (data) {
                if (data && data.error) {
                    return { results: [] };
                }
                var results = Array.isArray(data) ? data : [];
                results = results.filter(function (item) {
                    return item && item.text && String(item.text).trim() !== '';
                });
                return { results: results };
            },
            cache: true
        }
    });
}
</script>



<script>
function toggleReferenceName() {
    var sources = document.getElementById("sources");
    var wrap = document.getElementById("referenceNameWrap");
    var selectedOption = sources.options[sources.selectedIndex];
    var isReference = selectedOption && selectedOption.getAttribute('data-reference') === '1';
    wrap.style.display = isReference ? '' : 'none';
    if (!isReference) {
        document.getElementById("referenceName").value = '';
    }
}

function addlead() {
    // Get form value
    // s

    var screatedby = document.getElementById("screatedby").value;


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
    var assignedto = getAssignedToValue();
    var leadowner = document.getElementById("leadowner").value;
    var preferredcommunication = document.getElementById("preferredcommunication").value;
    var tags = document.getElementById("tags").value;
    var contactperson = document.getElementById("contactperson").value;

    // Prepare FormData
    const formData = new FormData();

    formData.append("action", "addlead");
    formData.append("sCreated_by", screatedby);
    formData.append("sLead_name", leadname);
    formData.append("sEmail", email);
    formData.append("sPhone", phone);
    formData.append("sAlternate_phone", alterphone);
    formData.append("sLead_source", sources);
    formData.append("sReferenceName", document.getElementById("referenceName").value);
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
    formData.append("categories", JSON.stringify(products.map(function() { return ''; })));

    // Send POST request
    fetch('api.php', {
        method: 'POST',
        body: formData
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
        const messageEl = document.getElementById('message');
        if (responseData.status === "success") {
            messageEl.innerHTML = responseData.message;
            messageEl.classList.remove('error-message');
            messageEl.classList.add('add-message');
            setTimeout(() => window.location.href = 'list-lead-master.php', 500);
        } else {
            messageEl.innerHTML = responseData.message || 'Failed to save lead.';
            messageEl.classList.remove('add-message');
            messageEl.classList.add('error-message');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        const messageEl = document.getElementById('message');
        messageEl.innerHTML = error.message || "An error occurred while adding the lead.";
        messageEl.classList.remove('add-message');
        messageEl.classList.add('error-message');
    });
}


function updatelead() {
    // Get form values
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
    var assignedto = getAssignedToValue();
    var leadowner = document.getElementById("leadowner").value;
    var preferredcommunication = document.getElementById("preferredcommunication").value;
    var tags = document.getElementById("tags").value;
    var contactperson = document.getElementById("contactperson").value;

    // Prepare FormData
    const formData = new FormData();
    formData.append("action", "updatelead");
    formData.append("leadId", leadId);
    formData.append("sLead_name", leadname);
    formData.append("sEmail", email);
    formData.append("sPhone", phone);
    formData.append("sAlternate_phone", alterphone);
    formData.append("sLead_source", sources);
    formData.append("sReferenceName", document.getElementById("referenceName").value);
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
    formData.append("categories", JSON.stringify(products.map(function() { return ''; })));

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
        messageEl.innerHTML = "An error occurred while updating the lead.";
        messageEl.classList.remove('add-message');
        messageEl.classList.add('error-message');
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
            document.getElementById("referenceName").value = lead.sReferenceName || '';
            toggleReferenceName();
            statuslead.value = lead.sLead_status || '';
            priority.value = lead.sLead_priority || '';
            leadtype.value = lead.sLead_type || '';
            companyname.value = lead.sCompany_name || '';
            industrytype.value = lead.sIndustry_type || '';
            designation.value = lead.sDesignation || '';
            website.value = lead.sWebsite || '';
            location.value = lead.sLocation || '';
            address.value = lead.sAddress || '';
            setAssignedToValue(lead.sAssigned_to);
            leadowner.value = lead.sLead_owner || '';
            contactperson.value = lead.sContactperson || ''; // Correctly assigning value here
            preferredcommunication.value = lead.sPreferred_communication || '';
            tags.value = lead.sTags || '';

            // Handle file links (ensure the elements exist in HTML)
            const fileLinks = responseData.files || {};

            function getFullFilePath(filename) {
                // If it already contains 'uploads/', don’t add it again
                return filename.startsWith('uploads/') ? filename : 'uploads/' + filename;
            }

            // Check if file elements exist before inserting the file links
            if (document.getElementById("viewFile1") && fileLinks.file1) {
                document.getElementById("viewFile1").innerHTML = `<a href="${getFullFilePath(fileLinks.file1)}" target="_blank">View File 1</a>`;
            }
            if (document.getElementById("viewFile2") && fileLinks.file2) {
                document.getElementById("viewFile2").innerHTML = `<a href="${getFullFilePath(fileLinks.file2)}" target="_blank">View File 2</a>`;
            }
            if (document.getElementById("viewFile3") && fileLinks.file3) {
                document.getElementById("viewFile3").innerHTML = `<a href="${getFullFilePath(fileLinks.file3)}" target="_blank">View File 3</a>`;
            }

            // Process products and populate the table
            const products = responseData.products || [];
            const allProducts = responseData.allProducts || [];
            const tableBody = document.getElementById('tablevalue');
            tableBody.innerHTML = '';

            // Map product id -> display name for selected options
            const productNameById = {};
            allProducts.forEach(function(p) {
                productNameById[String(p.iProductid)] = p.sProductname;
            });

            if (products.length > 0) {
                products.forEach(function(product) {
                    const row = document.createElement('tr');
                    const productId = String(product.sProductname || '');
                    const productLabelRaw = productNameById[productId]
                        || product.product_name
                        || product.sProductLabel
                        || productId
                        || 'Select Product';
                    const productLabel = String(productLabelRaw)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;');

                    // Keep selected product as a real <option> so Select2 shows it correctly
                    let selectedProductOption = '<option value="">Select Product</option>';
                    if (productId) {
                        selectedProductOption += '<option value="' + productId + '" selected>' + productLabel + '</option>';
                    }

                    row.innerHTML =
                        '<td>' +
                            '<select name="product[]" class="form-select product-select" style="width:100%;">' +
                                selectedProductOption +
                            '</select>' +
                        '</td>' +
                        '<td><input type="text" class="form-control" name="quantity[]" value="' + (product.sQuantity || '') + '"></td>' +
                        '<td><input type="text" class="form-control" name="rate[]" value="' + (product.sRate || '') + '"></td>';

                    tableBody.appendChild(row);

                    const $productSelect = $(row).find('.product-select');
                    $productSelect.select2({
                        placeholder: 'Select Product',
                        tags: true,
                        width: '100%',
                        ajax: {
                            url: 'fetch-inv-products.php',
                            type: 'GET',
                            dataType: 'json',
                            delay: 250,
                            data: function(params) {
                                return { searchTerm: params.term || '' };
                            },
                            processResults: function(data) {
                                if (data && data.error) {
                                    return { results: [] };
                                }
                                var results = Array.isArray(data) ? data : [];
                                results = results.filter(function(item) {
                                    return item && item.text && String(item.text).trim() !== '';
                                });
                                return { results: results };
                            },
                            cache: true
                        }
                    });

                    if (productId) {
                        // Ensure Select2 displays the current product name
                        var option = new Option(productLabelRaw, productId, true, true);
                        $productSelect.append(option).trigger('change');
                    }
                });
            } else {
                tableBody.innerHTML = '<tr><td colspan="3">No products available</td></tr>';
            }
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
   document.getElementById('companyname').addEventListener('input', function () {
  const query = this.value.trim();
  const dropdown = document.getElementById('companyDropdown');

  if (query.length > 0) {
    fetch('api.php?query=' + encodeURIComponent(query))
      .then(response => response.json())
      .then(data => {
        dropdown.innerHTML = '';
        if (data.status === 'success' && data.data.length > 0) {
          data.data.forEach(company => {
            const li = document.createElement('li');
            li.classList.add('list-group-item', 'list-group-item-action');
            li.textContent = company.sCompanyname;
            li.onclick = function () {
              document.getElementById('companyname').value = company.sCompanyname;
              dropdown.style.display = 'none';

              // Auto-fill company details
              fetch('api.php?companyname=' + encodeURIComponent(company.sCompanyname))
                .then(res => res.json())
                .then(info => {
                  if (info.status === 'success') {
                    const c = info.data;
                    document.getElementById('email').value = c.sEmail || '';
                    document.getElementById('industrytype').value = c.sIndustrytype || '';
                    document.getElementById('tags').value = c.sTags || '';
                    document.getElementById('phone').value = c.sPhone || '';
document.getElementById('address').value = c.sBillingaddress || '';
document.getElementById('contactperson').value = c.sContactname || '';
                  }
                });
            };
            dropdown.appendChild(li);
          });
          dropdown.style.display = 'block';
        } else {
          dropdown.style.display = 'none';
        }
      })
      .catch(err => {
        console.error('Autocomplete fetch error:', err);
        dropdown.style.display = 'none';
      });
  } else {
    dropdown.style.display = 'none';
  }
});

// Hide dropdown on click outside
document.addEventListener('click', function (event) {
  const dropdown = document.getElementById('companyDropdown');
  if (!dropdown.contains(event.target) && event.target !== document.getElementById('companyname')) {
    dropdown.style.display = 'none';
  }
});


   function addproduct() {
    var productName = document.getElementById("product").value.trim();
    var category = document.getElementById("parent_id").value.trim();

    if (productName === "") {
        alert("Please enter the product name.");
        return;
    }

    const data = {
        action: "saveproduct",
        product_name: productName,
        category: category
    };

    fetch('inventory-api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(responseData => {
    var messageEl = document.getElementById('message');
    if (responseData.status === "success") {
        messageEl.innerHTML = '<div class="text-success">Product added — it is now searchable in the Product dropdown above.</div>';

        setTimeout(() => {
            const modalEl = document.getElementById('productModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) {
                modal.hide();
            }
            document.getElementById("product").value = '';
            document.getElementById("parent_id").value = '';
            setTimeout(() => {
                messageEl.innerHTML = '';
            }, 3000);
        }, 800);

    } else {
        messageEl.innerHTML = '<div class="text-danger">' + (responseData.message || 'Failed to add product.') + '</div>';
    }
})
.catch(() => {
    document.getElementById('message').innerHTML = '<div class="text-danger">Failed to add product.</div>';
});

   }


</script>
<script>
$(document).ready(function () {
 $('.product-select').select2({
  placeholder: 'Type to search product',
  tags: true,
  minimumInputLength: 0,
  ajax: {
    url: 'fetch-inv-products.php',
    type: 'GET',
    dataType: 'json',
    delay: 250,
    data: function (params) {
      return {
        searchTerm: params.term || ''
      };
    },
    processResults: function (data) {
      if (data && data.error) {
        return { results: [] };
      }
      var results = Array.isArray(data) ? data : [];
      results = results.filter(function (item) {
        return item && item.text && String(item.text).trim() !== '';
      });
      return { results: results };
    },
    cache: true
  }
});

});



function getCategory(selectElement) {
    var productId = selectElement.value;

    if (productId) {
        // Send an AJAX request to fetch category
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "fetch_category.php?product_id=" + productId, true);
        xhr.onload = function () {
            if (xhr.status == 200) {
                var categorySelect = selectElement.closest('tr').querySelector('.category-select');
                categorySelect.innerHTML = xhr.responseText;  // Populate category options
            }
        };
        xhr.send();
    } else {
        // Reset category dropdown if no product is selected
        selectElement.closest('tr').querySelector('.category-select').innerHTML = '<option value="">Select Category</option>';
    }
}

</script>
<script>
function getAssignedToValue() {
    var vals = $('#assignedto').val();
    if (!vals) return '';
    if (Array.isArray(vals)) return vals.filter(Boolean).join(',');
    return String(vals);
}

function setAssignedToValue(raw) {
    var ids = String(raw || '')
        .split(',')
        .map(function (v) { return String(v).trim(); })
        .filter(Boolean);
    $('#assignedto').val(ids).trigger('change');
}

$(document).ready(function() {
    $('#assignedto').select2({
        placeholder: "Select Assigned to",
        allowClear: true,
        multiple: true,
        width: '100%'
    });
});






</script>



<script src="assets/js/app.js"></script>



</html>