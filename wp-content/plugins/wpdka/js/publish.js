(function($) {
    var wpdka_publish = {
        init: function() {
            this.setMaterialPublishState();
        },

        /**
         * Set material publish state
         */
        setMaterialPublishState: function() {
            $("#publishState").click(function(e) {
                e.preventDefault();
                var button = $(this);
                button.attr('disabled', true);
                $.ajax({
                    url: WPDKAPublish.ajaxurl,
                    data: {
                        action: 'wpdka_set_publish_state',
                        publishState: button.attr('data-dka-publish'),
                        object_guid: $('.single-material').attr('id'),
                        token: WPDKAPublish.token
                    },
                    dataType: 'JSON',
                    type: 'POST',
                    success: function(data) {
                        $('.single-material .publishinfo').html(data);
                        setTimeout(function() {
                            location.reload(true);
                        }, 1000);
                    },
                    error: function(errorThrown) {
                        $('.single-material .publishinfo').html(WPDKAPublish.error);
                        button.attr('disabled', false);
                        console.log(errorThrown);
                    }
                });

            });
        }
    };

    //Initiate class on page load
    $(document).ready(function() {
        wpdka_publish.init();
    });

})(jQuery);