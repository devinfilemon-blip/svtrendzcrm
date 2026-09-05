<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include 'layouts/config.php';
$msg="";


if(isset($_POST['leadId'])){
    $leadId = $_POST['leadId'] ;
  
}else{
    $leadId=0;
}


$salesOrderId = $_GET['id'] ?? 0; // ✅ added for edit mode

?>

<head>
    <title>Add Sales Order</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 
<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>




    <style>
        .add-message {
    color: green;
    font-weight: bold;  /* Optional */
}

.error-message {
    color: green;
    font-weight: bold;  /* Optional */
}
  .ck-editor__editable[role="textbox"] { min-height: 120px; }
  .ck-content { line-height: 1.45; }
  .ck-content p { margin: 0 0 8px; }
  .ck-content ul, .ck-content ol { margin-left: 1.2rem; }
  /* prevent table cell overflow */
  td .ck.ck-editor { width: 100%; }


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
                            <h4 class="mb-sm-0 font-size-18">Add Sales Order</h4>
                            

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Sales Order</a></li>
                                    <li class="breadcrumb-item active">Add Sales Order</li>
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
                               <form action="add-user.php" id="customerform" method="post" enctype="multipart/form-data">


                             
<!-- Sales Order Header -->
<input type="hidden" id="leadId" value="<?= $_GET['leadId'] ?? '' ?>">

<!-- <button type="button" class="btn btn-primary" style="float: right;" 
        onclick="window.location.href='sales-order-list.php?sales_order_id=<?php echo $salesOrderId; ?>';">
    List Sales Order
</button> -->

<br>
<div class="row">
    <div class="col-md-4">
        <label>Sales Order No:</label>
        <input type="text" id="sales_no" name="sales_no" class="form-control" required>
    </div>
    <div class="col-md-4">
        <label>Date:</label>
        <input type="date" id="sales_date" name="sales_date" class="form-control" required>
    </div>
    <div class="col-md-4">
        <label>Customer Name:</label>
        <input type="text" id="customer_name" name="customer_name" class="form-control" >
    </div>
</div>
<br>
<div class="row">
    <div class="col-md-4">
        <label>Kind Attn:</label>
        <input type="text" id="kind_attn" name="kind_attn" class="form-control">
    </div>
    <div class="col-md-4">
        <label>Mode of Transport:</label>
        <input type="text" id="mode_transport" name="mode_transport" class="form-control">
    </div>
    <div class="col-md-4">
        <label>GSTIN:</label>
        <input type="text" id="gstin" name="gstin" class="form-control">
    </div>
</div>
<br>

<div class="row">
    <div class="col-md-4">
        <label>Ref No:</label>
        <input type="text" id="ref_no" name="ref_no" class="form-control">
    </div>
    <div class="col-md-4">
        <label>Date:</label>
        <input type="date" id="ref_date" name="ref_date" class="form-control">
    </div>
     <div class="col-md-4">
        <label>Validity:</label>
        <input type="text" id="validity" name="validity" class="form-control">
    </div>
</div>
<br>

<div class="row">
    <div class="col-md-4">
        <label>Delivery:</label>
        <input type="text" id="delivery" name="delivery" class="form-control">
    </div>
    <div class="col-md-4">
        <label>Payments:</label>
        <input type="text" id="payments" name="payments" class="form-control">
    </div>
    <div class="col-md-4">
        <label>Special Note:</label>
        <input type="text" id="special_note" name="special_note" class="form-control">
    </div>
</div>
<br>

<div class="row">
    <div class="col-md-4">
        <label>GST (%)</label>
        <input type="number" step="0.01" id="gst_percent" name="gst_percent" class="form-control">
    </div>
    <div class="col-md-4">
        <label>Transport:</label>
        <input type="text" id="transport" name="transport" class="form-control">
    </div>
    <div class="col-md-4">
        <label>Transport Insurance:</label>
        <input type="text" id="transport_insurance" name="transport_insurance" class="form-control">
    </div>
