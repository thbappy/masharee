<script>
    $(document).on('submit', '#landlord-newsletter-form', function (e) {
        e.preventDefault();

        var email = $(this).find('input[type=email]').val();
        var button = $(this).find('button[type=submit]');
        var errrContaner = $('.form-message-show');

        $.ajax({
            url: "<?php echo e(route('landlord.frontend.newsletter.store.ajax')); ?>",
            type: "POST",
            data: {
                _token: "<?php echo e(csrf_token()); ?>",
                email: email
            },

            beforeSend: function() {
                button.text('Submitting..');
                button.attr('disabled', true);
            },
            success: function (data) {
                button.text('Subscribe');
                button.attr('disabled', false);
                $('input[type=email]').val('');

                errrContaner.html('<div class="alert alert-'+data.type+'">' + data.msg + '</div>');
            },
            error: function (data) {
                button.text('Subscribe');
                button.attr('disabled', false);
                $('input[type=email]').val('');

                var errors = data.responseJSON.errors;
                errrContaner.html('<div class="alert alert-danger">' + errors.email[0] + '</div>');
            }
        });
    });
</script>
<?php /**PATH /var/www/html/development/office/masharee/core/resources/views/components/custom-js/landlord-newsletter-store.blade.php ENDPATH**/ ?>