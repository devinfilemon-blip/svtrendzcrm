<?php
include 'layouts/session.php';
include 'layouts/head-main.php';
include 'layouts/config.php';
include 'layouts/crm-access.php';
crmRequireAdmin($link);
?>
<head>
    <title>Inward Stock</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        .success-message { color: green; font-weight: bold; }
        .error-message { color: red; font-weight: bold; }
        .btn-primary { color: #fff; background-color: #005aa5; border-color: #005aa5; }
        .crm-panel-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px; margin-bottom: 20px; }
        .crm-panel-card-head h6 { font-weight: 700; margin-bottom: 2px; }
        .crm-panel-card-head p { color: #6b7280; font-size: 13px; margin-bottom: 16px; }
        .inv-stock-hint { font-size: 12px; color: #6c757d; margin-top: 4px; }
        .select2-container { width: 100% !important; }
        .select2-container .select2-selection--single { height: 38px; padding: 4px 8px; border: 1px solid #ced4da; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 28px; }
    </style>
</head>
<?php include 'layouts/body.php'; ?>
<div id="layout-wrapper">
<?php include 'layouts/menu.php'; ?>
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Inward Stock</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Inventory</a></li>
                                    <li class="breadcrumb-item active">Inward</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div><span id="message"></span></div>

                <div class="row">
                    <div class="col-lg-4">
                        <div class="crm-panel-card">
                            <div class="crm-panel-card-head">
                                <h6><i class="bx bx-download me-1"></i> <span id="formTitle">Add Inward Entry</span></h6>
                                <p>Record stock received into inventory</p>
                            </div>
                            <form id="inwardForm" onsubmit="return false;">
                                <input type="hidden" id="inward_id" value="">
                                <div class="mb-3">
                                    <label class="form-label">Product <span class="text-danger">*</span></label>
                                    <select class="form-select" id="product_id"></select>
                                    <div class="inv-stock-hint" id="stockHint"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Date</label>
                                    <input type="date" class="form-control" id="date" value="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="qty" min="1" step="1">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label">Rate</label>
                                            <input type="number" class="form-control" id="rate" min="0" step="0.01" value="0">
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Amount</label>
                                    <input type="text" class="form-control" id="amountDisplay" value="₹0" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Supplier</label>
                                    <input type="text" class="form-control" id="supplier" placeholder="Supplier / vendor name">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Invoice No.</label>
                                    <input type="text" class="form-control" id="invoice_no">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Remarks</label>
                                    <textarea class="form-control" id="remarks" rows="2"></textarea>
                                </div>
                                <button type="button" class="btn btn-primary w-100" id="saveBtn" onclick="saveInward()"><i class="bx bx-save"></i> Save Inward</button>
                                <button type="button" class="btn btn-secondary w-100 mt-2" id="cancelEditBtn" onclick="resetInwardForm()" style="display:none;">Cancel Edit</button>
                            </form>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="crm-panel-card">
                            <div class="crm-panel-card-head">
                                <h6><i class="bx bx-list-ul me-1"></i> Inward Entries</h6>
                                <p>All stock received, most recent first</p>
                            </div>
                            <div class="table-responsive">
                                <table id="datatable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Product</th>
                                            <th>Qty</th>
                                            <th>Rate</th>
                                            <th>Amount</th>
                                            <th>Supplier</th>
                                            <th>Invoice No.</th>
                                            <th>Edit</th>
                                            <th>Delete</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include 'layouts/footer.php'; ?>
    </div>
</div>
<?php include 'layouts/right-sidebar.php'; ?>
<?php include 'layouts/vendor-scripts.php'; ?>
<script src="assets/js/app.js"></script>
<script>
function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    return String(text)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

function fmtMoney(n) {
    n = parseFloat(n) || 0;
    return '₹' + n.toLocaleString('en-IN', { maximumFractionDigits: 2 });
}

var productCache = [];

function loadProductOptions(selectedId) {
    return fetch('inventory-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'listactiveproducts' })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        productCache = res.data || [];
        var sel = document.getElementById('product_id');
        var opts = '<option value="">Select Product</option>';
        productCache.forEach(function(p) {
            var label = escapeHtml(p.sProductName) + (p.sProductCode ? ' (' + escapeHtml(p.sProductCode) + ')' : '');
            opts += '<option value="' + p.iProductid + '">' + label + '</option>';
        });
        sel.innerHTML = opts;
        if (selectedId) sel.value = selectedId;
        if ($(sel).hasClass('select2-hidden-accessible')) $(sel).select2('destroy');
        $(sel).select2({ placeholder: 'Search product…', allowClear: true, width: '100%' });
        updateStockHint();
    });
}

function updateStockHint() {
    var id = document.getElementById('product_id').value;
    var hint = document.getElementById('stockHint');
    var p = productCache.find(function(x) { return String(x.iProductid) === String(id); });
    if (!p) { hint.textContent = ''; return; }
    hint.textContent = 'Current stock: ' + (parseInt(p.iCurrentStock, 10) || 0) + ' ' + (p.sUnit || '');
    if (!document.getElementById('rate').dataset.touched) {
        document.getElementById('rate').value = p.fPurchaseRate || 0;
        recalcAmount();
    }
}

function recalcAmount() {
    var qty = parseFloat(document.getElementById('qty').value) || 0;
    var rate = parseFloat(document.getElementById('rate').value) || 0;
    document.getElementById('amountDisplay').value = fmtMoney(qty * rate);
}

document.getElementById('product_id').addEventListener('change', updateStockHint);
document.getElementById('qty').addEventListener('input', recalcAmount);
document.getElementById('rate').addEventListener('input', function() {
    this.dataset.touched = '1';
    recalcAmount();
});

function resetInwardForm() {
    document.getElementById('inwardForm').reset();
    document.getElementById('inward_id').value = '';
    document.getElementById('date').value = '<?php echo date('Y-m-d'); ?>';
    document.getElementById('rate').value = 0;
    document.getElementById('rate').dataset.touched = '';
    document.getElementById('amountDisplay').value = '₹0';
    document.getElementById('formTitle').textContent = 'Add Inward Entry';
    document.getElementById('saveBtn').textContent = ' Save Inward';
    document.getElementById('cancelEditBtn').style.display = 'none';
    updateStockHint();
}

function saveInward() {
    var productId = document.getElementById('product_id').value;
    var date = document.getElementById('date').value;
    var qty = document.getElementById('qty').value;
    if (!productId) { alert('Please select a product.'); return; }
    if (!date) { alert('Please choose a date.'); return; }
    if (!qty || parseFloat(qty) <= 0) { alert('Please enter a valid quantity.'); return; }

    var id = document.getElementById('inward_id').value;
    var data = {
        action: id ? 'updateinward' : 'saveinward',
        id: id,
        product_id: productId,
        date: date,
        qty: qty,
        rate: document.getElementById('rate').value,
        supplier: document.getElementById('supplier').value.trim(),
        invoice_no: document.getElementById('invoice_no').value.trim(),
        remarks: document.getElementById('remarks').value.trim()
    };

    fetch('inventory-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        var el = document.getElementById('message');
        el.innerHTML = res.message || '';
        el.className = res.status === 'success' ? 'success-message' : 'error-message';
        if (res.status === 'success') {
            resetInwardForm();
            loadProductOptions();
            loadInward();
        }
    })
    .catch(function() {
        document.getElementById('message').innerHTML = 'Save failed.';
        document.getElementById('message').className = 'error-message';
    });
}

function editInward(id) {
    fetch('inventory-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'getinwardbyid', id: id })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.status !== 'success') { alert(res.message || 'Unable to load.'); return; }
        var d = res.data;
        document.getElementById('inward_id').value = d.iInwardid;
        document.getElementById('date').value = d.dDate;
        document.getElementById('qty').value = d.iQty;
        document.getElementById('rate').value = d.fRate;
        document.getElementById('rate').dataset.touched = '1';
        document.getElementById('supplier').value = d.sSupplier || '';
        document.getElementById('invoice_no').value = d.sInvoiceNo || '';
        document.getElementById('remarks').value = d.sRemarks || '';
        loadProductOptions(d.iProductid).then(function() { recalcAmount(); });
        document.getElementById('formTitle').textContent = 'Edit Inward Entry';
        document.getElementById('saveBtn').textContent = ' Update Inward';
        document.getElementById('cancelEditBtn').style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}

function deleteInward(id) {
    if (!id || !confirm('Delete this inward entry?')) return;
    fetch('inventory-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'deleteinward', id: id })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        var el = document.getElementById('message');
        el.innerHTML = res.message || '';
        el.className = res.status === 'success' ? 'success-message' : 'error-message';
        loadProductOptions();
        loadInward();
    });
}

function loadInward() {
    $.ajax({
        url: 'inventory-api.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ action: 'listinward' }),
        success: function(response) {
            var rows = '';
            if (response.status === 'success') {
                var list = response.data || [];
                if (!list.length) {
                    rows = '<tr><td colspan="9" class="text-center">No inward entries found</td></tr>';
                } else {
                    list.forEach(function(r) {
                        var name = escapeHtml(r.sProductName || '') + (r.sProductCode ? ' (' + escapeHtml(r.sProductCode) + ')' : '');
                        rows += '<tr>';
                        rows += '<td>' + escapeHtml(r.dDate || '') + '</td>';
                        rows += '<td>' + name + '</td>';
                        rows += '<td>' + r.iQty + ' ' + escapeHtml(r.sUnit || '') + '</td>';
                        rows += '<td>' + fmtMoney(r.fRate) + '</td>';
                        rows += '<td>' + fmtMoney(r.fAmount) + '</td>';
                        rows += '<td>' + escapeHtml(r.sSupplier || '-') + '</td>';
                        rows += '<td>' + escapeHtml(r.sInvoiceNo || '-') + '</td>';
                        rows += '<td><button class="btn btn-success btn-sm" onclick="editInward(' + r.iInwardid + ')">Edit</button></td>';
                        rows += '<td><button class="btn btn-danger btn-sm" onclick="deleteInward(' + r.iInwardid + ')">Delete</button></td>';
                        rows += '</tr>';
                    });
                }
            } else {
                rows = '<tr><td colspan="9" class="text-center">No inward entries found</td></tr>';
            }
            if ($.fn.DataTable.isDataTable('#datatable')) {
                $('#datatable').DataTable().destroy();
            }
            $('#datatable tbody').html(rows);
            if (response.status === 'success' && (response.data || []).length) {
                $('#datatable').DataTable({ order: [] });
            }
        },
        error: function() {
            alert('Failed to load inward entries.');
        }
    });
}

$(document).ready(function() {
    loadProductOptions();
    loadInward();
});
</script>
</body>
</html>