</div>
<br>




<br>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Product Name</th>
            <th>Description</th>
            <th>Quantity</th>
            <th>Rate</th>
            <th>Discount (%)</th>
            <th>Amount</th>
            <th>Amount After Discount</th>
        </tr>
    </thead>
    <tbody id="tablevalue"></tbody>
    <tfoot>
        <tr>
            <td colspan="6" class="text-end"><strong>Total:</strong></td>
            <td><input type="number" id="total_amount" name="total_amount" class="form-control" readonly></td>
        </tr>
    </tfoot>
</table>


<button type="button" class="btn btn-primary" onclick="saveSalesOrder()">Save Sales Order</button>
<input type="hidden" id="salesOrderId" value="<?= $salesOrderId ?>">


<?php if ($salesOrderId > 0): ?>
    <a href="print-sales-order.php?sales_order_id=<?= $salesOrderId ?>" target="_blank" class="btn btn-secondary">
        Print Sales Order
    </a>
<?php endif; ?>
<div id="editor"></div>
<div id="outline-container"></div>




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
// let salesOrderId = null;
function saveSalesOrder() {
    const salesOrderId    = document.getElementById("salesOrderId").value; 
    const sales_no        = document.getElementById("sales_no").value;
    const sales_date      = document.getElementById("sales_date").value;
    const customer_name   = document.getElementById("customer_name").value;
    const total_amount    = document.getElementById("total_amount").value;
    const leadId          = document.getElementById("leadId").value || 0;

    const kind_attn       = document.getElementById("kind_attn").value;
    const mode_transport  = document.getElementById("mode_transport").value;
    const gstin           = document.getElementById("gstin").value;
    const ref_no          = document.getElementById("ref_no").value;
    const ref_date        = document.getElementById("ref_date").value;
    const delivery        = document.getElementById("delivery").value;
    const payments        = document.getElementById("payments").value;
    const special_note    = document.getElementById("special_note").value;
    const gst_percent     = document.getElementById("gst_percent").value;
    const transport       = document.getElementById("transport").value;
    const transport_insurance = document.getElementById("transport_insurance").value;
    const validity        = document.getElementById("validity").value;

    let products = [], descriptions = [], quantities = [], rates = [], discounts = [], amounts = [], after_discounts = [];
    document.querySelectorAll("#tablevalue tr").forEach(row => {
        products.push(row.querySelector("[name='product[]']").value);
    let desText = '';
const textarea = row.querySelector("[name='des[]']");
if(textarea && textarea.editorInstance) {
    desText = textarea.editorInstance.getData();
} else {
    desText = textarea ? textarea.value : '';
}
descriptions.push(desText);

        quantities.push(row.querySelector("[name='quantity[]']").value);
        rates.push(row.querySelector("[name='rate[]']").value);
        discounts.push(row.querySelector("[name='discount[]']").value);
        amounts.push(row.querySelector("[name='amount[]']").value);
        after_discounts.push(row.querySelector("[name='after_discount[]']").value);
    });

    const payload = {
        action: salesOrderId && salesOrderId != 0 ? 'updateSalesOrder' : 'saveSalesOrder',
        sales_order_id: salesOrderId,
        leadId,
        sales_no,
        sales_date,
        customer_name,
        total_amount,
        kind_attn,
        mode_transport,
        gstin,
        ref_no,
        ref_date,
        delivery,
        payments,
        special_note,
        gst_percent,
        transport,
        transport_insurance,
        validity,
        product: products,
        des: descriptions,
        quantity: quantities,
        rate: rates,
        discount: discounts,
        amount: amounts,
        after_discount: after_discounts
    };

    fetch("api.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            alert(data.message);
            window.location.href = "sales-order-list.php";
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(err => {
        console.error("Fetch error:", err);
        alert("An error occurred while saving the sales order.");
    });
}


function getSalesOrderById() {
    const salesOrderId = document.getElementById("salesOrderId").value;
    if (!salesOrderId || salesOrderId == 0) return; // only if editing

    fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'getSalesOrderById', id: salesOrderId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            const so = data.salesOrder;

            // ✅ Fill header fields
            document.getElementById("sales_no").value        = so.sales_no;
            document.getElementById("sales_date").value      = so.sales_date;
            document.getElementById("customer_name").value   = so.customer_name;
            document.getElementById("total_amount").value    = so.total_amount || 0;
            document.getElementById("kind_attn").value       = so.sKindattn || '';
            document.getElementById("mode_transport").value  = so.sModeoftransport || '';
            document.getElementById("gstin").value           = so.sGstin || '';
            document.getElementById("ref_no").value          = so.sRefno || '';
            document.getElementById("ref_date").value        = so.sDate || '';
            document.getElementById("delivery").value        = so.delivery || '';
            document.getElementById("payments").value        = so.payment || '';
            document.getElementById("special_note").value    = so.specialnote || '';
            document.getElementById("gst_percent").value     = so.gst || '';
            document.getElementById("transport").value       = so.sTransport || '';
            document.getElementById("transport_insurance").value = so.sTransportInsurance || '';
            document.getElementById("validity").value        = so.validity || '';

            // ✅ Fill product items
            const tbody = document.getElementById('tablevalue');
            tbody.innerHTML = '';

     data.items.forEach((item, idx) => {
    const quantity = parseFloat(item.quantity) || 0;
    const rate = parseFloat(item.rate) || 0;
    const discount = parseFloat(item.discount || 0); // only if you stored it
    const amount = parseFloat(item.amount) || (quantity * rate);
    const afterDiscount = amount - (amount * discount / 100);

    const row = document.createElement('tr');
    row.innerHTML = `
        <td>
            <select name="product[]" class="form-select">
                <option value="">Select</option>
                ${data.allProducts.map(prod => {
                    const selected = prod.iProductid == item.product_id ? 'selected' : '';
                    return `<option value="${prod.iProductid}" ${selected}>${prod.sProductname}</option>`;
                }).join('')}
            </select>
        </td>
      <td><textarea class="form-control description-editor" name="des[]">${item.description || ''}</textarea></td>

        <td><input type="number" class="form-control" id="qty_${idx}" name="quantity[]" value="${quantity}" step="0.01" oninput="updateAmount(${idx})"></td>
        <td><input type="number" class="form-control" id="rate_${idx}" name="rate[]" value="${rate}" step="0.01" oninput="updateAmount(${idx})"></td>
        <td><input type="number" class="form-control" id="discount_${idx}" name="discount[]" value="${discount}" step="0.01" oninput="updateAmount(${idx})"></td>
        <td><input type="number" class="form-control" id="amount_${idx}" name="amount[]" value="${amount.toFixed(2)}" readonly></td>
        <td><input type="number" class="form-control" id="after_discount_${idx}" name="after_discount[]" value="${afterDiscount.toFixed(2)}" readonly></td>
    `;
    tbody.appendChild(row);
});


            updateTotal(); // ✅ recalc total
            initEditors();
        } else {
            alert("Error loading sales order: " + data.message);
        }
    })
    .catch(err => console.error("Error loading sales order:", err));
}


