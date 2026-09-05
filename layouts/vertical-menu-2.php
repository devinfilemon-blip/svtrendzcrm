<header id="page-topbar">
    <div class="navbar-header">
        <div class="d-flex">
            <!-- LOGO -->
            <div class="navbar-brand-box">
                <a href="index.php" class="logo logo-dark">
                    <span class="logo-sm">
                        <img src="assets/images/logo.svg" alt="" height="22">
                    </span>
                    <span class="logo-lg">
                        <img src="assets/images/logo-dark.png" alt="" height="17">
                    </span>
                </a>

                <a href="index.php" class="logo logo-light">
                    <span class="logo-sm">
                        <img src="assets/images/logo-light.svg" alt="" height="22">
                    </span>
                    <span class="logo-lg">
                        <img src="assets/images/logo-light.png" alt="" height="19">
                    </span>
                </a>
            </div>

            <button type="button" class="btn btn-sm px-3 font-size-16 header-item waves-effect" id="vertical-menu-btn">
                <i class="fa fa-fw fa-bars"></i>
            </button>

            <!-- App Search-->
            <form class="app-search d-none d-lg-block">
                <div class="position-relative">
                    <input type="text" class="form-control" placeholder="<?php echo $language["Search"]; ?>">
                    <span class="bx bx-search-alt"></span>
                </div>
            </form>

            <div class="dropdown dropdown-mega d-none d-lg-block ms-2">
                <!-- <button type="button" class="btn header-item waves-effect" data-bs-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
                    <span key="t-megamenu"><?php echo $language["Mega_Menu"]; ?></span>
                    <i class="mdi mdi-chevron-down"></i> 
                </button> -->
              <!--   <div class="dropdown-menu dropdown-megamenu">
                    <div class="row">
                        <div class="col-sm-8">
    
                            <div class="row">
                                <div class="col-md-4">
                                    <h5 class="font-size-14 mt-0" key="t-ui-components"><?php echo $language["UI_Components"]; ?></h5>
                                    <ul class="list-unstyled megamenu-list">
                                        <li>
                                            <a href="javascript:void(0);" key="t-lightbox"><?php echo $language["Lightbox"]; ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-range-slider"><?php echo $language["Range_Slider"]; ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-sweet-alert"><?php echo $language["Sweet_Alert"]; ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-rating"><?php echo $language["Rating"]; ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-forms"><?php echo $language["Forms"]; ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-tables"><?php echo $language["Tables"]; ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-charts"><?php echo $language["Charts"]; ?></a>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-4">
                                    <h5 class="font-size-14 mt-0" key="t-applications"><?php echo $language["Applications"]; ?></h5>
                                    <ul class="list-unstyled megamenu-list">
                                        <li>
                                            <a href="javascript:void(0);" key="t-ecommerce"><?php echo $language["Ecommerce"]; ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-calendar"><?php echo $language["Calendar"]; ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-email"><?php echo $language["Email"]; ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-projects"><?php echo $language["Projects"]; ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-tasks"><?php echo $language["Tasks"]; ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-contacts"><?php echo $language["Contacts"]; ?></a>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-4">
                                    <h5 class="font-size-14 mt-0" key="t-extra-pages"><?php echo $language["Extra_Pages"]; ?></h5>
                                    <ul class="list-unstyled megamenu-list">
                                        <li>
                                            <a href="javascript:void(0);" key="t-light-sidebar"><?php echo $language["Light_Sidebar"]; ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-compact-sidebar"><?php echo $language["Compact_Sidebar"]; ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-horizontal"><?php echo $language["Horizontal_layout"]; ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-maintenance"><?php echo $language["Maintenance"]; ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-coming-soon"><?php echo $language["Coming_Soon"]; ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-timeline"><?php echo $language["Timeline"]; ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-faqs"><?php echo $language["FAQs"]; ?></a>
                                        </li>
                            
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="row">
                                <div class="col-sm-6">
                                    <h5 class="font-size-14 mt-0" key="t-ui-components"><?php echo $language["UI_Components"]; ?></h5>
                                    <ul class="list-unstyled megamenu-list">
                                        <li>
                                            <a href="javascript:void(0);" key="t-lightbox"><?php echo $language["Lightbox"]; ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-range-slider"><?php echo $language["Range_Slider"]; ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-sweet-alert"><?php echo $language["Sweet_Alert"]; ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-rating"><?php echo $language["Rating"]; ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-forms"><?php echo $language["Forms"]; ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-tables"><?php echo $language["Tables"]; ?></a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" key="t-charts"><?php echo $language["Charts"]; ?></a>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-sm-5">
                                    <div>
                                        <img src="assets/images/megamenu-img.png" alt="" class="img-fluid mx-auto d-block">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div> -->
            </div>
        </div>

        <div class="d-flex">

            <div class="dropdown d-inline-block d-lg-none ms-2">
                <button type="button" class="btn header-item noti-icon waves-effect" id="page-header-search-dropdown"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="mdi mdi-magnify"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                    aria-labelledby="page-header-search-dropdown">
        
                    <form class="p-3">
                        <div class="form-group m-0">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Search ..." aria-label="Recipient's username">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit"><i class="mdi mdi-magnify"></i></button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item waves-effect"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <?php if ($lang == 'en') { ?>
                        <img src="assets/images/flags/us.jpg" alt="Header Language" height="16" class="mr-1"> <span class="align-middle">English</span>
                    <?php } ?>
                    <?php if ($lang == 'es') { ?>
                        <img src="assets/images/flags/spain.jpg" alt="Header Language" height="16" class="mr-1"> <span class="align-middle">Spanish</span>
                    <?php } ?>
                    <?php if ($lang == 'de') { ?>
                        <img src="assets/images/flags/germany.jpg" alt="Header Language" height="16" class="mr-1"> <span class="align-middle">German</span>
                    <?php } ?>
                    <?php if ($lang == 'it') { ?>
                        <img src="assets/images/flags/italy.jpg" alt="Header Language" height="16" class="mr-1"> <span class="align-middle">Italian</span>
                    <?php } ?>
                    <?php if ($lang == 'ru') { ?>
                        <img src="assets/images/flags/russia.jpg" alt="Header Language" height="16" class="mr-1"> <span class="align-middle">Russian</span>
                    <?php } ?>
                </button>
                <div class="dropdown-menu dropdown-menu-end">

                    
                    <a href="?lang=en" class="dropdown-item notify-item language" data-lang="en">
                        <img src="assets/images/flags/us.jpg" alt="user-image" class="me-1" height="12"> <span class="align-middle">English</span>
                    </a>
                    
                    <a href="?lang=es" class="dropdown-item notify-item language" data-lang="sp">
                        <img src="assets/images/flags/spain.jpg" alt="user-image" class="me-1" height="12"> <span class="align-middle">Spanish</span>
                    </a>

                    
                    <a href="?lang=de" class="dropdown-item notify-item language" data-lang="gr">
                        <img src="assets/images/flags/germany.jpg" alt="user-image" class="me-1" height="12"> <span class="align-middle">German</span>
                    </a>

                    
                    <a href="?lang=it" class="dropdown-item notify-item language" data-lang="it">
                        <img src="assets/images/flags/italy.jpg" alt="user-image" class="me-1" height="12"> <span class="align-middle">Italian</span>
                    </a>

                    
                    <a href="?lang=ru" class="dropdown-item notify-item language" data-lang="ru">
                        <img src="assets/images/flags/russia.jpg" alt="user-image" class="me-1" height="12"> <span class="align-middle">Russian</span>
                    </a>
                </div>
            </div> -->

            <div class="dropdown d-none d-lg-inline-block ms-1">
                <button type="button" class="btn header-item noti-icon waves-effect"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="bx bx-customize"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                    <div class="px-lg-2">
                        <div class="row g-0">
                            <div class="col">
                                <a class="dropdown-icon-item" href="#">
                                    <img src="assets/images/brands/github.png" alt="Github">
                                    <span>GitHub</span>
                                </a>
                            </div>
                            <div class="col">
                                <a class="dropdown-icon-item" href="#">
                                    <img src="assets/images/brands/bitbucket.png" alt="bitbucket">
                                    <span>Bitbucket</span>
                                </a>
                            </div>
                            <div class="col">
                                <a class="dropdown-icon-item" href="#">
                                    <img src="assets/images/brands/dribbble.png" alt="dribbble">
                                    <span>Dribbble</span>
                                </a>
                            </div>
                        </div>

                        <div class="row g-0">
                            <div class="col">
                                <a class="dropdown-icon-item" href="#">
                                    <img src="assets/images/brands/dropbox.png" alt="dropbox">
                                    <span>Dropbox</span>
                                </a>
                            </div>
                            <div class="col">
                                <a class="dropdown-icon-item" href="#">
                                    <img src="assets/images/brands/mail_chimp.png" alt="mail_chimp">
                                    <span>Mail Chimp</span>
                                </a>
                            </div>
                            <div class="col">
                                <a class="dropdown-icon-item" href="#">
                                    <img src="assets/images/brands/slack.png" alt="slack">
                                    <span>Slack</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dropdown d-none d-lg-inline-block ms-1">
                <button type="button" class="btn header-item noti-icon waves-effect" data-toggle="fullscreen">
                    <i class="bx bx-fullscreen"></i>
                </button>
            </div>

            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item noti-icon waves-effect" id="page-header-notifications-dropdown"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="bx bx-bell bx-tada"></i>
                    <span class="badge bg-danger rounded-pill">3</span>
                </button>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                    aria-labelledby="page-header-notifications-dropdown">
                    <div class="p-3">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="m-0" key="t-notifications"> <?php echo $language["Notifications"]; ?> </h6>
                            </div>
                            <div class="col-auto">
                                <a href="#!" class="small" key="t-view-all"> <?php echo $language["View_All"]; ?></a>
                            </div>
                        </div>
                    </div>
                    <div data-simplebar style="max-height: 230px;">
                        <a href="" class="text-reset notification-item">
                            <div class="media">
                                <div class="avatar-xs me-3">
                                    <span class="avatar-title bg-primary rounded-circle font-size-16">
                                        <i class="bx bx-cart"></i>
                                    </span>
                                </div>
                                <div class="media-body">
                                    <h6 class="mt-0 mb-1" key="t-your-order"><?php echo $language["Your_order_is_placed"]; ?></h6>
                                    <div class="font-size-12 text-muted">
                                        <p class="mb-1" key="t-grammer"><?php echo $language["languages_grammar"]; ?></p>
                                        <p class="mb-0"><i class="mdi mdi-clock-outline"></i> <span key="t-min-ago"><?php echo $language["3_min_ago"]; ?></span></p>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <a href="" class="text-reset notification-item">
                            <div class="media">
                                <img src="assets/images/users/avatar-3.jpg"
                                    class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                <div class="media-body">
                                    <h6 class="mt-0 mb-1">James Lemire</h6>
                                    <div class="font-size-12 text-muted">
                                        <p class="mb-1" key="t-simplified"><?php echo $language["simplified_English"]; ?></p>
                                        <p class="mb-0"><i class="mdi mdi-clock-outline"></i> <span key="t-hours-ago"><?php echo $language["1_hours_ago"]; ?></span></p>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <a href="" class="text-reset notification-item">
                            <div class="media">
                                <div class="avatar-xs me-3">
                                    <span class="avatar-title bg-success rounded-circle font-size-16">
                                        <i class="bx bx-badge-check"></i>
                                    </span>
                                </div>
                                <div class="media-body">
                                    <h6 class="mt-0 mb-1" key="t-shipped"><?php echo $language["Your_item_is_shipped"]; ?></h6>
                                    <div class="font-size-12 text-muted">
                                        <p class="mb-1" key="t-grammer"><?php echo $language["several_grammar"]; ?></p>
                                        <p class="mb-0"><i class="mdi mdi-clock-outline"></i> <span key="t-min-ago"><?php echo $language["3_min_ago"]; ?></span></p>
                                    </div>
                                </div>
                            </div>
                        </a>

                        <a href="" class="text-reset notification-item">
                            <div class="media">
                                <img src="assets/images/users/avatar-4.jpg"
                                    class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                <div class="media-body">
                                    <h6 class="mt-0 mb-1">Salena Layfield</h6>
                                    <div class="font-size-12 text-muted">
                                        <p class="mb-1" key="t-occidental"><?php echo $language["Cambridge_occidental"]; ?></p>
                                        <p class="mb-0"><i class="mdi mdi-clock-outline"></i> <span key="t-hours-ago"><?php echo $language["1_hours_ago"]; ?></span></p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="p-2 border-top d-grid">
                        <a class="btn btn-sm btn-link font-size-14 text-center" href="javascript:void(0)">
                            <i class="mdi mdi-arrow-right-circle me-1"></i> <span key="t-view-more"><?php echo $language["View_More"]; ?></span> 
                        </a>
                    </div>
                </div>
            </div>

            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img class="rounded-circle header-profile-user" src="assets/images/users/avatar-1.jpg"
                        alt="Header Avatar">
                    <span class="d-none d-xl-inline-block ms-1" key="t-henry"><?php echo ucfirst($_SESSION["username"]); ?></span>
                    <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <!-- item-->
                    <a class="dropdown-item" href="#"><i class="bx bx-user font-size-16 align-middle me-1"></i> <span key="t-profile"><?php echo $language["Profile"]; ?></span></a>
                    <a class="dropdown-item" href="#"><i class="bx bx-wallet font-size-16 align-middle me-1"></i> <span key="t-my-wallet"><?php echo $language["My_Wallet"]; ?></span></a>
                    <a class="dropdown-item d-block" href="#"><span class="badge bg-success float-end">11</span><i class="bx bx-wrench font-size-16 align-middle me-1"></i> <span key="t-settings"><?php echo $language["Settings"]; ?></span></a>
                    <a class="dropdown-item" href="#"><i class="bx bx-lock-open font-size-16 align-middle me-1"></i> <span key="t-lock-screen"><?php echo $language["Lock_screen"]; ?></span></a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-danger" href="logout.php"><i class="bx bx-power-off font-size-16 align-middle me-1 text-danger"></i> <span key="t-logout"><?php echo $language["Logout"]; ?></span></a>
                </div>
            </div>

           <!--  <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item noti-icon right-bar-toggle waves-effect">
                    <i class="bx bx-cog bx-spin"></i>
                </button>
            </div> -->

        </div>
    </div>
