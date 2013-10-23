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
                                '</div>'+
                                '<div class="form-group">'+
                                    '<label for="inputDescription" class="col-lg-2 control-label">Beskrivelse</label>'+
                                    '<div class="col-lg-10">'+
                                        '<input type="text" class="form-control" id="inputDescription" placeholder="Beskrivelse af samling.">'+
                                    '</div>'+
                                '</div>'+
                                // How should categories be presented? Dropdown e.g.
                                '<div class="form-group">'+
                                    '<label for="inputCategory" class="col-lg-2 control-label">Kategori</label>'+
                                    '<div class="col-lg-10">'+
                                        '<input type="text" class="form-control" id="inputCategory" placeholder="Samlingens kategori.">'+
                                    '</div>'+
                                '</div>'+
                        '</div>'+
                        '<div class="modal-footer">'+
                            '<button type="button" class="btn btn-default" data-dismiss="modal">Annuller</button>'+
                            '<button id="collection-create" type="button" class="btn btn-primary">Opret</button>'+
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
                        action: 'wpdkacollections_create_collection',
                        token: WPDKACollection.token
                    },
                    dataType: 'JSON',
                    type: 'POST',
                    success:function(data){
                        console.log(data);
                        confirmModal.modal('hide');
                    },
                    error: function(errorThrown){
                        alert("Error. Couldn't create collection.");
                        console.log("error.");
                        console.log(errorThrown);
                    }
                });

            });
        }
    };

    //Initiate class on page load
    $(document).ready(function(){ wpdkacollections.init(); });

})(jQuery);