getSalesOrderById(); // ✅ auto-load if edit


function updateAmount(index) {
    const qty = parseFloat(document.getElementById(`qty_${index}`).value) || 0;
    const rate = parseFloat(document.getElementById(`rate_${index}`).value) || 0;
    const discount = parseFloat(document.getElementById(`discount_${index}`).value) || 0;

    const amount = qty * rate;
    const afterDiscount = amount - (amount * discount / 100);

    document.getElementById(`amount_${index}`).value = amount.toFixed(2);
    document.getElementById(`after_discount_${index}`).value = afterDiscount.toFixed(2);

    updateTotal();
}


function updateTotal() {
    let total = 0;
    document.querySelectorAll("[name='after_discount[]']").forEach(el => {
        total += parseFloat(el.value) || 0;
    });
    document.getElementById("total_amount").value = total.toFixed(2);
}


    function getleadbyid() {
    var leadId = document.getElementById("leadId").value;
    if (!leadId || leadId == 0 || leadId == '') return;

    const data = { action: "getleadbyid", leadId: leadId };

    fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(responseData => {
        if (responseData.status === "success" && responseData.lead) {
            const lead = responseData.lead;

            // Fill header fields
            document.getElementById("customer_name").value = lead.sCompany_name || '';
            // document.getElementById("sales_date").value = lead.sLead_date || new Date().toISOString().split('T')[0];
   

            // Process products
            const products = responseData.products;
            const tableBody = document.getElementById('tablevalue');
            tableBody.innerHTML = '';

           products.forEach((product, idx) => {
    const desc = product.sDesc && product.sDesc.trim() !== '' ? product.sDesc : '';
    const quantity = parseFloat(product.sQuantity) || 0;
    const rate = parseFloat(product.sRate) || 0;
    const amount = quantity * rate;

   const row = document.createElement('tr');
row.innerHTML = `
    <td>
        <select name="product[]" class="form-select">
            <option value="">Select</option>
            ${responseData.allProducts.map(prod => {
                const selected = prod.iProductid == product.sProductname ? 'selected' : '';
                return `<option value="${prod.iProductid}" ${selected}>${prod.sProductname}</option>`;
            }).join('')}
        </select>
    </td>
    <td> <textarea class="description-editor form-control" name="des[]">${desc}</textarea></td>
    <td><input type="number" class="form-control" id="qty_${idx}" name="quantity[]" value="${quantity}" step="0.01" oninput="updateAmount(${idx})"></td>
    <td><input type="number" class="form-control" id="rate_${idx}" name="rate[]" value="${rate}" step="0.01" oninput="updateAmount(${idx})"></td>
    <td><input type="number" class="form-control" id="discount_${idx}" name="discount[]" value="0" step="0.01" oninput="updateAmount(${idx})"></td>
    <td><input type="number" class="form-control" id="amount_${idx}" name="amount[]" value="${amount.toFixed(2)}" readonly></td>
    <td><input type="number" class="form-control" id="after_discount_${idx}" name="after_discount[]" value="${amount.toFixed(2)}" readonly></td>
`;

    tableBody.appendChild(row);
});

// ✅ Calculate total after rows are added
updateTotal();
initEditors();
        }
    })
    .catch(error => console.error('Error:', error));
}

