<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include 'layouts/config.php';
$msg="";
$readonly = true; 

if (isset($_GET['leadId'])) {
    $leadId = $_GET['leadId'];
} elseif (isset($_POST['leadId'])) {
    $leadId = $_POST['leadId'];
} else {
    $leadId = 0;
}

?>

<head>
    <title>Add Lead</title>
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

#replies-list {
    padding: 10px;
}

.reply-user-info {
    font-size: 12px; /* Set font size to 12px */
    color: grey; /* You can adjust the color as needed */
    margin-top: 4px; /* Optional: add some space below the user info */
}

.reply-item {
    
    justify-content: space-between; /* Space between info and buttons */
    align-items: flex-start; /* Align items to the top */
    padding: 10px;
    margin-bottom: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
}

   

.reply-item p {
    margin: 0;
    padding: 2px 0;
}

.delete-btn {
    background-color: red;
    color: white;
    border-radius: 5px;
 
}

.delete-btn:hover {
    background-color: darkred;
}

.reply-container{ 
    position: relative;
    padding: 12px 14px;
    border: 1px solid #e8e4f3;
    border-radius: 10px;
    margin-bottom: 12px;
    background: #fff;
}

.reply-container-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 8px;
}

.reply-delete-btn {
    flex-shrink: 0;
    width: 34px;
    height: 34px;
    border: none;
    border-radius: 8px;
    background: #fef2f2;
    color: #dc2626;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    cursor: pointer;
    transition: background 0.2s ease, color 0.2s ease;
}

.reply-delete-btn:hover {
    background: #dc2626;
    color: #fff;
}

