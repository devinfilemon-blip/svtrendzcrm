<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include 'layouts/config.php';
$msg="";


if(isset($_POST['customerId'])){
    $customerId = $_POST['customerId'] ;
  
}else{
    $customerId=0;
}

?>

<head>
    <title>Add Customer</title>
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
                            <h4 class="mb-sm-0 font-size-18">Add Customer</h4>
                            

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Customer</a></li>
                                    <li class="breadcrumb-item active">Add Customer</li>
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
                                <action="add-user.php" id="customerform" method="post" enctype="multipart/form-data">

                                <input type="hidden" id="customerId" name="customerId" value="<?php  echo $customerId; ?>" >

                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="companyname" class="form-label">Company Name</label>
                                        <input type="text" class="form-control" id="companyname" name="companyname" value="<?php if(isset($output)) echo $output['sCompanyname']; ?>" required>
                                    </div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="gstin" class="form-label">GSTIN</label>
                                        <input type="text" class="form-control" id="gstin" name="gstin" value="<?php if(isset($output)) echo $output['sGstin']; ?>">
                                    </div>
                                </div>
                                <br>
                                    <div class="row">
                                    <div class="col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php if(isset($output)) echo $output['sEmail']; ?>" required>
                                        </div>
                                        </div>
                                  
                                        <br>
                                   
                                        <div class="row">
                                    <div class="col-md-6">
                                      
                                            <label for="billing" class="form-label">Billing Address</label>
                                            <!-- Use <textarea> instead of <input type="text"> for multi-line text -->
                                            <textarea class="form-control" id="billing" name="billing" rows="3" required><?php if (isset($output)) echo $output['sBillingaddress']; ?></textarea>
                                    </div>
                                </div>

                                <br>

                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="shipping" class="form-label">Shipping Address</label>
                                        <textarea class="form-control" id="shipping" name="shipping" rows="3" required><?php if (isset($output)) echo $output['sShippingaddress']; ?></textarea>
                                    </div>
                                </div>

                                    <br>
                                    <div class="row">
                                    <div class="col-md-6">
                                        <label for="customertype" class="form-label">Customer Type</label>
                                        <select class="form-control" id="customertype" name="customertype" required>
                                        <option value="">Select Customer Type</option>
                                            <option value="individual" <?php if (isset($output) && $output['sCustomertype'] == 'individual') echo 'selected'; ?>>Individual</option>
                                            <option value="corporate" <?php if (isset($output) && $output['sCustomertype'] == 'corporate') echo 'selected'; ?>>Corporate</option>
                                            <option value="government" <?php if (isset($output) && $output['sCustomertype'] == 'government') echo 'selected'; ?>>Government</option>
                                            <option value="other" <?php if (isset($output) && $output['sCustomertype'] == 'other') echo 'selected'; ?>>Other</option>
                                        </select>
                                    </div>
                                    </div>
                                    <br>
                                    <div class="row">
                                    <div class="col-md-6">
                                            <label for="industry" class="form-label">Industry Type</label>
                                            <input type="text" class="form-control" id="industry" name="industry" value="<?php if(isset($output)) echo $output['sIndustrytype']; ?>" required>
                                        
                                        </div>
                                        </div>
                                    
                                        <br>
                             


                                        <div class="row">
                                        <div class="col-md-6">
                                    <label for="text" class="form-label">Account manager</label>
                                    <select class="form-control" id="manager" name="manager" required">
                                            

                                            <option value="">Select User</option>
                                            <?php
                                                $stmt1 = $link->prepare('select * from tbluser');
                                                $stmt1->execute();
                                                $result1 = $stmt1->get_result();
                                                while($row1 = $result1->fetch_assoc()){
                                            ?>               
                                                <option value="<?php echo $row1['iUserid']; ?>" <?php if(isset($output) && $output['iUserid'] == $row['iUserid']) echo "selected"; ?>><?php echo $row1['sName']; ?></option>
                                            <?php
                                                }
                                            ?>
                                            </select>
                                </div>
                                </div>
                                <br>
                                <div class="row">
                                <div class="col-md-6">
                                        <label for="sources" class="form-label">Lead Source</label>
                                        <select class="form-control" id="sources" name="sources" required">
                                            

                                            <option value="">Select Sources</option>
                                            <?php
                                                $stmt = $link->prepare('select * from tblsources');
                                                $stmt->execute();
                                                $result = $stmt->get_result();
                                                while($row = $result->fetch_assoc()){
                                            ?>               
                                                <option value="<?php echo $row['iSourceid']; ?>" <?php if(isset($output) && $output['iSourceid'] == $row['iSourceid']) echo "selected"; ?>><?php echo $row['sSources']; ?></option>
                                            <?php
                                                }
                                            ?>
                                            </select>
                                        </div>
                                    </div>
                                    <br>
                                    <div class="row">
                                    <div class="col-md-6">
                                    <label for="tags" class="form-label">Tags</label>
                                    <input type="text" class="form-control" id="tags" name="tags" value="<?php if(isset($output)) echo $output['sTags']; ?>" required>
                                        </div></div>
                                  
                                 
                                 <br>
                                 
                               
                                        <div class="row">
                                        <div class="col-md-6">
                                    <label for="statusname" class="form-label">Status</label>
                                    <select class="form-control" id="statusname" name="statusname" required>
                                        <option value="">Select Status</option>
                                            <option value="Active" <?php if (isset($output) && $output['sStatus'] == 'Active') echo 'selected'; ?>>Active</option>
                                            <option value="Inactive" <?php if (isset($output) && $output['sStatus'] == 'Inactive') echo 'selected'; ?>>Inactive</option>
                                            <option value="Blacklisted" <?php if (isset($output) && $output['sStatus'] == 'Blacklisted') echo 'selected'; ?>>Blacklisted</option>
                                         
                                        </select>
                                  
                                </div>
                                </div>
                                 
                                                    <br>

                                <div class="row">
                                <div class="col-md-6">
                                    <label for="notes" class="form-label">Notes</label>
                                    <input type="text" class="form-control" id="notes" name="notes" value="<?php if(isset($output)) echo $output['sNotes']; ?>" required>
                                  
                                </div>
                                </div>


                                 <div class="row">
                                <div class="col-md-6">
                                    <label for="Referred" class="form-label">Referred by</label>
                                    <input type="text" class="form-control" id="Referred" name="Referred" value="<?php if(isset($output)) echo $output['sReferred']; ?>" required>
                                  
                                </div>
                                </div>
                                
                                <br>

  
                                <div class="col-md-12">
                                         
                                         <table id="" class="table table-bordered table-striped">            
                                             <thead>
                                               <tr>
                                               <th> <center>Contact Name</center></th>
                                               <th> <center>Referred Contact Type</center></th>
                                               <th> <center>Email</center></th>
                                               <th> <center>Phone no</center></th>
                                               <th> <center>Department</center></th>
                                             
                                            
                                             </tr>    
                                             
                                             </thead> 
                                             <tbody id="tablevalue">
                                               <?php 
                                               
                                               if(isset($output_values) && $output_values !=[]) {
                                               foreach($output_values as $item) {
                                                   
                                                   
                                                   ?>
                                             <tr>
                                             <td>
                                                   <input type="text" class="form-control" id="contactname"  name="contactname[]" value="<?php if(isset($item)) echo $item['sContactname'];?>" required>
                                               </td> 
                                               <td>
                                             <select  id="referredcontact" name="referredcontact[]" class="form-select "  required>
                                                <option value="">Select</option>  
                                                                                                    
                                                <?php

                                                $stmt = $link->prepare('select * from tblcontacttype order by iContactid');
                                                // $stmt->bind_param('s',$inNo);
                                                $stmt->execute();
                                                $result = $stmt->get_result();
                                                while($row = $result->fetch_assoc()){
                                                    ?>
                                                    <option value="<?php  echo $row['iContactid']; ?>" <?php  if((isset($item) && $item['iContactid'] == $row['iContactid'])  ){ echo "selected"; }  ?> ><?php echo $row['sContact'];  ?></option>
                                             <?php        
                                                }
                                                ?>
                                                </select> 
                                               </td>
                                               <td>
                                                   <input type="text" class="form-control" id="contactemail"  name="contactemail[]" value="<?php if(isset($item)) echo $item['sEmail'];?>" required>
                                               </td> 
                                               <td>
                                               <input type="text" class="form-control" id="phone" name="phone[]" 
                                                value="<?php if(isset($item)) echo $item['sPhone']; ?>" 
                                                pattern="^\d{10}$" maxlength="10" 
                                                title="Phone number must be exactly 10 digits" 
                                                required>
                                               </td>
                                               <td>
                                                   <input type="text" class="form-control" id="department"  name="department[]" value="<?php if(isset($item)) echo $item['sDepartment'];?>"   required>
                                               </td>
                                              
                                              
                                             </tr>
               
                                             <?php } 
                                     }else
                                     {

                                             
                                             ?>
               
                                            <tr> 
                                            <td>
                                            <input type="text" class="form-control"   name="contactname[]" value="" required>
                                            </td>
                                            <td>
                                            <select id="referredcontact" name="referredcontact[]" class="form-select" required>
                                                            <option value="">Select</option>
                                                            <?php $stmt = $link->prepare("SELECT * FROM tblcontacttype ORDER BY iContactid"); $stmt->execute(); $result = $stmt->get_result(); while($row = $result->fetch_assoc()) { ?>
                                                            <option value="<?php echo $row['iContactid']; ?>"><?php echo $row['sContact']; ?></option> 
                                                            <?php } ?></select>
                                            </td>
                                            <td>
                                            <input type="text" class="form-control"   name="contactemail[]"  value="" required>
                                            </td>
                                            <td>
                                            <input type="text" class="form-control" name="phone[]" pattern="^\d{10}$" maxlength="10" title="Phone number must be exactly 10 digits" required>
                                            </td>
                                            <td>
                                            <input type="text" class="form-control" name="department[]" required>
                                            </td>

                                       </tr>

                                       <?php 
                                       
                                    }                 
                                             ?>
               
                                             </tbody>
               
                                           </table>
               
                                           
                                           <button type='button' onclick='addreadings();'  class="btn btn-primary" style="float: right;">Add</button>
                                           <br> 
               
               
               

                             </div>

                                    <div>
                                    <button type="button" class="btn btn-primary w-md" onclick="<?php if(isset($customerId)&&$customerId!=0) echo "updatecustomer();" ;  else echo "addcustomer();" ?>">Save</button>

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


