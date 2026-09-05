<?php
if (!function_exists('crmIsAdmin')) {
    include_once __DIR__ . '/crm-access.php';
}
crmLoadUserAccess(isset($link) ? $link : null);
$crmIsAdmin = crmIsAdmin();
$crmHasLead = crmHasModule('lead', isset($link) ? $link : null);
$crmHasProject = crmHasModule('project', isset($link) ? $link : null);
$crmHasFinance = crmHasModule('finance', isset($link) ? $link : null);
$crmCanListQuote = crmCanListQuotation(isset($link) ? $link : null);
$crmIsClient = crmIsClient();
?>
<header id="page-topbar">
    <div class="navbar-header">
        <div class="crm-header-left">
            <button type="button" class="btn btn-sm header-item" id="vertical-menu-btn">
                <i class="fa fa-fw fa-bars"></i>
            </button>
            <a href="list-reminder_users.php" class="crm-header-icon" title="Notifications">
                <i class="bx bx-bell"></i>
                <span class="crm-notif-badge" id="crmNotifBadge" style="display: none;">0</span>
            </a>
            <?php if ($crmIsAdmin) : ?>
            <a href="faq.php" class="crm-header-icon d-inline-flex d-lg-none" title="FAQ">
                <i class="bx bx-help-circle"></i>
            </a>
            <?php endif; ?>
            <a href="MyProfile.php" class="crm-header-icon d-none d-md-inline-flex" title="Settings">
                <i class="bx bx-cog"></i>
            </a>
            <button type="button" class="crm-header-icon d-inline-flex d-lg-none" id="crmMobileSearchToggle" title="Search" aria-label="Open search">
                <i class="bx bx-search"></i>
            </button>
        </div>
        <div class="crm-header-search" id="crmHeaderSearch">
            <div class="crm-search-wrap">
                <i class="bx bx-search"></i>
                <input type="search" id="crmGlobalSearchInput" placeholder="Search leads & customers…" autocomplete="off" aria-label="Global search">
                <div class="crm-search-results" id="crmGlobalSearchResults" style="display:none;"></div>
            </div>
        </div>
        <div class="crm-header-right">
            <!-- Theme Toggle -->
            <button type="button" class="crm-theme-toggle" id="crmThemeToggle" title="Toggle Dark / Light theme" aria-label="Toggle theme">
                <i class="bx bx-moon" id="crmThemeIcon"></i>
            </button>
            <div class="dropdown d-inline-block">
                <button type="button" class="crm-user-btn" id="page-header-user-dropdown"
                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <div class="crm-user-avatar"><?php echo strtoupper(substr($_SESSION["username"] ?? 'U', 0, 1)); ?></div>
                    <div class="d-none d-xl-block">
                        <span class="crm-user-name"><?php echo ucfirst($_SESSION["username"] ?? 'User'); ?></span>
                        <span class="crm-user-role"><?php echo isset($_SESSION['userRole']) ? $_SESSION['userRole'] : 'User'; ?></span>
                    </div>
                    <i class="mdi mdi-chevron-down d-none d-xl-inline-block ms-1 crm-chevron-down"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item" href="MyProfile.php"><i class="fas fa-user-alt font-size-16 align-middle me-1"></i> My Profile</a>
                    <a class="dropdown-item" href="newpassword.php"><i class="fas fa-key font-size-16 align-middle me-1"></i> Change Password</a>
                    <?php if ($crmIsAdmin) : ?>
                    <a class="dropdown-item" href="faq.php"><i class="bx bx-help-circle font-size-16 align-middle me-1"></i> FAQ / Help</a>
                    <?php endif; ?>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-danger" href="logout.php"><i class="bx bx-power-off font-size-16 align-middle me-1 text-danger"></i> <?php echo $language["Logout"]; ?></a>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="crm-sidebar-overlay" id="crmSidebarOverlay" aria-hidden="true"></div>

