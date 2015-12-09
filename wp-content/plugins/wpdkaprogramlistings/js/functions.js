function daysInMonth(month, year) {
    return new Date(year, month, 0).getDate();
}

var dayOptions = {};
var monthOptions = {};

function changeNumberDays() {
    if ($('.programlisting-month select').val() > 0 && $('.programlisting-year select').val() > 0) {
        var currentDay = $('.programlisting-day select').val();
        var currentMonth = $('.programlisting-month select').val();
        var currentYear = $('.programlisting-year select').val();
        var days = daysInMonth($('.programlisting-month select').val(), $('.programlisting-year select').val());
        if (currentDay > days) {
            $('.programlisting-day select').val(days);
        }
        for (var i = 29; i <= 31; i++) {
            if (i > days) {
                if (!dayOptions[i]) {
                    dayOptions[i] = $('.programlisting-day select option[value=' + i + ']');
                }
                $('.programlisting-day select option[value=' + i + ']').remove();
            } else {
                if (dayOptions[i]) {
                    if (dayOptions[i].length > 1) {
                        $('.programlisting-day select').append(dayOptions[i][0]);
                    } else {
                        $('.programlisting-day select').append(dayOptions[i]);
                    }
                    delete dayOptions[i];
                }
            }
        }

        if (currentYear == 1925) {
            if (currentMonth == 3) {
                for (i = 1; i < 23; i++) {
                    if (!dayOptions[i]) {
                        dayOptions[i] = $('.programlisting-day select option[value=' + i + ']');
                    }
                    $('.programlisting-day select option[value=' + i + ']').remove();
                }
            } else {
                for (i = 22; i >= 1; i--) {
                    if (dayOptions[i]) {
                        if ($('.programlisting-day select option[value=' + i + ']').length === 0) {
                            $(dayOptions[i][0]).insertAfter('.programlisting-day select option:first-child');
                        }
                        delete dayOptions[i];
                    }
                }
            }
        }
        if (currentYear == 1984) {
            $('.programlisting-day select').val(1);
            for (i = 2; i <= 31; i++) {
                if (!dayOptions[i]) {
                    dayOptions[i] = $('.programlisting-day select option[value=' + i + ']');
                }
                $('.programlisting-day select option[value=' + i + ']').remove();
            }
        }
    }
}

var earliest = 0;

