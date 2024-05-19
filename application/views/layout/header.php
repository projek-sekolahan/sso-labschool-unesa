<header id="page-topbar">
            <div class="navbar-header">
                <div class="d-flex">
                    <!-- LOGO -->
                    <div class="navbar-brand-box">
                        <a href="<?=base_url()?>" class="logo logo-dark">
                            <span class="logo-sm">
                                <img src="<?=base_url()?>assets/images/favicon.png" alt="" height="22">
                            </span>
                            <span class="logo-lg">
								<img src="<?=base_url()?>assets/images/favicon.png" alt="Logo" height="40" style="display: inline-block; vertical-align: middle;"> <!-- Tambahkan inline CSS untuk styling -->
            					<p class="text-dark h4 mt-2 mb-0 ml-2 flex-grow-1" style="display: inline-block; vertical-align: middle; margin-left: 10px;">SmartApps</p> <!-- Tambahkan inline CSS untuk styling -->
                            </span>
                        </a>

                        <a href="<?=base_url()?>" class="logo logo-light">
                            <span class="logo-sm">
                                <img src="<?=base_url()?>assets/images/favicon.png" alt="" height="22">
                            </span>
                            <span class="logo-lg">
								<img src="<?=base_url()?>assets/images/favicon.png" alt="Logo" height="40" style="display: inline-block; vertical-align: middle;"> <!-- Tambahkan inline CSS untuk styling -->
            					<p class="text-light h4 mt-2 mb-0 ml-2 flex-grow-1" style="display: inline-block; vertical-align: middle; margin-left: 10px;">SmartApps</p> <!-- Tambahkan inline CSS untuk styling -->
                            </span>
                        </a>
                    </div>

                    <button type="button" class="btn btn-sm px-3 font-size-16 header-item waves-effect"
                        id="vertical-menu-btn">
                        <i class="fa fa-fw fa-bars"></i>
                    </button>

                </div>

                <div class="d-flex">
                    <div class="dropdown d-inline-block">
                        <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img class="rounded-circle header-profile-user" src="<?=$content['imgloc']?>" alt="Header Avatar">
                            <span class="d-none d-xl-inline-block ms-1" key="t-henry"><?=$content['username']?></span>
                            <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <!-- item-->
                            <a class="dropdown-item btn-action" data-view="detail" data-action="/api/client/users/profile_pengguna" data-param="<?=$content['email']?>" href="#">
                                <i class="bx bx-user font-size-16 align-middle me-1"></i>
                                <span key="t-profile">Profile</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-danger btn-logout" data-action="/api/client/auth/logout" href="#">
                                <i class="bx bx-power-off font-size-16 align-middle me-1 text-danger"></i> 
                                <span key="t-logout">Logout</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>
