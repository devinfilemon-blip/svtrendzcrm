<?php
include 'layouts/session.php';
include 'layouts/head-main.php';
include 'layouts/config.php';
include 'layouts/crm-access.php';
crmRequireAdmin($link);

$id = null;
if (isset($_POST['id'])) {
    $id = (int)$_POST['id'];
} elseif (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
}
$isEdit = ($id > 0);
?>
<head>
    <title><?php echo $isEdit ? 'Edit' : 'Add'; ?> Product</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .btn-primary { color: #fff; background-color: #005aa5; border-color: #005aa5; }
        .add-message { color: green; font-weight: bold; }
        .error-message { color: red; font-weight: bold; }
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
                            <h4 class="mb-sm-0 font-size-18"><?php echo $isEdit ? 'Edit' : 'Add'; ?> Product</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="inv-list-product.php">Inventory</a></li>
                                    <li class="breadcrumb-item active"><?php echo $isEdit ? 'Edit' : 'Add'; ?> Product</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-body">
                                <div><span id="message"></span></div>
                                <form id="productForm" method="post" onsubmit="return false;">
                                    <input type="hidden" id="id" name="id" value="<?php echo $isEdit ? (int)$id : ''; ?>">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="product_name">Product Name / Description *</label>
                                                <input type="text" class="form-control" id="product_name" name="product_name" placeholder="e.g. KPT Pneumato PPR Pipe 25mm PN16" required>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label" for="product_code">Product Code</label>
                                                <input type="text" class="form-control" id="product_code" name="product_code" placeholder="Part No.">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label" for="category">Category / Brand</label>
                                                <input type="text" class="form-control" id="category" name="category" list="categoryList" placeholder="e.g. KPT Pneumato">
                                                <datalist id="categoryList"></datalist>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label" for="hsn_code">HSN Code</label>
                                                <input type="text" class="form-control" id="hsn_code" name="hsn_code">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label" for="unit">Unit</label>
                                                <input type="text" class="form-control" id="unit" name="unit" list="unitList" value="Nos">
                                                <datalist id="unitList">
                                                    <option value="Nos">
                                                    <option value="Mtrs">
                                                    <option value="Meters">
                                                    <option value="Kg">
                                                    <option value="Ltr">
                                                    <option value="Box">
                                                    <option value="Set">
                                                </datalist>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label" for="purchase_rate">Purchase Rate</label>
                                                <input type="number" class="form-control" id="purchase_rate" name="purchase_rate" value="0" min="0" step="0.01">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label" for="sale_rate">Sale Rate</label>
                                                <input type="number" class="form-control" id="sale_rate" name="sale_rate" value="0" min="0" step="0.01">
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label" for="opening_stock">Opening Stock</label>
                                                <input type="number" class="form-control" id="opening_stock" name="opening_stock" value="0">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label" for="reorder_level">Reorder Level</label>
                                                <input type="number" class="form-control" id="reorder_level" name="reorder_level" value="0" min="0">
                                            </div>
                                        </div>
                                    </div>

                                    <button type="button" class="btn btn-primary w-md" onclick="saveProduct()"><?php echo $isEdit ? 'Update' : 'Save'; ?></button>
                                    <a href="inv-list-product.php" class="btn btn-secondary w-md ms-2">List</a>
                                </form>
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
var editId = <?php echo $isEdit ? (int)$id : 0; ?>;

function showMessage(msg, ok) {
    var el = document.getElementById('message');
    el.innerHTML = msg || '';
    el.className = ok ? 'add-message' : 'error-message';
}

function loadCategoryOptions() {
    fetch('inventory-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'listcategories' })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        var opts = '';
        (res.data || []).forEach(function(c) {
            opts += '<option value="' + c.replace(/"/g, '&quot;') + '">';
        });
        document.getElementById('categoryList').innerHTML = opts;
    });
}

function saveProduct() {
    var name = document.getElementById('product_name').value.trim();
    if (!name) { alert('Product name is required.'); return; }

    var data = {
        action: editId > 0 ? 'updateproduct' : 'saveproduct',
        id: editId,
        product_name: name,
        product_code: document.getElementById('product_code').value.trim(),
        category: document.getElementById('category').value.trim(),
        hsn_code: document.getElementById('hsn_code').value.trim(),
        unit: document.getElementById('unit').value.trim(),
        purchase_rate: document.getElementById('purchase_rate').value,
        sale_rate: document.getElementById('sale_rate').value,
        opening_stock: document.getElementById('opening_stock').value,
        reorder_level: document.getElementById('reorder_level').value
    };

    fetch('inventory-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        showMessage(res.message || '', res.status === 'success');
        if (res.status === 'success') {
            setTimeout(function() { window.location.href = 'inv-list-product.php'; }, 700);
        }
    })
    .catch(function() { showMessage('Save failed.', false); });
}

function fillProductForm(d) {
    document.getElementById('product_name').value = d.sProductName || '';
    document.getElementById('product_code').value = d.sProductCode || '';
    document.getElementById('category').value = d.sCategory || '';
    document.getElementById('hsn_code').value = d.sHsnCode || '';
    document.getElementById('unit').value = d.sUnit || 'Nos';
    document.getElementById('purchase_rate').value = d.fPurchaseRate || 0;
    document.getElementById('sale_rate').value = d.fSaleRate || 0;
    document.getElementById('opening_stock').value = d.iOpeningStock || 0;
    document.getElementById('reorder_level').value = d.iReorderLevel || 0;
}

loadCategoryOptions();

if (editId > 0) {
    fetch('inventory-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'getproductbyid', id: editId })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.status === 'success' && res.data) {
            fillProductForm(res.data);
        } else {
            showMessage(res.message || 'Failed to load product.', false);
        }
    });
}
</script>
</body>
</html>
