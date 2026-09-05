<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include 'layouts/config.php'; ?>

<head>
    <title>FAQ & Help — <?php echo APP_NAME; ?></title>
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
                            <h4 class="mb-sm-0 font-size-18"><strong>FAQ & Help Center</strong></h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active">FAQ</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hero -->
                <div class="crm-faq-hero">
                    <div class="crm-faq-hero-icon">
                        <i class="bx bx-help-circle"></i>
                    </div>
                    <div class="crm-faq-hero-text">
                        <h4><strong>How can we help you?</strong></h4>
                        <p>Browse frequently asked questions about <strong><?php echo APP_NAME; ?></strong> — leads, follow-ups, reminders, quotations, and daily workflows.</p>
                    </div>
                </div>

                <!-- Quick jump -->
                <div class="crm-faq-quick-nav">
                    <a href="#section-start" class="crm-faq-quick-link"><i class="bx bx-rocket"></i> <strong>Getting Started</strong></a>
                    <a href="#section-leads" class="crm-faq-quick-link"><i class="bx bx-briefcase"></i> <strong>Leads</strong></a>
                    <a href="#section-dashboard" class="crm-faq-quick-link"><i class="bx bx-grid-alt"></i> <strong>Dashboard</strong></a>
                    <a href="#section-master" class="crm-faq-quick-link"><i class="bx bx-cog"></i> <strong>Settings</strong></a>
                </div>

                <!-- Section 1 -->
                <div class="crm-faq-section-card" id="section-start">
                    <div class="crm-faq-section-head">
                        <span class="crm-faq-section-icon"><i class="bx bx-rocket"></i></span>
                        <div>
                            <h5><strong>Getting Started</strong></h5>
                            <p>Login, roles, and basic navigation</p>
                        </div>
                    </div>

                    <div class="accordion crm-faq-accordion" id="faqGettingStarted">

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1" aria-expanded="true">
                                    <strong>What is <?php echo APP_NAME; ?>?</strong>
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqGettingStarted">
                                <div class="accordion-body">
                                    <p><strong><?php echo APP_NAME; ?></strong> is a web-based <strong>Customer Relationship Management (CRM)</strong> system built for sales teams.</p>
                                    <p class="mb-2"><strong>Key capabilities:</strong></p>
                                    <ul>
                                        <li><strong>Capture & manage leads</strong> with full company and contact details</li>
                                        <li><strong>Track follow-ups</strong> and activity history on every lead</li>
                                        <li><strong>Set reminders</strong> so no opportunity is missed</li>
                                        <li><strong>View sales pipeline</strong> on an interactive dashboard</li>
                                        <li><strong>Create & print quotations</strong> linked to leads and customers</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    <strong>How do I log in?</strong>
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqGettingStarted">
                                <div class="accordion-body">
                                    <ol class="crm-faq-steps">
                                        <li>Open the <strong>Login page</strong> in your browser.</li>
                                        <li>Enter your <strong>Username</strong> provided by your administrator.</li>
                                        <li>Enter your <strong>Password</strong>.</li>
                                        <li>Click the <strong>Log In</strong> button.</li>
                                    </ol>
                                    <p class="crm-faq-note"><i class="bx bx-info-circle"></i> <strong>Forgot password?</strong> Contact your <strong>Admin</strong> to reset it.</p>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    <strong>What is the difference between Admin and User roles?</strong>
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqGettingStarted">
                                <div class="accordion-body">
                                    <div class="crm-faq-role-grid">
                                        <div class="crm-faq-role-box">
                                            <span class="crm-faq-role-badge crm-faq-role-badge--admin">Admin</span>
                                            <ul>
                                                <li>View <strong>all leads</strong> across the team</li>
                                                <li>Manage <strong>Master data</strong> (statuses, sources, products)</li>
                                                <li><strong>User management</strong> — add & list users</li>
                                                <li><strong>Export leads</strong> to Excel</li>
                                                <li><strong>User Dashboard</strong> — view each rep's pipeline</li>
                                            </ul>
                                        </div>
                                        <div class="crm-faq-role-box">
                                            <span class="crm-faq-role-badge crm-faq-role-badge--user">User</span>
                                            <ul>
                                                <li>Add and edit <strong>leads</strong></li>
                                                <li>View <strong>My Leads</strong> (assigned + owned)</li>
                                                <li>Log <strong>follow-ups</strong> and set <strong>reminders</strong></li>
                                                <li>Create and print <strong>quotations</strong></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2 -->
                <div class="crm-faq-section-card" id="section-leads">
                    <div class="crm-faq-section-head">
                        <span class="crm-faq-section-icon"><i class="bx bx-briefcase-alt-2"></i></span>
                        <div>
                            <h5><strong>Leads & Follow-ups</strong></h5>
                            <p>Managing leads, assignments, and daily sales activity</p>
                        </div>
                    </div>

                    <div class="accordion crm-faq-accordion" id="faqLeads">

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    <strong>How do I add a new lead?</strong>
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqLeads">
                                <div class="accordion-body">
                                    <p class="crm-faq-path"><i class="bx bx-link"></i> <strong>Menu:</strong> Lead Management → Lead → <strong>Add Lead</strong></p>
                                    <p class="mb-2"><strong>Fill in these details:</strong></p>
                                    <ul>
                                        <li><strong>Company</strong> — name, industry, address, location</li>
                                        <li><strong>Contact</strong> — person, designation, email, phone</li>
                                        <li><strong>Lead info</strong> — source, status, priority</li>
                                        <li><strong>Assignment</strong> — assigned rep and lead owner</li>
                                        <li><strong>Products</strong> — add line items if applicable</li>
                                    </ul>
                                    <p>Click <strong>Save</strong> to create the lead.</p>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                    <strong>What is "Assigned to Me" vs "Lead Owner"?</strong>
                                </button>
                            </h2>
                            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqLeads">
                                <div class="accordion-body">
                                    <p class="crm-faq-path"><i class="bx bx-link"></i> <strong>Menu:</strong> Lead Management → <strong>My Leads</strong></p>
                                    <ul>
                                        <li><strong>Assigned to Me</strong> — Leads currently <strong>delegated to you</strong> for follow-up and action.</li>
                                        <li><strong>Lead Owner</strong> — Leads you <strong>originally created or own</strong>, even if reassigned to another rep.</li>
                                    </ul>
                                    <p class="crm-faq-note"><i class="bx bx-info-circle"></i> This <strong>dual ownership model</strong> keeps accountability clear across the team.</p>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                                    <strong>How do I log a follow-up on a lead?</strong>
                                </button>
                            </h2>
                            <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqLeads">
                                <div class="accordion-body">
                                    <ol class="crm-faq-steps">
                                        <li>Open the lead from <strong>My Leads</strong> or the lead list.</li>
                                        <li>Go to the <strong>Follow-up</strong> section.</li>
                                        <li>Enter your <strong>notes / description</strong>.</li>
                                        <li>Set the <strong>next follow-up date</strong>.</li>
                                        <li>Update the <strong>lead status</strong> if needed.</li>
                                        <li>Attach files — up to <strong>3 documents</strong> per follow-up.</li>
                                        <li>Click <strong>Save</strong>.</li>
                                    </ol>
                                    <p class="crm-faq-note"><i class="bx bx-info-circle"></i> The <strong>pipeline dashboard</strong> automatically reflects the latest follow-up status.</p>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq7">
                                    <strong>How do reminders work?</strong>
                                </button>
                            </h2>
                            <div id="faq7" class="accordion-collapse collapse" data-bs-parent="#faqLeads">
                                <div class="accordion-body">
                                    <p class="crm-faq-path"><i class="bx bx-link"></i> <strong>Menu:</strong> Reminder → <strong>Add Reminder</strong> / <strong>List Reminder</strong></p>
                                    <ul>
                                        <li>Create reminders linked to leads and follow-up tasks.</li>
                                        <li><strong>Today's reminders</strong> appear on your <strong>Dashboard</strong>.</li>
                                        <li>A <strong>notification badge</strong> in the header shows today's reminder count.</li>
                                        <li>Use <strong>List Reminder</strong> to view all upcoming and past tasks.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3 -->
                <div class="crm-faq-section-card" id="section-dashboard">
                    <div class="crm-faq-section-head">
                        <span class="crm-faq-section-icon"><i class="bx bx-grid-alt"></i></span>
                        <div>
                            <h5><strong>Dashboard & Quotations</strong></h5>
                            <p>Pipeline visibility and document generation</p>
                        </div>
                    </div>

                    <div class="accordion crm-faq-accordion" id="faqDashboard">

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq8">
                                    <strong>What does the Dashboard show?</strong>
                                </button>
                            </h2>
                            <div id="faq8" class="accordion-collapse collapse" data-bs-parent="#faqDashboard">
                                <div class="accordion-body">
                                    <p class="crm-faq-path"><i class="bx bx-link"></i> <strong>Menu:</strong> <strong>Dashboard</strong></p>
                                    <p class="mb-2"><strong>Your daily CRM snapshot includes:</strong></p>
                                    <ul>
                                        <li><strong>Today's Reminders</strong> — tasks due today</li>
                                        <li><strong>Follow-ups Due</strong> — upcoming activities</li>
                                        <li><strong>Assigned Leads</strong> — active lead count</li>
                                        <li><strong>Pipeline Chart</strong> — visual breakdown by lead status</li>
                                        <li><strong>Status Cards</strong> — click any card to view leads in that stage</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq9">
                                    <strong>How do I create a quotation?</strong>
                                </button>
                            </h2>
                            <div id="faq9" class="accordion-collapse collapse" data-bs-parent="#faqDashboard">
                                <div class="accordion-body">
                                    <ol class="crm-faq-steps">
                                        <li>Open the lead you want to quote.</li>
                                        <li>Create a <strong>Quotation</strong> — customer and product details are pre-filled.</li>
                                        <li>Use the <strong>rich text editor</strong> to format your content.</li>
                                        <li>Click <strong>Save</strong>.</li>
                                        <li>Use <strong>Print</strong> to generate a professional document.</li>
                                    </ol>
                                    <p class="crm-faq-path"><i class="bx bx-link"></i> <strong>Menu:</strong> Quotation → <strong>List Quotation</strong> to view all quotations.</p>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq10">
                                    <strong>Can I export lead data?</strong>
                                </button>
                            </h2>
                            <div id="faq10" class="accordion-collapse collapse" data-bs-parent="#faqDashboard">
                                <div class="accordion-body">
                                    <p><strong>Yes — Admin users only.</strong></p>
                                    <ul>
                                        <li><strong>Export leads to Excel</strong> from the lead list page with date range and assignee filters.</li>
                                        <li><strong>Import customers from Excel</strong> under Master → Customer for bulk data upload.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 4 -->
                <div class="crm-faq-section-card" id="section-master">
                    <div class="crm-faq-section-head">
                        <span class="crm-faq-section-icon"><i class="bx bx-data"></i></span>
                        <div>
                            <h5><strong>Master Data & Settings</strong></h5>
                            <p>Configuration, profile, and account settings</p>
                        </div>
                    </div>

                    <div class="accordion crm-faq-accordion" id="faqMaster">

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq11">
                                    <strong>What is Master data and who can manage it?</strong>
                                </button>
                            </h2>
                            <div id="faq11" class="accordion-collapse collapse" data-bs-parent="#faqMaster">
                                <div class="accordion-body">
                                    <p class="crm-faq-path"><i class="bx bx-link"></i> <strong>Menu:</strong> <strong>Master</strong> (Admin only)</p>
                                    <p class="mb-2"><strong>Master data includes:</strong></p>
                                    <ul class="crm-faq-tags-list">
                                        <li><strong>Lead Status</strong></li>
                                        <li><strong>Lead Sources</strong></li>
                                        <li><strong>Priority Levels</strong></li>
                                        <li><strong>Contact Types</strong></li>
                                        <li><strong>Communication Types</strong></li>
                                        <li><strong>Categories</strong></li>
                                        <li><strong>Departments</strong></li>
                                        <li><strong>Customers</strong></li>
                                        <li><strong>Products</strong></li>
                                    </ul>
                                    <p class="crm-faq-note"><i class="bx bx-info-circle"></i> Only <strong>Admin</strong> users can add or edit master records.</p>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq12">
                                    <strong>How do I update my profile or change my password?</strong>
                                </button>
                            </h2>
                            <div id="faq12" class="accordion-collapse collapse" data-bs-parent="#faqMaster">
                                <div class="accordion-body">
                                    <ol class="crm-faq-steps">
                                        <li>Click your <strong>profile avatar</strong> in the top-right corner.</li>
                                        <li>Select <strong>My Profile</strong> to update your name and details.</li>
                                        <li>Select <strong>Change Password</strong> to set a new password.</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact -->
                <div class="crm-faq-contact">
                    <div class="crm-faq-contact-icon">
                        <i class="bx bx-support"></i>
                    </div>
                    <div>
                        <h6><strong>Still need help?</strong></h6>
                        <p>Contact your <strong>system administrator</strong> or reach out to <strong>Infilemon Technologies</strong> for support, training, or feature requests.</p>
                        <p class="mb-0"><strong>Developed by:</strong> Infilemon Technologies &nbsp;|&nbsp; <strong>Product:</strong> <?php echo APP_NAME; ?> — <?php echo APP_TAGLINE; ?></p>
                    </div>
                </div>

            </div>
        </div>

        <?php include 'layouts/footer.php'; ?>
    </div>
</div>

<?php include 'layouts/vendor-scripts.php'; ?>
<script src="assets/js/app.js"></script>

</body>
</html>
