(function($) {

    /**
     * Main class for custom functions
     * @type {Object}
     */
    var wpdkacollections = {

        /**
         * Initiator
         * @return {void} 
         */
        init: function() {

            this.addCreateCollectionListener();
            this.addEditCollectionListener();

        },

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

            $('.addCollection').on('click', function(e) {
                e.preventDefault();
                createCollectionModal.modal('show');
            });

            createCollectionModal.on('click','#collection-create', function(e) {
                e.preventDefault();
                createCollectionModal.find('button').attr('disabled',true);

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
                        createCollectionModal.modal('hide');
                        location.reload();
                    },
                    error: function(errorThrown) {
                        alert("Error. Couldn't create collection.");
                        console.log("error.");
                        console.log(errorThrown);
                        createCollectionModal.find('button').attr('disabled',false);
                    }
                });

            });
        },
        addEditCollectionListener: function() {
            var editCollectionModal = $('<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">'+
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
                            '<button id="collection-remove" type="button" class="btn btn-danger pull-left" data-dismiss="modal">Slet</button>'+
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

            $('.editCollection').on('click', function(e) {
                e.preventDefault();
                editCollectionModal.modal('show');
            });
        }
    };

    //Initiate class on page load
    $(document).ready(function(){ wpdkacollections.init(); });

})(jQuery);
