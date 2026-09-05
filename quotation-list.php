<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include 'layouts/config.php';
include_once 'layouts/crm-access.php';
if (!crmCanListQuotation($link)) {
    header('Location: index.php');
    exit;
}
$canManageQuotation = crmCanManageQuotation($link) ? 1 : 0;
?>

<head>
    <title>List Quotation</title>
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
                            <h4 class="mb-sm-0 font-size-18">List Quotation</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Quotation</a></li>
                                    <li class="breadcrumb-item active">List Quotation</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div><span id="message"></span></div>

                <div class="crm-quote-toolbar">
                    <div class="crm-quote-search-wrap">
                        <i class="bx bx-search"></i>
                        <input type="text" id="quoteSearchInput" placeholder="Search by quotation no. or customer...">
                    </div>
                </div>

                <div class="row g-3" id="quotationCards">
                    <div class="col-12 text-center text-muted py-5">Loading…</div>
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
var canManageQuotation = <?php echo (int)$canManageQuotation; ?>;
var quotationStatusOptions = ['Draft', 'Sent', 'Accepted', 'Rejected'];
var allQuotations = [];

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

function escapeQuoteHtml(text) {
    if (text === null || text === undefined) return '';
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function statusClass(status) {
    var s = (status || 'Draft').toLowerCase();
    if (['draft', 'sent', 'accepted', 'rejected'].indexOf(s) === -1) s = 'draft';
    return 'crm-quote-status--' + s;
}

function formatMoney(val) {
    var num = parseFloat(val);
    if (isNaN(num)) return '—';
    return '₹' + num.toLocaleString('en-IN', { maximumFractionDigits: 0 });
}

function statusOptionsHtml(selected) {
    return quotationStatusOptions.map(function (opt) {
        return '<option value="' + opt + '"' + (opt === selected ? ' selected' : '') + '>' + opt + '</option>';
    }).join('');
}

function renderQuotationCards(list) {
    var $wrap = $('#quotationCards').empty();
    if (!list.length) {
        $wrap.html('<div class="col-12 text-center text-muted py-5">No quotations found</div>');
        return;
    }

    list.forEach(function (q, index) {
        var status = q.status || 'Draft';
        var address = (q.customer_address || '').trim();
        var netCost = formatMoney(q.total_amount);
        var validity = q.validity ? escapeQuoteHtml(q.validity) : '—';
        var editLink = 'add-quotation.php?id=' + q.id;
        var printLink = 'print-quotation.php?id=' + q.id;
        var projectLink = 'add-client-project.php?prefill_name=' + encodeURIComponent(q.customer_name || '') +
            '&quotation_no=' + encodeURIComponent(q.quotation_no || '');

        var actionsHtml =
            '<a href="' + editLink + '" class="crm-quote-action"><i class="bx bx-edit-alt"></i> Edit</a>' +
            '<a href="' + printLink + '" target="_blank" class="crm-quote-action"><i class="bx bx-printer"></i> Print</a>' +
            '<a href="' + projectLink + '" class="crm-quote-action"><i class="bx bx-briefcase-alt-2"></i> Project</a>';

        var deleteHtml = canManageQuotation
            ? '<button type="button" class="crm-quote-action crm-quote-action--delete" onclick="deleteQuotation(' + q.id + ')"><i class="bx bx-trash"></i> Delete</button>'
            : '';

        var statusSelectHtml = canManageQuotation
            ? '<select class="crm-quote-status-select ' + statusClass(status) + '" data-id="' + q.id + '" onchange="updateQuotationStatus(this)">' + statusOptionsHtml(status) + '</select>'
            : '<span class="crm-quote-status-select ' + statusClass(status) + '" style="cursor:default;">' + escapeQuoteHtml(status) + '</span>';

        var card =
            '<div class="col-lg-4 col-md-6 crm-quote-col" data-search="' + escapeQuoteHtml(((q.quotation_no || '') + ' ' + (q.customer_name || '')).toLowerCase()) + '" style="animation-delay:' + (0.03 * index) + 's">' +
                '<div class="crm-quote-card">' +
                    '<div class="crm-quote-card-top">' +
                        '<span class="crm-quote-no">' + escapeQuoteHtml(q.quotation_no || '—') + '</span>' +
                        statusSelectHtml +
                    '</div>' +
                    '<a href="add-quotation.php?id=' + q.id + '" class="crm-quote-customer">' + escapeQuoteHtml(q.customer_name || '—') + '</a>' +
                    '<div class="crm-quote-address"><i class="bx bx-map"></i><span>' + (address ? escapeQuoteHtml(address) : '—') + '</span></div>' +
                    '<div class="crm-quote-metrics">' +
                        '<div>' +
                            '<span class="crm-quote-metric-label">Net Cost</span>' +
                            '<span class="crm-quote-metric-value">' + netCost + '</span>' +
                        '</div>' +
                        '<div class="text-end">' +
                            '<span class="crm-quote-metric-label">Validity</span>' +
                            '<span class="crm-quote-metric-value">' + validity + '</span>' +
                        '</div>' +
                    '</div>' +
                    '<div class="crm-quote-actions">' +
                        '<div class="crm-quote-action-group">' + actionsHtml + '</div>' +
                        deleteHtml +
                    '</div>' +
                '</div>' +
            '</div>';

        $wrap.append(card);
    });
}

function applyQuoteSearch() {
    var term = $('#quoteSearchInput').val().trim().toLowerCase();
    if (!term) {
        renderQuotationCards(allQuotations);
        return;
    }
    var filtered = allQuotations.filter(function (q) {
        var hay = ((q.quotation_no || '') + ' ' + (q.customer_name || '')).toLowerCase();
        return hay.indexOf(term) !== -1;
    });
    renderQuotationCards(filtered);
}

function loadQuotations() {
    fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'getQuotations' })
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.status !== 'success') {
            allQuotations = [];
            renderQuotationCards([]);
            return;
        }
        if (typeof data.canManage !== 'undefined') {
            canManageQuotation = parseInt(data.canManage, 10) === 1 ? 1 : 0;
        }
        if (Array.isArray(data.statusOptions) && data.statusOptions.length) {
            quotationStatusOptions = data.statusOptions;
        }
        allQuotations = data.quotations || [];
        applyQuoteSearch();
    })
    .catch(function(err) {
        console.error(err);
        alert('An error occurred while fetching quotations.');
    });
}

function updateQuotationStatus(selectEl) {
    var $select = $(selectEl);
    var id = $select.data('id');
    var status = $select.val();
    var previousClass = $select.attr('class');

    fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'updateQuotationStatus', id: id, status: status })
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.status === 'success') {
            $select.attr('class', 'crm-quote-status-select ' + statusClass(status));
            var q = allQuotations.find(function (item) { return String(item.id) === String(id); });
            if (q) q.status = status;
        } else {
            $select.attr('class', previousClass);
            alert(data.message || 'Unable to update status.');
        }
    })
    .catch(function() {
        $select.attr('class', previousClass);
        alert('An error occurred while updating status.');
    });
}

function deleteQuotation(id) {
    if (!canManageQuotation) {
        alert('Access denied.');
        return;
    }
    if (!confirm('Are you sure you want to delete this quotation?')) return;

    fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'deleteQuotation', id: id })
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        var msg = document.getElementById('message');
        msg.textContent = data.message || '';
        msg.className = data.status === 'success' ? 'success-message' : 'error-message';
        loadQuotations();
    });
}

$(document).ready(function() {
    checkTokenStatus();
    loadQuotations();
    $('#quoteSearchInput').on('input', applyQuoteSearch);
});
</script>

</body>
</html>
