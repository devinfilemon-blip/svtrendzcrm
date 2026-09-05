<!-- Late theme overrides (win over page-level Select2 / plugin CSS) -->
<link href="assets/css/crm-dark.css?v=20250805a" rel="stylesheet" type="text/css" />

<!-- JAVASCRIPT -->

<script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/libs/metismenu/metisMenu.min.js"></script>
<script src="assets/libs/simplebar/simplebar.min.js"></script>
<script src="assets/libs/node-waves/waves.min.js"></script>

<script src="assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>


<script>
/* Sync body theme class as soon as this footer script runs (before DOMContentLoaded) */
(function () {
    try {
        if (localStorage.getItem('crm_theme') === 'dark' && document.body) {
            document.body.classList.add('crm-dark');
            document.documentElement.classList.add('crm-dark', 'crm-dark-preload');
        }
    } catch (e) {}
})();
</script>
<script>
function updateCrmNotifBadge() {
    var badge = document.getElementById('crmNotifBadge');
    if (!badge) return;

    fetch('dashboard_data.php', { credentials: 'same-origin' })
        .then(function (response) { return response.json(); })
        .then(function (res) {
            if (res.status !== 'success') return;

            var count = parseInt(res.reminders, 10) || 0;
            badge.textContent = count > 99 ? '99+' : String(count);
            badge.style.display = count > 0 ? 'flex' : 'none';
        })
        .catch(function () {});
}

window.refreshCrmNotifBadge = updateCrmNotifBadge;

document.addEventListener('DOMContentLoaded', updateCrmNotifBadge);

function initCrmMobileSidebar() {
    var overlay = document.getElementById('crmSidebarOverlay');

    function closeSidebar() {
        document.body.classList.remove('sidebar-enable');
        if (overlay) overlay.setAttribute('aria-hidden', 'true');
    }

    function openSidebar() {
        document.body.classList.add('sidebar-enable');
        if (overlay) overlay.setAttribute('aria-hidden', 'false');
    }

    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }

    document.querySelectorAll('#sidebar-menu a[href]').forEach(function (link) {
        var href = link.getAttribute('href') || '';
        if (href && href.indexOf('javascript') !== 0 && href !== '#') {
            link.addEventListener('click', function () {
                if (window.innerWidth < 992) {
                    closeSidebar();
                }
            });
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && document.body.classList.contains('sidebar-enable')) {
            closeSidebar();
        }
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth >= 992) {
            closeSidebar();
        }
    });
}

document.addEventListener('DOMContentLoaded', initCrmMobileSidebar);

/* ============================================================
   CRM THEME TOGGLE (Light ↔ Dark)
   ============================================================ */
