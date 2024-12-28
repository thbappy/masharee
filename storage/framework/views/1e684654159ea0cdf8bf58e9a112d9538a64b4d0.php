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

<section class="featured-area section-bg-1" data-padding-top="<?php echo e($data['padding_top']); ?>"
         data-padding-bottom="<?php echo e($data['padding_bottom']); ?>" id="<?php echo e($data['section_id']); ?>">
    <div class="featured-shapes">
        <?php echo render_image_markup_by_attachment_id($data['bg_shape_image'], '','full',false); ?>

    </div>
    <div class="container">
        <div class="section-title">
            <?php echo $final_title; ?>


            <p class="section-para"> <?php echo e($data['subtitle']); ?> </p>
        </div>
        <div class="single-feature-wrapper">
            <div class="row g-0 mt-5">
                <?php if(array_key_exists('repeater_title_', $data['repeater_data'])): ?>
                    <?php $__currentLoopData = $data['repeater_data']['repeater_title_']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $info): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="single-feature">
                                <div class="single-feature-icon radius-10">
                                    <?php echo render_image_markup_by_attachment_id($data['repeater_data']['repeater_image_'][$key], '', 'full', false); ?>

                                </div>
                                <div class="single-feature-content mt-4">
                                    <h3 class="single-feature-content-title"><a
                                            href="<?php echo e($data['repeater_data']['repeater_button_link_'][$key] ?? '#'); ?>"> <?php echo e($data['repeater_data']['repeater_title_'][$key]); ?> </a>
                                    </h3>
                                    <p class="single-feature-content-para mt-3"> <?php echo e($data['repeater_data']['repeater_description_'][$key]); ?> </p>

                                    <?php if(!empty($data['repeater_data']['repeater_button_link_'][$key])): ?>
                                        <a href="<?php echo e($data['repeater_data']['repeater_button_link_'][$key] ?? '#'); ?>" class="single-feature-content-btn-explore mt-4"> <?php echo e($data['repeater_data']['repeater_button_text_'][$key]); ?> <i
                                                class="las la-arrow-right"></i> </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php /**PATH /var/www/html/development/office/masharee/core/plugins/PageBuilder/views/landlord/addons/header/FeaturesOne.blade.php ENDPATH**/ ?>