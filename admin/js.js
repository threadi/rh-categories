jQuery( document ).ready( function ($) {
    // handler for color-picker.
    if ( jQuery.isFunction( jQuery.fn.wpColorPicker ) ) {
        jQuery( 'input.rh-category-color-picker' ).wpColorPicker();
    }

    // image handling: on upload button click.
    $('body.wp-admin').on( 'click', '.rh-category-img-upl', function(e){
        e.preventDefault();
        let button = $(this),
            custom_uploader = wp.media({
                title: 'Insert image',
                library : {
                    type : 'image'
                },
                button: {
                    text: 'Use this image' // button label text
                },
                multiple: false
            }).on('select', function() { // it also has "open" and "close" events
                var attachment = custom_uploader.state().get('selection').first().toJSON();
                button.html('<img src="' + attachment.url + '">').next().show().next().val(attachment.id);
            }).open();

    });

    // image handling: on remove button click
    $('body.wp-admin').on('click', '.rh-category-img-remove', function(e){
        e.preventDefault();
        let button = $(this);
        button.next().val('');
        button.parents('td').find('img').remove();
        button.hide().prev().html('Upload image');
    });
});