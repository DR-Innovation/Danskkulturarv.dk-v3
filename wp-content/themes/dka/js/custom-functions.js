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

		},

		/**
		 * Update search filter labels according to their checkbox state
		 * Tell ToggleAll button
		 * Force form submit on every change
		 * @return {void}
		 */
		addCheckboxListener: function() {

			//Update label classes according to check state
			$('.search').on('change', 'input.chaos-filter', function(e) {
				var $checkbox = $(this);
				var $label = $checkbox.parent();

				$label.toggleClass("active", $checkbox.is(":checked"));

				//Update filter-btn-all
				dka_api.updateToggleAllState($label.closest('.filter-container'));
			});

			//Fire on load to get current states
			$('input.chaos-filter').change();

			//Force update on change and sync similar inputs
			$('.search').on('change', 'input.chaos-filter, .btn-group-media-type input', function(e) {
				
				var $checkbox = $(this);

				//Synchronize checkboxes of same name and value
				$('input[name="'+$checkbox.attr('name')+'"][value="'+$checkbox.val()+'"]').not($checkbox).each( function(e) {
					//No need to update active class because we force submit
					//change event will lead to infinite recursion
					$(this).attr('checked', $checkbox.is(':checked'));
				});

				dka_api.forceSubmitForm();
			});

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
				$("input.chaos-filter", $(this).parent()).attr("checked", false)
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
					smoothHeight: true
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

	//Initiate class on page load
	$(document).ready(function() {
		dka_api.init();
	});

})(jQuery);