.reply-status-container {
    font-size: 13px;
    margin-top: 8px;
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
                              

                                <input type="hidden" id="leadId" name="leadId" value="<?php  echo $leadId; ?>" >

                              <div class="border border-dark rounded p-3 mb-3">
  <div class="row mb-2">
    <div class="col-md-6">
      <label for="leadname" class="form-label">Lead Created By</label>
      <input type="text" class="form-control" id="createdby"
             value="<?php if(isset($output)) echo $output['sCreated_by']; ?>" readonly>
    </div>
    <div class="col-md-6 position-relative">
      <label for="companyname" class="form-label">Date</label>
      <input type="text" class="form-control" id="created"
             value="<?php if(isset($output)) echo $output['sCreated_date']; ?>" readonly>
      <ul id="companyDropdown" class="list-group" style="display:none;"></ul>
      <!-- Move the toggle button here so it's aligned without extra <br> -->
      <button
        class="btn btn-outline-primary btn-sm"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#moreDetails"
        aria-expanded="false"
        aria-controls="moreDetails"
        id="toggleBtn"
        style="position: absolute; top: 0; right: 0;"
      >
        <span id="toggleIcon">＋</span> More
      </button>
    </div>
  </div>


 
                                        <div class="collapse mt-3" id="moreDetails">
                                        <div class="row">

                                            <div class="col-md-6">
      <label for="leadname" class="form-label">Subject</label>
      <input type="text" class="form-control" id="leadname" name="leadname"
             value="<?php if(isset($output)) echo $output['sLead_name']; ?>" readonly>
    </div>

     <div class="col-md-6">
      <label for="companyname" class="form-label">Company Name</label>
      <input type="text" class="form-control" id="companyname" name="companyname"
             value="<?php if(isset($output)) echo $output['sCompany_name']; ?>" readonly>
              </div>

                                        <div class="col-md-6">
                                    <label for="industrytype" class="form-label">Industry Type</label>
                                    <input type="text" class="form-control" id="industrytype" name="industrytype" value="<?php if(isset($output)) echo $output['sIndustry_type']; ?>" readonly>
                                  
                                </div>

                                    <!--  -->
                                

                                   
                                    <div class="col-md-6">
                                        <label for="contactperson" class="form-label">Contact person Name</label>
                                        <input type="text" class="form-control" id="contactperson" name="contactperson" value="<?php if(isset($output)) echo $output['sContactperson']; ?>" readonly>
                                    </div>
                                    </div>
<br>

                                    <div class="row">
                                    <div class="col-md-6">
                                    <label for="designation" class="form-label">Designation</label>
                                    <input type="text" class="form-control" id="designation" name="designation" value="<?php if(isset($output)) echo $output['sDesignation']; ?>" readonly>
                                  
                                </div>

                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php if(isset($output)) echo $output['sEmail']; ?>" readonly>
                                        </div>
                             
                                
                                </div>
                                

                                
                                 
                                   
                           
<br>


<div class="row">
                            <div class="col-md-6">
                                      
                                      <label for="phone" class="form-label">Phone Number</label>
                                      <input type="text" class="form-control" id="phone" name="phone" value="<?php if(isset($output)) echo $output['sPhone']; ?>" readonly>
                              </div>

                              <div class="col-md-6">
                            <label for="alterphone" class="form-label">Alternate Phone Number</label>
                            <input type="text" class="form-control" id="alterphone" name="alterphone" value="<?php if(isset($output)) echo $output['sAlternate_phone']; ?>" readonly>
                        </div>
                            <!--  -->

                                    </div>
                                    <br>

                                    <div class="row">
                                    <div class="col-md-6">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control" id="address" name="address" readonly><?php if(isset($output)) echo $output['sAddress']; ?></textarea>
                                    </div>

                                    <div class="col-md-6">
                                    <label for="location" class="form-label">Location</label>
                                    <input type="text" class="form-control" id="location" name="location" value="<?php if(isset($output)) echo $output['sLocation']; ?>" readonly>
                                  
                                </div>

    </div>
    <br>
                                <div class="row">
                                <div class="col-md-6">
                                        <label for="sources" class="form-label">Lead Source</label>
                                        <select class="form-control" id="sources" name="sources" disabled>
                                             <option value="">Select Sources</option>
                                            <?php
                                                $stmt1 = $link->prepare('select * from tblsources');
                                                $stmt1->execute();
                                                $result1 = $stmt1->get_result();
                                                while($row1 = $result1->fetch_assoc()){
                                                    $isReferenceSource = stripos($row1['sSources'], 'reference') !== false;
                                            ?>
                                                <option value="<?php echo $row1['iSourceid']; ?>" <?php echo $isReferenceSource ? 'data-reference="1"' : ''; ?> <?php if(isset($output) && $output['sLead_source'] == $row1['iSourceid']) echo "selected"; ?>><?php echo $row1['sSources']; ?></option>
                                            <?php
                                                }
                                            ?>
                                            </select>
                                        </div>
                                    <div class="col-md-6" id="referenceNameWrap" style="display:none;">
                                        <label for="referenceName" class="form-label">Reference Person Name</label>
                                        <input type="text" class="form-control" id="referenceName" name="referenceName" disabled>
                                    </div>

                                
                                    <div class="col-md-6">
                                    <label for="statuslead" class="form-label">Lead Status</label>
                                    <select class="form-control" id="statuslead" name="statuslead" disabled>
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
                                            <select class="form-control" id="priority" name="priority" disabled>
                                            

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
                                    <label for="text" class="form-label">Description</label>
                                    <input type="text" class="form-control" id="leadtype" name="leadtype" value="<?php if(isset($output)) echo $output['sLead_type']; ?>" readonly>
                                </div>
                                </div>
                               
                               
                                       
<br>
                              
                               
                                <div class="row">
                                <div class="col-md-6">
                                    <label for="assignedto" class="form-label">Assigned to</label>
                                    <select class="form-control" id="assignedto" name="assignedto[]" multiple="multiple" disabled>
                                            <?php
                                                $assignedSelected = [];
                                                if (isset($output['sAssigned_to']) && $output['sAssigned_to'] !== '' && $output['sAssigned_to'] !== null) {
                                                    $assignedSelected = array_filter(array_map('trim', explode(',', (string)$output['sAssigned_to'])));
                                                }
                                                $stmt1 = $link->prepare('select * from tbluser');
                                                $stmt1->execute();
                                                $result1 = $stmt1->get_result();
                                                while($row1 = $result1->fetch_assoc()){
                                                    $uid = (string)$row1['iUserid'];
                                                    $selected = in_array($uid, $assignedSelected, true) ? 'selected' : '';
                                            ?>               
                                                <option value="<?php echo $uid; ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($row1['sName']); ?></option>
                                            <?php
                                                }
                                            ?>
                                            </select>
                                  
                                </div>
 
                        <div class="col-md-6">
                    <label for="leadowner" class="form-label">Lead Owner</label>
                    <select class="form-control" id="leadowner" name="leadowner" disabled>
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
                            <div class="row">
                               
                               
                               <div class="col-md-6">
                           <label for="website" class="form-label">Website</label>
                           <input type="text" class="form-control" id="website" name="website" value="<?php if(isset($output)) echo $output['sWebsite']; ?>" readonly>
                         </div>

                     <div class="col-md-6">
                   <label for="tags" class="form-label">Tags</label>
                   <input type="text" class="form-control" id="tags" name="tags" value="<?php if(isset($output)) echo $output['sTags']; ?>" readonly>
                   </div>
                       </div>
                   
<br>

<div class="row">


        <div class="col-md-6">
    <label for="preferredcommunication" class="form-label">Preferred Communication</label>
    <select class="form-control" id="preferredcommunication" name="preferredcommunication" disabled>
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

<!-- File Upload 1 -->
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





<br>



<div class="col-md-12">
                                         
                                         <table id="" class="table table-bordered table-striped">            
                                             <thead>
                                               <tr>
                                               <th> <center>Product Name</center></th>
                                               <th> <center>Category</center></th>
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
                                           <tr> 
    <td>
        <select id="product" name="product[]" class="form-select" readonly>
            <option value="">Select</option>
            <?php 
            $stmt = $link->prepare("SELECT * FROM tblproduct ORDER BY iProductid"); 
            $stmt->execute(); 
            $result = $stmt->get_result(); 
            while($row = $result->fetch_assoc()) { ?>
                <option value="<?php echo $row['iProductid']; ?>"><?php echo $row['sProductname']; ?></option> 
            <?php } ?>
        </select>
    </td>

    <td>
        <select name="category[]" class="form-select category-select" required style="width:100%;">
            <option value="">Select Category</option>
            <?php
            $stmt = $link->prepare("SELECT * FROM tblcategoryname ORDER BY sCategoryname");
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                echo "<option value='".$row['id']."'>".$row['sCategoryname']."</option>";
            }
            ?>
        </select>
    </td>

    <td>
        <input type="text" class="form-control" name="quantity[]" value="" readonly>
    </td>

    <td>
        <input type="text" class="form-control" name="rate[]" required readonly>
    </td>
