/* =============================================================
 * Custom functions goes here
 * ============================================================ */

(function($) {

  /**
   * Main class for custom functions
   * @type {Object}
   */
  var dka_api = {

    /**
     * Initiator
     * @return {void}
     */
    init: function() {

      this.addCheckboxListener();
      this.addToggleAllListener();
      this.addFlexSliders();
      this.socialSharePopup();
      // this.focusSearchBar();
      this.cookiePolicy();
    },

    cookiePolicy: function() {
      $('.footer_cookie_policy').on('click', '.exit', function(e) {
        e.preventDefault();
        var d = new Date();
        d.setTime(d.getTime() + (365 * 24 * 60 * 60 * 1000));
        var expires = "expires=" + d.toGMTString();
        document.cookie = "cookie_policy_seen=true; " + expires;
        $('.footer_cookie_policy').remove();
      });
    },

    focusSearchBar: function() {
      $('input[name="' + dka.query_key_freetext + '"]').focus();
    },

    /**
     * Update search filter labels according to their checkbox state
     * Tell ToggleAll button
     * Force form submit on every change
     * @return {void}
     */
    addCheckboxListener: function() {

      var submitTimer;

      //Update label classes according to check state
      $('.search').on('change', 'input.chaos-filter', function(e) {
        var $checkbox = $(this);
        var $label = $checkbox.parent();

        $label.toggleClass("active", $checkbox.prop("checked"));

        //Update filter-btn-all
        dka_api.updateToggleAllState($label.closest('.filter-container'));
      });

      //Fire on load to get current states
      $('input.chaos-filter').change();

      //Force update on change and sync similar inputs
            $('.search').on('change', 'input.chaos-filter, .btn-group-media-type input', function(e) {

          var $checkbox = $(this);

          //Only sync on uncheck because else we would get dupe params in GET
          if (!$checkbox.prop('checked')) {
            //Synchronize checkboxes of same name and value
                    $('input[name="' + $checkbox.attr('name') + '"][value="' + $checkbox.val() + '"]:checked').not($checkbox).each(function(e) {
              //No need to update active class because we force submit
              //change event will lead to infinite recursion
              $(this).prop('checked', false);
            });
          }

          //Use timer to let user click more than once
          //and events to complete
          if (submitTimer)
            clearTimeout(submitTimer);

          submitTimer = setTimeout(function() {
            dka_api.forceSubmitForm();
          }, 400);

        })

    },

    /**
     * Update ToggleAll according to the number of search filters checked
     * @param  {jQuery Object} $container
     * @return {void}
     */
    updateToggleAllState: function($container) {
      var $checkedBoxes = $("input.chaos-filter:checkbox:checked", $container);
      var $allButton = $(".filter-btn-all", $container);

      $allButton.toggleClass("active", $checkedBoxes.length == 0);
    },
    /**
     * Reset search filters on ToggleAll
     * @return {void}
     */
    addToggleAllListener: function() {
      // Show all buttons
      $('.search').on('click', '.filter-btn-all', function(e) {
        // Change the state and fire the change event.
                $("input.chaos-filter", $(this).closest('.filter-container')).prop("checked", false)
          .change();
      });
    },

    /**
     * Force click on form submit
     * @return {void}
     */
    forceSubmitForm: function() {
      $("#searchsubmit").click();
    },

    /**
     * Adding FlexSlider functionality
     * Binds to .flexslider
     * @return {void}
     */
    addFlexSliders: function() {
      if ($().flexslider) {
        $('.flexslider').flexslider({
          animation: "slide",
          touch: true,
          smoothHeight: false
        });
      }
    },

    /**
     * Open window in popup instead of new
     * Get social counts
     * @return {void}
     */
    socialSharePopup: function() {
      var objectGUID = $(".single-material[id]").each(function() {
        var $this = $(this);
        $.post(dka.ajax_url, {
          action: "wpdka_social_counts",
          object_guid: $this.attr('id')
        }, function(response) {
                    $(".social-share[href*=facebook]", $this).attr('title', $(".social-share[href*=facebook]", $this).attr('title') + " (" + response.facebook_total_count + ")");
                    $(".social-share[href*=twitter]", $this).attr('title', $(".social-share[href*=twitter]", $this).attr('title') + " (" + response.twitter_total_count + ")");
                    $(".social-share[href*=google]", $this).attr('title', $(".social-share[href*=google]", $this).attr('title') + " (" + response.google_plus_total_count + ")");
        }, 'json');
      });

      $(".social-share").click(function(e) {

        e.preventDefault();

        var width = 600;
        var height = 400;
        var left = (screen.width / 2) - (width / 2);
        var top = (screen.height / 2) - (height / 2);
        window.open(
          $(this).attr('href'),
          '',
                    'menubar=no, toolbar=no, resizable=yes, scrollbars=yes, height=' + height + ', width=' + width + ', top=' + top + ', left=' + left + ''
        );

      });
    },

  }



  /*
   * MISC UI functions
   * by Andreas Larsen, autumn 2015
   */


  /*
   * jQuery throttle / debounce - v1.1 - 3/7/2010
   * http://benalman.com/projects/jquery-throttle-debounce-plugin/
   *
   * Copyright (c) 2010 "Cowboy" Ben Alman
   * Dual licensed under the MIT and GPL licenses.
   * http://benalman.com/about/license/
   */
  $.throttle = jq_throttle = function(t, i, o, e) {
    function n() {
      function n() {
        d = +new Date, o.apply(r, v)
      }

      function a() {
        u = void 0
      }
      var r = this,
        g = +new Date - d,
        v = arguments;
      e && !u && n(), u && clearTimeout(u), void 0 === e && g > t ? n() : i !==
        !0 && (u = setTimeout(e ? a : n, void 0 === e ? t - g : t))
    }
    var u, d = 0;
    return "boolean" != typeof i && (e = o, o = i, i = void 0), $.guid && (n.guid =
      o.guid = o.guid || $.guid++), n
  };

  //Initiate class on page load
  $(document).ready(function() {
    dka_api.init();

    var navBig = 100, // height of big screen nav
      navSmall = 50, // height of nav when scrolled + on small screens
      navScroll = navBig - navSmall, // Distance between...
      navBigScreen = 768, // screenwidth for switch between normal and mobile nav
      brandSize = 38, // font-size of navbrand on big screen
      resizeFactor = 0.34, // trial/error...to end at the correct small size
      parallaxFactor = 3; // how much longer to scroll relative to height resize

    function navResize() {
      var scroll = $(document).scrollTop(),
        $navbar = $('#wrap .navbar'),
        $brand = $(".navbar-brand"),
        $width = $(window).width();
      if ($width > navBigScreen) {
        if (scroll == 0) {
          $navbar.css({
            'height': navBig
          });
          $brand.css({
            'font-size': brandSize
          })
          $('.navbar').removeClass('show-search-button');
        } else if (scroll > 0 && scroll < navScroll * parallaxFactor) {
          $navbar.css({
            'height': navBig - scroll / parallaxFactor
          });
          $brand.css({
            'font-size': brandSize - scroll / parallaxFactor * resizeFactor
          })
          $('.navbar').removeClass('show-search-button');
        } else if (scroll > navScroll * parallaxFactor) {
          $navbar.css({
            'height': navSmall
          });
          $brand.css({
            'font-size': brandSize - navScroll * resizeFactor
          })
          $('.navbar').addClass('show-search-button');
        }
      } else {
        $navbar.css({
          'height': ''
        });
        $brand.css({
          'font-size': ''
        });
        $('.navbar').removeClass('show-search-button');
      }
    };
    $(window).scroll($.throttle(10, navResize));
    $(window).resize($.throttle(10, navResize));
  });

  //
  $('.search-toggle a').click(function(e) {
    e.preventDefault();
    $('.navbar-collapse').removeClass('in');
    $('.dark-search input[type=text]').focus();
    $(document).scrollTop(0);
    $('.dark-search').addClass('nav-search-pressed').delay(1000).queue(function(){
      $(this).removeClass("nav-search-pressed").dequeue();
    });
  });

  // Remove focus from select when element was selected
  $("select").change(function() {
    $(this).blur();
  });

  $(".close").click(function(e) {
    e.preventDefault();
    $(this).parent().hide(); // this is the link element that was clicked
  })

  $(".btn-advanced-search").click(function() {
    $(this).removeClass('in');
  });

})(jQuery);
