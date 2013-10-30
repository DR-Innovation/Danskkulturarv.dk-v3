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

			this.addRenameBulkListener();
			this.addRenameTagListener();

		},

		addRenameBulkListener: function() {
			var editElem;
			$('#doaction,#doaction2').click( function(e) {
				var selected = $('[name="action"]').find(':selected');
				if(selected.val() == "-1") {
					e.preventDefault();
				} else if(selected.val() == 'rename') {
					e.preventDefault();

					var checked = $('.check-column').find('input:checked');
					if(!editElem && checked.length > 0) {
						editElem = '<tr id="wpdkatags-quickedit"><td colspan="5"><input name="dka-tag-new" type="text" value=""><input type="submit" name="" id="doaction3" class="button action" value="Omdøb valgte" /></td></tr>';
						$('.wp-list-table.widefat tbody').prepend(editElem);
					} else if(checked.length == 0) {
						$('#wpdkatags-quickedit').remove();
						editElem = null;
					}

				}
				
			});
		},

		/**
		 * Listen to rename tag actions
		 */
		addRenameTagListener: function() { 
			var current_parent,
			temp_content,
			guid;
			$('.column-title').on('click', '.wpdkatags-rename', function(e) {
				e.preventDefault();

				if(current_parent && temp_content) {
					current_parent.html(temp_content.html());
				}

				current_parent = $(this).parents('td');
				temp_content = current_parent.clone();
				guid = $(this).attr('id');

				var title = current_parent.find('strong').text();

				current_parent.html('<input id="wpdkatags-rename-name" type="text" value="'+title+'"><input class="submitbutton wpdkatags-rename-submit" type="button" value="Rename"><button class="button wpdkatags-rename-cancel">Annuller</button');

			});

			$('.column-title').on('click', '.wpdkatags-rename-cancel', function(e) {
				e.preventDefault();

				if(current_parent && temp_content) {
					current_parent.html(temp_content.html());
					current_parent = temp_content = guid = null;
				}

			});

			$('.column-title').on('click', '.wpdkatags-rename-submit', function(e) {
				e.preventDefault();

				var button = $(this);
				button.attr('disabled',true);

				if(current_parent && temp_content && guid) {
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
							console.log(data);
							button.attr('disabled',false);
							temp_content.find('strong').text(data.tag);
							current_parent.html(temp_content.html());
							current_parent = temp_content = guid = null;
						},
						error: function(errorThrown){
							button.attr('disabled',false);
							console.log("error.");
							console.log(errorThrown);
						}
					});
				}
			});
		}

	}

	//Initiate class on page load
	$(document).ready(function(){ wpdkatags_admin.init(); });

})(jQuery);
