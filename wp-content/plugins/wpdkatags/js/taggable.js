(function($) {

	/**
	 * Main class for custom functions
	 * @type {Object}
	 */
	var wpdkatags_taggable = {

		/**
		 * Initiator
		 * @return {void} 
		 */
		init: function() {

			var container = $(".usertags");
			$("#object-taggable").click( function(e) {
				e.preventDefault();

				var button = $(this);
				button.attr('disabled',true);

				$.ajax({
					url: WPDKATags_taggable.ajaxurl,
					data:{
						action: 'wpdkatags_taggable',
						taggable: button.attr('data-dka-taggable'),
						object_guid: $('.single-material').attr('id'),
						token: WPDKATags_taggable.token
					},
					dataType: 'JSON',
					type: 'POST',
					success:function(data){
						button.attr('disabled',false);

						container.html('<div class="alert alert-info">'+data+'</div>');
						
					},
					error: function(errorThrown){
						button.attr('disabled',false);

						container.append('<div class="alert alert-danger">'+errorThrown.responseText+'</div>');
					}
				});
	
			});

		}

	}

	//Initiate class on page load
	$(document).ready(function(){ wpdkatags_taggable.init(); });

})(jQuery);