<!-- ========== Left Sidebar Start ========== -->
<div class="vertical-menu">
    <div data-simplebar class="h-100">
        <div class="crm-sidebar-brand">
            <a href="index.php">
                <img src="<?php echo APP_LOGO; ?>" alt="<?php echo APP_NAME; ?>" class="crm-sidebar-logo">
                <img src="<?php echo APP_ICON; ?>" alt="<?php echo APP_NAME; ?>" class="crm-sidebar-icon">
            </a>
        </div>
        <div id="sidebar-menu">
            <ul class="metismenu list-unstyled" id="side-menu">
                <li class="menu-title" key="t-menu"><?php echo $language["Menu"]; ?></li>
                <?php if ($crmIsClient) : ?>
                <li>
                    <a href="client-dashboard.php" class="waves-effect">
                        <i class="bx bx-grid-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="client-portal-projects.php" class="waves-effect">
                        <i class="bx bx-briefcase"></i>
                        <span>My Projects</span>
                    </a>
                </li>
                <?php else : ?>
                <li>
                    <a href="index.php" class="waves-effect">
                        <i class="bx bx-grid-alt"></i>
                        <span key="t-dashboards"><?php echo $language["Dashboard"]; ?></span>
                    </a>
                </li>
                <?php if ($crmIsAdmin || $crmHasLead) : ?>
                <li>
                    <a href="kanban_pipeline.php" class="waves-effect">
                        <i class="bx bx-columns"></i>
                        <span>Pipeline Board</span>
                    </a>
                </li>
                <?php endif; ?>
                <li>
                    <a href="calendar.php" class="waves-effect">
                        <i class="bx bx-calendar"></i>
                        <span>Calendar</span>
                    </a>
                </li>

              
                        
<!--    
<li>
    <a href="javascript: void(0);" class="has-arrow waves-effect">
        <i class="bx bx-layout"></i>
        <span key="t-layouts">Lead</span>
    </a>
    <ul class="sub-menu" aria-expanded="false">
        <li><a href="add-lead-master.php" key="t-saas">Add Lead</a></li>
        <?php if (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'Admin') : ?>
            <li><a href="list-lead-master.php" key="t-crypto">List Lead</a></li>
        <?php endif; ?>
    </ul>
</li>

<li>
    <a href="myleads.php">
        <i class="bx bx-layout"></i>
        <span key="t-layouts">My Leads</span>
    </a>
</li> -->

<!-- 
  <?php
// Only show "My Leads" if user is NOT an Admin
if (isset($_SESSION["userRole"]) && $_SESSION["userRole"] !== "Admin") {

    // Flag to check if "My Leads" option is already displayed
    $isDisplayed = false;

    // Fetch all leads
    $stmt = $link->prepare('SELECT * FROM tblleads');
    $stmt->execute();
    $result = $stmt->get_result();

    // Loop through all the leads
    while ($approveroutput = $result->fetch_assoc()) {
        // Check if the user is either the assigned user or the lead owner
        if ($_SESSION["user_id"] == $approveroutput["sAssigned_to"] || $_SESSION["user_id"] == $approveroutput["sLead_owner"]) {
            // If "My Leads" is not displayed yet, display it
            if (!$isDisplayed) {
?>
                <li>
                    <a href="myleads.php">
                        <i class="bx bx-layout"></i>
                        <span key="t-layouts">My Leads</span>
                    </a>
                </li>
<?php
                $isDisplayed = true; // Set the flag to true to avoid duplicate displays
            }
        }
    }
}
?> -->

    <!-- <li>
        <a href="javascript: void(0);" class="has-arrow waves-effect">
            <i class="bx bx-layout"></i>
            <span key="t-layouts">Users</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">
            <li><a href="add-user.php" key="t-saas">Add User</a></li>
            <li><a href="list-user.php" key="t-crypto">List Users</a></li>
        </ul>
    </li>


    
    <li>
        <a href="javascript: void(0);" class="has-arrow waves-effect">
            <i class="bx bx-layout"></i>
            <span key="t-layouts">Lead Status</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">
            <li><a href="lead-status-master.php" key="t-saas">Add Lead Status</a></li>
            <li><a href="list-status.php" key="t-crypto">List Lead Status</a></li>
        </ul>
    </li>


    <li>
        <a href="javascript: void(0);" class="has-arrow waves-effect">
            <i class="bx bx-layout"></i>
            <span key="t-layouts">Contact Type</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">
            <li><a href="add-contact-type.php" key="t-saas">Add Contact Type</a></li>
            <li><a href="list-contact.php" key="t-crypto">List Contact Type</a></li>
        </ul>
    </li>


    
    <li>
        <a href="javascript: void(0);" class="has-arrow waves-effect">
            <i class="bx bx-layout"></i>
            <span key="t-layouts">Lead Sources</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">
            <li><a href="add-lead-sources.php" key="t-saas">Add Lead Sources</a></li>
            <li><a href="list-sources.php" key="t-crypto">List Lead Sources</a></li>
        </ul>
    </li>


     
    <li>
        <a href="javascript: void(0);" class="has-arrow waves-effect">
            <i class="bx bx-layout"></i>
            <span key="t-layouts">Priority Level</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">
            <li><a href="add-priority-level.php" key="t-saas">Add Priority Level</a></li>
            <li><a href="list-priority-level.php" key="t-crypto">List Priority Level</a></li>
        </ul>
    </li>


       
    <li>
        <a href="javascript: void(0);" class="has-arrow waves-effect">
            <i class="bx bx-layout"></i>
            <span key="t-layouts">Communication Type</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">
            <li><a href="add-communication-type.php" key="t-saas">Add Communication Type</a></li>
            <li><a href="list-communication.php" key="t-crypto">List Communication Type</a></li>
        </ul>
    </li>

    <li>
        <a href="javascript: void(0);" class="has-arrow waves-effect">
            <i class="bx bx-layout"></i>
            <span key="t-layouts">Category Name</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">
            <li><a href="add-categoryname.php" key="t-saas">Add Category Name </a></li>
            <li><a href="list-category.php" key="t-crypto">List Category Name</a></li>
        </ul>
    </li>

    <li>
        <a href="javascript: void(0);" class="has-arrow waves-effect">
            <i class="bx bx-layout"></i>
            <span key="t-layouts">Department</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">
            <li><a href="add-department.php" key="t-saas">Add Department</a></li>
            <li><a href="list-department.php" key="t-crypto">List Department</a></li>
        </ul>
    </li>

    <li>
        <a href="javascript: void(0);" class="has-arrow waves-effect">
            <i class="bx bx-layout"></i>
            <span key="t-layouts">Task Type</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">
            <li><a href="add-tasktype.php" key="t-saas">Add Task Type</a></li>
            <li><a href="list-task.php" key="t-crypto">List Task Type</a></li>
        </ul>
    </li>


    <li>
        <a href="javascript: void(0);" class="has-arrow waves-effect">
            <i class="bx bx-layout"></i>
            <span key="t-layouts">Customer</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">
            <li><a href="add-customer.php" key="t-saas">Add Customer</a></li>
            <li><a href="list-customer.php" key="t-crypto">List Customer</a></li>
        </ul>
    </li>

    <li>
        <a href="javascript: void(0);" class="has-arrow waves-effect">
            <i class="bx bx-layout"></i>
            <span key="t-layouts">Product</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">
            <li><a href="add-product.php" key="t-saas">Add Product</a></li>
            <li><a href="list-product.php" key="t-crypto">List Product</a></li>
        </ul>
    </li>
  <li>
        <a href="javascript: void(0);" class="has-arrow waves-effect">
            <i class="bx bx-layout"></i>
            <span key="t-layouts">Reminder</span>
        </a>
        <ul class="sub-menu" aria-expanded="false">
            <li><a href="add-reminders.php" key="t-saas">Add Reminder</a></li>
            <li><a href="list-reminders.php" key="t-crypto">List Reminder</a></li>
        </ul>
    </li>
    -->


