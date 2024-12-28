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

<section class="works-area section-bg-1" data-padding-top="<?php echo e($data['padding_top']); ?>" data-padding-bottom="<?php echo e($data['padding_bottom']); ?>" id="<?php echo e($data['section_id']); ?>">
    <div class="container">
        <div class="section-title">
            <?php echo $final_title; ?>

            <p class="section-para"> <?php echo e($data['subtitle']); ?> </p>
        </div>
        <div class="works-wrapper">
            <div class="row mt-4">
                <?php if(array_key_exists('repeater_title_', $data['repeater_data'])): ?>
                    <?php $__currentLoopData = $data['repeater_data']['repeater_title_']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $info): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="col-lg-4 col-md-6 mt-4">
                            <div class="single-works center-text bg-white">
                                <div class="single-works-content">
                                    <span class="single-works-content-number"> <?php echo e($data['repeater_data']['repeater_number_'][$key] ?? ''); ?> </span>
                                    <h3 class="single-works-content-title mt-3"> <?php echo e($data['repeater_data']['repeater_title_'][$key] ?? ''); ?> </h3>
                                    <p class="single-works-content-para mt-3">  <?php echo e($data['repeater_data']['repeater_description_'][$key] ?? ''); ?> </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php /**PATH /var/www/html/development/office/masharee/core/plugins/PageBuilder/views/landlord/addons/common/how_it_works.blade.php ENDPATH**/ ?>