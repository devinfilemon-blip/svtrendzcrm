<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include 'layouts/config.php';
include_once 'layouts/crm-access.php';
crmRequireModule('lead', $link);
?>

<head>
    <title>My Leads</title>
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

                <div class="crm-dash-hero mb-4">
                    <div class="crm-dash-hero-content">
                        <span class="crm-dash-greeting">Lead Management</span>
                        <h2>My Leads</h2>
                        <p>Leads assigned to you and leads you own. Click a row to open lead details.</p>
                    </div>
                    <div class="crm-dash-hero-meta">
                        <div class="crm-dash-date">
                            <i class="bx bx-briefcase"></i>
                            <span id="leadCountLabel">Loading…</span>
                        </div>
                    </div>
                </div>

                <div id="message" class="mb-3"></div>

                <!-- Assigned to Me -->
                <div class="crm-leads-section">
                    <div class="crm-panel-card mb-0">
                        <div class="crm-panel-card-head">
                            <div>
                                <h6><i class="bx bx-user-check me-1"></i> Assigned to Me</h6>
                                <p>Leads currently assigned to you for follow-up</p>
                            </div>
                        </div>
                        <div class="crm-leads-table-wrap">
                            <div class="table-responsive">
                                <table id="datatable" class="table table-hover crm-leads-table mb-0">
                                    <thead>
                                        <tr>
                                            <th class="col-sr">Sr No.</th>
                                            <th>Company Name</th>
                                            <th>Product</th>
                                            <th>Amount</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th class="col-actions">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lead Owner -->
                <div class="crm-leads-section">
                    <div class="crm-panel-card mb-0">
                        <div class="crm-panel-card-head">
                            <div>
                                <h6><i class="bx bx-crown me-1"></i> Lead Owner</h6>
                                <p>Leads where you are the lead owner</p>
                            </div>
                        </div>
                        <div class="crm-leads-table-wrap">
                            <div class="table-responsive">
                                <table id="datatable-owner" class="table table-hover crm-leads-table mb-0">
                                    <thead>
                                        <tr>
                                            <th class="col-sr">Sr No.</th>
                                            <th>Company Name</th>
                                            <th>Product</th>
                                            <th>Amount</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th class="col-actions">Actions</th>
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
var USER_ID = <?php echo isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0; ?>;

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

function triggerReplay(leadId) {
    var form = document.createElement('form');
    form.method = 'post';
    form.action = 'lead-replay.php';
    form.target = '_blank';
    var input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'leadId';
    input.value = leadId;
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function formatDate(dateString) {
    var date = new Date(dateString);
    if (isNaN(date)) return dateString || '—';
    var day = String(date.getDate()).padStart(2, '0');
    var month = String(date.getMonth() + 1).padStart(2, '0');
    var year = date.getFullYear();
    return day + '-' + month + '-' + year;
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function priorityBadge(priority) {
    var p = (priority || 'N/A').toLowerCase();
    var cls = 'crm-badge--default';
    if (p.indexOf('high') !== -1) cls = 'crm-badge--priority-high';
    else if (p.indexOf('medium') !== -1 || p.indexOf('med') !== -1) cls = 'crm-badge--priority-medium';
    else if (p.indexOf('low') !== -1) cls = 'crm-badge--priority-low';
    return '<span class="crm-badge ' + cls + '">' + escapeHtml(priority || 'N/A') + '</span>';
}

function statusBadge(status) {
    return '<span class="crm-badge crm-badge--status">' + escapeHtml(status || 'N/A') + '</span>';
}

function buildLeadRow(lead, index) {
    var totalProductAmount = 0;
    (lead.products || []).forEach(function(prod) {
        totalProductAmount += parseFloat(prod.total_amount) || 0;
    });

    var productNames = lead.productNames || '';
    var leadId = lead.iLead_id;

    return '<tr class="crm-lead-row" data-lead-id="' + leadId + '">' +
        '<td class="col-sr">' + (index + 1) + '</td>' +
        '<td>' + escapeHtml(lead.sCompany_name || lead.sLead_name || '—') + '</td>' +
        '<td><span class="crm-cell-truncate" title="' + escapeHtml(productNames) + '">' + escapeHtml(productNames || '—') + '</span></td>' +
        '<td class="col-amount">' + totalProductAmount.toFixed(2) + '</td>' +
        '<td>' + formatDate(lead.sCreated_date) + '</td>' +
        '<td>' + statusBadge(lead.sLead_status) + '</td>' +
        '<td class="col-actions">' +
            '<a href="add-quotation.php?leadId=' + leadId + '" class="btn btn-sm btn-outline-success" onclick="event.stopPropagation()">Quote</a>' +
            '<a href="add-sales-order.php?leadId=' + leadId + '" class="btn btn-sm btn-outline-warning" onclick="event.stopPropagation()">Order</a>' +
        '</td>' +
    '</tr>';
}

var dtLanguage = {
    search: 'Search:',
    lengthMenu: 'Show _MENU_ entries',
    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
    infoEmpty: 'No entries to show',
    zeroRecords: 'No matching leads found',
    paginate: { previous: 'Prev', next: 'Next' }
};

function initLeadsTable(selector, rows, emptyColspan) {
    if ($.fn.DataTable.isDataTable(selector)) {
        $(selector).DataTable().destroy();
    }

    var $tbody = $(selector + ' tbody');
    if (!rows.length) {
        $tbody.html('<tr><td colspan="' + emptyColspan + '" class="crm-leads-empty">No leads found</td></tr>');
        return 0;
    }

    $tbody.html(rows.join(''));
    $(selector).DataTable({
        paging: true,
        pageLength: 10,
        lengthChange: true,
        searching: true,
        ordering: true,
        order: [[0, 'asc']],
        info: true,
        autoWidth: false,
        scrollX: true,
        language: dtLanguage,
        columnDefs: [
            { orderable: false, targets: -1 },
            { className: 'text-nowrap', targets: [4, 5] }
        ]
    });

    $(selector + ' tbody').off('click', 'tr.crm-lead-row').on('click', 'tr.crm-lead-row', function() {
        triggerReplay($(this).data('lead-id'));
    });

    return rows.length;
}

function myleads() {
    $.ajax({
        url: 'api.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ action: 'myleads', id: USER_ID }),
        success: function(response) {
            if (response.status === 'success') {
                var rows = response.data.map(function(lead, i) { return buildLeadRow(lead, i); });
                var count = initLeadsTable('#datatable', rows, 7);
                updateLeadCount(count, null);
            } else {
                initLeadsTable('#datatable', [], 7);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
        }
    });
}

function loadLeadOwnerLeads() {
    $.ajax({
        url: 'api.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ action: 'leadownerleads', id: USER_ID }),
        success: function(response) {
            if (response.status === 'success') {
                var rows = response.data.map(function(lead, i) { return buildLeadRow(lead, i); });
                var count = initLeadsTable('#datatable-owner', rows, 7);
                updateLeadCount(null, count);
            } else {
                initLeadsTable('#datatable-owner', [], 7);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error (leadowner):', error);
        }
    });
}

var assignedCount = null;
var ownerCount = null;

function updateLeadCount(assigned, owner) {
    if (assigned !== null) assignedCount = assigned;
    if (owner !== null) ownerCount = owner;
    if (assignedCount !== null && ownerCount !== null) {
        $('#leadCountLabel').text(assignedCount + ' assigned · ' + ownerCount + ' owned');
    }
}

$(document).ready(function() {
    checkTokenStatus();
    myleads();
    loadLeadOwnerLeads();
});
</script>

</body>
</html>
