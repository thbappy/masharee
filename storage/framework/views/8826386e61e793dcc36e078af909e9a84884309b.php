<?php
    if (str_contains($data['title'], '{h}') && str_contains($data['title'], '{/h}'))
    {
        $text = explode('{h}',$data['title']);

        $highlighted_word = explode('{/h}', $text[1])[0];

        $highlighted_text = '<span class="section-shape title-shape">'. $highlighted_word .'</span>';
        $final_title = '<h2 class="title">'.str_replace('{h}'.$highlighted_word.'{/h}', $highlighted_text, $data['title']).'</h2>';
    } else {
        $final_title = '<h2 class="title">'. $data['title'] .'</h2>';
    }
?>

<style>
    .all-features a{
        color: var(--main-color-one);
    }
    .all-features a:hover{
        border-bottom: 1px solid var(--main-color-one);
    }
    .plan-description {
        background: var(--section-bg-1);
    }
    .plan-description p{
        text-align: justify;
        hyphens: none;
    }
    .single-price:hover .plan-description {
        background: #ffffff;
    }
</style>

<section class="pricing-area section-bg-1" data-padding-top="<?php echo e($data['padding_top']); ?>"
         data-padding-bottom="<?php echo e($data['padding_bottom']); ?>" id="<?php echo e($data['section_id']); ?>">
    <div class="container">
        <div class="section-title">
            <?php echo $final_title; ?>

            <p class="section-para"> <?php echo e($data['subtitle']); ?> </p>
        </div>

        <div class="row justify-content-center mt-4">
            <div class="col-lg-6 mt-4">
                <div class="pricing-tab-list center-text">
                    <ul class="tabs price-tab radius-10">
                        <?php $__currentLoopData = ($data['plan_types']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $type_data_tab = match ($type) {
                                    0 => 'month',
                                    1 => 'year',
                                    2 => 'lifetime'
                                };
                            ?>
                            <li data-tab="tab-<?php echo e($type_data_tab); ?>" class="price-tab-list <?php echo e($loop->first ? 'active' : ''); ?>"> <?php echo e(\App\Enums\PricePlanTypEnums::getText($type)); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php $__currentLoopData = $data['all_price_plan']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan_type => $plan_items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $id= '';
                $active = '';
                $period = '';
                if($plan_type == 0){
                    $id = 'month';
                    $active = 'show active';
                    $period = __('/mo');
                }elseif($plan_type == 1){
                    $id = 'year';
                     $period = __('/yr');
                }else{
                     $id = 'lifetime';
                      $period = __('/lt');
                }

                $content_center_class = count($plan_items) <= 3 ? 'justify-content-center' : '';
            ?>

            <div class="tab-content-item <?php echo e($active); ?>" id="tab-<?php echo e($id); ?>">
                <div class="row <?php echo e($content_center_class); ?> mt-4">
                    <?php $__currentLoopData = $plan_items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $price_plan_item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $featured_condition = $key == 1 ? 'active' : '';
                        ?>

                        <div class="col-lg-4 col-md-6 mt-4">
                            <div class="single-price radius-10 <?php echo e($featured_condition); ?>">
                                <span class="single-price-sub-title mb-5 radius-5"> <?php echo e($price_plan_item->package_badge); ?> </span>
                                <div class="single-price-top center-text">
                                    <span
                                        class="single-price-top-plan"> <?php echo e($price_plan_item->title); ?> </span>
                                    <h3 class="single-price-top-title mt-4"> <?php echo e(amount_with_currency_symbol($price_plan_item->price)); ?>

                                        <sub><?php echo e($period); ?></sub></h3>
                                </div>
                                <ul class="single-price-list mt-4">
                                    <?php if(!empty($price_plan_item->page_permission_feature)): ?>
                                        <li class="single-price-list-item">
                                            <span class="check-icon"> <i class="las la-check"></i> </span>
                                            <span>
                                                <strong>
                                                    <?php if($price_plan_item->page_permission_feature < 0): ?>
                                                        <?php echo e(__('Page Unlimited')); ?>

                                                    <?php else: ?>
                                                         <?php echo e(__(sprintf('Page %d', $price_plan_item->page_permission_feature) )); ?>

                                                    <?php endif; ?>
                                                </strong>
                                            </span>
                                        </li>
                                    <?php endif; ?>

                                    <?php if(!empty($price_plan_item->product_permission_feature)): ?>
                                        <li class="single-price-list-item">
                                            <span class="check-icon"> <i class="las la-check"></i> </span>
                                            <span>
                                                <strong>
                                                    <?php if($price_plan_item->product_permission_feature < 0): ?>
                                                        <?php echo e(__('Product Unlimited')); ?>

                                                    <?php else: ?>
                                                        <?php echo e(__(sprintf('Product %d',$price_plan_item->product_permission_feature) )); ?>

                                                    <?php endif; ?>
                                                </strong>
                                            </span>
                                        </li>
                                    <?php endif; ?>

                                    <?php if(!empty($price_plan_item->blog_permission_feature)): ?>
                                        <li class="single-price-list-item">
                                            <span class="check-icon"> <i class="las la-check"></i> </span>
                                            <span>
                                                <strong>
                                                    <?php if($price_plan_item->blog_permission_feature < 0): ?>
                                                        <?php echo e(__('Blog Unlimited')); ?>

                                                    <?php else: ?>
                                                        <?php echo e(__(sprintf('Blog %d',$price_plan_item->blog_permission_feature) )); ?>

                                                    <?php endif; ?>
                                                </strong>
                                            </span>
                                        </li>
                                    <?php endif; ?>

                                        <?php if(!empty($price_plan_item->storage_permission_feature)): ?>
                                            <li class="single-price-list-item">
                                                <span class="check-icon"> <i class="las la-check"></i> </span>
                                                <span>
                                                    <strong>
                                                        <?php if($price_plan_item->storage_permission_feature < 0): ?>
                                                            <?php echo e(__('Storage Unlimited')); ?>

                                                        <?php else: ?>
                                                            <?php echo e(__(sprintf('Storage %d MB',$price_plan_item->storage_permission_feature) )); ?>

                                                        <?php endif; ?>
                                                    </strong>
                                                </span>
                                            </li>
                                        <?php endif; ?>
                                </ul>

                                <?php if(!empty($price_plan_item->description)): ?>
                                    <div class="mt-4 p-3 rounded plan-description">
                                        <p><?php echo $price_plan_item->description; ?></p>
                                    </div>
                                <?php endif; ?>

                                <div class="btn-wrapper text-center all-features mt-4 mt-lg-4">
                                    <a href="<?php echo e(route('landlord.frontend.plan.order',$price_plan_item->id)); ?>"><?php echo e(__('View All Features')); ?></a>
                                </div>
                                <div class="btn-wrapper mt-4 mt-lg-4">
                                    <?php
                                        $buy_text = $price_plan_item->price > 0 ? __('Buy Now') : __('Get Now');
                                    ?>
                                    <?php if($price_plan_item->has_trial == true): ?>
                                        <div class="d-flex justify-content-center">
                                            <a href="<?php echo e(route('landlord.frontend.plan.order',$price_plan_item->id)); ?>" class="cmn-btn cmn-btn-outline-one color-one w-100 mx-1">
                                                <?php echo e($buy_text); ?> </a>

                                            <a href="<?php echo e(route('landlord.frontend.plan.view',[$price_plan_item->id, 'trial'])); ?>" class="cmn-btn cmn-btn-outline-one color-one w-100 mx-1">
                                                <?php echo e(__('Try Now')); ?> </a>
                                        </div>
                                    <?php else: ?>
                                        <a href="<?php echo e(route('landlord.frontend.plan.order',$price_plan_item->id)); ?>" class="cmn-btn cmn-btn-outline-one color-one w-100">
                                            <?php echo e($buy_text); ?> </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</section>
<?php /**PATH /var/www/html/development/office/masharee/core/plugins/PageBuilder/views/landlord/addons/common/price-plan.blade.php ENDPATH**/ ?>