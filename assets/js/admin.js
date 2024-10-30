(function($) {

    "use strict";

    $(document).ready(function() {

        /* Color picker */
        $('.meks_ap-colors').wpColorPicker();


        $('body').on('click', '.meks-notice .notice-dismiss', function(){

            $.ajax( {
                url: ajaxurl,
                method: "POST",
                data: {
                    action: 'meks_remove_notification'
                }
            });

        });


    });

})(jQuery);