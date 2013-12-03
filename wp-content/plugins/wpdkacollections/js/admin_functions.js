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

			//this.addBulkListener();
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

		addBulkListener: function() {
			var editID = "wpdkatags-quickedit";
			var editElem;
			$('#doaction,#doaction2').click( function(e) {
				var selected = $('[name="action"]').find(':selected');
				if(selected.val() == "-1") {
					e.preventDefault();

				//When renaming in bulk, add text input to table
				//On submit, this input and checked tags will be handled (non-AJAX)
				} else if(selected.val() == 'rename') {
					e.preventDefault();

					var checked = $('.check-column').find('input:checked');
					if(!editElem && checked.length > 0) {
						editElem = '<tr id="'+editID+'"><td colspan="5"><input name="dka-tag-new" type="text" value=""><input type="submit" name="" id="doaction3" class="button button-primary action" value="'+WPDKATagObjects.renameBulk+'" /> <input type="button" class="button wpdkatags-quickedit-cancel" value="'+WPDKATagObjects.cancel+'" /></td></tr>';
						$('.dka-tag-objects tbody').prepend(editElem);
					} else if(checked.length == 0) {
						$('#'+editID).remove();
						editElem = null;
					}

				//Show confirm dialog on delete
				} else if(selected.val() == 'delete') {
					if(confirm(WPDKATagObjects.confirmDelete) == false) {
						e.preventDefault();
					}
				}
				
			});

			//Reset bulkElem on change and cancel
			$('[name="action"]').change( function(e) {
				$('#'+editID).remove();
				editElem = null;
			});
			$('.dka-tag-objects tbody').on('click', '.wpdkatags-quickedit-cancel', function(e) {
				$('#'+editID).remove();
				editElem = null;
			});			
		},

		/**
		 * Listen to rename tag actions
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

					$('.error').remove();

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
							current_parent.append('<div class="error">'+errorThrown.responseText+'</div>')
						}
					});
				}
			});
		}

	}

	//Initiate class on page load
	$(document).ready(function(){ wpdkacollections_admin.init(); });

})(jQuery);