</tr>


                                            
                                              
                                             </tr>
               
                                             <?php } 
                                     }else
                                     {

                                             
                                             ?>
               
                                        <tr> 
    <td>
        <select id="product" name="product[]" class="form-select" readonly>
            <option value="">Select</option>
            <?php 
            $stmt = $link->prepare("SELECT * FROM tblproduct ORDER BY iProductid"); 
            $stmt->execute(); 
            $result = $stmt->get_result(); 
            while($row = $result->fetch_assoc()) { ?>
                <option value="<?php echo $row['iProductid']; ?>"><?php echo $row['sProductname']; ?></option> 
            <?php } ?>
        </select>
    </td>

    <td>
        <select name="category[]" class="form-select category-select" required style="width:100%;">
            <option value="">Select Category</option>
            <?php
            $stmt = $link->prepare("SELECT * FROM tblcategoryname ORDER BY sCategoryname");
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                echo "<option value='".$row['id']."'>".$row['sCategoryname']."</option>";
            }
            ?>
        </select>
    </td>

    <td>
        <input type="text" class="form-control" name="quantity[]" value="" readonly>
    </td>

    <td>
        <input type="text" class="form-control" name="rate[]" required readonly>
    </td>
</tr>

                                       <?php 
                                       
                                    }                 
                                             ?>
               
                                             </tbody>
               
                                           </table>
               </div>
