jQuery( document ).ready( function ($) {
    // run only if mobil-view is not used.
    if( false === $('.rh-categories-filter .mobile-button-close').is(':visible') ) {
        // add event on each checkbox in filter.
        $('.rh-categories-filter input[type="checkbox"]').on("change", function() {
            // get the filter-object.
            let obj = $(this).parents('.rh-categories-filter');

            // mark the label.
            if( this.checked ) {
                $(this).parents('label').addClass('active');
            }
            else {
                $(this).parents('label').removeClass('active');
            }

            // get all checked categories.
            let categories = [];
            $('.rh-categories-filter .rh-categories input[type="checkbox"]:checked').each(function() {
                categories.push( $(this).val() );
            });

            // get all checked tags.
            let tags = [];
            $('.rh-categories-filter .rh-tags input[type="checkbox"]:checked').each(function() {
                tags.push( $(this).val() );
            });

            // start request.
            $.ajax({
                type: "POST",
                url: rh_categories_js.ajax_url,
                data: {
                    'action': 'rh_categories_filter',
                    'nonce': rh_categories_js.filter_nonce,
                    'categories': categories,
                    'tags': tags
                },
                beforeSend: function() {
                    obj.addClass('loading');
                    if( tags.length > 0 || categories.length > 0 ) {
                        obj.addClass('rh-active-filter');
                    }
                    else {
                        obj.removeClass('rh-active-filter');
                    }
                },
                success: function( data ) {
                    $(".elementor-widget-loop-grid").html(data);
                    obj.removeClass('loading');
                }
            });
        });
    }
});