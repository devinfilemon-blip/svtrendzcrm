<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include 'layouts/config.php';
include_once 'layouts/crm-access.php';
if (!crmCanManageQuotation($link)) {
    header('Location: index.php');
    exit;
}
?>

<head>
    <title>Quotation Requests</title>
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
                            <h4 class="mb-sm-0 font-size-18">Quotation Requests</h4>
                        </div>
                    </div>
                </div>

                <div id="message" class="mb-3"></div>

                <div class="card">
                    <div class="card-body">
                        <p class="text-muted mb-3">Requests submitted by employees (web or mobile) asking that a quotation be prepared. Click a row to open or create that project's quotation.</p>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Project</th>
                                        <th>Requested By</th>
                                        <th>Notes</th>
                                        <th>Status</th>
                                        <th>Requested On</th>
                                        <th style="width:220px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="requestsBody">
                                    <tr><td colspan="6" class="text-center text-muted">Loading…</td></tr>
                                </tbody>
                            </table>
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
function escapeHtml(str) {
    return String(str == null ? '' : str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function showMsg(text, ok) {
    document.getElementById('message').innerHTML =
        '<div class="alert alert-' + (ok ? 'success' : 'danger') + ' alert-dismissible fade show" role="alert">' +
        escapeHtml(text) +
        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    if (ok) {
        setTimeout(function () { document.getElementById('message').innerHTML = ''; }, 2500);
    }
}

function api(payload) {
    return fetch('api.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    }).then(function (r) { return r.json(); });
}

function formatDateTime(dt) {
    if (!dt) return '—';
    var d = new Date(String(dt).replace(' ', 'T'));
    if (isNaN(d.getTime())) return dt;
    return d.toLocaleString();
}

function statusBadge(status) {
    var cls = status === 'Fulfilled' ? 'bg-soft-success text-success'
        : (status === 'Rejected' ? 'bg-soft-danger text-danger' : 'bg-soft-warning text-warning');
    return '<span class="badge ' + cls + '">' + escapeHtml(status) + '</span>';
}

// Opens the project's existing quotation if one exists, else starts a new one for that lead.
function openQuotationForLead(leadId) {
    api({ action: 'getQuotationByLeadId', leadId: leadId }).then(function (res) {
        if (res && res.status === 'success' && res.quotation && res.quotation.id) {
            window.location.href = 'add-quotation.php?leadId=' + leadId + '&id=' + res.quotation.id;
        } else {
            window.location.href = 'add-quotation.php?leadId=' + leadId;
        }
    });
}

function loadRequests() {
    api({ action: 'list_quotation_requests' }).then(function (res) {
        var $body = $('#requestsBody');
        if (!res || res.status !== 'success') {
            $body.html('<tr><td colspan="6" class="text-center text-danger">Unable to load requests</td></tr>');
            return;
        }
        var rows = res.data || [];
        if (!rows.length) {
            $body.html('<tr><td colspan="6" class="text-center text-muted">No quotation requests yet</td></tr>');
            return;
        }
        var html = '';
        rows.forEach(function (r) {
            var actions = '<button type="button" class="btn btn-sm btn-outline-primary me-1" data-open-lead="' + r.lead_id + '"><i class="bx bx-file"></i> Open Quotation</button>';
            if (r.status === 'Pending') {
                actions += '<button type="button" class="btn btn-sm btn-outline-success me-1" data-req-status="' + r.id + '" data-status="Fulfilled" title="Mark Fulfilled"><i class="bx bx-check"></i></button>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger" data-req-status="' + r.id + '" data-status="Rejected" title="Reject"><i class="bx bx-x"></i></button>';
            }
            html += '<tr>' +
                '<td>' + escapeHtml(r.project_name || ('Project #' + r.lead_id)) + '</td>' +
                '<td>' + escapeHtml(r.requested_by_name || '—') + '</td>' +
                '<td>' + escapeHtml(r.notes || '—') + '</td>' +
                '<td>' + statusBadge(r.status) + (r.quotation_id ? ' <span class="text-muted small">#' + r.quotation_id + '</span>' : '') + '</td>' +
                '<td>' + formatDateTime(r.created_at) + '</td>' +
                '<td>' + actions + '</td>' +
            '</tr>';
        });
        $body.html(html);
    });
}

$('#requestsBody').on('click', '[data-open-lead]', function () {
    openQuotationForLead($(this).data('open-lead'));
});

$('#requestsBody').on('click', '[data-req-status]', function () {
    var id = $(this).data('req-status');
    var status = $(this).data('status');
    api({ action: 'update_quotation_request_status', id: id, status: status }).then(function (res) {
        showMsg((res && res.message) || 'Update failed', !!(res && res.status === 'success'));
        if (res && res.status === 'success') loadRequests();
    });
});

$(document).ready(loadRequests);
</script>
</html>
