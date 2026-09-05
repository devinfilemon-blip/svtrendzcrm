<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include 'layouts/config.php';
include_once 'layouts/crm-access.php';
crmRequireModule('lead', $link);
?>

<head>
    <title>Pipeline Board — <?php echo APP_NAME; ?></title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <link href="assets/css/crm-kanban.css?v=4" rel="stylesheet" type="text/css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<?php include 'layouts/body.php'; ?>
<script>document.body.classList.add('crm-kanban-page');</script>

<div id="layout-wrapper">
    <?php include 'layouts/menu.php'; ?>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="crm-dash-hero mb-4">
                    <div class="crm-dash-hero-content">
                        <span class="crm-dash-greeting">Lead Pipeline</span>
                        <h2>Infilemon Technologies</h2>
                        <p>Drag leads between columns to update their status. Click a card to open lead details.</p>
                    </div>
                    <div class="crm-dash-hero-meta">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="kanbanRefreshBtn">
                            <i class="bx bx-refresh"></i> Refresh
                        </button>
                    </div>
                </div>

                <div class="crm-kanban-toolbar">
                    <div class="crm-kanban-filter">
                        <label for="kanbanProductFilter" class="form-label mb-1">Filter by Product</label>
                        <select id="kanbanProductFilter" class="form-select form-select-sm">
                            <option value="">All Products</option>
                        </select>
                    </div>
                    <div class="crm-kanban-toolbar-actions">
                        <a href="add-lead-master.php" class="btn btn-primary btn-sm">
                            <i class="bx bx-plus"></i> Add Lead
                        </a>
                    </div>
                </div>
                <p class="crm-kanban-hint"><i class="bx bx-mobile-alt"></i> Swipe columns sideways · Long-press a card to drag</p>

                <div id="kanbanMessage" class="mb-3"></div>

                <div class="crm-kanban-board-wrap">
                    <div class="crm-kanban-board" id="kanbanBoard">
                        <div class="crm-kanban-loading">
                            <i class="bx bx-loader-alt bx-spin"></i> Loading pipeline…
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
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="assets/js/app.js"></script>

