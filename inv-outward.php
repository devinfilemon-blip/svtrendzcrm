<?php
include 'layouts/session.php';
include 'layouts/head-main.php';
include 'layouts/config.php';
include 'layouts/crm-access.php';
crmRequireAdmin($link);
?>
<head>
    <title>Outward Stock</title>
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
        .inv-stock-hint.low { color: #dc2626; font-weight: 600; }
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
                            <h4 class="mb-sm-0 font-size-18">Outward Stock</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Inventory</a></li>
                                    <li class="breadcrumb-item active">Outward</li>
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
                                <h6><i class="bx bx-upload me-1"></i> <span id="formTitle">Add Outward Entry</span></h6>
                                <p>Record stock issued out of inventory</p>
                            </div>
                            <form id="outwardForm" onsubmit="return false;">
                                <input type="hidden" id="outward_id" value="">
                                <div class="mb-3">
                                    <label class="form-label">Product <span class="text-danger">*</span></label>
                                    <select class="form-select" id="product_id"></select>
                                    <div class="inv-stock-hint" id="stockHint"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Date</label>
                                    <input type="date" class="form-control" id="date" value="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="qty" min="1" step="1">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Purpose</label>
                                    <select class="form-select" id="purpose">
                                        <option value="Sale">Sale</option>
                                        <option value="Site Use">Site Use</option>
                                        <option value="Damage/Loss">Damage / Loss</option>
                                        <option value="Return to Supplier">Return to Supplier</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Won Project (optional)</label>
                                    <select class="form-select" id="lead_id">
                                        <option value="">— None —</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Issued To</label>
                                    <input type="text" class="form-control" id="issued_to" placeholder="Customer / site / person">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Remarks</label>
                                    <textarea class="form-control" id="remarks" rows="2"></textarea>
                                </div>
                                <button type="button" class="btn btn-primary w-100" id="saveBtn" onclick="saveOutward()"><i class="bx bx-save"></i> Save Outward</button>
                                <button type="button" class="btn btn-secondary w-100 mt-2" id="cancelEditBtn" onclick="resetOutwardForm()" style="display:none;">Cancel Edit</button>
                            </form>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="crm-panel-card">
                            <div class="crm-panel-card-head">
                                <h6><i class="bx bx-list-ul me-1"></i> Outward Entries</h6>
                                <p>All stock issued, most recent first</p>
                            </div>
                            <div class="table-responsive">
                                <table id="datatable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Product</th>
                                            <th>Qty</th>
                                            <th>Purpose</th>
                                            <th>Project</th>
                                            <th>Issued To</th>
                                            <th>Remarks</th>
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
    if (!p) { hint.textContent = ''; hint.classList.remove('low'); return; }
    var stock = parseInt(p.iCurrentStock, 10) || 0;
    hint.textContent = 'Available stock: ' + stock + ' ' + (p.sUnit || '');
    hint.classList.toggle('low', stock <= 0);
}

document.getElementById('product_id').addEventListener('change', updateStockHint);

function loadWonProjects(selectedId) {
    return fetch('inventory-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'listwonprojects' })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        var sel = document.getElementById('lead_id');
        var opts = '<option value="">— None —</option>';
        (res.data || []).forEach(function(p) {
            opts += '<option value="' + p.iLead_id + '">' + escapeHtml(p.sCompany_name || '') + '</option>';
        });
        sel.innerHTML = opts;
        if (selectedId) sel.value = selectedId;
    });
}

function resetOutwardForm() {
    document.getElementById('outwardForm').reset();
    document.getElementById('outward_id').value = '';
    document.getElementById('date').value = '<?php echo date('Y-m-d'); ?>';
    document.getElementById('lead_id').value = '';
    document.getElementById('formTitle').textContent = 'Add Outward Entry';
    document.getElementById('saveBtn').textContent = ' Save Outward';
    document.getElementById('cancelEditBtn').style.display = 'none';
    updateStockHint();
}