(function () {
    var DARK_CLASS = 'crm-dark';
    var STORAGE_KEY = 'crm_theme';

    function applyTheme(dark, notify) {
        if (dark) {
            document.body.classList.add(DARK_CLASS);
            document.documentElement.classList.add(DARK_CLASS, 'crm-dark-preload');
            document.documentElement.style.colorScheme = 'dark';
            document.documentElement.style.backgroundColor = '#0b0f1e';
        } else {
            document.body.classList.remove(DARK_CLASS);
            document.documentElement.classList.remove(DARK_CLASS, 'crm-dark-preload');
            document.documentElement.style.colorScheme = '';
            document.documentElement.style.backgroundColor = '';
        }
        var meta = document.getElementById('crmThemeColorMeta');
        if (meta) {
            meta.setAttribute('content', dark ? '#0b0f1e' : '#9333ea');
        }
        var icon = document.getElementById('crmThemeIcon');
        if (icon) {
            icon.className = dark ? 'bx bx-sun' : 'bx bx-moon';
        }
        var btn = document.getElementById('crmThemeToggle');
        if (btn) {
            btn.title = dark ? 'Switch to Light theme' : 'Switch to Dark theme';
        }
        // Only notify on user toggle (not initial page apply)
        if (notify) {
            try {
                window.dispatchEvent(new CustomEvent('crm-theme-changed', {
                    detail: { dark: !!dark }
                }));
            } catch (e) {}
            // Backup refresh for dashboard charts
            setTimeout(function () {
                if (typeof window.refreshCrmPipelineChart === 'function') {
                    window.refreshCrmPipelineChart();
                }
            }, 50);
        }
    }

    function bindThemeToggle() {
        var toggle = document.getElementById('crmThemeToggle');
        if (!toggle || toggle.getAttribute('data-crm-theme-bound') === '1') return;
        toggle.setAttribute('data-crm-theme-bound', '1');
        toggle.addEventListener('click', function () {
            var nowDark = !document.body.classList.contains(DARK_CLASS);
            localStorage.setItem(STORAGE_KEY, nowDark ? 'dark' : 'light');
            applyTheme(nowDark, true);
        });
    }

    function initTheme() {
        applyTheme(localStorage.getItem(STORAGE_KEY) === 'dark', false);
        bindThemeToggle();
    }

    // Body already exists when this footer script runs — apply now (no white wait)
    if (document.body) {
        initTheme();
    } else {
        document.addEventListener('DOMContentLoaded', initTheme);
    }
})();

function enhanceCrmActionButtons(root) {
    var $scope = root ? $(root) : $(document);
    var $cells = $scope.find('#datatable td, .table-responsive .table td');

    $cells.find('.btn-info').each(function () {
        var $btn = $(this);
        if (!$btn.find('i.bx').length) {
            $btn.prepend('<i class="bx bx-show"></i>');
        }
        $btn.addClass('crm-btn-action crm-btn-view');
    });

    $cells.find('button.btn-success').each(function () {
        var $btn = $(this);
        if (!$btn.find('i.bx').length) {
            $btn.prepend('<i class="bx bx-edit-alt"></i>');
        }
        $btn.addClass('crm-btn-action crm-btn-edit');
    });

    $cells.find('.btn-danger, .delete-btn').each(function () {
        var $btn = $(this);
        if (!$btn.find('i.bx').length) {
            $btn.prepend('<i class="bx bx-trash"></i>');
        }
        $btn.addClass('crm-btn-action crm-btn-delete');
    });

    $cells.find('a.btn-success').each(function () {
        var $btn = $(this);
        var label = $.trim($btn.text()).toLowerCase();
        if (!$btn.find('i.bx').length) {
            if (label === 'view') {
                $btn.prepend('<i class="bx bx-show"></i>').addClass('crm-btn-view');
            } else {
                $btn.prepend('<i class="bx bx-file"></i>');
            }
        }
        $btn.addClass('crm-btn-action');
    });

    $cells.find('.btn-warning').each(function () {
        var $btn = $(this);
        if (!$btn.find('i.bx').length) {
            $btn.prepend('<i class="bx bx-cart"></i>');
        }
        $btn.addClass('crm-btn-action crm-btn-sales');
    });
}

$(document).ready(function () {
    enhanceCrmActionButtons();

    if ($.fn.DataTable && $('#datatable').length && !$.fn.DataTable.isDataTable('#datatable')) {
        var isMobile = window.matchMedia('(max-width: 991.98px)').matches;
        var table = $('#datatable').DataTable({
            responsive: false,
            scrollX: isMobile,
            autoWidth: false,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
            language: {
                search: '',
                searchPlaceholder: 'Search records...',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                paginate: { previous: 'Prev', next: 'Next' }
            }
        });

        table.on('draw.dt', function () {
            enhanceCrmActionButtons('#datatable');
        });
    } else if ($('#datatable').length) {
        $('#datatable').on('draw.dt', function () {
            enhanceCrmActionButtons('#datatable');
        });
    }
});
</script>
<script src="assets/js/crm-global-search.js"></script>
<?php include __DIR__ . '/ai-agent-widget.php'; ?>