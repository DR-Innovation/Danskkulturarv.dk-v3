(function($) {

	/**
	 * Main class for custom functions
	 * @type {Object}
	 */
	var wpdkatags = {

		/**
		 * Initiator
		 * @return {void} 
		 */
		init: function() {

			this.addCreateTagListener();
			this.addFlagTagListener();

		},

		/**
		 * Listen to tag submussion and use AJAX
		 * to handle request
		 */
		addCreateTagListener: function() { 
			var container = $(".usertags");
			$("#usertag-submit").click( function(e) {
				e.preventDefault();

				$(this).attr('disabled',true);
				var button = $(this);
				var input = $('#usertag-add');

				button.attr('disabled',true);
				$.ajax({
					url: WPDKATags.ajaxurl,
					data:{
						action: 'wpdkatags_submit_tag',
						tag: input.val(),
						object_guid: $('.single-material').attr('id'),
						token: WPDKATags.token
					},
					dataType: 'JSON',
					type: 'POST',
					success:function(data){
						console.log(data);
						button.attr('disabled',false);
						var tag = '<a class="usertag tag" href="'+data.link+'">'+data.title+'<i class="icon-remove flag-tag" id="'+data.guid+'"></i></a>'
						var notag = container.find("span");
						 if(notag.length > 0) {
							 notag.remove();
						}
						container.append(tag);
						input.val("");
					},
					error: function(errorThrown){
						button.attr('disabled',false);
						console.log("error.");
						console.log(errorThrown);
					}
				});
			});
		},

		/**
		 * Listen to tag flagging and
		 * create modal confirmation upon AJAX request
		 */
		addFlagTagListener: function() {

			var confirmModal = $('<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">'+
				'<div class="modal-dialog">'+
					'<div class="modal-content">'+
						'<div class="modal-header">'+
							'<h4 class="modal-title">Bekræftelse</h4>'+
						'</div>'+
						'<div class="modal-body"></div>'+
						'<div class="modal-footer">'+
							'<button id="usertag-flag-confirm" type="button" class="btn btn-primary">Fortsæt</button>'+
							'<button type="button" class="btn btn-default" data-dismiss="modal">Annuller</button>'+
						'</div>'+
					'</div>'+
				'</div>'+
			'</div>');
			confirmModal.modal({
				keyboard:false,
				show:false,
				backdrop:'static'
			});
			var current_tag;

			$('.usertags').on('click','.flag-tag', function(e) {
				e.preventDefault();
				current_tag = $(this);
				confirmModal.find('.modal-body').text('Ønsker du virkelig at flagge dette tag? '+current_tag.parent().text());
				confirmModal.find('button').attr('disabled',false);
				confirmModal.modal('show');
			});

			confirmModal.on('click','#usertag-flag-confirm', function(e) {
				e.preventDefault();
				confirmModal.find('button').attr('disabled',true);

				$.ajax({
					url: WPDKATags.ajaxurl,
					data:{
						action: 'wpdkatags_flag_tag',
						tag_guid: current_tag.attr('id'),
						object_guid: $('.single-material').attr('id'),
						token: WPDKATags.token
					},
					dataType: 'JSON',
					type: 'POST',
					success:function(data){
						console.log(data);
						confirmModal.modal('hide');
					},
					error: function(errorThrown){
						confirmModal.modal('hide');
						console.log("error.");
						console.log(errorThrown);
					}
				});

			});

		}

	}

	//Initiate class on page load
	$(document).ready(function(){ wpdkatags.init(); });

})(jQuery);
