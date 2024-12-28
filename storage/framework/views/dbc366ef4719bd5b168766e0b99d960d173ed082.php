<header class="header-style-01">
    <!-- Menu area Starts -->
    <nav class="navbar navbar-area nav-absolute navbar-expand-lg">
        <div class="container nav-container">
            <div class="responsive-mobile-menu">
                <div class="logo-wrapper">
                    <a href="<?php echo e(url('/')); ?>" class="logo">
                        <?php if(!empty(get_static_option('site_logo'))): ?>
                            <?php echo render_image_markup_by_attachment_id(get_static_option('site_logo')); ?>

                        <?php else: ?>
                            <h2 class="site-title"><?php echo e(get_static_option('site_'.get_user_lang().'_title')); ?></h2>
                        <?php endif; ?>
                    </a>
                </div>
                <a href="javascript:void(0)" class="click-nav-right-icon">
                    <i class="las la-user-circle"></i>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#multi_tenancy_menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
            <div class="navbar-inner-all">
                <div class="collapse navbar-collapse" id="multi_tenancy_menu">
                    <ul class="navbar-nav">
                        <?php echo render_frontend_menu($primary_menu); ?>

                    </ul>
                </div>
                <div class="navbar-right-content show-nav-content">
                    <div class="single-right-content">
                        <?php if( Auth::guard('web')->check()): ?>
                            <div class="btn-wrapper">
                                <?php
                                    $route = auth()->guest() == 'admin' ? route('landlord.admin.dashboard') : route('landlord.user.home');
                                ?>
                                    <a class="cmn-btn cmn-btn-bg-1" href="<?php echo e($route); ?>"><?php echo e(get_static_option('default_dashboard_text') ?? __('Dashboard')); ?>  </a>
                                    <a class="cmn-btn cmn-btn-bg-1" href="<?php echo e(route('landlord.user.logout')); ?>"><?php echo e(get_static_option('default_logout_text') ?? __('Logout')); ?></a>
                            </div>
                        <?php else: ?>
                            <div class="btn-wrapper">
                                <?php if(get_static_option('default_menu_item') == get_static_option('default_login_text')): ?>
                                    <a href="<?php echo e(route('landlord.user.login')); ?>" class="cmn-btn cmn-btn-bg-1"><?php echo e(get_static_option('default_login_text') ?? __("Login")); ?></a>
                                 
                                <?php elseif(get_static_option('default_menu_item') == get_static_option('default_register_text')): ?>
                                    <a href="<?php echo e(route('landlord.user.register')); ?>" class="cmn-btn cmn-btn-bg-1"><?php echo e(get_static_option('default_register_text') ?? __("Get Started")); ?></a>
                                <?php else: ?>
                                    <a href="<?php echo e(route('landlord.user.register')); ?>" class="cmn-btn cmn-btn-bg-1"><?php echo e(get_static_option('default_register_text') ?? __("Get Started")); ?></a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <!-- Menu area end -->
</header>
<?php /**PATH /var/www/html/development/office/masharee/core/resources/views/landlord/frontend/partials/navbar.blade.php ENDPATH**/ ?>