function saveOutward() {
    var productId = document.getElementById('product_id').value;
    var date = document.getElementById('date').value;
    var qty = document.getElementById('qty').value;
    if (!productId) { alert('Please select a product.'); return; }
    if (!date) { alert('Please choose a date.'); return; }
    if (!qty || parseFloat(qty) <= 0) { alert('Please enter a valid quantity.'); return; }

    var p = productCache.find(function(x) { return String(x.iProductid) === String(productId); });
    var id = document.getElementById('outward_id').value;
    if (p && !id) {
        var stock = parseInt(p.iCurrentStock, 10) || 0;
        if (parseFloat(qty) > stock && !confirm('Quantity (' + qty + ') exceeds available stock (' + stock + '). Save anyway?')) {
            return;
        }
    }

    var data = {
        action: id ? 'updateoutward' : 'saveoutward',
        id: id,
        product_id: productId,
        date: date,
        qty: qty,
        lead_id: document.getElementById('lead_id').value,
        purpose: document.getElementById('purpose').value,
        issued_to: document.getElementById('issued_to').value.trim(),
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
            resetOutwardForm();
            loadProductOptions();
            loadOutward();
        }
    })
    .catch(function() {
        document.getElementById('message').innerHTML = 'Save failed.';
        document.getElementById('message').className = 'error-message';
    });
}

function editOutward(id) {
    fetch('inventory-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'getoutwardbyid', id: id })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.status !== 'success') { alert(res.message || 'Unable to load.'); return; }
        var d = res.data;
        document.getElementById('outward_id').value = d.iOutwardid;
        document.getElementById('date').value = d.dDate;
        document.getElementById('qty').value = d.iQty;
        document.getElementById('purpose').value = d.sPurpose || 'Sale';
        document.getElementById('issued_to').value = d.sIssuedTo || '';
        document.getElementById('remarks').value = d.sRemarks || '';
        loadProductOptions(d.iProductid);
        loadWonProjects(d.iLeadid);
        document.getElementById('formTitle').textContent = 'Edit Outward Entry';
        document.getElementById('saveBtn').textContent = ' Update Outward';
        document.getElementById('cancelEditBtn').style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}

function deleteOutward(id) {
    if (!id || !confirm('Delete this outward entry?')) return;
    fetch('inventory-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'deleteoutward', id: id })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        var el = document.getElementById('message');
        el.innerHTML = res.message || '';
        el.className = res.status === 'success' ? 'success-message' : 'error-message';
        loadProductOptions();
        loadOutward();
    });
}

function loadOutward() {
    $.ajax({
        url: 'inventory-api.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ action: 'listoutward' }),
        success: function(response) {
            var rows = '';
            if (response.status === 'success') {
                var list = response.data || [];
                if (!list.length) {
                    rows = '<tr><td colspan="9" class="text-center">No outward entries found</td></tr>';
                } else {
                    list.forEach(function(r) {
                        var name = escapeHtml(r.sProductName || '') + (r.sProductCode ? ' (' + escapeHtml(r.sProductCode) + ')' : '');
                        rows += '<tr>';
                        rows += '<td>' + escapeHtml(r.dDate || '') + '</td>';
                        rows += '<td>' + name + '</td>';
                        rows += '<td>' + r.iQty + ' ' + escapeHtml(r.sUnit || '') + '</td>';
                        rows += '<td>' + escapeHtml(r.sPurpose || '') + '</td>';
                        rows += '<td>' + escapeHtml(r.sProjectCompany || '-') + '</td>';
                        rows += '<td>' + escapeHtml(r.sIssuedTo || '-') + '</td>';
                        rows += '<td>' + escapeHtml(r.sRemarks || '-') + '</td>';
                        rows += '<td><button class="btn btn-success btn-sm" onclick="editOutward(' + r.iOutwardid + ')">Edit</button></td>';
                        rows += '<td><button class="btn btn-danger btn-sm" onclick="deleteOutward(' + r.iOutwardid + ')">Delete</button></td>';
                        rows += '</tr>';
                    });
                }
            } else {
                rows = '<tr><td colspan="9" class="text-center">No outward entries found</td></tr>';
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
            alert('Failed to load outward entries.');
        }
    });
}

$(document).ready(function() {
    loadProductOptions();
    loadWonProjects();
    loadOutward();
});
</script>
</body>
</html>
