<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include 'layouts/config.php';
$msg="";


if(isset($_POST['leadId'])){
    $leadId = $_POST['leadId'] ;
  
}else{
    $leadId=0;
}


$quotationId = $_GET['id'] ?? 0; // ✅ added for edit mode

?>

<head>
    <title>Add Tax Invoice</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
  .select2-container { width: 100% !important; min-width: 160px; }
  .select2-container .select2-selection--single { height: 38px; padding: 4px 8px; border: 1px solid #ced4da; }

  #tablevalue td {
    vertical-align: top;
  }
  #tablevalue td.action-cell,
  table.table thead th.action-col {
    width: 90px;
    min-width: 90px;
    max-width: 90px;
    text-align: center;
    vertical-align: top;
    white-space: nowrap;
    padding-top: 12px;
  }
  #tablevalue td.action-cell .btn-remove-product {
    display: inline-block;
    white-space: nowrap;
  }
  table.table th,
  table.table td {
    vertical-align: top;
  }
  table.table td input.form-control,
  table.table td select.form-select {
    min-width: 70px;
  }
  table.table td:first-child select.form-select {
    min-width: 140px;
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
                            <h4 class="mb-sm-0 font-size-18">Add Tax Invoice</h4>


                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Tax Invoice</a></li>
                                    <li class="breadcrumb-item active">Add Tax Invoice</li>
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


                             
<!-- Quotation Header -->
<input type="hidden" id="leadId" value="<?= $_GET['leadId'] ?? '' ?>">

<!-- <button type="button" class="btn btn-primary" style="float: right;" 
        onclick="window.location.href='quotation-list.php?quotation_id=<?php echo $quotationId; ?>';">
    List Quotation
</button> -->

<br>
<div class="row">
    <div class="col-md-4">
        <label>Tax Invoice No:</label>
        <input type="text" id="quotation_no" name="quotation_no" class="form-control" required >
    </div>
<script>
// Auto-increment Quotation No (only for new)
document.addEventListener('DOMContentLoaded', function() {
    var quotationId = document.getElementById('quotationId').value;
    if (!quotationId || quotationId == 0) {
        fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'getNextQuotationNo' })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('quotation_no').value = data.next_quotation_no;
            } else {
                const d = new Date();
                const yy = String(d.getFullYear()).slice(-2);
                const mm = String(d.getMonth() + 1).padStart(2, '0');
                document.getElementById('quotation_no').value = 'ITPL/' + yy + '/' + mm + '/01';
            }
        })
        .catch(() => {
            const d = new Date();
            const yy = String(d.getFullYear()).slice(-2);
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            document.getElementById('quotation_no').value = 'ITPL/' + yy + '/' + mm + '/01';
        });
    }
});
</script>
    <div class="col-md-4">
        <label>Date:</label>
        <input type="date" id="quotation_date" name="quotation_date" class="form-control" required>
    </div>
    <div class="col-md-4">
        <label>Customer Name:</label>
        <input type="text" id="customer_name" name="customer_name" class="form-control" >
    </div>
</div>
<br>
<div class="row">
    <div class="col-md-6">
        <label>Address:</label>
        <textarea id="customer_address" name="customer_address" class="form-control" rows="2"></textarea>
    </div>
    <div class="col-md-6">
        <label>Phone Number:</label>
        <input type="text" id="customer_phone" name="customer_phone" class="form-control">
    </div>
</div>
<br>
<div class="row">
    <div class="col-md-3">
        <label>Customer GSTIN:</label>
        <input type="text" id="gstin" name="gstin" class="form-control" placeholder="e.g. 27AAZFD0652J1ZZ">
    </div>
    <div class="col-md-3">
        <label>GST % (CGST + SGST):</label>
        <input type="number" id="gst_percent" name="gst_percent" class="form-control" value="18" min="0" step="0.01">
    </div>
    <div class="col-md-3">
        <label>Validity:</label>
        <input type="text" id="validity" name="validity" class="form-control" placeholder="e.g. 10 days">
    </div>
    <div class="col-md-3">
        <label>Delivery:</label>
        <input type="text" id="delivery" name="delivery" class="form-control" placeholder="e.g. 15 working days">
    </div>
</div>
<br>
<div class="row">
    <div class="col-md-4">
        <label>Payment Terms:</label>
        <input type="text" id="payments" name="payments" class="form-control" placeholder="e.g. 30% Advance & remaining after installation">
    </div>
    <div class="col-md-4">
        <label>Transport:</label>
        <input type="text" id="transport" name="transport" class="form-control" placeholder="Transport name">
    </div>
    <div class="col-md-4">
        <label>Special Note:</label>
        <input type="text" id="special_note" name="special_note" class="form-control">
    </div>
