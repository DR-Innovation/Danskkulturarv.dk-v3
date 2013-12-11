(function($) {

	/**
	 * Main class for custom functions
	 * @type {Object}
	 */
	var wpdkacollections_admin = {

		/**
		 * Initiator
		 * @return {void} 
		 */
		init: function() {

			this.addQuickEditListener();
			this.addDeleteConfirm();

		},

		addDeleteConfirm: function() {
			$('.dka-collections').on('click','.submitdelete', function(e) {
				if(confirm(WPDKACollections.confirmDelete) == false) {
					e.preventDefault();
				}
			});
		},

		/**
		 * Listen to quickedit action
		 */
		addQuickEditListener: function() { 
			var current_parent,
			temp_content,
			guid,
			spinner = $('<div class="spinner"></div>');

			//Switch title column with input form
			$('.dka-collections').on('click', '.wpdkacollections-quickedit', function(e) {
				e.preventDefault();

				//Remove bulk edit if present
				$('#wpdkacollections-quickedit').remove();

				if(current_parent && temp_content) {
					current_parent.html(temp_content.html());
				}

				current_parent = $(this).parents('tr');
				temp_content = current_parent.clone();
				guid = $(this).attr('id');

				$('.title',current_parent).each( function(e) {
					var value = $('strong',this).text();
					$(this).html('<input type="text" id="wpdkacollections-title" value="'+value+'"><div><input type="button" class="button button-primary wpdkacollections-quickedit-submit" value="'+WPDKACollections.update+'" /> <input type="button" class="button wpdkacollections-quickedit-cancel" value="'+WPDKACollections.cancel+'" /></div>');
					$(this).find('div').append(spinner);
					spinner.hide();
				});

				$('.description,.rights',current_parent).each( function(e) {
					var id = $(this).attr('class').split(' ')[0];
					var value = $(this).text();
					$(this).html('<textarea type="text" id="wpdkacollections-'+id+'">'+value+'</textarea>');
				});

				$('.type',current_parent).each( function(e) {
					var value = $(this).text();
					var result = '<select id="wpdkacollections-type">';
					for(var t in WPDKACollections.types) {
						var type = WPDKACollections.types[t];
						if(type == value) {
							result += '<option value="'+t+'" selected="selected">'+type+'</option>';
						} else {
							result += '<option value="'+t+'">'+type+'</option>';
						}
						
					}
					result += '</select>';

					$(this).html(result);
				});

				$('.status',current_parent).each( function(e) {
					var value = $(this).text();
					var result = '<select id="wpdkacollections-status">';
					for(var t in WPDKACollections.states) {
						var type = WPDKACollections.states[t];
						if(type == value) {
							result += '<option value="'+t+'" selected="selected">'+type+'</option>';
						} else {
							result += '<option value="'+t+'">'+type+'</option>';
						}
						
					}
					result += '</select>';

					$(this).html(result);
				});

			});

			//Reset back to original content on cancel
			$('.dka-collections').on('click', '.wpdkacollections-quickedit-cancel', function(e) {
				e.preventDefault();

				if(current_parent && temp_content) {
					current_parent.html(temp_content.html());
					current_parent = temp_content = guid = null;
				}

			});

			//On successfuly submit reset back to original content with new title
			//Otherwise show error
			$('.dka-collections').on('click', '.wpdkacollections-quickedit-submit', function(e) {
				e.preventDefault();

				if(current_parent && temp_content && guid) {

					$('.error',current_parent).remove();

					var buttons = $('.column-title input');
					buttons.attr('disabled',true);

					spinner.show();

					$.ajax({
						url: ajaxurl,
						data:{
							action: 'wpdkacollections_edit_collection',
							object_guid: guid,
							title: $('#wpdkacollections-title').val(),
							description: $('#wpdkacollections-description').val(),
							rights: $('#wpdkacollections-rights').val(),
							type: $('#wpdkacollections-type').val(),
							status: $('#wpdkacollections-status').val(),
							nonce: $("#_wpnonce").val()
						},
						dataType: 'JSON',
						type: 'POST',
						success:function(data){
							buttons.attr('disabled',false);
							spinner.hide();

							temp_content.find('.title strong a').text(data.title);
							temp_content.find('.description').text(data.description);
							temp_content.find('.rights').text(data.rights);
							temp_content.find('.type').text(data.type);
							temp_content.find('.status').text(data.status);
							current_parent.html(temp_content.html());
							current_parent = temp_content = guid = null;
							
						},
						error: function(errorThrown){
							buttons.attr('disabled',false);
							spinner.hide();
							$('.title',current_parent).append('<div class="error">'+errorThrown.responseText+'</div>')
						}
					});
				}
			});
		}

	}

	//Initiate class on page load
	$(document).ready(function(){ wpdkacollections_admin.init(); });

})(jQuery);