<!-- Lead Management -->
<?php if ($crmIsAdmin || $crmHasLead) : ?>
<li>
    <a href="javascript: void(0);" class="has-arrow waves-effect">
        <i class="bx bx-briefcase-alt-2"></i>
        <span>Lead Management</span>
    </a>
    <ul class="sub-menu" aria-expanded="false">
        <li><a href="add-lead-master.php">Add Lead</a></li>
        <li><a href="list-lead-master.php">List Leads</a></li>
        <li><a href="myleads.php">My Leads</a></li>
        <li><a href="list-followups_users.php">Follow Up List</a></li>
    </ul>
</li>
<?php endif; ?>

<!-- Project Management -->
<?php if ($crmIsAdmin || $crmHasProject) : ?>
<li>
    <a href="javascript: void(0);" class="has-arrow waves-effect">
        <i class="bx bx-task"></i>
        <span>Project Management</span>
    </a>
    <ul class="sub-menu" aria-expanded="false">
        <li><a href="project-management.php">Won Projects</a></li>
        <li><a href="project-management-all.php">All Projects</a></li>
    </ul>
</li>
<?php endif; ?>

<!-- Sales Management -->
<?php if ($crmIsAdmin || $crmHasFinance) : ?>
<li>
    <a href="javascript: void(0);" class="has-arrow waves-effect">
        <i class="bx bx-rupee"></i>
        <span>Sales Management</span>
    </a>
    <ul class="sub-menu" aria-expanded="false">
        <li><a href="add-payment.php">Add Payment</a></li>
        <li><a href="list-payment.php">List Payments</a></li>
        <li><a href="add-pnl.php">Add P&amp;L Entry</a></li>
        <li><a href="list-pnl.php">List P&amp;L Entries</a></li>
        <li><a href="pnl-statement.php">P&amp;L Statement</a></li>
    </ul>
</li>
<?php endif; ?>

