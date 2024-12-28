<style>
    :root {
        --main-color-one: <?php echo e(get_static_option('main_color_one') ?? 'rgb(240, 72, 83)'); ?>;
        --main-color-one-rgb: <?php echo e(get_static_option('main_color_one') ? str_replace(['rgb','(',')'], '',get_static_option('main_color_one')) : '240, 72, 83'); ?>;
        --main-color-two: <?php echo e(get_static_option('main_color_two') ?? '#FF805D'); ?>;
        --main-color-three: <?php echo e(get_static_option('main_color_three') ?? '#599A8D'); ?>;
        --main-color-four: <?php echo e(get_static_option('main_color_four') ?? '#1E88E5'); ?>;
        --secondary-color: <?php echo e(get_static_option('secondary_color') ?? '#F7A3A8'); ?>;
        --secondary-color-two: <?php echo e(get_static_option('secondary_color_two') ?? '#ffdcd2'); ?>;
        --section-bg-1: <?php echo e(get_static_option('section_bg_1') ?? '#FFFBFB'); ?>;
        --section-bg-2: <?php echo e(get_static_option('section_bg_2') ?? '#FFF6EE'); ?>;
        --section-bg-3: <?php echo e(get_static_option('section_bg_3') ?? '#F4F8FB'); ?>;
        --section-bg-4: <?php echo e(get_static_option('section_bg_4') ?? '#F2F3FB'); ?>;
        --section-bg-5: <?php echo e(get_static_option('section_bg_5') ?? '#F9F5F2'); ?>;
        --section-bg-6: <?php echo e(get_static_option('section_bg_6') ?? '#E5EFF8'); ?>;
        --heading-color: <?php echo e(get_static_option('heading_color') ?? '#333333'); ?>;
        --body-color: <?php echo e(get_static_option('body_color') ?? '#666666'); ?>;
        --light-color: <?php echo e(get_static_option('light_color') ?? '#666666'); ?>;
        --extra-light-color: <?php echo e(get_static_option('extra_light_color') ?? '#888888'); ?>;
        --review-color: <?php echo e(get_static_option('review_color') ?? '#FABE50'); ?>;
        --new-color: <?php echo e(get_static_option('new_color') ?? '#5AB27E'); ?>;

        --heading-font: "<?php echo e(get_static_option('heading_font_family')); ?>",sans-serif;
        --body-font:"<?php echo e(get_static_option('body_font_family')); ?>",sans-serif;
    }
</style>
<?php /**PATH /var/www/html/development/office/masharee/core/resources/views/landlord/frontend/partials/color-font-variable.blade.php ENDPATH**/ ?>