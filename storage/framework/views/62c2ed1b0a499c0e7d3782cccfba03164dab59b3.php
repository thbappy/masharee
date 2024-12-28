<?php
    $dynamic_style = 'assets/landlord/frontend/css/dynamic-style.css';
?>
<?php if(file_exists($dynamic_style)): ?>
    <link rel="stylesheet" href="<?php echo e(asset($dynamic_style)); ?>">
<?php endif; ?>
<?php /**PATH /var/www/html/development/office/masharee/core/resources/views/components/landlord-others/dynamic-style.blade.php ENDPATH**/ ?>