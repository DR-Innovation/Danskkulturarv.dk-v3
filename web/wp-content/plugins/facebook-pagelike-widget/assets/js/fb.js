(function($) {
    "use strict";

    var removeFBLoader = function() {
        var $loader = $('.fb_loader');
        if ($loader.length > 0) {
            $loader.fadeOut('slow', function() {
                $(this).remove(); 
                $('.fb-page').addClass('fb_plugin_rendered'); 
            });
        }
    };

    window.fbAsyncInit = window.fbAsyncInit || function() {
        if (typeof FB !== 'undefined') {
            FB.Event.subscribe('xfbml.render', function() {
                removeFBLoader();
            });
        }
    };

    $(document).ready(function() {
        // Initial parse for standard loads
        if (typeof FB !== 'undefined' && typeof FB.XFBML !== 'undefined') {
            FB.XFBML.parse();
        }

        // Fallback for slow connections
        setTimeout(removeFBLoader, 8000);
    });

    /** Admin Widget Logic **/
    function toggleWidthField(context) {
        $(context).find('.adapt-click').each(function() {
            var isChecked = $(this).is(':checked');
            $(this).closest('form').find('.width_option').toggle(!isChecked);
        });
    }

    $(document).ready(function() {
        toggleWidthField(document);
        $(document).on('change', '.adapt-click', function() {
            toggleWidthField($(this).closest('form'));
        });
        $(document).on('widget-added widget-updated', function(event, widget) {
            toggleWidthField(widget);
            if (typeof FB !== 'undefined' && typeof FB.XFBML !== 'undefined') {
                FB.XFBML.parse();
            }
        });
    });
})(jQuery);