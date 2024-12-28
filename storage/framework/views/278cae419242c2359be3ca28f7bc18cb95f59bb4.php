<?php
$line_shape = get_static_option('highlight_text_shape');
$highlighted_image = get_attachment_image_by_id($line_shape);
$highlighted_image = !empty($highlighted_image) ? $highlighted_image['img_url'] : '';
?>
<style>
    .title-shape::before {
        background-image: url("<?php echo e($highlighted_image); ?>") !important;
    }
</style>
<?php /**PATH /var/www/html/development/office/masharee/core/resources/views/components/landlord-others/highlighted-text.blade.php ENDPATH**/ ?>