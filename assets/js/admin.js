/**
 * Casdoor Admin JavaScript
 */
(function($) {
    'use strict';

    $(window).on('load', function() {
        // Initialize accordion if jQuery UI is available
        if (typeof $.fn.accordion === 'function') {
            $('#accordion').accordion({
                heightStyle: 'content',
                collapsible: true,
                active: false
            });

            // Handle hash navigation
            var hash = window.location.hash;
            if (hash) {
                var anchor = $(hash);
                if (anchor.length > 0) {
                    var index = anchor.parent().index();
                    if (index >= 0) {
                        $('#accordion').accordion('option', 'active', Math.floor(index / 2));
                    }
                }
            }
        } else {
            // Fallback: simple toggle functionality
            $('#accordion h3').on('click', function() {
                $(this).next('div').slideToggle(200);
            });
        }
    });

})(jQuery);