<!-- Quotation (separate top-level menu, not nested under Sales Management) -->
<?php if ($crmIsAdmin || $crmHasFinance || $crmCanListQuote) : ?>
<li>
    <a href="javascript: void(0);" class="has-arrow waves-effect">
        <i class="bx bx-file"></i>
        <span>Tax Invoice</span>
    </a>
    <ul class="sub-menu" aria-expanded="false">
        <li><a href="quotation-list.php">List Tax Invoice</a></li>
        <li><a href="upload-pdf.php">Upload PDF</a></li>
    </ul>
</li>
<?php endif; ?>

<!-- HRM (Admin only) -->
<?php if ($crmIsAdmin) : ?>
<li>
    <a href="javascript: void(0);" class="has-arrow waves-effect">
        <i class="bx bx-id-card"></i>
        <span>HRM</span>
    </a>
    <ul class="sub-menu" aria-expanded="false">
        <li><a href="hr-dashboard.php">HR Dashboard</a></li>
        <li><a href="add-employee.php">Add Employee</a></li>
        <li><a href="list-employee.php">Employee List</a></li>
        <li><a href="payroll.php">Payroll</a></li>
        <li><a href="list-payroll.php">Payroll List</a></li>
    </ul>
</li>
<?php endif; ?>

<!-- Inventory (Admin only) -->
<?php if ($crmIsAdmin) : ?>
<li>
    <a href="javascript: void(0);" class="has-arrow waves-effect">
        <i class="bx bx-package"></i>
        <span>Inventory</span>
    </a>
    <ul class="sub-menu" aria-expanded="false">
        <li><a href="inv-add-product.php">Add Product</a></li>
        <li><a href="inv-list-product.php">Product List</a></li>
        <li><a href="inv-inward.php">Inward</a></li>
        <li><a href="inv-outward.php">Outward</a></li>
    </ul>
</li>
<?php endif; ?>

<!-- HR Letters (Admin only) -->
<?php if ($crmIsAdmin) : ?>
<li>
    <a href="javascript: void(0);" class="has-arrow waves-effect">
        <i class="bx bx-file-blank"></i>
        <span>HR Letters</span>
    </a>
    <ul class="sub-menu" aria-expanded="false">
        <li><a href="generate-offer-letter.php">Add Offer Letter</a></li>
        <li><a href="list-offer-letter.php">List Offer Letters</a></li>
        <li><a href="generate-joining-letter.php">Add Joining Letter</a></li>
        <li><a href="list-joining-letter.php">List Joining Letters</a></li>
    </ul>
</li>
<?php endif; ?>

<!-- User Management (Admin only) -->
<?php if ($crmIsAdmin) : ?>
<li>
    <a href="javascript: void(0);" class="has-arrow waves-effect">
        <i class="bx bx-group"></i>
        <span>User Management</span>
    </a>
    <ul class="sub-menu" aria-expanded="false">
        <li><a href="add-user.php">Add User</a></li>
        <li><a href="list-user.php">List Users</a></li>
    </ul>
</li>
<?php endif; ?>