function addreadings(){

var table=document.getElementById("tablevalue");

html="";

var html = '<tr>' +
'<td>' +
'<input type="text" class="form-control"   name="contactname[]" value="" required>' +
'</td>' +
'<td>' +
'<select name="referredcontact[]" class="form-select" required>' +
                '<option value="">Select</option>' +
                '<?php $stmt = $link->prepare("SELECT * FROM tblcontacttype ORDER BY iContactid"); $stmt->execute(); $result = $stmt->get_result(); while($row = $result->fetch_assoc()) { ?>' +
                '<option value="<?php echo $row['iContactid']; ?>"><?php echo $row['sContact']; ?></option>' +
                '<?php } ?></select>' +
'</td>' +
'<td>' +
'<input type="text" class="form-control"   name="contactemail[]"  value="" required>' +
'</td>' +
'<td>' +
'<input type="text" class="form-control" name="phone[]" pattern="^\d{10}$" maxlength="10" title="Phone number must be exactly 10 digits" required>' +
'</td>' +
'<td>' +
'<input type="text" class="form-control" name="department[]" required>' +
'</td>' +
'</tr>';

var newRow = table.insertRow();
newRow.innerHTML = html;
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


function addcustomer() {
    // Get the values from the customer form
    var companyname = document.getElementById("companyname").value;
    var gstin = document.getElementById("gstin").value;
    var email = document.getElementById("email").value;
    var billing = document.getElementById("billing").value;
    var shipping = document.getElementById("shipping").value;
    var customertype = document.getElementById("customertype").value;
    var industry = document.getElementById("industry").value;
    var manager = document.getElementById("manager").value;
    var sources = document.getElementById("sources").value;
    var tags = document.getElementById("tags").value;
    var statusname = document.getElementById("statusname").value;
    var notes = document.getElementById("notes").value;
    var Referred  = document.getElementById("Referred").value;

    // Arrays for contact details
    var contactNames = [];
    var referredContacts = [];
    var contactEmails = [];
    var phones = [];
    var departments = [];

    // Loop through each row in the table and collect the contact values
    var rows = document.querySelectorAll('#tablevalue tr');
    rows.forEach(function(row) {
        var contactname = row.querySelector('input[name="contactname[]"]').value;
        var referredcontact = row.querySelector('select[name="referredcontact[]"]').value;
        var contactemail = row.querySelector('input[name="contactemail[]"]').value;
        var phone = row.querySelector('input[name="phone[]"]').value;
        var department = row.querySelector('input[name="department[]"]').value;

        // Push values into the arrays
        contactNames.push(contactname);
        referredContacts.push(referredcontact);
        contactEmails.push(contactemail);
        phones.push(phone);
        departments.push(department);
    });

    // Validate if any required field is empty
    if (companyname == "" || email == "" || billing == "" || shipping == "" || customertype == "" || industry == "" || manager == "" || sources == "" || tags == "" || statusname == "" || notes == "") {
        alert("Please fill in all customer information.");
        return false;
    }

    // Validate if any contact details are empty
    for (let i = 0; i < contactNames.length; i++) {
        if (contactNames[i] == "" || referredContacts[i] == "" || contactEmails[i] == "" || phones[i] == "" || departments[i] == "") {
            alert("Please fill in all contact information.");
            return false;
        }

        // Validate phone number to ensure it's 10 digits
        if (!/^\d{10}$/.test(phones[i])) {
            alert("Please enter a valid 10-digit phone number.");
            return false;
        }
    }

    // Prepare data to send to API
    const data = {
        action: "addcustomer",
        companyname: companyname,
        gstin: gstin,
        email: email,
        billing: billing,
        shipping: shipping,
        customertype: customertype,
        industry: industry,
        manager: manager,
        sources: sources,
        tags: tags,
        statusname: statusname,
        notes: notes,
        contactNames: contactNames,
        referredContacts: referredContacts,
        contactEmails: contactEmails,
        phones: phones,
        Referred:Referred,
        departments: departments
    };
    //console.log(data);
    // Send the data via fetch to the API
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
                window.location.href = 'list-customer.php';  // Redirect to list of customers
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






function updatecustomer() {
    var customerId=  <?php if(isset($customerId)) echo $customerId; else echo 0?>;
    // Get the updated values from the customer form
    var companyname = document.getElementById("companyname").value;
    var gstin = document.getElementById("gstin").value;
    var email = document.getElementById("email").value;
    var billing = document.getElementById("billing").value;
    var shipping = document.getElementById("shipping").value;
    var customertype = document.getElementById("customertype").value;
    var industry = document.getElementById("industry").value;
    var manager = document.getElementById("manager").value;
    var sources = document.getElementById("sources").value;
    var tags = document.getElementById("tags").value;
    var statusname = document.getElementById("statusname").value;
    var notes = document.getElementById("notes").value;
    var Referred = document.getElementById("Referred").value;

    var contactNames = [];
    var referredContacts = [];
    var contactEmails = [];
    var phones = [];
    var departments = [];

    // Loop through each row in the table and collect the contact values
    var rows = document.querySelectorAll('#tablevalue tr');
    rows.forEach(function(row) {
        var contactname = row.querySelector('input[name="contactname[]"]').value;
        var referredcontact = row.querySelector('select[name="referredcontact[]"]').value;
        var contactemail = row.querySelector('input[name="contactemail[]"]').value;
        var phone = row.querySelector('input[name="phone[]"]').value;
        var department = row.querySelector('input[name="department[]"]').value;

        contactNames.push(contactname);
        referredContacts.push(referredcontact);
        contactEmails.push(contactemail);
        phones.push(phone);
        departments.push(department);
    });

    // Validate if any required field is empty
    if (companyname == "" || email == "" || billing == "" || shipping == "" || customertype == "" || industry == "" || manager == "" || sources == "" || tags == "" || statusname == "" || notes == "") {
        alert("Please fill in all customer information.");
        return false;
    }

    for (let i = 0; i < contactNames.length; i++) {
        if (contactNames[i] == "" || referredContacts[i] == "" || contactEmails[i] == "" || phones[i] == "" || departments[i] == "") {
            alert("Please fill in all contact information.");
            return false;
        }

        // Validate phone number to ensure it's 10 digits
        if (!/^\d{10}$/.test(phones[i])) {
            alert("Please enter a valid 10-digit phone number.");
            return false;
        }
    }

    // Prepare data to send to API
    const data = {
        action: "updatecustomer",
        customerId: customerId,  // Include the customer ID for updating
        companyname: companyname,
        gstin: gstin,
        email: email,
        billing: billing,
        shipping: shipping,
        customertype: customertype,
        industry: industry,
        manager: manager,
        sources: sources,
        tags: tags,
        statusname: statusname,
        notes: notes,
        contactNames: contactNames,
        referredContacts: referredContacts,
        contactEmails: contactEmails,
        phones: phones,
        Referred:Referred,
        departments: departments
    };

  

    // Send the data via fetch to the API
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
                window.location.href = 'list-customer.php';  // Redirect to list of customers
            }, 500);
        } else {
            document.getElementById('message').innerHTML = responseData.message;
            document.getElementById('message').classList.add('error-message');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('message').innerHTML = "An error occurred while updating the customer.";
        document.getElementById('message').classList.add('error-message');
    });
}





