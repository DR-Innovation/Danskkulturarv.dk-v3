(function($) {

	/**
	 * Main class for custom functions
	 * @type {Object}
	 */
	var wpdkatags_admin = {

		/**
		 * Initiator
		 * @return {void} 
		 */
		init: function() {

			this.addBulkListener();
			this.addRenameTagListener();
			this.addDeleteConfirm();

		},

		addDeleteConfirm: function() {
			$('.dka-tag-objects').on('click','.submitdelete', function(e) {
				if(confirm(WPDKATagObjects.confirmDelete) == false) {
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
		addRenameTagListener: function() { 
			var current_parent,
			temp_content,
			guid,
			spinner = $('<div class="spinner"></div>');

			//Switch title column with input form
			$('.column-title').on('click', '.wpdkatags-rename', function(e) {
				e.preventDefault();

				//Remove bulk edit if present
				$('#wpdkatags-quickedit').remove();

				if(current_parent && temp_content) {
					current_parent.html(temp_content.html());
				}

				current_parent = $(this).parents('td');
				temp_content = current_parent.clone();
				guid = $(this).attr('id');

				var title = current_parent.find('strong').text();

				current_parent.html('<input id="wpdkatags-rename-name" class="regular-text" type="text" value="'+title+'"><input type="button" class="button button-primary wpdkatags-rename-submit" value="'+WPDKATagObjects.rename+'" /> <input type="button" class="button wpdkatags-rename-cancel" value="'+WPDKATagObjects.cancel+'" />');
				spinner.hide();
				current_parent.append(spinner);

			});

			//Reset back to original content on cancel
			$('.column-title').on('click', '.wpdkatags-rename-cancel', function(e) {
				e.preventDefault();

				if(current_parent && temp_content) {
					current_parent.html(temp_content.html());
					current_parent = temp_content = guid = null;
				}

			});

			//On successfuly submit reset back to original content with new title
			//Otherwise show error
			$('.column-title').on('click', '.wpdkatags-rename-submit', function(e) {
				e.preventDefault();

				if(current_parent && temp_content && guid) {

					$('.error').remove();

					var buttons = $('.column-title input');
					buttons.attr('disabled',true);

					spinner.show();

					$.ajax({
						url: ajaxurl,
						data:{
							action: 'wpdkatags_rename_tag',
							tag_guid: guid,
							tag: $('#wpdkatags-rename-name').val(),
							nonce: $("#_wpnonce").val()
						},
						dataType: 'JSON',
						type: 'POST',
						success:function(data){
							buttons.attr('disabled',false);
							spinner.hide();

							temp_content.find('strong').text(data.tag);
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
	$(document).ready(function(){ wpdkatags_admin.init(); });

})(jQuery);
