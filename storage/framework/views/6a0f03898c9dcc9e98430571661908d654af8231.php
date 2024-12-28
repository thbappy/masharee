<?php
    if(!isset($page_post)){
        return;
    }
?>

<?php echo \Plugins\PageBuilder\PageBuilderSetup::render_frontend_pagebuilder_content_for_dynamic_page('dynamic_page',$page_post->id); ?>


<?php if(Auth::guard('admin')->user()): ?>
    <?php echo $__env->make('tenant.frontend.partials.inpage-edit',['page_post' => $page_post], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php endif; ?>
<?php /**PATH /var/www/html/development/office/masharee/core/resources/views/tenant/frontend/partials/pages-portion/dynamic-page-builder-part.blade.php ENDPATH**/ ?>