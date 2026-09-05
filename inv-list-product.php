<?php
include 'layouts/session.php';
include 'layouts/head-main.php';
include 'layouts/config.php';
include 'layouts/crm-access.php';
crmRequireAdmin($link);
?>
<head>
    <title>Product List</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .success-message { color: green; font-weight: bold; }
        .error-message { color: red; font-weight: bold; }
        .btn-primary { color: #fff; background-color: #005aa5; border-color: #005aa5; }
        .badge-active { background-color: #34c38f; }
        .badge-inactive { background-color: #74788d; }
        .badge-low-stock { background-color: #f46a6a; }
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
                            <h4 class="mb-sm-0 font-size-18">Product List</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Inventory</a></li>
                                    <li class="breadcrumb-item active">Product List</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12 text-end d-flex flex-wrap gap-2 justify-content-end">
                        <a href="inv-export-products.php" class="btn btn-outline-secondary btn-sm"><i class="bx bx-export"></i> Export</a>
                        <a href="inv-import-products.php" class="btn btn-outline-secondary btn-sm"><i class="bx bx-import"></i> Import</a>
                        <a href="inv-add-product.php" class="btn btn-primary btn-sm">+ Add Product</a>
                    </div>
                </div>

                <div class="table-responsive">
                    <div><span id="message"></span></div>
                    <table id="datatable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Sr No.</th>
                                <th>Code</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Unit</th>
                                <th>Purchase Rate</th>
                                <th>Current Stock</th>
                                <th>Status</th>
                                <th>Edit</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
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

function loadProducts() {
    $.ajax({
        url: 'inventory-api.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ action: 'listproducts' }),
        success: function(response) {
            var rows = '';
            if (response.status === 'success') {
                var list = response.data || [];
                if (!list.length) {
                    rows = '<tr><td colspan="10" class="text-center">No products found</td></tr>';
                } else {
                    list.forEach(function(r, index) {
                        var stock = parseInt(r.iCurrentStock, 10) || 0;
                        var reorder = parseInt(r.iReorderLevel, 10) || 0;
                        var lowStock = reorder > 0 && stock <= reorder;
                        var statusBadge = r.sStatus === 'Active' ? 'badge-active' : 'badge-inactive';
                        rows += '<tr>';
                        rows += '<td>' + (index + 1) + '</td>';
                        rows += '<td>' + escapeHtml(r.sProductCode || '-') + '</td>';
                        rows += '<td>' + escapeHtml(r.sProductName || '') + '</td>';
                        rows += '<td>' + escapeHtml(r.sCategory || '-') + '</td>';
                        rows += '<td>' + escapeHtml(r.sUnit || '') + '</td>';
                        rows += '<td>' + fmtMoney(r.fPurchaseRate) + '</td>';
                        rows += '<td>' + stock + (lowStock ? ' <span class="badge badge-low-stock">Low</span>' : '') + '</td>';
                        rows += '<td><span class="badge ' + statusBadge + '">' + escapeHtml(r.sStatus || '') + '</span></td>';
                        rows += '<td><a class="btn btn-success btn-sm" href="inv-add-product.php?id=' + r.iProductid + '">Edit</a></td>';
                        rows += '<td><button class="btn btn-danger btn-sm" onclick="deleteProduct(' + r.iProductid + ')">Delete</button></td>';
                        rows += '</tr>';
                    });
                }
            } else {
                rows = '<tr><td colspan="10" class="text-center">No products found</td></tr>';
            }
            if ($.fn.DataTable.isDataTable('#datatable')) {
                $('#datatable').DataTable().destroy();
            }
            $('#datatable tbody').html(rows);
            if (response.status === 'success' && (response.data || []).length) {
                $('#datatable').DataTable({ order: [[0, 'asc']] });
            }
        },
        error: function() {
            alert('Failed to load products.');
        }
    });
}

function deleteProduct(id) {
    if (!id || !confirm('Delete this product?')) return;
    fetch('inventory-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'deleteproduct', id: id })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        var el = document.getElementById('message');
        el.innerHTML = res.message || '';
        el.className = res.status === 'success' ? 'success-message' : 'error-message';
        loadProducts();
    });
}

$(document).ready(loadProducts);
</script>
</body>
</html>