</div>
<br>
<input type="hidden" id="kind_attn" name="kind_attn">
<input type="hidden" id="mode_transport" name="mode_transport">
<input type="hidden" id="pr_refno" name="pr_refno">
<input type="hidden" id="pr_date" name="pr_date">
<input type="hidden" id="transport_insurance" name="transport_insurance">




<br>
<div class="d-flex justify-content-between align-items-center mb-2">
    <strong>Products</strong>
    <button type="button" class="btn btn-primary btn-sm" onclick="addProductRow()">Add Product</button>
</div>
<div class="table-responsive">
<table class="table table-bordered align-middle mb-0">
    <thead>
        <tr>
            <th>Item Name</th>
            <th>HSN/SAC</th>
            <th>Quantity</th>
            <th>Unit</th>
            <th>Price/Unit</th>
            <th>Discount (%)</th>
            <th>Amount</th>
            <th>Amount After Discount</th>
            <th class="action-col">Action</th>
        </tr>
    </thead>
    <tbody id="tablevalue"></tbody>
    <tfoot>
        <tr>
            <td colspan="7" class="text-end"><strong>Total:</strong></td>
            <td><input type="number" id="total_amount" name="total_amount" class="form-control" ></td>
            <td class="action-cell"></td>
        </tr>
    </tfoot>
</table>
</div>


<button type="button" class="btn btn-primary" onclick="saveQuotation()">Save Tax Invoice</button>
<input type="hidden" id="quotationId" value="<?= $quotationId ?>">


<?php if ($quotationId > 0): ?>
    <a href="print-quotation.php?quotation_id=<?= $quotationId ?>" target="_blank" class="btn btn-secondary">
        Print Tax Invoice
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
// let quotationId = null;
function saveQuotation() {
    const quotationId     = document.getElementById("quotationId").value; 
    const quotation_no    = document.getElementById("quotation_no").value;
    const quotation_date  = document.getElementById("quotation_date").value;
    const customer_name   = document.getElementById("customer_name").value;
    const customer_address = document.getElementById("customer_address").value;
    const customer_phone  = document.getElementById("customer_phone").value;
    const total_amount    = document.getElementById("total_amount").value;
    const leadId          = document.getElementById("leadId").value || 0;

    const kind_attn       = document.getElementById("kind_attn").value;
    const mode_transport  = document.getElementById("mode_transport").value;
    const gstin           = document.getElementById("gstin").value;
    const pr_refno        = document.getElementById("pr_refno").value;
    const pr_date         = document.getElementById("pr_date").value;
    // const octroi          = document.getElementById("octroi").value;
    const delivery        = document.getElementById("delivery").value;
    const payments        = document.getElementById("payments").value;
    const special_note    = document.getElementById("special_note").value;
    const gst_percent     = document.getElementById("gst_percent").value;
    const transport       = document.getElementById("transport").value;
    const transport_insurance = document.getElementById("transport_insurance").value;
    const validity        = document.getElementById("validity").value;

    let products = [], descriptions = [], quantities = [], rates = [], discounts = [], amounts = [], after_discounts = [], hsns = [], units = [];
    document.querySelectorAll("#tablevalue tr").forEach(row => {
        products.push(row.querySelector("[name='product[]']").value);
        hsns.push(row.querySelector("[name='hsn[]']").value);
        units.push(row.querySelector("[name='unit[]']").value);
        descriptions.push('');
        quantities.push(row.querySelector("[name='quantity[]']").value);
        rates.push(row.querySelector("[name='rate[]']").value);
        discounts.push(row.querySelector("[name='discount[]']").value);
        amounts.push(row.querySelector("[name='amount[]']").value);
        after_discounts.push(row.querySelector("[name='after_discount[]']").value);
    });

    const payload = {
        action: quotationId && quotationId != 0 ? 'updateQuotation' : 'saveQuotation',
        quotation_id: quotationId,
        leadId,
        quotation_no,
        quotation_date,
        customer_name,
        customer_address,
        customer_phone,
        total_amount,
        kind_attn,
        mode_transport,
        gstin,
        pr_refno,
        pr_date,
        // octroi,
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
        after_discount: after_discounts,
        hsn: hsns,
        unit: units
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
            window.location.href = "quotation-list.php";
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(err => {
        console.error("Fetch error:", err);
        alert("An error occurred while saving the quotation.");
    });
}


let allProductsList = [];
let productRowIndex = 0;

function buildProductOptionsHtml(selectedId, selectedLabel) {
    let html = '<option value="">Select Product</option>';
    if (selectedId) {
        const label = selectedLabel || ('Product #' + selectedId);
        html += `<option value="${selectedId}" selected>${label}</option>`;
    }
    // Keep any already-known products as fallback options
    (allProductsList || []).forEach(prod => {
        if (String(prod.iProductid) === String(selectedId)) return;
        html += `<option value="${prod.iProductid}">${prod.sProductname}</option>`;
    });
    return html;
}

function initProductSelect($el) {
    if (!$el || !$el.length) return;
    if ($el.hasClass('select2-hidden-accessible')) {
        $el.select2('destroy');
    }
    $el.select2({
        placeholder: 'Search product…',
        allowClear: true,
        width: '100%',
        ajax: {
            url: 'fetch-inv-products.php',
            type: 'GET',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { searchTerm: params.term || '' };
            },
            processResults: function (data) {
                if (data && data.error) return { results: [] };
                var results = Array.isArray(data) ? data : [];
                return {
                    results: results.filter(function (item) {
                        return item && item.text && String(item.text).trim() !== '';
                    })
                };
            },
            cache: true
        }
    });
}