<!-- Master -->
<?php if ($crmIsAdmin) : ?>
<li>
    <a href="javascript: void(0);" class="has-arrow waves-effect">
        <i class="bx bx-data"></i>
        <span>Master</span>
    </a>
    <ul class="sub-menu" aria-expanded="false">

        <!-- Lead Status -->
        <li>
            <a href="javascript: void(0);" class="has-arrow waves-effect">Lead Status</a>
            <ul class="sub-menu" aria-expanded="false">
                <li><a href="lead-status-master.php">Add Lead Status</a></li>
                <li><a href="list-status.php">List Lead Status</a></li>
            </ul>
        </li>

        <!-- Contact Type -->
        <li>
            <a href="javascript: void(0);" class="has-arrow waves-effect">Contact Type</a>
            <ul class="sub-menu" aria-expanded="false">
                <li><a href="add-contact-type.php">Add Contact Type</a></li>
                <li><a href="list-contact.php">List Contact Type</a></li>
            </ul>
        </li>

        <!-- Lead Sources -->
        <li>
            <a href="javascript: void(0);" class="has-arrow waves-effect">Lead Sources</a>
            <ul class="sub-menu" aria-expanded="false">
                <li><a href="add-lead-sources.php">Add Lead Sources</a></li>
                <li><a href="list-sources.php">List Lead Sources</a></li>
            </ul>
        </li>

        <!-- Priority Level -->
        <li>
            <a href="javascript: void(0);" class="has-arrow waves-effect">Priority Level</a>
            <ul class="sub-menu" aria-expanded="false">
                <li><a href="add-priority-level.php">Add Priority Level</a></li>
                <li><a href="list-priority-level.php">List Priority Level</a></li>
            </ul>
        </li>

        <!-- Communication Type -->
        <li>
            <a href="javascript: void(0);" class="has-arrow waves-effect">Communication Type</a>
            <ul class="sub-menu" aria-expanded="false">
                <li><a href="add-communication-type.php">Add Communication Type</a></li>
                <li><a href="list-communication.php">List Communication Type</a></li>
            </ul>
        </li>

        <!-- Category Name -->
        <li>
            <a href="javascript: void(0);" class="has-arrow waves-effect">Category Name</a>
            <ul class="sub-menu" aria-expanded="false">
                <li><a href="add-categoryname.php">Add Category Name</a></li>
                <li><a href="list-category.php">List Category Name</a></li>
            </ul>
        </li>

        <!-- Department -->
        <li>
            <a href="javascript: void(0);" class="has-arrow waves-effect">Department</a>
            <ul class="sub-menu" aria-expanded="false">
                <li><a href="add-department.php">Add Department</a></li>
                <li><a href="list-department.php">List Department</a></li>
            </ul>
        </li>

        <!-- Task Type -->
        <li>
            <a href="javascript: void(0);" class="has-arrow waves-effect">Task Type</a>
            <ul class="sub-menu" aria-expanded="false">
                <li><a href="add-tasktype.php">Add Task Type</a></li>
                <li><a href="list-task.php">List Task Type</a></li>
            </ul>
        </li>

          <li>
        <a href="javascript: void(0);" class="has-arrow waves-effect">Customer</a>
        
        <ul class="sub-menu" aria-expanded="false">
            <li><a href="add-customer.php" key="t-saas">Add Customer</a></li>
            <li><a href="list-customer.php" key="t-crypto">List Customer</a></li>
        </ul>
    </li>

    <li>
          <a href="javascript: void(0);" class="has-arrow waves-effect">Product</a>
        <ul class="sub-menu" aria-expanded="false">
            <li><a href="add-product.php" key="t-saas">Add Product</a></li>
            <li><a href="list-product.php" key="t-crypto">List Product</a></li>
        </ul>
    </li>
 
    </ul>
</li>
<?php endif; ?>
<li>
    <a href="javascript: void(0);" class="has-arrow waves-effect">
        <i class="bx bx-bell"></i>
        <span>Reminder</span>
    </a>
    <ul class="sub-menu" aria-expanded="false">
        <li><a href="add-reminders.php">Add Reminder</a></li>
        <li><a href="list-reminders.php">List Reminder</a></li>
    </ul>
</li>

<li>
    <a href="javascript: void(0);" class="has-arrow waves-effect">
        <i class="bx bx-user-voice"></i>
        <span>Re-engage</span>
    </a>
    <ul class="sub-menu" aria-expanded="false">
        <li><a href="add-reengage.php">Add Re-engage</a></li>
        <li><a href="list-reengage.php">List Re-engage</a></li>
    </ul>
</li>

<li>
    <a href="javascript: void(0);" class="has-arrow waves-effect">
        <i class="bx bx-notepad"></i>
        <span>Daily Report</span>
    </a>
    <ul class="sub-menu" aria-expanded="false">
        <li><a href="add-daily-report.php">Add Daily Report</a></li>
        <li><a href="list-daily-report.php">List Daily Report</a></li>
    </ul>
</li>

<!--
<li>
    <a href="javascript: void(0);" class="has-arrow waves-effect">
        <i class="bx bx-file"></i>
        <span>Quotation</span>
    </a>
    <ul class="sub-menu" aria-expanded="false">
      
        <li><a href="quotation-list.php">List Quotation</a></li>
    </ul>
</li>
-->

<!--
<li>
    <a href="javascript: void(0);" class="has-arrow waves-effect">
        <i class="bx bx-cart"></i>
        <span>Sales Order</span>
    </a>
    <ul class="sub-menu" aria-expanded="false">

        <li><a href="sales-order-list.php">List Sales Order</a></li>
    </ul>
</li>
-->
    <?php if ($crmIsAdmin) : ?>
            <li><a href="display-users.php"><i class="bx bx-bar-chart-alt-2"></i> User Dashboard</a></li>
        <?php endif; ?>
                <?php endif; ?>

                <?php if ($crmIsAdmin) : ?>
                <li class="menu-title">Help</li>
                <li>
                    <a href="faq.php" class="waves-effect">
                        <i class="bx bx-help-circle"></i>
                        <span>FAQ</span>
                    </a>
                </li>
                <?php endif; ?>

            </ul>
        </div>
        <!-- Sidebar -->
    </div>
</div>
<!-- Left Sidebar End -->