<?php echo $__env->make('landlord.frontend.partials.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="breadcrumb-area breadcrumb-padding section-bg-1 <?php if((in_array(request()->route()->getName(),['landlord.homepage','landlord.dynamic.page']) && $page_post->breadcrumb == 0 )): ?>
     d-none
<?php endif; ?>">
    <div class="breadcrumb-shapes">
        <img src="<?php echo e(!empty(get_static_option('background_left_shape_image')) ? get_attachment_image_by_id(get_static_option('background_left_shape_image'))['img_url'] ?? '' : asset('assets/img/banner/left-dot-line.png')); ?>" alt="">
        <img src="<?php echo e(!empty(get_static_option('background_right_shape_image')) ? get_attachment_image_by_id(get_static_option('background_right_shape_image'))['img_url'] ?? '' : asset('assets/img/banner/right-dot-line.png')); ?>" alt="">
    </div>
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="breadcrumb-contents center-text">
                    <h2 class="breadcrumb-contents-title"> <?php echo $__env->yieldContent('page-title'); ?> </h2>
                    <ul class="breadcrumb-contents-list mt-3">
                        <li class="breadcrumb-contents-list-item"> <a href="<?php echo e(route('landlord.homepage')); ?>"><?php echo e(__('Home')); ?></a> </li>
                        <li class="breadcrumb-contents-list-item"> <?php echo $__env->yieldContent('page-title'); ?> </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php echo $__env->yieldContent('content'); ?>
<?php echo $__env->make('landlord.frontend.partials.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php /**PATH /var/www/html/development/office/masharee/core/resources/views/landlord/frontend/frontend-page-master.blade.php ENDPATH**/ ?>