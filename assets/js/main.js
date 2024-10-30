(function($) {

    "use strict";

    $(document).ready(function() {


        if (!$('.meks-ap-player').length) {
            return;
        }

        if ( !$('.meks-ap-player').hasClass('meks-void-player') ) {
           window.meks_ap_detect_audio($('body'));
       }



        /* Toggle the player */

        $('body').on('click', '.meks-ap-toggle', function(e) {
            e.preventDefault();
            $('.meks-ap').toggleClass('meks-ap-collapsed');
        });

         /* Hide volume button on mobile devices */
        var isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
        if (isMobile) {
           $('.meks-ap .mejs-volume-button').hide();
        }

    });

    window.meks_ap_detect_audio = function(container) {

        window.meks_ap_player_status = '';

        /* Detect first playable source and initialize the player */
        $.each(meks_ap_settings.selectors, function(selector, item) {
           
            if (!container.find(selector).length) {
                return;
            }

            var source = '';

            if (selector == 'iframe') {

                // var elements_to_check = Array.from( item.match );
                // elements_to_check = elements_to_check.join( ',' );
                var type = '';

                item.match.forEach(function(element, item){
                    var found = container.find( element );
                    if ( found.length ) {
                        source = found;
                        type = element;
                        return;
                    }
                    return;
                })

                if ( source == '' || source == undefined ) {
                    return
                }

                source = source.first();
                
                type = type.match(/'(.*\.)/);
                if ( type != null ) {
                    item.type = type[1].slice( 0, (type[1].length - 1) );
                }

                item.type += ' custom';

            } else {

                if (item.element == 'self') {
                    source = container.find(selector).first();
                } else {
                    source = container.find(selector).first().hide().find(item.element);
                }
            }


            if (item.type == 'audio') {
                window.meks_ap_player_status = 'audio';
            } else if (item.type == 'custom') {
                window.meks_ap_player_status = 'custom';
            } else {
               window.meks_ap_player_status = item.type;
            }

            window.meks_ap_player_init(source);

            return false;

        });


    };


    window.meks_ap_player_init = function(source) {

        source.appendTo('.meks-ap-player').attr('id', 'meks-ap-player-source').attr('preload', 'auto').attr('width', '').attr('height', '').removeClass();

        var player = '';

        if ($('.meks-ap-player audio').length) {

            player = new MediaElementPlayer('meks-ap-player-source', {
                'classPrefix': 'mejs-',
                'isVideo': false,
                'setDimensions': false,
                'alwaysShowControls': true,
                'audioVolume': 'vertical',
                'startVolume': meks_ap_settings.player.volume,
                'skipBackInterval': 15,
                'jumpForwardInterval': 15,
                'timeAndDurationSeparator': '<span> / </span>',
                'features': Array.from(meks_ap_settings.player.controls),
                'speeds': ['2', '1.5', '1.25', '0.75'],
                'defaultSpeed': '1',
                success: function(mediaElement, originalNode, instance) {

                    mediaElement.addEventListener('ended', function(e) {
                        $(document).trigger('meks_ap_player_ended');
                    }, false);

                }
            });

        }

        window.meks_ap_player = player;

        /* Player jump on specified time */
        $(document).on('click', '.meks-jump-player', function() {

            var $this = $(this);
            var minutes = $this.data('minutes') * 60 * 1000;
            var seconds = $this.data('seconds') * 1000;
            
            var total = minutes + seconds;
            total =  Number(total / 1000).toFixed(3);

            window.meks_ap_player.media.setCurrentTime( Math.min( player.media.duration, total ));
                       
            // paly
            if ( window.meks_ap_player.media.getPaused() ) {
                window.meks_ap_player.media.play();
            }

            // marker play functionality when player is 100% ready
            var playerInterval = setInterval(() => {
                if (window.meks_ap_player.media.getReadyState() === 4 ) {
                    $('body').find('#meks-ap-player .mejs-playpause-button').trigger('click');
                    window.meks_ap_player.media.play();
                    clearInterval(playerInterval);
                }
            }, 500);

        });

        $(document).trigger('meks_ap_player_loaded');
    };




})(jQuery);