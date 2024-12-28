<!DOCTYPE html>
<html dir="<?php echo e(\App\Facades\GlobalLanguage::user_lang_dir()); ?>"
      lang="<?php echo e(\App\Facades\GlobalLanguage::user_lang_slug()); ?>">
<head>
    <?php echo renderHeadStartHooks(); ?>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <?php if(isset($page_post) && $page_post->id == get_static_option('home_page')): ?>
        <title>
            <?php echo e(get_static_option('site_title')); ?>

            <?php if(!empty(get_static_option('site_tag_line'))): ?>
                - <?php echo e(get_static_option('site_tag_line')); ?>

            <?php endif; ?>
        </title>
        <?php echo render_site_seo(); ?>

    <?php else: ?>
        <?php if(!empty(SEOMeta::generate())): ?>
            <?php echo SEOMeta::generate(); ?>

        <?php else: ?>
            <title><?php echo $__env->yieldContent('page-title'); ?></title>
            <link rel="canonical" href="<?php echo e(canonical_url()); ?>"/>
        <?php endif; ?>

        <?php echo OpenGraph::generate(); ?>

        <?php echo Twitter::generate(); ?>

        <?php echo JsonLd::generate(); ?>

    <?php endif; ?>

    <?php echo load_google_fonts(); ?>

    <?php echo render_favicon_by_id(get_static_option('site_favicon')); ?>


    <link rel="stylesheet" href="<?php echo e(global_asset('assets/landlord/frontend/css/bootstrap.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/landlord/frontend/css/animate.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/landlord/frontend/css/slick.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/landlord/frontend/css/nice-select.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/landlord/frontend/css/line-awesome.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/common/css/jquery.ihavecookies.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/landlord/frontend/css/odometer.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/landlord/frontend/css/common.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(global_asset('assets/common/css/toastr.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(global_asset('assets/common/css/loader.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/landlord/frontend/css/style.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/landlord/common/css/helpers.css')); ?>">

    <link rel="stylesheet" href="<?php echo e(global_asset('assets/landlord/frontend/css/custom-style.css')); ?>">

    <?php if(\App\Facades\GlobalLanguage::user_lang_dir() == 'rtl'): ?>
        <link rel="stylesheet" href="<?php echo e(asset('assets/landlord/frontend/css/rtl.css')); ?>">
    <?php endif; ?>

    <?php echo $__env->make('landlord.frontend.partials.color-font-variable', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->yieldContent('style'); ?>



    <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.landlord-others.dynamic-style','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('landlord-others.dynamic-style'); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.landlord-others.highlighted-text','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('landlord-others.highlighted-text'); ?>
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

    <?php echo renderHeadEndHooks(); ?>


    <?php if(get_static_option('site_third_party_tracking_code')): ?>
        <script>
            <?php echo get_static_option('site_third_party_tracking_code'); ?>

        </script>
    <?php endif; ?>
</head>
<body>
    
<?php echo renderBodyStartHooks(); ?>

<?php echo $__env->make('tenant.frontend.partials.loader', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('landlord.frontend.partials.navbar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php /**PATH /var/www/html/development/office/masharee/core/resources/views/landlord/frontend/partials/header.blade.php ENDPATH**/ ?>