// Auto-update amount
function updateAmount(index) {
    const qty = parseFloat(document.getElementById(`qty_${index}`).value) || 0;
    const rate = parseFloat(document.getElementById(`rate_${index}`).value) || 0;
    const discount = parseFloat(document.getElementById(`discount_${index}`).value) || 0;

    const amount = qty * rate;
    const afterDiscount = amount - (amount * discount / 100);

    document.getElementById(`amount_${index}`).value = amount.toFixed(2);
    document.getElementById(`after_discount_${index}`).value = afterDiscount.toFixed(2);

    updateTotal();
}


getleadbyid();




</script>
<script>
    let editors = []; // global array of CKEditor instances

function initEditors() {
    // Destroy existing editors first (if any)
    editors.forEach(editor => editor.destroy().catch(() => {}));
    editors = [];

    document.querySelectorAll('.description-editor').forEach(el => {
        ClassicEditor
            .create(el, {
                toolbar: [
                    'bold', 'italic', 'underline', '|',
                    'bulletedList', 'numberedList', '|',
                    'link', 'blockQuote', 'insertTable', '|',
                    'undo', 'redo'
                ]
            })
            .then(editor => {
                editors.push(editor);
                el.editorInstance = editor; // optional, but keep instance on element
            })
            .catch(error => {
                console.error(error);
            });
    });
}



</script>




<script src="assets/js/app.js"></script>