<script>
(function () {
    'use strict';

    var sortableInstances = [];

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function showMessage(text, type) {
        var $box = $('#kanbanMessage');
        $box.html('<div class="alert alert-' + (type || 'info') + ' alert-dismissible fade show" role="alert">' +
            escapeHtml(text) +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
        if (type === 'success') {
            setTimeout(function () { $box.empty(); }, 2500);
        }
    }

    function openLead(leadId) {
        var form = document.createElement('form');
        form.method = 'post';
        form.action = 'add-lead-master.php';
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'leadId';
        input.value = leadId;
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }

    function priorityClass(priority) {
        var p = (priority || '').toLowerCase();
        if (p.indexOf('high') !== -1) return 'crm-kanban-card--high';
        if (p.indexOf('medium') !== -1 || p.indexOf('med') !== -1) return 'crm-kanban-card--medium';
        if (p.indexOf('low') !== -1) return 'crm-kanban-card--low';
        return '';
    }

    function destroySortables() {
        sortableInstances.forEach(function (s) {
            if (s && s.destroy) s.destroy();
        });
        sortableInstances = [];
    }

    function initSortables() {
        destroySortables();
        var isTouch = window.matchMedia('(hover: none), (max-width: 767.98px)').matches;
        document.querySelectorAll('.crm-kanban-column-body').forEach(function (col) {
            var instance = Sortable.create(col, {
                group: 'kanban-leads',
                animation: 180,
                ghostClass: 'crm-kanban-card-ghost',
                dragClass: 'crm-kanban-card-drag',
                delay: isTouch ? 180 : 0,
                delayOnTouchOnly: true,
                touchStartThreshold: 5,
                forceFallback: false,
                onStart: function () {
                    window.__kanbanDragging = true;
                },
                onEnd: function (evt) {
                    setTimeout(function () { window.__kanbanDragging = false; }, 100);

                    var leadId = evt.item.getAttribute('data-lead-id');
                    var newStatusId = evt.to.getAttribute('data-status-id');
                    var oldStatusId = evt.from.getAttribute('data-status-id');

                    if (!leadId || !newStatusId || newStatusId === oldStatusId) return;

                    updateCounts();

                    $.ajax({
                        url: 'api.php',
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            action: 'update_lead_status',
                            leadId: parseInt(leadId, 10),
                            statusId: parseInt(newStatusId, 10)
                        }),
                        success: function (res) {
                            if (res.status === 'success') {
                                showMessage('Lead status updated successfully.', 'success');
                            } else {
                                showMessage(res.message || 'Failed to update status.', 'danger');
                                loadKanban();
                            }
                        },
                        error: function () {
                            showMessage('Network error while updating status.', 'danger');
                            loadKanban();
                        }
                    });
                }
            });
            sortableInstances.push(instance);
        });
    }

    function updateCounts() {
        document.querySelectorAll('.crm-kanban-column').forEach(function (col) {
            var count = col.querySelectorAll('.crm-kanban-card').length;
            var badge = col.querySelector('.crm-kanban-column-count');
            if (badge) badge.textContent = count;
        });
    }

    function renderKanban(columns) {
        var $board = $('#kanbanBoard');
        if (!columns.length) {
            $board.html('<div class="crm-kanban-empty">No lead statuses configured. Add statuses under Master → Lead Status.</div>');
            return;
        }

        var html = '';
        columns.forEach(function (col, index) {
            var themeIndex = index % 8;
            html += '<div class="crm-kanban-column" data-status-id="' + col.id + '">' +
                '<div class="crm-kanban-column-head crm-kanban-column-head--' + themeIndex + '">' +
                    '<span class="crm-kanban-column-title">' + escapeHtml(col.name) + '</span>' +
                    '<span class="crm-kanban-column-count">' + (col.leads ? col.leads.length : 0) + '</span>' +
                '</div>' +
                '<div class="crm-kanban-column-body" data-status-id="' + col.id + '">';

            (col.leads || []).forEach(function (lead) {
                var rateHtml = '';
                if (lead.lead_rate !== undefined && lead.lead_rate !== null && Number(lead.lead_rate) > 0) {
                    rateHtml = '<div class="crm-kanban-card-rate"><i class="bx bx-rupee"></i> Rate: &#8377;' +
                        Number(lead.lead_rate).toLocaleString('en-IN', { maximumFractionDigits: 2 }) + '</div>';
                }
                html += '<div class="crm-kanban-card ' + priorityClass(lead.sPrioritylevel) + '" data-lead-id="' + lead.iLead_id + '">' +
                    '<div class="crm-kanban-card-title">' + escapeHtml(lead.sCompany_name || lead.sLead_name || 'Lead #' + lead.iLead_id) + '</div>' +
                    (lead.sLead_name ? '<div class="crm-kanban-card-sub">' + escapeHtml(lead.sLead_name) + '</div>' : '') +
                    '<div class="crm-kanban-card-meta">' +
                        (lead.sContactperson ? '<span><i class="bx bx-user"></i> ' + escapeHtml(lead.sContactperson) + '</span>' : '') +
                        (lead.sPhone ? '<span><i class="bx bx-phone"></i> ' + escapeHtml(lead.sPhone) + '</span>' : '') +
                    '</div>' +
                    rateHtml +
                    (lead.sPrioritylevel ? '<span class="crm-kanban-card-priority">' + escapeHtml(lead.sPrioritylevel) + '</span>' : '') +
                    (lead.assigned_to_name ? '<div class="crm-kanban-card-assignee"><i class="bx bx-user-circle"></i> ' + escapeHtml(lead.assigned_to_name) + '</div>' : '') +
                '</div>';
            });

            html += '</div></div>';
        });

        $board.html(html);

        $board.find('.crm-kanban-card').on('click', function () {
            if (window.__kanbanDragging) return;
            openLead($(this).data('lead-id'));
        });

        initSortables();
    }

    function loadProducts() {
        return $.ajax({
            url: 'api.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ action: 'fngetlistproduct' }),
            success: function (res) {
                var $sel = $('#kanbanProductFilter');
                var current = $sel.val() || '';
                $sel.find('option:not(:first)').remove();
                if (res.status === 'success' && Array.isArray(res.data)) {
                    res.data.forEach(function (p) {
                        var id = p.iProductid;
                        var name = p.sProductname || ('Product #' + id);
                        if (p.sCategoryname) name += ' (' + p.sCategoryname + ')';
                        $sel.append($('<option></option>').attr('value', id).text(name));
                    });
                }
                $sel.val(current);
            }
        });
    }

    function loadKanban() {
        $('#kanbanBoard').html('<div class="crm-kanban-loading"><i class="bx bx-loader-alt bx-spin"></i> Loading pipeline…</div>');
        destroySortables();

        var productId = $('#kanbanProductFilter').val() || '';
        var payload = { action: 'kanban_data' };
        if (productId) payload.productId = productId;

        $.ajax({
            url: 'api.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(payload),
            success: function (res) {
                if (res.status === 'success') {
                    renderKanban(res.data || []);
                } else {
                    $('#kanbanBoard').html('<div class="crm-kanban-empty">' + escapeHtml(res.message || 'Unable to load pipeline.') + '</div>');
                }
            },
            error: function () {
                $('#kanbanBoard').html('<div class="crm-kanban-empty">Failed to load pipeline data.</div>');
            }
        });
    }

    $(document).ready(function () {
        loadProducts().always(function () {
            loadKanban();
        });
        $('#kanbanRefreshBtn').on('click', loadKanban);
        $('#kanbanProductFilter').on('change', loadKanban);
    });
})();
</script>

</body>
</html>