function getcustomerbyid() {
    // Ensure you have a valid customerId before sending the request
    var customerId = document.getElementById("customerId").value;  // For example, get it from an input field
   // console.log(customerId);

    if (customerId ==0) {

       // alert('Invalid customer ID.');
        return;
    }

    // Form fields to be populated
        var companyname = document.getElementById("companyname");
        var gstin = document.getElementById("gstin");
        var email = document.getElementById("email");
        var billing = document.getElementById("billing");
        var shipping = document.getElementById("shipping");
        var customertype = document.getElementById("customertype");
        var industry = document.getElementById("industry");
        var manager = document.getElementById("manager");
        var sources = document.getElementById("sources");
        var tags = document.getElementById("tags");
        var statusname = document.getElementById("statusname");
        var notes = document.getElementById("notes");
        var Referred = document.getElementById("Referred");
    // Arrays for contact details
    var contactNames = [];
    var referredContacts = [];
    var contactEmails = [];
    var phones = [];
    var departments = [];

    // Prepare data to send to the backend
    const data = {
        action: "getcustomerbyid", // Action to fetch the customer details
        customerId: customerId     // Pass the customer ID
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
        if (responseData.status === "success" && responseData.customer) {
            const customer = responseData.customer;

            console.log(responseData.customer);

            // Populate the form fields with the retrieved customer data
            companyname.value = customer.sCompanyname || '';
            gstin.value = customer.sGstin || '';
            email.value = customer.sEmail || '';
            billing.value = customer.sBillingaddress || '';
            shipping.value = customer.sShippingaddress || '';
            customertype.value = customer.sCustomertype || '';
            industry.value = customer.sIndustrytype || '';
            manager.value = customer.iUserid || '';
            sources.value = customer.iSourceid || '';
            tags.value = customer.sTags || '';
            statusname.value = customer.sStatus || '';
            notes.value = customer.sNotes || '';
            Referred.value = customer.sReferred || '';
            console.log(manager);
            const tableBody = document.getElementById('tablevalue');
            tableBody.innerHTML = ''; // Clear existing rows

            if (customer.contacts && customer.contacts.length > 0) {
            customer.contacts.forEach(contact => {
            const row = document.createElement('tr');

   
            const referredContactSelect = document.createElement('select');
            referredContactSelect.name = 'referredcontact[]';
            referredContactSelect.classList.add('form-select');
            referredContactSelect.required = true;

     
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'Select';
            referredContactSelect.appendChild(defaultOption);

      
            fetch('getContactTypes.php')  // Adjust the URL to your API or server endpoint
                .then(response => response.json())
                .then(contactTypes => {
                    contactTypes.forEach(contactType => {

                    console.log(contactType);
                    console.log(contactType.iContactid == contact.iContactid);
                    const option = document.createElement('option');
                    option.value = contactType.iContactid;
                    option.textContent = contactType.sContact;

                
                    if (contactType.iContactid == contact.iContactid) {
                        option.selected = true;
                    }

                    referredContactSelect.appendChild(option);
                });
            })
            .catch(error => console.error('Error fetching contact types:', error));

        
            row.innerHTML = `
                <td><input type="text" class="form-control" name="contactname[]" value="${contact.sContactname || ''}" required></td>
                <td></td> <!-- Empty cell for referredcontact, will be filled by JavaScript -->
                <td><input type="text" class="form-control" name="contactemail[]" value="${contact.sEmail || ''}" required></td>
                <td><input type="text" class="form-control" name="phone[]" value="${contact.sPhone || ''}" required></td>
                <td><input type="text" class="form-control" name="department[]" value="${contact.sDepartment || ''}" required></td>
            `;
            
      
            row.cells[1].appendChild(referredContactSelect);

        // Finally, append the row to the table
        tableBody.appendChild(row);
    });
} else {
    const noContactsMessage = document.createElement('tr');
    noContactsMessage.innerHTML = '<td colspan="5">No contacts available for this customer.</td>';
    tableBody.appendChild(noContactsMessage);
}
           
        } else {
            alert('Error fetching customer data: ' + (responseData.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while retrieving the customer data.');
    });
}


getcustomerbyid();

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




<script src="assets/js/app.js"></script>


