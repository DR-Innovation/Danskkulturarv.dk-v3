var dka = {
    init: function() {
        this.overlay();
    },
    formChange: function(embed_text) {
        $('.js-embed').text(embed_text);

        // Autoplay, Start- and end time offset. Only if type is not a picture.
        if (embed.type != 'billede') {

            var getPar = false; // True for & and false for ?
            var time_string = ''; // Time string to contain start and/or end time offset (for the iframe html).
            var text = $('.js-embed').text(); // Current iframe html
            var time_start = $('.timeoffset').val(); // Gets time start offset from input field
            var time_stop = $('.timeoffset_stop').val(); // Gets time stop offset from input field
            var time_offset_start = 0; // Start offset in seconds.
            var time_offset_stop = 0; // Stop offset in seconds.
            var autoplay_string = ''; // Autoplay string to set into iframe html.
            var thumbnail_string = ''; // Thumbnail instead of the actual material

            // Autoplay
            if ($('.js-autoplay').is(':checked')) {
                autoplay_string = '?autoplay=1';
                getPar = true;
            }

            /*if ($('.js-thumbnail').is(':checked')) {
                $('.time_option input').attr('disabled', 'disabled');
                $('.js-autoplay').attr('disabled', 'disabled');
                autoplay_string = '';
                time_string = '';
                thumbnail_string = '?thumbnail=1';
                getPar = true;
            } else {
                $('.time_option input').removeAttr('disabled');
                $('.js-autoplay').removeAttr('disabled');
            }*/

            // Robustness with regex. Finds time in seconds or minutes and seconds (min:sec)
            // If using thumbnail we don't need to check time start and time end since they are disabled then.
            if (!thumbnail_string)  {
                // Start time offset
                if (/^(([0-9]*):)?([0-9])+$/.test(time_start)) {
                    // Splitting minutes from seconds (if there are any)
                    if (time_start.indexOf(':') >= 0) {
                        var timesplit = time_start.split(':');
                        time_offset_start = (timesplit[0] * 60) + parseInt(timesplit[1]);
                    } else {
                        time_offset_start = time_start;
                    }

                    // If the time_start is more than 0 seconds, it will be added to the iframe html.
                    if (time_offset_start > 0) {
                        time_string = (getPar ? '&' : '?') + 'start=' + time_offset_start;
                        getPar = true;
                    } else {
                        //$('.timeoffset').val('0:00');
                    }
                } else {
                    //$('.timeoffset').val('0:00');
                }

                // Stop time offset
                if (/^(([0-9]*):)?([0-9])+$/.test(time_stop)) {
                    // Splitting minutes from seconds (if there are any)
                    if (time_stop.indexOf(':') >= 0) {
                        var timesplit = time_stop.split(':');
                        time_offset_stop = (timesplit[0] * 60) + parseInt(timesplit[1]);
                    } else {
                        time_offset_stop = time_stop;
                    }

                    // If the time is more than 0 seconds and greater than time_offset_start, it will be added to the iframe html.
                    if (time_offset_stop > 0 && time_offset_stop > time_offset_start) {
                        time_string += (getPar ? '&' : '?') + 'stop=' + time_offset_stop;
                        getPar = true;
                    } else {
                        //$('.timeoffset_stop').val('');
                    }
                } else {
                    //$('.timeoffset_stop').val('');
                }
            }

            if (autoplay_string || thumbnail_string || time_string) {
                $('.js-embed').text($('.js-embed').text().replace(/(\/embed)\/?([^"]*)(\")/, '$1/' + autoplay_string + thumbnail_string + time_string + '$3'));
            }
        }

        // Update Height and Width
        // Finds the width and height from the data-attributes.
        var width = $('.size-selector option:selected').data('width');
        var height = $('.size-selector option:selected').data('height');
        // Checks if the data-attributes are set else it should be custom.
        if (width && height && !isNaN(width) && !isNaN(height)) {
            $('.js-embed').text($('.js-embed').text().replace(/(width=\")[0-9]*(\")/, '$1' + width + '$2'));
            $('.js-embed').text($('.js-embed').text().replace(/(height=\")[0-9]*(\")/, '$1' + height + '$2' + (width == 100 && height == 100 ? ' style="width: 100%; height: 100%;"' : '')));
        } else if (!isNaN($('.custom_size .custom_height').val()) && !isNaN($('.custom_size .custom_width').val())) {
            // Custom size - Getting values from text inputs.
            if ($('.custom_size .custom_width').val() > 0 && $('.custom_size .custom_height').val() > 0) {
                $('.js-embed').text($('.js-embed').text().replace(/(width=\")[0-9]*(\")/, '$1' + $('.custom_size .custom_width').val() + '$2'));
                $('.js-embed').text($('.js-embed').text().replace(/(height=\")[0-9]*(\")/, '$1' + $('.custom_size .custom_height').val() + '$2'));
            }
        }
    },
    overlay: function() {
        // If someone trying to access /embed without an iframe.
        // Shows an overlay with the html of how to implement the material in an iframe. 
        var window_self = false;
        try {
            if (window.self === window.top) {
                window_self = true;
            }
        } catch (e) {

        }
        if (window_self) {
            // 
            document.querySelectorAll('.player')[0].outerHTML += '<div class="overlay">' +
                '<div class="info">' +
                '<h1>Embed</h1><textarea class="js-embed" onClick="this.select()" readonly>' + embed.html + '</textarea>' +
                '<p class="lead">Vær opmærksom på, at embedding af materialer fra ' + embed.blogname + ', kun er tilladt på <strong>udvalgte</strong> domæner.</p>' +
                '</div>';

            document.querySelectorAll('.info')[0].innerHTML += '<div class="custom_form"><div class="embed_customize">' +
                '<div class="options"><span>' + embed.size_string + '</span>' +
                '<span class="js-size">' +
                '<div class="custom_size"><input type="text" class="inline custom_width" placeholder="' + embed.width_string + '" /> x <input type="text" class="inline custom_height" placeholder="' + embed.height_string + '" /></div>' +
                '</span>' +
                '</div>' +
                '</div></div></div>' +
                '<a href="#" onClick="$(\'.overlay\').remove(); return false;" class="exit">&times;</a>';

            if (embed.type != 'billede') {
                document.querySelectorAll('.info .embed_customize')[0].innerHTML = '<div class="options time_option"><span>' + embed.start_string + '</span><input type="text" maxlength="10" value="0:00" placeholder="0:00" class="timeoffset" />' +
                    ' - <input type="text" maxlength="10" value="" class="timeoffset_stop" /></div>' +
                    '<div class="options autplay_option"><span>' + embed.autoplay_string + '</span><input style="float: right;" type="checkbox" class="js-autoplay" value="1" /></div>' +
                    //'<div class="options thumbnail_option"><span>' + embed.thumbnail_string + '</span><input style="float: right;" type="checkbox" class="js-thumbnail" value="1" /></div>' +
                    document.querySelectorAll('.info .embed_customize')[0].innerHTML;
            }

            // Adding selectbox with different sizes.
            // Different sizes coming from embed. Adding data-attributes like data-width and data-height.
            var sizes = '<select class="size-selector">';
            for (var i = 0; i < embed.sizes.length; i++) {
                sizes += '<option value="' + i + '" data-width="' + embed.sizes[i].width + '" data-height="' + embed.sizes[i].height + '">' + embed.sizes[i].label + '</option>';
            }
            sizes += '</select></div>';

            $(function() {
                var getPar = false; // True for & and false for ?

                $('.js-size').html(sizes + $('.js-size').html());
                var embed_text = $('.js-embed').text();

                $('.embed_customize').on('keyup', 'input', function() {
                    dka.formChange(embed_text);
                    return false;
                });

                $('.embed_customize').on('change', '.js-autoplay', function() {
                    dka.formChange(embed_text);
                });

                $('.embed_customize').on('change', '.js-thumbnail', function() {
                    dka.formChange(embed_text);
                });

                // If custom size is checked then it should be replaced with a textfield. 
                $('.embed_customize').on('change', '.size-selector', function() {
                    if ($('.size-selector option:selected').data('width') == 0 && $('.size-selector option:selected').data('height') == 0) {
                        $('.embed_customize .custom_size').show();
                    } else {
                        $('.embed_customize .custom_size').hide();
                    }
                    dka.formChange(embed_text);
                    return false;
                });
            });
        }
    }
}

dka.init();