</div></div>

                                           <!-- <button type='button' onclick='addreadings();'  class="btn btn-primary" style="float: right;">Add more</button>
                                           <br>  -->
               <br>
               
                                           <br>
         <form id="replyForm" enctype="multipart/form-data">
  <div class="box1">
    <!-- Description -->
    <div class="col-md-12">
      <label>Description</label>
      <textarea class="form-control" name="description" id="description" required></textarea>
    </div>
    <br>

    <!-- File Uploads -->
    <div class="col-md-12">
      <label>File Upload 1</label>
      <input type="file" class="form-control" name="fileUpload[]" id="fileUpload1">
    </div>
    <br>
    <div class="col-md-12">
      <label>File Upload 2</label>
      <input type="file" class="form-control" name="fileUpload[]" id="fileUpload2">
    </div>
    <br>
    <div class="col-md-12">
      <label>File Upload 3</label>
      <input type="file" class="form-control" name="fileUpload[]" id="fileUpload3">
    </div>
    <br>

    <!-- Follow Up Date & Status -->
    <div class="row">
      <div class="col-md-6">
        <label>Follow Up Date</label>
        <input type="date" class="form-control" name="followupdate" id="followupdate">
      </div>
      <div class="col-md-6">
        <label>Status</label>
        <select class="form-control" name="statusleadss" id="statusleadss" required>
          <option value="">Select Status</option>
          <?php
          include 'layouts/config.php';
          $stmt1 = $link->prepare('SELECT * FROM tblstatus');
          $stmt1->execute();
          $result1 = $stmt1->get_result();
          while($row1 = $result1->fetch_assoc()){
              echo '<option value="' . $row1['iStatusid'] . '">' . $row1['sStatus'] . '</option>';
          }
          ?>
        </select>
      </div>
    </div>

    <!-- Hidden Lead ID -->
    <input type="hidden" name="leadId" id="leadId" value="<?php echo $leadId ?? ''; ?>">

    <br>
    <button type="button" class="btn btn-primary" onclick="addReplay()">Save</button>
   <div id="replies-list"></div>
  </div>
</form>

  <br>

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

<!-- Right Sidebar -->
<?php include 'layouts/right-sidebar.php'; ?>
<!-- Right-bar -->
<?php include 'layouts/vendor-scripts.php'; ?>

<!-- JAVASCRIPT -->
<script>
const crmProductOptions = <?php
    $productOptions = [];
    $stmtProducts = $link->prepare('SELECT iProductid, sProductname FROM tblproduct ORDER BY iProductid');
    if ($stmtProducts) {
        $stmtProducts->execute();
        $productResult = $stmtProducts->get_result();
        while ($productRow = $productResult->fetch_assoc()) {
            $productOptions[] = [
                'id' => $productRow['iProductid'],
                'name' => $productRow['sProductname'],
            ];
        }
    }
    echo json_encode($productOptions);
?>;

 function addReplay() {
  const form = document.getElementById("replyForm");
  const formData = new FormData(form);
  formData.append("action", "addreplay");

  fetch("add-replay-api.php", {
    method: "POST",
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    const msg = document.getElementById("message");
    msg.innerHTML = data.message;
    msg.className = data.status === "success" ? "text-success" : "text-danger";
    
    if (data.status === "success") {
      setTimeout(() => window.location.href = "myleads.php", 800);
    }
  })
  .catch(err => {
    console.error("Error:", err);
    document.getElementById("message").innerHTML = "An error occurred.";
  });
}




