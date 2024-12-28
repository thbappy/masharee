<?php $__env->startSection('title'); ?>
    <?php echo e(__('User Login')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('User Login')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <!-- sign-in area start -->
    <div class="sign-in-area-wrapper padding-top-100 padding-bottom-100">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-9 col-lg-7 col-xl-6 col-xxl-5">
                    <div class="sign-in register signIn-signUp-wrapper">
                        <h4 class="title signin-contents-title"><?php echo e(__('Sign In')); ?></h4>
                        <div class="form-wrapper custom--form mt-5">
                            <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.error-msg','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('error-msg'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
                            <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.flash-msg','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('flash-msg'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
                            <form action="" method="post" enctype="multipart/form-data" class="account-form" id="login_form_order_page">
                                <div class="error-wrap"></div>
                                <div class="form-group single-input">
                                    <label for="exampleInputEmail1" class="label-title mb-3"><?php echo e(__('Username')); ?><span class="required">*</span></label>
                                    <input type="text" name="username" class="form-control form--control" id="exampleInputEmail1" placeholder="<?php echo e(__('Type your username')); ?>">
                                </div>
                                <div class="form-group single-input mt-4">
                                    <label for="exampleInputEmail1" class="label-title mb-3"><?php echo e(__('Password')); ?><span class="required">*</span></label>
                                    <input type="password" name="password" class="form-control form--control" id="exampleInputPassword1" placeholder="<?php echo e(__('Password')); ?>">
                                </div>

                                <div class="form-group single-input form-check mt-4">
                                    <div class="box-wrap">
                                        <div class="left">
                                            <div class="checkbox-inlines">
                                                <input type="checkbox" name="remember" class="form-check-input check-input" id="exampleCheck1">
                                                <label class="form-check-label checkbox-label" for="exampleCheck1"><?php echo e(__('Remember me')); ?></label>
                                            </div>
                                        </div>
                                        <div class="right forgot-password">
                                            <a href="<?php echo e(route('tenant.user.forget.password')); ?>" class="forgot-btn"><?php echo e(__('Forgot Password?')); ?></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="btn-wrapper mt-4">
                                    <button type="submit" id="login_btn" class="cmn-btn cmn-btn-bg-1 w-100"><?php echo e(__('Sign In')); ?></button>

                                    <?php if(moduleExists('SmsGateway') && get_static_option('otp_login_status')): ?>
                                        <p class="font-weight-bold text-center my-2"><?php echo e(__('Or')); ?></p>
                                        <a href="<?php echo e(route(route_prefix().'user.login.otp')); ?>" class="cmn-btn cmn-btn-outline-one color-one w-100" style="padding: 10px 25px"><?php echo e(__('Sign In with OTP')); ?></a>
                                    <?php endif; ?>
                                </div>
                            </form>
                            <p class="info mt-3"><?php echo e(__("Do not have an account")); ?> <a href="<?php echo e(route(route_prefix().'user.register')); ?>" class="active"> <strong><?php echo e(__('Sign up')); ?></strong> </a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- sign-in area end -->
<?php $__env->stopSection(); ?>
<?php $__env->startSection('scripts'); ?>
   <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.custom-js.ajax-login','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('custom-js.ajax-login'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('landlord.frontend.frontend-page-master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/development/office/masharee/core/resources/views/landlord/frontend/user/login.blade.php ENDPATH**/ ?>