(function($) {

	/**
	 * Main class for custom functions
	 * @type {Object}
	 */
	var wpdkacollections = {

		modalAddObject: null,
		collections: [],

		/**
		 * Initiator
		 * @return {void} 
		 */
		init: function() {

			this.addInsertObjectToRelationListener();
			this.addCreateCollectionListener();
			this.addEditCollectionListener();	

		},

		getCollections: function() {
			$.ajax({
				url: WPDKACollections.ajaxurl,
				data:{
					action: 'wpdkacollections_get_collections',
					token: WPDKACollections.token
				},
				dataType: 'JSON',
				type: 'POST',
				success:function(data) {
					wpdkacollections.collections = data;
					wpdkacollections.modalAddObject.find('select').html(wpdkacollections.parseCollectionsToSelect());
				},
				error: function(errorThrown) {
					alert(errorThrown.responseText);
					console.log("error.");
				}
			});
		},

		parseCollectionsToSelect: function() {
			var result = '';
			console.log(this.collections);
			for(var c in this.collections) {
				var collection = this.collections[c];
				console.log(collection.guid);
				result += '<option value="'+collection.guid+'">'+collection.title+'</option>';
			}
			return result;
		},

		addInsertObjectToRelationListener: function() {

			this.modalAddObject = $('<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">'+
				'<div class="modal-dialog">'+
					'<div class="modal-content">'+
						'<div class="modal-header">'+
							'<h4 class="modal-title">Add object to collection</h4>'+
						'</div>'+
						'<div class="modal-body">'+
							'<div class="form-horizontal">'+
								'<div class="form-group">'+
									'<label for="textDescription" class="col-lg-2 control-label">Samling</label>'+
									'<div class="col-lg-10 input-group">'+
										'<select name="collection-select" class="collection-select form-control">'+
										'</select>'+
										'<span class="input-group-btn">'+
											'<button class="btn btn-default" id="add-collection" type="button">Ny?</button>'+
										'</span>'+
									'</div>'+
								'</div>'+
								'<hr>'+
								'<div class="form-group">'+
									'<label for="textDescription" class="col-lg-2 control-label">Beskrivelse</label>'+
									'<div class="col-lg-10">'+
										'<textarea class="form-control" rows="3" id="textDescription" placeholder="Beskrivelse af tilhørsforhold."></textarea>'+
									'</div>'+
								'</div>'+
							'</div>'+
						'</div>'+
						'<div class="modal-footer"><button id="collection-create" type="button" class="btn btn-primary" disabled>Add</button> <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button></div>'+
						'</div>'+
					'</div>'+
				'</div>');

			this.modalAddObject.modal({
				keyboard:true,
				show:false
			});

			$('.search-object').on('click', '.add-to-collection', function(e) {
				e.preventDefault();

				//console.log($(this).parents('.thumbnail').attr('id'));

				if(wpdkacollections.collections.length == 0) {
					wpdkacollections.getCollections();
				}

				wpdkacollections.modalAddObject.modal('show');
			});


		},

		// addCollectionModeListener: function() {

		// 	$('#wp-admin-bar-new-dka-collection').on('click','.ab-item', function(e) {
		// 		e.preventDefault();
		// 		wpdkacollections.setCollectionMode(true);

		// 	})

		// },

		// setCollectionMode: function(mode) {
		// 	this.collectionMode = mode;

		// 	var panel = $('<div style="position:fixed; bottom:0; left:0; right:0; height:100px; background:green;"></div>')

		// 	if(this.collectionMode === true) {
		// 		$(document).append(panel);
		// 	} else {
		// 		panel.remove();
		// 	}

		// },

		/**
		 * Listen to tag submussion and use AJAX
		 * to handle request
		 */
		addCreateCollectionListener: function() {
			var createCollectionModal = $('<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">'+
				'<div class="modal-dialog">'+
					'<div class="modal-content">'+
						'<div class="modal-header">'+
							'<h4 class="modal-title">Oprettelse af samling</h4>'+
						'</div>'+
						'<div class="modal-body">'+
							'<form class="form-horizontal" role="form">'+
								'<div class="form-group">'+
									'<label for="inputName" class="col-lg-2 control-label">Navn*</label>'+
									'<div class="col-lg-10">'+
										'<input type="text" class="form-control" id="inputName" placeholder="Navn på samling." required>'+
									'</div>'+
								'</div><hr>'+
								'<div class="form-group">'+
									'<label for="textDescription" class="col-lg-2 control-label">Beskrivelse</label>'+
									'<div class="col-lg-10">'+
										'<textarea class="form-control" rows="3" id="textDescription" placeholder="Beskrivelse af samling."></textarea>'+
									'</div>'+
								'</div><hr>'+
								'<div class="form-group">'+
									'<label for="inputRights" class="col-lg-2 control-label">Rettigheder</label>'+
									'<div class="col-lg-10">'+
										'<input type="text" class="form-control" rows="3" id="inputRights" placeholder="Rettigheder for samlingen."></textarea>'+
									'</div>'+
								'</div><hr>'+
								// How should categories be presented? Dropdown e.g.
								'<div class="form-group">'+
									'<label for="inputCategory" class="col-lg-2 control-label">Kategori</label>'+
									'<div class="col-lg-10">'+
										'<input type="text" class="form-control" id="inputCategory" placeholder="Samlingens kategori.">'+
									'</div>'+
								'</div>'+
						'</div>'+
						'<div class="modal-footer">'+
							'<button id="collection-create" type="button" class="btn btn-primary">Opret</button>'+
							'<button type="button" class="btn btn-default" data-dismiss="modal">Annuller</button>'+
						'</div>'+
					'</div>'+
				'</div>'+
			'</div>');
			createCollectionModal.modal({
				keyboard:false,
				show:false,
				backdrop:'static'
			});

			this.modalAddObject.on('click', '#add-collection', function(e) {
				e.preventDefault();
				createCollectionModal.modal('show');
				
			});

			// createCollectionModal.on('keydown', function(e) {
			// 	if ($('#inputName').val().length > 0) {
			// 		$('#collection-create').attr('disabled',false);
			// 	} else {
			// 		$('#collection-create').attr('disabled',true);
			// 	}
			// });

			createCollectionModal.on('click','#collection-create', function(e) {
				e.preventDefault();
				if ($('#inputName').val().length > 0) {

					var buttons = createCollectionModal.find('button');

					buttons.attr('disabled',true);

					$.ajax({
						url: WPDKACollections.ajaxurl,
						data:{
							action: 'wpdkacollections_add_collection',
							collectionTitle: $('#inputName').val(),
							collectionDescription: $('#textDescription').text(),
							collectionRights: $('#inputRights').val(),
							collectionCategory: $('#inputCategory').val(),
							token: WPDKACollections.token
						},
						dataType: 'JSON',
						type: 'POST',
						success:function(data) {
							console.log(data);
							var option = '<option value="'+data.guid+'" selected="selected">'+data.title+'</option>';
							wpdkacollections.modalAddObject.find('select').append(option);
							createCollectionModal.modal('hide');
							buttons.attr('disabled',false);
						},
						error: function(errorThrown) {
							alert(errorThrown.responseText);
							console.log("error.");
							console.log(errorThrown);
							buttons.attr('disabled',false);
						}
					});
				}

			});
		},
		addEditCollectionListener: function() {
			var editCollectionModal = $('<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">'+
				'<div class="modal-dialog">'+
					'<div class="modal-content">'+
						'<div class="modal-header">'+
							'<h4 class="modal-title">Ændring af samling</h4>'+
						'</div>'+
						'<div class="modal-body">'+
							'<form class="form-horizontal" role="form">'+
								'<div class="form-group">'+
									'<label for="inputName" class="col-lg-2 control-label">Navn*</label>'+
									'<div class="col-lg-10">'+
										'<input type="text" class="form-control" id="inputName" placeholder="Navn på samling." value="' + WPDKACollections.inputName + '" required>'+
									'</div>'+
								'</div><hr>'+
								'<div class="form-group">'+
									'<label for="textDescription" class="col-lg-2 control-label">Beskrivelse</label>'+
									'<div class="col-lg-10">'+
										'<textarea class="form-control" rows="3" id="textDescription" placeholder="Beskrivelse af samling." value="' + WPDKACollections.inputDescription + '"></textarea>'+
									'</div>'+
								'</div><hr>'+
								'<div class="form-group">'+
									'<label for="inputRights" class="col-lg-2 control-label">Rettigheder</label>'+
									'<div class="col-lg-10">'+
										'<input type="text" class="form-control" rows="3" id="inputRights" placeholder="Rettigheder for samlingen." value="' + WPDKACollections.inputRights + '"></textarea>'+
									'</div>'+
								'</div><hr>'+
								// How should categories be presented? Dropdown e.g.
								'<div class="form-group">'+
									'<label for="inputCategory" class="col-lg-2 control-label">Kategori</label>'+
									'<div class="col-lg-10">'+
										'<input type="text" class="form-control" id="inputCategory" placeholder="Samlingens kategori." value="' + WPDKACollections.categories + '">'+
									'</div>'+
								'</div>'+
						'</div>'+
						'<div class="modal-footer">'+
							'<button id="collection-remove" type="button" class="btn btn-danger pull-left">Slet</button>'+
							'<button id="collection-save" type="button" class="btn btn-primary">Gem</button>'+
							'<button type="button" class="btn btn-default" data-dismiss="modal">Annuller</button>'+
						'</div>'+
					'</div>'+
				'</div>'+
			'</div>');
			editCollectionModal.modal({
				keyboard:false,
				show:false,
				backdrop:'static'
			});

			// Open edit-modal
			$('.editCollection').on('click', function(e) {
				e.preventDefault();
				$('.modal-title').text('test');
				editCollectionModal.modal('show');
			});

			// Remove collection from modal
			editCollectionModal.on('click', '#collection-remove', function(e) {
				e.preventDefault();
				editCollectionModal.find('button').attr('disabled',true);

				$.ajax({
					url: WPDKACollections.ajaxurl,
					data:{
						action: 'wpdkacollections_delete_collection',
						object_guid: $('.editCollection').attr('id'),
						token: WPDKACollections.token
					},
					dataType: 'JSON',
					type: 'POST',
					success:function(data) {
						console.log(data);
						createCollectionModal.modal('hide');
						location.reload();
					},
					error: function(errorThrown) {
						alert(errorThrown.responseText);
						console.log("error.");
						console.log(errorThrown);
						editCollectionModal.find('button').attr('disabled',false);
					}
				});
			});

			// Save changes to collection form modal
			editCollectionModal.on('click', '#collection-save', function(e) {
				e.preventDefault();

				if ($('#inputName').val().length > 0) {
					editCollectionModal.find('button').attr('disabled',true);

					$.ajax({
						url: WPDKACollections.ajaxurl,
						data:{
							action: 'wpdkacollections_edit_collection',
							object_guid: $('.editCollection').attr('id'),
							collectionTitle: $('#inputName').val(),
							collectionDescription: $('#textDescription').text(),
							collectionRights: $('#inputRights').val(),
							collectionCategory: $('#inputCategory').val(),
							token: WPDKACollections.token
						},
						dataType: 'JSON',
						type: 'POST',
						success:function(data) {
							console.log(data);
							createCollectionModal.modal('hide');
							location.reload();
						},
						error: function(errorThrown) {
							alert(errorThrown.responseText);
							console.log("error.");
							console.log(errorThrown);
							editCollectionModal.find('button').attr('disabled',false);
						}
					});
				}
			});
		}
	};

	//Initiate class on page load
	$(document).ready(function(){ wpdkacollections.init(); });

})(jQuery);
