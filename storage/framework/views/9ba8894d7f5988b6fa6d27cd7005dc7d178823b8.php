<?php
    $title =        $data['title'];
    $subtitle =     $data['subtitle'];
    $button_text =  $data['button_text'];
    $button_url =   $data['button_url'];
    $button_icon=   $data['button_icon'];
    $padding_top =  $data['padding_top'];
    $padding_bottom =  $data['padding_bottom'];
?>

<div class="banner-area banner-padding section-bg-1" data-padding-top="<?php echo e($padding_top); ?>"
     data-padding-bottom="<?php echo e($padding_bottom); ?>" id="<?php echo e($data['section_id']); ?>">
    <div class="banner-shpes">
        <?php echo render_image_markup_by_attachment_id($data['bg_shape_image']); ?>

        <?php echo render_image_markup_by_attachment_id($data['left_shape_image']); ?>

        <?php echo render_image_markup_by_attachment_id($data['right_shape_image']); ?>

    </div>
    <div class="container">
        <div class="row justify-content-between align-items-center flex-column-reverse flex-lg-row">
            <div class="col-lg-6 mt-4">
                <div class="banner-content-wrapper">
                    <div class="banner-content">
                        <?php
                            if (str_contains($data['title'], '{h}') && str_contains($data['title'], '{/h}'))
                                {
                                    $text = explode('{h}',$data['title']);

                                    $highlighted_word = explode('{/h}', $text[1])[0];

                                    $highlighted_text = '<span class="banner-content-title-shape title-shape">'. $highlighted_word .'</span>';
                                    $final_title = '<h1 class="banner-content-title">'.str_replace('{h}'.$highlighted_word.'{/h}', $highlighted_text, $data['title']).'</h1>';
                                } else {
                                    $final_title = '<h1 class="banner-content-title">'. $data['title'] .'</h1>';
                                }
                        ?>

                        <?php echo $final_title; ?>


                        <p class="banner-content-para mt-4"> <?php echo e($data['subtitle']); ?> </p>
                        <div class="btn-wrapper mt-4 mt-lg-5">
                            <a href="<?php echo e($data['button_url'] ?? 'javascript:void(0)'); ?>"
                               class="cmn-btn cmn-btn-bg-1"> <?php echo e($data['button_text']); ?> <i class="las la-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mt-4">
                <div class="banner-thumb-wrapper">
                    <div class="banner-radius-shape">
                        <?php echo render_image_markup_by_attachment_id($data['right_background_shape']); ?>

                    </div>
                    <div class="banner-thumb-content-wrapper">
                        <div class="banner-thumb-content">
                            <div class="banner-thumb-content-shapes">
                                <?php echo render_image_markup_by_attachment_id($data['right_floating_image_1']); ?>

                                <?php echo render_image_markup_by_attachment_id($data['right_floating_image_2']); ?>

                                <?php echo render_image_markup_by_attachment_id($data['right_floating_image_3']); ?>

                            </div>

                            <?php echo \App\Facades\ImageRenderFacade::getParent($data['right_foreground_image'], 'banner-thumb')->getGrandChild(is_lazy: true)->render(); ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH /var/www/html/development/office/masharee/core/plugins/PageBuilder/views/landlord/addons/header/HeaderOne.blade.php ENDPATH**/ ?>