function fetchReplies() {
    var leadId = <?php echo (int)$leadId; ?>;
    if (!leadId) {
        return;
    }
    fetch('add-replay-api.php?action=fetchReplies&lead_id=' + leadId, {
        method: 'GET',
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.statusText);
        }
        return response.json();
    })
    .then(data => {
        console.log('API Response:', data);
        const repliesList = document.getElementById('replies-list');
        if (!repliesList) return;

        if (data.status === 'success' && Array.isArray(data.replies)) {
            repliesList.innerHTML = '';

            if (data.replies.length === 0) {
                repliesList.innerHTML = '<p class="text-muted mb-0">No replies yet.</p>';
                return;
            }

            data.replies.forEach(reply => {
                let replyContainer = document.createElement('div');
                replyContainer.classList.add('reply-container');

                // Header: user info + delete
                let headRow = document.createElement('div');
                headRow.classList.add('reply-container-head');

                let userInfoContainer = document.createElement('div');
                userInfoContainer.classList.add('reply-user-info-container');

                let userInfo = document.createElement('p');
                userInfo.classList.add('reply-user-info');
                userInfo.innerHTML = `<strong>${reply.sName || 'User'}</strong>&nbsp;&nbsp;&nbsp;
                                      <strong>${formatTimestamp(new Date(reply.sCreatedTimestamp))}</strong>`;
                userInfoContainer.appendChild(userInfo);
                headRow.appendChild(userInfoContainer);

                let deleteBtn = document.createElement('button');
                deleteBtn.type = 'button';
                deleteBtn.classList.add('reply-delete-btn');
                deleteBtn.title = 'Delete follow-up';
                deleteBtn.innerHTML = '<i class="bx bx-trash"></i>';
                deleteBtn.addEventListener('click', function () {
                    deleteReply(reply.rId, replyContainer);
                });
                headRow.appendChild(deleteBtn);

                replyContainer.appendChild(headRow);

                // Description
                let description = document.createElement('p');
                description.classList.add('reply-description');
                description.innerHTML = `<strong>Description:</strong> ${reply.sDescription}`;
                replyContainer.appendChild(description);

                // Follow-up Date
                let followupDate = document.createElement('p');
                followupDate.classList.add('reply-followup-date');
                followupDate.innerHTML = `<strong>Follow-up Date:</strong> ${reply.sFollowupdate}`;
                replyContainer.appendChild(followupDate);

                // Status
                let statusContainer = document.createElement('div');
                statusContainer.classList.add('reply-status-container');

                let status = document.createElement('p');
                status.classList.add('reply-status');
                status.innerHTML = `<strong>Status:</strong> ${reply.sStatus}`;
                statusContainer.appendChild(status);

                // Action Section (Links to Uploaded Files)
                let actionContainer = document.createElement('div');
                actionContainer.classList.add('reply-action');

                // Handle file uploads
             let fileUploads = [reply.sFileupload, reply.sFileupload2, reply.sFileupload3];


                fileUploads.forEach((filePath, index) => {
                    if (filePath) {
                        let fileLink = document.createElement('a');
                        fileLink.href = filePath;
                        fileLink.target = '_blank';
                        fileLink.textContent = `Download File ${index + 1}`;
                        actionContainer.appendChild(fileLink);
                        actionContainer.appendChild(document.createElement('br')); // Add a line break
                    }
                });

                // If no files are available, display a message
                if (!reply.sFileupload && !reply.sFileupload2 && !reply.sFileupload3) {
                    let noFileMessage = document.createElement('p');
                    noFileMessage.classList.add('no-file-message');
                    noFileMessage.textContent = 'No files available.';
                    actionContainer.appendChild(noFileMessage);
                }

                replyContainer.appendChild(actionContainer);
                replyContainer.appendChild(statusContainer);
                repliesList.appendChild(replyContainer);
            });
        } else {
            console.error('API response indicates failure:', data);
        }
    })
    .catch(error => {
        console.error('Error fetching replies:', error);
    });
}

// Function to format the timestamp in dd/mm/yyyy format and time
function formatTimestamp(date) {
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    const hours = date.getHours();
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${day}-${month}-${year} ${hours}:${minutes}`;
}

// Call the function to fetch and display the replies
fetchReplies();



function deleteReply(replyId, replyDiv) {
    // Confirm deletion
    if (confirm('Are you sure you want to delete this follow-up?')) {
        fetch('add-replay-api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=delete&replyId=${replyId}`,
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                replyDiv.remove();
                const repliesList = document.getElementById('replies-list');
                if (repliesList && !repliesList.querySelector('.reply-container')) {
                    repliesList.innerHTML = '<p class="text-muted mb-0">No replies yet.</p>';
                }
            } else {
                alert('Error deleting follow-up: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error deleting reply:', error);
            alert('An error occurred while deleting the reply.');
        });
    }
}


