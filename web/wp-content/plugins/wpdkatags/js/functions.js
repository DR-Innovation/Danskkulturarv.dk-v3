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
	
				var button = $(this);

				$(container).find('.alert').remove();

				button.attr('disabled',true);
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
						button.attr('disabled',false);

						var tag = '<a class="usertag tag" href="'+data.link+'">'+data.title+'<i class="icon-remove flag-tag" id="'+data.guid+'"></i></a>'
						var notag = container.find(".alert");
						 if(notag.length > 0) {
							 notag.remove();
						}
						container.append(tag);
						container.append('<div class="alert alert-success">'+data.success+'</div>');
						input.val("");
					},
					error: function(errorThrown){
						button.attr('disabled',false);

						container.append('<div class="alert alert-danger">'+errorThrown.responseText+'</div>');
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
							'<h4 class="modal-title">'+WPDKATags.confirmTitle+'</h4>'+
						'</div>'+
						'<div class="modal-body"></div>'+
						'<div class="modal-footer">'+
							'<button id="usertag-flag-confirm" type="button" class="btn btn-primary">'+WPDKATags.yes+'</button>'+
							'<button type="button" class="btn btn-default" data-dismiss="modal">'+WPDKATags.no+'</button>'+
						'</div>'+
					'</div>'+
				'</div>'+
			'</div>');
			var alertModal = $('<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">'+
				'<div class="modal-dialog">'+
					'<div class="modal-content">'+
						'<div class="modal-header">'+
							'<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>'+
							'<h4 class="modal-title">'+WPDKATags.confirmTitle+'</h4>'+
						'</div>'+
						'<div class="modal-body"></div>'+
					'</div>'+
				'</div>'+
			'</div>'); 
			confirmModal.modal({
				keyboard:false,
				show:false,
				backdrop:'static'
			});
			alertModal.modal({
				keyboard:true,
				show:false,
			});
			var current_tag;

			$('.usertags').on('click','.flag-tag', function(e) {
				e.preventDefault();
				current_tag = $(this);
				confirmModal.find('.modal-body').html(WPDKATags.confirmBody+' <div><strong>'+current_tag.parent().text()+'</strong></div>');
				confirmModal.find('button').attr('disabled',false);
				confirmModal.modal('show');
			});

			confirmModal.on('click','#usertag-flag-confirm', function(e) {
				e.preventDefault();

				if(current_tag != null) {
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
							confirmModal.modal('hide');
							alertModal.find('.modal-body').html('<div class="alert alert-success">'+data+'</div>');
							alertModal.modal('show');

							current_tag.parent().remove();
							current_tag = null;

							setTimeout(function() {
								alertModal.modal('hide');
							}, 3000);
						},
						error: function(errorThrown){
							confirmModal.modal('hide');
							
							alertModal.find('.modal-body').html('<div class="alert alert-danger">'+errorThrown.responseText+'</div>');
							alertModal.modal('show');

							current_tag = null;

							setTimeout(function() {
								alertModal.modal('hide');
							}, 3000);
						}
					});					
				}

			});

		}

	}

	//Initiate class on page load
	$(document).ready(function(){ wpdkatags.init(); });

})(jQuery);