function changeMonth() {
    if (earliest != 0 && ($('.programlisting-year select').val() == 1925 || $('.programlisting-year select').val() == 1984)) {
        if (earliest == 1984) {
            for (var i = 2; i >= 1; i--) {
                if (monthOptions[i]) {
                    if ($('.programlisting-month select option[value=' + i + ']').length === 0) {
                        $(monthOptions[i][0]).insertAfter('.programlisting-month select option:first-child');
                    }
                    delete monthOptions[i];
                }
            }
            for (i = 31; i >= 1; i--) {
                if (dayOptions[i]) {
                    if ($('.programlisting-day select option[value=' + i + ']').length === 0) {
                        $(dayOptions[i][0]).insertAfter('.programlisting-day select option:first-child');
                    }
                    delete dayOptions[i];
                }
            }
        } else if (earliest == 1925) {
            for (var i = 1; i <= 12; i++) {
                if (monthOptions[i]) {
                    // $('.programlisting-month select option[value=' + i + ']').remove();
                    if ($('.programlisting-month select option[value=' + i + ']').length === 0) {
                        // $(monthOptions[i][0]).insertAfter('.programlisting-month select option:first-child');
                        $('.programlisting-month select').append(monthOptions[i][0]);
                    }
                    delete monthOptions[i];
                }
            }
            for (i = 1; i <= 31; i++) {
                if (dayOptions[i]) {
                    if ($('.programlisting-day select option[value=' + i + ']').length === 0) {
                        $('.programlisting-day select').append(dayOptions[i][0]);
                    }
                    delete dayOptions[i];
                }
            }
        }
    }
    if ($('.programlisting-year select').val() == 1925) { // Only program listings from 23/3 1925
        earliest = 1925;
        var currentMonth = $('.programlisting-month select').val();
        var currentDay = $('.programlisting-day select').val();
        if (currentMonth < 3) {
            $('.programlisting-month select').val(3);
            if (currentDay < 23) {
                $('.programlisting-day select').val(23);
            }
        }
        for (var i = 1; i <= 12; i++) {
            if (i < 3) {
                if (!monthOptions[i]) {
                    monthOptions[i] = $('.programlisting-month select option[value=' + i + ']');
                }
                $('.programlisting-month select option[value=' + i + ']').remove();
            } else {
                if ($('.programlisting-month select option[value=' + i + ']').length === 0) {
                    $('.programlisting-month select').append(monthOptions[i][0]);
                }
                delete monthOptions[i];
            }
        }
    } else if ($('.programlisting-year select').val() == 1984) { // Only program listings to 1/1 1984
        earliest = 1984;
        $('.programlisting-month select').val(1);
        var currentDay = $('.programlisting-day select').val();
        if (currentDay > 1) {
            $('.programlisting-day select').val(1);
        }
        for (var i = 2; i <= 12; i++) {
            if (!monthOptions[i]) {
                monthOptions[i] = $('.programlisting-month select option[value=' + i + ']');
            }
            $('.programlisting-month select option[value=' + i + ']').remove();
        }

    } else {
        if (earliest == 1925) {
            for (var i = 2; i >= 1; i--) {
                if (monthOptions[i]) {
                    if ($('.programlisting-month select option[value=' + i + ']').length === 0) {
                        $(monthOptions[i][0]).insertAfter('.programlisting-month select option:first-child');
                    }
                    delete monthOptions[i];
                }
            }
            for (i = 22; i >= 1; i--) {
                if (dayOptions[i]) {
                    if ($('.programlisting-day select option[value=' + i + ']').length === 0) {
                        $(dayOptions[i][0]).insertAfter('.programlisting-day select option:first-child');
                    }
                    delete dayOptions[i];
                }
            }
        } else if (earliest == 1984) {
            for (var i = 1; i <= 12; i++) {
                if (monthOptions[i]) {
                    // $('.programlisting-month select option[value=' + i + ']').remove();
                    if ($('.programlisting-month select option[value=' + i + ']').length === 0) {
                        // $(monthOptions[i][0]).insertAfter('.programlisting-month select option:first-child');
                        $('.programlisting-month select').append(monthOptions[i][0]);
                    }
                    delete monthOptions[i];
                }
            }
            for (i = 2; i < 29; i++) {
                if (dayOptions[i]) {
                    if ($('.programlisting-day select option[value=' + i + ']').length === 0) {
                        $('.programlisting-day select').append(dayOptions[i][0]);
                    }
                    delete dayOptions[i];
                }
            }
        }
        earliest = 0;
    }
}

function drawArrowhead(locx, locy, angle, sizex, sizey, ctx) {
    var hx = sizex / 2;
    var hy = sizey / 2;
    ctx.translate((locx), (locy));
    ctx.rotate(angle);
    ctx.translate(-hx, -hy);

    ctx.beginPath();
    ctx.moveTo(0, 0);
    ctx.lineTo(0, 1 * sizey);
    ctx.lineTo(1 * sizex, 1 * hy);
    ctx.closePath();
    ctx.fill();
}


// returns radians
function findAngle(sx, sy, ex, ey) {
    // make sx and sy at the zero point
    return Math.atan((ey - sy) / (ex - sx));
}

var sx = 200;
var sy = 100;
var ex = 200;
var ey = 10;




$(function() {
    changeMonth();
    changeNumberDays();
    $('.programlisting-year select').change(function() {
        changeMonth();
        changeNumberDays();
    });

    $('.programlisting-month select').change(function() {
        changeNumberDays();
    });

    $('.js-change-search').click(function(e) {
        e.preventDefault();
        $('.js-free-text-search-content').toggleClass('hidden');
        $('.js-date-search-content').toggleClass('hidden');
        if ($('.js-free-text-search-content').is(':visible')) {
            $('.js-free-text-search-content .programlistings-search-text').focus();
            $('.full-text-search-div').hide();
        } else {
            $('.full-text-search-div').show();
        }
        return false;
    });

    $('.schedule-free-text-search .hover-info').popover();

    var can = document.getElementById('full-text-search-arrow');
    if (can) {
        var ctx = can.getContext('2d');
        ctx.beginPath();
        ctx.fillStyle = "rgb(7,127,253)";
        ctx.moveTo(100, 100);
        ctx.quadraticCurveTo(sx, sy, ex, ey);
        ctx.strokeStyle = "rgb(7,127,253)";
        ctx.stroke();

        var ang = findAngle(sx, sy, ex, ey);
        ctx.fillRect(ex, ey, 2, 2);
        drawArrowhead(ex, ey, ang, 15, 10, ctx);
    }

    $('.modal-ok').click(function() {
      $('.modal').modal('hide');
    });

    $('.fake-link').click(function(e){
      e.preventDefault;
      var href = $(this).attr('data-href');
      window.open(href);
    });
});