function addProductRow(item, skipInit) {
    const tbody = document.getElementById('tablevalue');
    const idx = productRowIndex++;
    const quantity = item ? (parseFloat(item.quantity) || 0) : 0;
    const rate = item ? (parseFloat(item.rate) || 0) : 0;
    const discount = item ? (parseFloat(item.discount) || 0) : 0;
    const amount = item
        ? (parseFloat(item.amount) || (quantity * rate))
        : 0;
    let afterDiscount = amount;
    if (item && item.after_discount !== undefined && item.after_discount !== null && item.after_discount !== '') {
        const storedAfter = parseFloat(item.after_discount);
        if (!isNaN(storedAfter)) afterDiscount = storedAfter;
    } else if (discount) {
        afterDiscount = amount - (amount * discount / 100);
    }
    const selectedId = item ? (item.product_id || item.sProductname || '') : '';
    const selectedLabel = item
        ? (item.sProductname || item.product_name || '')
        : '';
    // Prefer label from allProductsList if item has id only
    let label = selectedLabel;
    if (selectedId && !label) {
        const found = (allProductsList || []).find(p => String(p.iProductid) === String(selectedId));
        if (found) label = found.sProductname;
    }
    const hsn = item ? (item.sHsn || item.item_hsn || '') : '';
    const unit = item ? (item.sUnit || item.item_unit || '') : '';

    const row = document.createElement('tr');
    row.innerHTML = `
        <td>
            <select name="product[]" class="form-select product-select">
                ${buildProductOptionsHtml(selectedId, label)}
            </select>
        </td>
        <td><input type="text" class="form-control" id="hsn_${idx}" name="hsn[]" value="${hsn}"></td>
        <td><input type="number" class="form-control" id="qty_${idx}" name="quantity[]" value="${quantity}" step="0.01" oninput="updateAmount(${idx})"></td>
        <td><input type="text" class="form-control" id="unit_${idx}" name="unit[]" value="${unit}"></td>
        <td><input type="number" class="form-control" id="rate_${idx}" name="rate[]" value="${rate}" step="0.01" oninput="updateAmount(${idx})"></td>
        <td><input type="number" class="form-control" id="discount_${idx}" name="discount[]" value="${discount}" step="0.01" oninput="updateAmount(${idx})"></td>
        <td><input type="number" class="form-control" id="amount_${idx}" name="amount[]" value="${amount.toFixed(2)}" oninput="updateFromAmount(${idx})"></td>
        <td><input type="number" class="form-control" id="after_discount_${idx}" name="after_discount[]" value="${afterDiscount.toFixed(2)}" oninput="updateTotal()"></td>
        <td class="action-cell">
            <button type="button" class="btn btn-danger btn-sm btn-remove-product" onclick="removeProductRow(this)">Remove</button>
        </td>
    `;
    tbody.appendChild(row);
    const $productSelect = $(row).find('.product-select');
    initProductSelect($productSelect);
    $productSelect.on('select2:select', function(e) {
        const picked = e.params.data;
        if (picked && picked.hsn) document.getElementById(`hsn_${idx}`).value = picked.hsn;
        if (picked && picked.unit) document.getElementById(`unit_${idx}`).value = picked.unit;
    });
    updateTotal();
}