function viewFile(filePath) {
    let viewWindow = window.open(filePath, '_blank');
    if (!viewWindow) {
        alert('Could not open the file in a new window.');
    }
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
<script>


function addreadings(){
var table=document.getElementById("tablevalue");
if (!table) return;

var productOptionsHtml = '<option value="">Select</option>';
crmProductOptions.forEach(function(product) {
    productOptionsHtml += '<option value="' + product.id + '">' + product.name + '</option>';
});

var html = '<tr>' +
'<td><select name="product[]" class="form-select" required>' + productOptionsHtml + '</select></td>' +
'<td><input type="text" class="form-control" name="quantity[]" value="" required></td>' +
'<td><input type="text" class="form-control" name="rate[]" required></td>' +
'</tr>';

var newRow = table.insertRow();
newRow.innerHTML = html;
}



</script>
<script>
function formatDateOnly(datetimeStr) {
    if (!datetimeStr) return '';

    // Replace space with 'T' if needed to ensure proper parsing
    const fixedStr = datetimeStr.replace(' ', 'T');

    const date = new Date(fixedStr);
    if (!isNaN(date)) {
        const yyyy = date.getFullYear();
        const mm = String(date.getMonth() + 1).padStart(2, '0');
        const dd = String(date.getDate()).padStart(2, '0');
        return `${dd}-${mm}-${yyyy}`; // Format: DD-MM-YYYY
    }
    return '';
}


function addlead() {
    // Collect values from the form fields
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

    // Initialize arrays for products, quantities, and rates
    var products = [];
    var quantities = [];
    var rates = [];

    // Loop through the table rows to collect product, quantity, and rate data
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

    // Validate required fields
    if (companyname.trim() == "" || contactperson.trim() == "" || phone.trim() == "" || leadname.trim() == "") {
        alert("Please fill in all customer information.");
        return false;
    }

    // Prepare the data object for the API request
    const data = {
        action: "addlead",
        sLead_name: leadname,
        sEmail: email,
        sPhone: phone,
        sAlternate_phone: alterphone,
        sLead_source: sources,
        sLead_status: statuslead,
        sLead_priority: priority,
        sLead_type: leadtype,
        sCompany_name: companyname,
        sIndustry_type: industrytype,
        sDesignation: designation,
        sWebsite: website,
        sLocation: location,
        sAddress: address,
        sAssigned_to: assignedto,
        sLead_owner: leadowner,
        sPreferred_communication: preferredcommunication,
        sTags: tags,
        sContactperson: contactperson,  // Add contactperson field
        products: products,  // Product data
        quantities: quantities,  // Quantity data
        rates: rates  // Rate data
    };

    // Send data to the API using fetch
    fetch('api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(responseData => {
        if (responseData.status === "success") {
            document.getElementById('message').innerHTML = responseData.message;
            document.getElementById('message').classList.add('add-message');
            
            setTimeout(function() {
                window.location.href = 'list-lead-master.php';  // Redirect to list of customers
            }, 500);
        } else {
            document.getElementById('message').innerHTML = responseData.message;
            document.getElementById('message').classList.add('error-message');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('message').innerHTML = "An error occurred while adding the customer.";
        document.getElementById('message').classList.add('error-message');
    });
}


function updatelead() {
    var formData = new FormData();

    formData.append('action', 'updatelead');
    formData.append('leadId', document.getElementById("leadId").value);
    formData.append('sLead_name', document.getElementById("leadname").value);
    formData.append('sEmail', document.getElementById("email").value);
    formData.append('sPhone', document.getElementById("phone").value);
    formData.append('sAlternate_phone', document.getElementById("alterphone").value);
    formData.append('sLead_source', document.getElementById("sources").value);
    formData.append('sLead_status', document.getElementById("statuslead").value);
    formData.append('sLead_priority', document.getElementById("priority").value);
    formData.append('sLead_type', document.getElementById("leadtype").value);
    formData.append('sCompany_name', document.getElementById("companyname").value);
    formData.append('sIndustry_type', document.getElementById("industrytype").value);
    formData.append('sDesignation', document.getElementById("designation").value);
    formData.append('sWebsite', document.getElementById("website").value);
    formData.append('sLocation', document.getElementById("location").value);
    formData.append('sAddress', document.getElementById("address").value);
    formData.append('sAssigned_to', document.getElementById("assignedto").value);
    formData.append('sLead_owner', document.getElementById("leadowner").value);
    formData.append('sPreferred_communication', document.getElementById("preferredcommunication").value);
    formData.append('sTags', document.getElementById("tags").value);
    formData.append('sContactperson', document.getElementById("contactperson").value);

    // Append product arrays as JSON strings
    var products = [], quantities = [], rates = [];
    document.querySelectorAll('#tablevalue tr').forEach(row => {
        var product = row.querySelector('select[name="product[]"]');
        var quantity = row.querySelector('input[name="quantity[]"]');
        var rate = row.querySelector('input[name="rate[]"]');
        if(product && quantity && rate) {
            products.push(product.value);
            quantities.push(quantity.value);
            rates.push(rate.value);
        }
    });
    formData.append('products', JSON.stringify(products));
    formData.append('quantities', JSON.stringify(quantities));
    formData.append('rates', JSON.stringify(rates));

    // Append files
    var fileUpload1 = document.getElementById('fileUpload');
    if (fileUpload1 && fileUpload1.files.length > 0) {
        formData.append('fileUpload', fileUpload1.files[0]);
    }
    var fileUpload2 = document.getElementById('fileUpload2');
    if (fileUpload2 && fileUpload2.files.length > 0) {
        formData.append('fileUpload2', fileUpload2.files[0]);
    }
    var fileUpload3 = document.getElementById('fileUpload3');
    if (fileUpload3 && fileUpload3.files.length > 0) {
        formData.append('fileUpload3', fileUpload3.files[0]);
    }

    fetch('api.php', {
        method: 'POST',
        body: formData  // DO NOT set Content-Type; browser sets it with boundary
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
    var created = document.getElementById("created");
    var createdby = document.getElementById("createdby");

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
    .then(response => {
        if (!response.ok) {
            throw new Error('HTTP error ' + response.status);
        }
        return response.json();
    })
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
            var referenceNameField = document.getElementById("referenceName");
            var referenceNameWrap = document.getElementById("referenceNameWrap");
            var selectedSourceOption = sources.options[sources.selectedIndex];
            var isReferenceSource = selectedSourceOption && selectedSourceOption.getAttribute('data-reference') === '1';
            referenceNameField.value = lead.sReferenceName || '';
            referenceNameWrap.style.display = isReferenceSource ? '' : 'none';
            statuslead.value = lead.sLead_status || '';
            priority.value = lead.sLead_priority || '';
            leadtype.value = lead.sLead_type || '';
            companyname.value = lead.sCompany_name || '';
            industrytype.value = lead.sIndustry_type || '';
            designation.value = lead.sDesignation || '';
            website.value = lead.sWebsite || '';
            location.value = lead.sLocation || '';
            address.value = lead.sAddress || '';
            var assignedIds = String(lead.sAssigned_to || '')
                .split(',')
                .map(function (v) { return String(v).trim(); })
                .filter(Boolean);
            $('#assignedto').val(assignedIds).trigger('change');
            leadowner.value = lead.sLead_owner || '';
            contactperson.value = lead.sContactperson || ''; // Correctly assigning value here
            preferredcommunication.value = lead.sPreferred_communication || '';
            tags.value = lead.sTags || '';
            createdby.value = responseData.created_by_display || lead.sCreated_by || '-';
            created.value = formatDateOnly(lead.sCreated_date || '');

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
            const categories = responseData.categories || []; // Assuming categories are also returned by the API
            const tableBody = document.getElementById('tablevalue');
            tableBody.innerHTML = ''; // Clear any existing rows

            if (products.length > 0) {
                products.forEach(product => {
                    const row = document.createElement('tr');

                    // Generate category options
//              const categoryOptions = categories.map(category => {
//  const selected = String(category.iCategoryid) === String(product.category) ? 'selected' : '';
//     return `<option value="${category.id}" ${selected}>${category.sCategoryname}</option>`;
// }).join('');

const categoryOptions = categories.map(category => {
  const selected = String(category.id) === String(product.category) ? 'selected' : '';
  return `<option value="${category.id}" ${selected}>${category.sCategoryname}</option>`;
}).join('');



                    row.innerHTML = `
                        <td>
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
                            <select name="category[]" class="form-select" disabled>
                                <option value="">-</option>
                                ${categoryOptions}
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
            } else {
                tableBody.innerHTML = '<tr><td colspan="4">No products available</td></tr>';
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
</script>

<script>
const toggleBtn = document.getElementById('toggleBtn');
const toggleIcon = document.getElementById('toggleIcon');
const moreDetails = document.getElementById('moreDetails');

if (toggleBtn && toggleIcon && moreDetails) {
  moreDetails.addEventListener('show.bs.collapse', () => {
    toggleIcon.innerText = '−';
    toggleBtn.innerText = ' ';
    toggleBtn.prepend(toggleIcon);
  });

  moreDetails.addEventListener('hide.bs.collapse', () => {
    toggleIcon.innerText = '＋';
    toggleBtn.innerText = ' ';
    toggleBtn.prepend(toggleIcon);
  });
}
</script>


<script src="assets/js/app.js"></script>

</body>
</html>
