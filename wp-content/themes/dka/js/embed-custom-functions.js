var dka = {
    init: function() {
        this.overlay();
    },
    formChange: function(embed_text) {
        $('.js-embed').text(embed_text);

        if (embed.type != 'billede') {
            var time_string = '';
            var getPar = false; // True for & and false for ?

            var autoplay_string = '';
            // Autoplay
            if ($('.js-autoplay').is(':checked')) {
                autoplay_string = '?autoplay=1';
                getPar = true;
            }

            // Update Start.
            var text = $('.js-embed').text();
            var time = $('.timeoffset').val();
            var time_stop = $('.timeoffset_stop').val();
            // Robustness with regex. Finds time in seconds or minutes and seconds (min:sec)
            if (/^(([0-9]*):)?([0-9])+$/.test(time)) {
                var time_extra = 0;
                if (time.indexOf(':') >= 0) {
                    var timesplit = time.split(':');
                    time_extra = (timesplit[0] * 60) + parseInt(timesplit[1]);
                } else {
                    time_extra = time;
                }

                // If the time is more than 0 seconds, it will be added to the iframe html.
                if (time_extra > 0) {
                    time_string = (getPar ? '&' : '?') + 'start=' + time_extra;
                    getPar = true;
                } else {
                    //$('.timeoffset').val('0:00');
                }
            } else {
                //$('.timeoffset').val('0:00');
            }

            // Stop time
            if (/^(([0-9]*):)?([0-9])+$/.test(time_stop)) {
                var time_extra_stop = 0;
                if (time_stop.indexOf(':') >= 0) {
                    var timesplit = time_stop.split(':');
                    time_extra_stop = (timesplit[0] * 60) + parseInt(timesplit[1]);
                } else {
                    time_extra_stop = time_stop;
                }

                // If the time is more than 0 seconds, it will be added to the iframe html.
                if (time_extra_stop > 0) {
                    time_string += (getPar ? '&' : '?') + 'stop=' + time_extra_stop;
                    getPar = true;
                } else {
                    //$('.timeoffset_stop').val('');
                }
            } else {
                //$('.timeoffset_stop').val('');
            }

            if (autoplay_string || time_string) {
                $('.js-embed').text($('.js-embed').text().replace(/(\/embed)\/?([^"]*)(\")/, '$1/' + autoplay_string + time_string + '$3'));
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
            $('.js-embed').text($('.js-embed').text().replace(/(width=\")[0-9]*(\")/, '$1' + $('.custom_size .custom_width').val() + '$2'));
            $('.js-embed').text($('.js-embed').text().replace(/(height=\")[0-9]*(\")/, '$1' + $('.custom_size .custom_height').val() + '$2'));
        } else {
            $('.custom_size .custom_width').val('');
            $('.custom_size .custom_height').val('');
        }
    },
    overlay: function() {
        // If someone trying to access /embed without an iframe.
        // Shows an overlay with the html of how to implement the material in an iframe. 
        try {
            if (window.self === window.top) {
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
                    '<a href="#" onClick="this.parentNode.parentNode.removeChild(this.parentNode); return false;" class="exit">&times;</a>';

                if (embed.type != 'billede') {
                    document.querySelectorAll('.info .embed_customize')[0].innerHTML = '<div class="options"><span>' + embed.start_string + '</span><input type="text" maxlength="10" value="0:00" placeholder="0:00" class="timeoffset" />' +
                        ' - <input type="text" maxlength="10" value="" class="timeoffset_stop" /></div>' +
                        '<div class="options"><span>' + embed.autoplay_string + '</span><input style="float: right;" type="checkbox" class="js-autoplay" value="1" /></div>' +
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

                    // If custom size is checked then it should be replaced with a textfield. 
                    $('.embed_customize').on('change', '.size-selector', function() {
                        dka.formChange(embed_text);
                        return false;
                    });
                });
            }
        } catch (e) {}
    }
}

dka.init();