function removeProductRow(btn) {
    const row = btn.closest('tr');
    if (row) {
        const $sel = $(row).find('.product-select');
        if ($sel.hasClass('select2-hidden-accessible')) $sel.select2('destroy');
        row.remove();
    }
    updateTotal();
}

function getQuotationById() {
    const quotationId = document.getElementById("quotationId").value;
    if (!quotationId || quotationId == 0) return; // only if editing

    fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'getQuotationById', id: quotationId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            const q = data.quotation;

            // ✅ Fill header fields
            document.getElementById("quotation_no").value    = q.quotation_no;
            document.getElementById("quotation_date").value  = q.quotation_date;
            document.getElementById("customer_name").value   = q.customer_name;
            document.getElementById("customer_address").value = q.customer_address || '';
            document.getElementById("customer_phone").value  = q.customer_phone || '';
            document.getElementById("total_amount").value    = q.total_amount || 0;
            document.getElementById("kind_attn").value       = q.sKindattn || '';
            document.getElementById("mode_transport").value  = q.sModeoftransport || '';
            document.getElementById("gstin").value           = q.sGstin || '';
            document.getElementById("pr_refno").value        = q.sRefno || '';
            document.getElementById("pr_date").value         = q.sDate || '';
            document.getElementById("delivery").value        = q.delivery || '';
            document.getElementById("payments").value        = q.payment || '';
            document.getElementById("special_note").value    = q.specialnote || '';
            document.getElementById("gst_percent").value     = q.gst || '';
            document.getElementById("transport").value       = q.sTransport || '';
            document.getElementById("transport_insurance").value = q.sTransportInsurance || '';
            document.getElementById("validity").value        = q.validity || '';
            if (q.leadId) document.getElementById("leadId").value = q.leadId;

            allProductsList = data.allProducts || [];
            const tbody = document.getElementById('tablevalue');
            tbody.innerHTML = '';
            productRowIndex = 0;

            (data.items || []).forEach((item) => {
                addProductRow(item, true);
            });

            updateTotal();
        } else {
            alert("Error loading quotation: " + data.message);
        }
    })
    .catch(err => console.error("Error loading quotation:", err));
}


getQuotationById(); // ✅ auto-load if edit


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

function updateFromAmount(index) {
    const amount = parseFloat(document.getElementById(`amount_${index}`).value) || 0;
    const discount = parseFloat(document.getElementById(`discount_${index}`).value) || 0;
    const afterDiscount = amount - (amount * discount / 100);
    document.getElementById(`after_discount_${index}`).value = afterDiscount.toFixed(2);
    updateTotal();
}

function updateTotal() {
    let total = 0;
    document.querySelectorAll("[name='after_discount[]']").forEach(el => {
        total += parseFloat(el.value) || 0;
    });
    // If after_discount rows are empty/zero, sum amount[] instead
    if (total === 0) {
        document.querySelectorAll("[name='amount[]']").forEach(el => {
            total += parseFloat(el.value) || 0;
        });
    }
    document.getElementById("total_amount").value = total.toFixed(2);
}


    function getleadbyid() {
    var leadId = document.getElementById("leadId").value;
    if (!leadId || leadId == 0 || leadId == '') return;
    // Skip lead reload when editing an existing quotation
    const quotationId = document.getElementById("quotationId").value;
    if (quotationId && quotationId != 0) return;

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
            document.getElementById("customer_address").value = lead.sAddress || '';
            document.getElementById("customer_phone").value = lead.sPhone || '';
            // Set GSTIN from API response (from tblcustomer)
            if (responseData.gstin !== undefined) {
                document.getElementById("gstin").value = responseData.gstin;
            } else {
                document.getElementById("gstin").value = '';
            }

            allProductsList = responseData.allProducts || [];
            const tableBody = document.getElementById('tablevalue');
            tableBody.innerHTML = '';
            productRowIndex = 0;

            (responseData.products || []).forEach((product) => {
                addProductRow({
                    product_id: product.sProductname,
                    sProductname: product.product_name || '',
                    description: product.sDesc || '',
                    quantity: product.sQuantity,
                    rate: product.sRate,
                    discount: 0,
                    amount: (parseFloat(product.sQuantity) || 0) * (parseFloat(product.sRate) || 0),
                    after_discount: (parseFloat(product.sQuantity) || 0) * (parseFloat(product.sRate) || 0)
                }, true);
            });

            updateTotal();
        }
    })
    .catch(error => console.error('Error:', error));
}

getleadbyid();




</script>


<script src="assets/js/app.js"></script>