</header>

<!-- ========== Left Sidebar Start ========== -->
<div class="vertical-menu">

    <div data-simplebar class="h-100">

        <!--- Sidemenu -->
        <div id="sidebar-menu">
            <!-- Left Menu Start -->
            <ul class="metismenu list-unstyled" id="side-menu">
                <li class="menu-title" key="t-menu"><?php echo $language["Menu"]; ?></li>

                <li>
                    <a href="javascript: void(0);" class="waves-effect">
                        <i class="bx bx-home-circle"></i><span class="badge rounded-pill bg-info float-end">04</span>
                        <span key="t-dashboards"><?php echo $language["Dashboard"]; ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="index.php" key="t-default"><?php echo $language["Default"]; ?></a></li>
                        <li><a href="dashboard-saas.php" key="t-saas"><?php echo $language["Saas"]; ?></a></li>
                        <li><a href="dashboard-crypto.php" key="t-crypto"><?php echo $language["Crypto"]; ?></a></li>
                        <li><a href="dashboard-blog.php" key="t-blog"><?php echo $language["Blog"]; ?></a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-layout"></i>
                        <span key="t-layouts"><?php echo $language["Layouts"]; ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="true">
                        <li>
                            <a href="javascript: void(0);" class="has-arrow" key="t-vertical"><?php echo $language["Vertical"]; ?></a>
                            <ul class="sub-menu" aria-expanded="true">
                                <li><a href="layouts-light-sidebar.php" key="t-light-sidebar"><?php echo $language["Light_Sidebar"]; ?></a></li>
                                <li><a href="layouts-compact-sidebar.php" key="t-compact-sidebar"><?php echo $language["Compact_Sidebar"]; ?></a></li>
                                <li><a href="layouts-icon-sidebar.php" key="t-icon-sidebar"><?php echo $language["Icon_Sidebar"]; ?></a></li>
                                <li><a href="layouts-boxed.php" key="t-boxed-width"><?php echo $language["Boxed_Width"]; ?></a></li>
                                <li><a href="layouts-preloader.php" key="t-preloader"><?php echo $language["Preloader"]; ?></a></li>
                                <li><a href="layouts-colored-sidebar.php" key="t-colored-sidebar"><?php echo $language["Colored_Sidebar"]; ?></a></li>
                                <li><a href="layouts-scrollable.php" key="t-scrollable"><?php echo $language["Scrollable"]; ?></a></li>
                            </ul>
                        </li>

                        <li>
                            <a href="javascript: void(0);" class="has-arrow" key="t-horizontal"><?php echo $language["Horizontal"]; ?></a>
                            <ul class="sub-menu" aria-expanded="true">
                                <li><a href="layouts-horizontal.php" key="t-horizontal"><?php echo $language["Horizontal"]; ?></a></li>
                                <li><a href="layouts-hori-topbar-light.php" key="t-topbar-light"><?php echo $language["Topbar_Light"]; ?></a></li>
                                <li><a href="layouts-hori-boxed-width.php" key="t-boxed-width"><?php echo $language["Boxed_Width"]; ?></a></li>
                                <li><a href="layouts-hori-preloader.php" key="t-preloader"><?php echo $language["Preloader"]; ?></a></li>
                                <li><a href="layouts-hori-colored-header.php" key="t-colored-topbar"><?php echo $language["Colored_Header"]; ?></a></li>
                                <li><a href="layouts-hori-scrollable.php" key="t-scrollable"><?php echo $language["Scrollable"]; ?></a></li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <li class="menu-title" key="t-apps"><?php echo $language["Apps"]; ?></li>

                <li>
                    <a href="calendar.php" class="waves-effect">
                        <i class="bx bx-calendar"></i>
                        <span key="t-calendar"><?php echo $language["Calendar"]; ?></span>
                    </a>
                </li>

                <li>
                    <a href="chat.php" class="waves-effect">
                        <i class="bx bx-chat"></i>
                        <span key="t-chat"><?php echo $language["Chat"]; ?></span>
                    </a>
                </li>

                <li>
                    <a href="apps-filemanager.php" class="waves-effect">
                        <i class="bx bx-file"></i>
                        <span class="badge rounded-pill bg-success float-end" key="t-new"><?php echo $language["New"]; ?></span>
                        <span key="t-file-manager"><?php echo $language["File_Manager"]; ?></span>
                    </a>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-store"></i>
                        <span key="t-ecommerce"><?php echo $language["Ecommerce"]; ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="ecommerce-products.php" key="t-products"><?php echo $language["Products"]; ?></a></li>
                        <li><a href="ecommerce-product-detail.php" key="t-product-detail"><?php echo $language["Product_Detail"]; ?></a></li>
                        <li><a href="ecommerce-orders.php" key="t-orders"><?php echo $language["Orders"]; ?></a></li>
                        <li><a href="ecommerce-customers.php" key="t-customers"><?php echo $language["Customers"]; ?></a></li>
                        <li><a href="ecommerce-cart.php" key="t-cart"><?php echo $language["Cart"]; ?></a></li>
                        <li><a href="ecommerce-checkout.php" key="t-checkout"><?php echo $language["Checkout"]; ?></a></li>
                        <li><a href="ecommerce-shops.php" key="t-shops"><?php echo $language["Shops"]; ?></a></li>
                        <li><a href="ecommerce-add-product.php" key="t-add-product"><?php echo $language["Add_Product"]; ?></a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-bitcoin"></i>
                        <span key="t-crypto"><?php echo $language["Crypto"]; ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="crypto-wallet.php" key="t-wallet"><?php echo $language["Wallet"]; ?></a></li>
                        <li><a href="crypto-buy-sell.php" key="t-buy"><?php echo $language["Buy_Sell"]; ?></a></li>
                        <li><a href="crypto-exchange.php" key="t-exchange"><?php echo $language["Exchange"]; ?></a></li>
                        <li><a href="crypto-lending.php" key="t-lending"><?php echo $language["Lending"]; ?></a></li>
                        <li><a href="crypto-orders.php" key="t-orders"><?php echo $language["Orders"]; ?></a></li>
                        <li><a href="crypto-kyc-application.php" key="t-kyc"><?php echo $language["KYC_Application"]; ?></a></li>
                        <li><a href="crypto-ico-landing.php" key="t-ico"><?php echo $language["ICO_Landing"]; ?></a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-envelope"></i>
                        <span key="t-email"><?php echo $language["Email"]; ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="email-inbox.php" key="t-inbox"><?php echo $language["Inbox"]; ?></a></li>
                        <li><a href="email-read.php" key="t-read-email"><?php echo $language["Read_Email"]; ?></a></li>
                        <li>
                            <a href="javascript: void(0);">
                                <span class="badge rounded-pill badge-soft-success float-end" key="t-new"><?php echo $language["New"]; ?></span>
                                <span key="t-email-templates"><?php echo $language["Templates"]; ?></span>
                            </a>
                            <ul class="sub-menu" aria-expanded="true">
                                <li><a href="email-template-basic.php" key="t-basic-action"><?php echo $language["Basic_Action"]; ?></a></li>
                                <li><a href="email-template-alert.php" key="t-alert-email"><?php echo $language["Alert_Email"]; ?></a></li>
                                <li><a href="email-template-billing.php" key="t-bill-email"><?php echo $language["Billing_Email"]; ?></a></li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-receipt"></i>
                        <span key="t-invoices"><?php echo $language["Invoices"]; ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="invoices-list.php" key="t-invoice-list"><?php echo $language["Invoice_List"]; ?></a></li>
                        <li><a href="invoices-detail.php" key="t-invoice-detail"><?php echo $language["Invoice_Detail"]; ?></a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-briefcase-alt-2"></i>
                        <span key="t-projects"><?php echo $language["Projects"]; ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="projects-grid.php" key="t-p-grid"><?php echo $language["Projects_Grid"]; ?></a></li>
                        <li><a href="projects-list.php" key="t-p-list"><?php echo $language["Projects_List"]; ?></a></li>
                        <li><a href="projects-overview.php" key="t-p-overview"><?php echo $language["Project_Overview"]; ?></a></li>
                        <li><a href="projects-create.php" key="t-create-new"><?php echo $language["Create_New"]; ?></a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-task"></i>
                        <span key="t-tasks"><?php echo $language["Tasks"]; ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="tasks-list.php" key="t-task-list"><?php echo $language["Task_List"]; ?></a></li>
                        <li><a href="tasks-kanban.php" key="t-kanban-board"><?php echo $language["Kanban_Board"]; ?></a></li>
                        <li><a href="tasks-create.php" key="t-create-task"><?php echo $language["Create_Task"]; ?></a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bxs-user-detail"></i>
                        <span key="t-contacts"><?php echo $language["Contacts"]; ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="contacts-grid.php" key="t-user-grid"><?php echo $language["User_Grid"]; ?></a></li>
                        <li><a href="contacts-list.php" key="t-user-list"><?php echo $language["User_List"]; ?></a></li>
                        <li><a href="contacts-profile.php" key="t-profile"><?php echo $language["Profile"]; ?></a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="waves-effect">
                        <span class="badge rounded-pill bg-success float-end" key="t-new"><?php echo $language["New"]; ?></span>
                        <i class="bx bx-detail"></i>
                        <span key="t-blog"><?php echo $language["Blog"]; ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="blog-list.php" key="t-blog-list"><?php echo $language["Blog_List"]; ?></a></li>
                        <li><a href="blog-grid.php" key="t-blog-grid"><?php echo $language["Blog_Grid"]; ?></a></li>
                        <li><a href="blog-details.php" key="t-blog-details"><?php echo $language["Blog_Details"]; ?></a></li>
                    </ul>
                </li>

                <li class="menu-title" key="t-pages"><?php echo $language["Pages"]; ?></li>

                <li>
                    <a href="javascript: void(0);" class="waves-effect">
                        <span class="badge rounded-pill bg-success float-end" key="t-new"><?php echo $language["New"]; ?></span>
                        <i class="bx bx-user-circle"></i>
                        <span key="t-authentication"><?php echo $language["Authentication"]; ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="auth-login-1.php" key="t-login"><?php echo $language["Login"]; ?></a></li>
                        <li><a href="auth-login-2.php" key="t-login-2"><?php echo $language["Login"]; ?> 2</a></li>
                        <li><a href="auth-register-1.php" key="t-register"><?php echo $language["Register"]; ?></a></li>
                        <li><a href="auth-register-2.php" key="t-register-2"><?php echo $language["Register"]; ?> 2</a></li>
                        <li><a href="auth-recoverpw-1.php" key="t-recover-password"><?php echo $language["Recover_Password"]; ?></a></li>
                        <li><a href="auth-recoverpw-2.php" key="t-recover-password-2"><?php echo $language["Recover_Password"]; ?> 2</a></li>
                        <li><a href="auth-lock-screen.php" key="t-lock-screen"><?php echo $language["Lock_screen"]; ?></a></li>
                        <li><a href="auth-lock-screen-2.php" key="t-lock-screen-2"><?php echo $language["Lock_screen"]; ?> 2</a></li>
                        <li><a href="auth-confirm-mail.php" key="t-confirm-mail"><?php echo $language["Confirm_Mail"]; ?></a></li>
                        <li><a href="auth-confirm-mail-2.php" key="t-confirm-mail-2"><?php echo $language["Confirm_Mail"]; ?> 2</a></li>
                        <li><a href="auth-email-verification.php" key="t-email-verification"><?php echo $language["Email_verification"]; ?></a></li>
                        <li><a href="auth-email-verification-2.php" key="t-email-verification-2"><?php echo $language["Email_verification"]; ?> 2</a></li>
                        <li><a href="auth-two-step-verification.php" key="t-two-step-verification"><?php echo $language["Two_step_verification"]; ?></a></li>
                        <li><a href="auth-two-step-verification-2.php" key="t-two-step-verification-2"><?php echo $language["Two_step_verification"]; ?> 2</a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-file"></i>
                        <span key="t-utility"><?php echo $language["Utility"]; ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="pages-starter.php" key="t-starter-page"><?php echo $language["Starter_Page"]; ?></a></li>
                        <li><a href="pages-maintenance.php" key="t-maintenance"><?php echo $language["Maintenance"]; ?></a></li>
                        <li><a href="pages-comingsoon.php" key="t-coming-soon"><?php echo $language["Coming_Soon"]; ?></a></li>
                        <li><a href="pages-timeline.php" key="t-timeline"><?php echo $language["Timeline"]; ?></a></li>
                        <li><a href="pages-faqs.php" key="t-faqs"><?php echo $language["FAQs"]; ?></a></li>
                        <li><a href="pages-pricing.php" key="t-pricing"><?php echo $language["Pricing"]; ?></a></li>
                        <li><a href="pages-404.php" key="t-error-404"><?php echo $language["Error_404"]; ?></a></li>
                        <li><a href="pages-500.php" key="t-error-500"><?php echo $language["Error_500"]; ?></a></li>
                    </ul>
                </li>

                <li class="menu-title" key="t-components"><?php echo $language["Components"]; ?></li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-tone"></i>
                        <span key="t-ui-elements"><?php echo $language["UI_Elements"]; ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="ui-alerts.php" key="t-alerts"><?php echo $language["Alerts"]; ?></a></li>
                        <li><a href="ui-buttons.php" key="t-buttons"><?php echo $language["Buttons"]; ?></a></li>
                        <li><a href="ui-cards.php" key="t-cards"><?php echo $language["Cards"]; ?></a></li>
                        <li><a href="ui-carousel.php" key="t-carousel"><?php echo $language["Carousel"]; ?></a></li>
                        <li><a href="ui-dropdowns.php" key="t-dropdowns"><?php echo $language["Dropdowns"]; ?></a></li>
                        <li><a href="ui-grid.php" key="t-grid"><?php echo $language["Grid"]; ?></a></li>
                        <li><a href="ui-images.php" key="t-images"><?php echo $language["Images"]; ?></a></li>
                        <li><a href="ui-lightbox.php" key="t-lightbox"><?php echo $language["Lightbox"]; ?></a></li>
                        <li><a href="ui-modals.php" key="t-modals"><?php echo $language["Modals"]; ?></a></li>
                        <li><a href="ui-rangeslider.php" key="t-range-slider"><?php echo $language["Range_Slider"]; ?></a></li>
                        <li><a href="ui-session-timeout.php" key="t-session-timeout"><?php echo $language["Session_Timeout"]; ?></a></li>
                        <li><a href="ui-progressbars.php" key="t-progress-bars"><?php echo $language["Progress_Bars"]; ?></a></li>
                        <li><a href="ui-sweet-alert.php" key="t-sweet-alert"><?php echo $language["Sweet_Alert"]; ?></a></li>
                        <li><a href="ui-tabs-accordions.php" key="t-tabs-accordions"><?php echo $language["Tabs_&_Accordions"]; ?></a></li>
                        <li><a href="ui-typography.php" key="t-typography"><?php echo $language["Typography"]; ?></a></li>
                        <li><a href="ui-video.php" key="t-video"><?php echo $language["Video"]; ?></a></li>
                        <li><a href="ui-general.php" key="t-general"><?php echo $language["General"]; ?></a></li>
                        <li><a href="ui-colors.php" key="t-colors"><?php echo $language["Colors"]; ?></a></li>
                        <li><a href="ui-rating.php" key="t-rating"><?php echo $language["Rating"]; ?></a></li>
                        <li><a href="ui-notifications.php" key="t-notifications"><?php echo $language["Notifications"]; ?></a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="waves-effect">
                        <i class="bx bxs-eraser"></i>
                        <span class="badge rounded-pill bg-danger float-end">10</span>
                        <span key="t-forms"><?php echo $language["Forms"]; ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="form-elements.php" key="t-form-elements"><?php echo $language["Form_Elements"]; ?></a></li>
                        <li><a href="form-layouts.php" key="t-form-layouts"><?php echo $language["Form_Layouts"]; ?></a></li>
                        <li><a href="form-validation.php" key="t-form-validation"><?php echo $language["Form_Validation"]; ?></a></li>
                        <li><a href="form-advanced.php" key="t-form-advanced"><?php echo $language["Form_Advanced"]; ?></a></li>
                        <li><a href="form-editors.php" key="t-form-editors"><?php echo $language["Form_Editors"]; ?></a></li>
                        <li><a href="form-uploads.php" key="t-form-upload"><?php echo $language["Form_File_Upload"]; ?></a></li>
                        <li><a href="form-xeditable.php" key="t-form-xeditable"><?php echo $language["Form_Xeditable"]; ?></a></li>
                        <li><a href="form-repeater.php" key="t-form-repeater"><?php echo $language["Form_Repeater"]; ?></a></li>
                        <li><a href="form-wizard.php" key="t-form-wizard"><?php echo $language["Form_Wizard"]; ?></a></li>
                        <li><a href="form-mask.php" key="t-form-mask"><?php echo $language["Form_Mask"]; ?></a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-list-ul"></i>
                        <span key="t-tables">Tables</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="tables-basic.php" key="t-basic-tables"><?php echo $language["Basic_Tables"]; ?></a></li>
                        <li><a href="tables-datatable.php" key="t-data-tables"><?php echo $language["Data_Tables"]; ?></a></li>
                        <li><a href="tables-responsive.php" key="t-responsive-table"><?php echo $language["Responsive_Table"]; ?></a></li>
                        <li><a href="tables-editable.php" key="t-editable-table"><?php echo $language["Editable_Table"]; ?></a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bxs-bar-chart-alt-2"></i>
                        <span key="t-charts"><?php echo $language["Charts"]; ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="charts-apex.php" key="t-apex-charts"><?php echo $language["Apex_Charts"]; ?></a></li>
                        <li><a href="charts-echart.php" key="t-e-charts"><?php echo $language["E_Charts"]; ?></a></li>
                        <li><a href="charts-chartjs.php" key="t-chartjs-charts"><?php echo $language["Chartjs_Charts"]; ?></a></li>
                        <li><a href="charts-flot.php" key="t-flot-charts"><?php echo $language["Flot_Charts"]; ?></a></li>
                        <li><a href="charts-tui.php" key="t-ui-charts"><?php echo $language["Toast_UI_Charts"]; ?></a></li>
                        <li><a href="charts-knob.php" key="t-knob-charts"><?php echo $language["Jquery_Knob_Charts"]; ?></a></li>
                        <li><a href="charts-sparkline.php" key="t-sparkline-charts"><?php echo $language["Sparkline_Charts"]; ?></a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-aperture"></i>
                        <span key="t-icons"><?php echo $language["Icons"]; ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="icons-boxicons.php" key="t-boxicons">Boxicons</a></li>
                        <li><a href="icons-materialdesign.php" key="t-material-design">Material Design</a></li>
                        <li><a href="icons-dripicons.php" key="t-dripicons">Dripicons</a></li>
                        <li><a href="icons-fontawesome.php" key="t-font-awesome">Font awesome</a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-map"></i>
                        <span key="t-maps"><?php echo $language["Maps"]; ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="maps-google.php" key="t-g-maps"><?php echo $language["Google_Maps"]; ?></a></li>
                        <li><a href="maps-vector.php" key="t-v-maps"><?php echo $language["Vector_Maps"]; ?></a></li>
                        <li><a href="maps-leaflet.php" key="t-l-maps"><?php echo $language["Leaflet_Maps"]; ?></a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-share-alt"></i>
                        <span key="t-multi-level"><?php echo $language["Multi_Level"]; ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="true">
                        <li><a href="javascript: void(0);" key="t-level-1-1"><?php echo $language["Level_1.1"]; ?></a></li>
                        <li>
                            <a href="javascript: void(0);" class="has-arrow" key="t-level-1-2"><?php echo $language["Level_1.2"]; ?></a>
                            <ul class="sub-menu" aria-expanded="true">
                                <li><a href="javascript: void(0);" key="t-level-2-1"><?php echo $language["Level_2.1"]; ?></a></li>
                                <li><a href="javascript: void(0);" key="t-level-2-2"><?php echo $language["Level_2.2"]; ?></a></li>
                            </ul>
                        </li>
                    </ul>
                </li>

            </ul>
        </div>
        <!-- Sidebar -->
    </div>
</div>
<!-- Left Sidebar End -->