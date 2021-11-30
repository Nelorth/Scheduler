jQuery(function ($) {
    // Make the shortcode textbox in the admin menu clickable.
    $('.shortcode-textbox').click(function () {
        $(this).select();
    });

    // Intercept a delete button click with a confirmation dialog.
    $('.delete-button').click(function () {
        if (!confirm(translator.confirmDelete)) {
            return false;
        }
    });
});