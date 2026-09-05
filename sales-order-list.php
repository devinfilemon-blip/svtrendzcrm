<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include 'layouts/config.php'; ?>

<head>
    <title>List Sales Order</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
                            <h4 class="mb-sm-0 font-size-18">List Sales Orders</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Sales Order</a></li>
                                    <li class="breadcrumb-item active">List Sales Orders</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div><span id="message"></span></div>

                <div class="table-responsive">
                    <table id="datatable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Sr No.</th>
                                <th>Sales Order No</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Total Amount</th>
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
function checkTokenStatus() {
    $.ajax({
        url: 'check_token.php',
        method: 'GET',
        success: function(response) {
            if (response.status === 'error') {
                alert(response.message);
                window.location.href = 'auth-login.php';
            }
        },
        error: function() {
            window.location.href = 'auth-login.php';
        }
    });
}

function initSalesOrderTable(rows) {
    if ($.fn.DataTable.isDataTable('#datatable')) {
        $('#datatable').DataTable().destroy();
    }
    $('#datatable tbody').html(rows);
    var table = $('#datatable').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[0, 'asc']]
    });
    table.off('draw.dt.crmActions').on('draw.dt.crmActions', function() {
        if (typeof enhanceCrmActionButtons === 'function') {
            enhanceCrmActionButtons('#datatable');
        }
    });
    if (typeof enhanceCrmActionButtons === 'function') {
        enhanceCrmActionButtons('#datatable');
    }
}

function loadSalesOrders() {
    fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'getSalesOrders' })
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.status !== 'success') {
            initSalesOrderTable('<tr><td colspan="7">No sales orders found</td></tr>');
            return;
        }

        var rows = '';
        data.salesOrders.forEach(function(so, index) {
            rows += '<tr>' +
                '<td>' + (index + 1) + '</td>' +
                '<td>' + (so.sales_no || '—') + '</td>' +
                '<td>' + (so.customer_name || '—') + '</td>' +
                '<td>' + (so.sales_date || '—') + '</td>' +
                '<td>' + (so.total_amount || '0') + '</td>' +
                '<td>' +
                    '<form action="add-sales-order.php" method="get" class="d-inline">' +
                        '<input type="hidden" name="id" value="' + so.id + '">' +
                        '<button type="submit" class="btn btn-success">Edit</button>' +
                    '</form>' +
                '</td>' +
                '<td>' +
                    '<button type="button" class="btn btn-danger delete-btn" onclick="deleteSalesOrder(' + so.id + ')">Delete</button>' +
                '</td>' +
            '</tr>';
        });

        initSalesOrderTable(rows || '<tr><td colspan="7">No sales orders found</td></tr>');
    })
    .catch(function(err) {
        console.error(err);
        alert('An error occurred while fetching sales orders.');
    });
}

function deleteSalesOrder(id) {
    if (!confirm('Are you sure you want to delete this sales order?')) return;

    fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'deleteSalesOrder', id: id })
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        var msg = document.getElementById('message');
        msg.textContent = data.message || '';
        msg.className = data.status === 'success' ? 'success-message' : 'error-message';
        loadSalesOrders();
    });
}

$(document).ready(function() {
    checkTokenStatus();
    loadSalesOrders();
});
</script>

</body>
</html>
