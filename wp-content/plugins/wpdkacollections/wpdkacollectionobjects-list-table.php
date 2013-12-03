<?php

class WPDKACollectionObjects_List_Table extends WPDKACollections_List_Table {

    const NAME_SINGULAR = 'dka-material';
    const NAME_PLURAL = 'dka-materials';

    protected $_current_collection;

    /**
     * Constructor
     */
    public function __construct($args = array()){
                
        $args = wp_parse_args( $args, array(
			'singular'  => self::NAME_SINGULAR,
			'plural'    => self::NAME_PLURAL,
			'ajax'      => false        //does this table support ajax?
		) );
				
		//Set parent defaults
		parent::__construct( $args );

        wp_enqueue_script('jquery-ui-sortable');
        add_action('admin_footer', function() {
        	?>
        	<script type="text/javascript">
        		jQuery(document).ready(function($) {
					// Return a helper with preserved width of cells
					var fixHelper = function(e, ui) {
						ui.children().each(function() {
							$(this).width($(this).width());
						});
						return ui;
					};

					$(".dka-materials tbody").sortable({
						helper: fixHelper,
						forcePlaceholderSize: true
					}).disableSelection();

					var wpdkaSortGuids = [];

					$('#wpdkacollections-sort').click(function(e) {
						e.preventDefault();
						wpdkaSortGuids = [];
						$('.dka-collections tbody input:checkbox').each( function() {
							 wpdkaSortGuids.push($(this).val());
						});

						var button = $(this);
						button.attr('disabled',true);

						$.ajax({
							url: ajaxurl,
							data:{
								action: 'wpdkacollections_sortable',
								guids: wpdkaSortGuids,
								collection_guid: $('input[name="dka-collection"]').val(),
								nonce: $("#_wpnonce").val()
							},
							dataType: 'JSON',
							type: 'POST',
							success:function(data){
								console.log(data);
								button.attr('disabled',false);
								
							},
							error: function(errorThrown){
								button.attr('disabled',false);
							}
						});
					});
					
				});
        	</script>
        	<?php
        });
    }

    /**
     * Get current collection (if there are any)
     * @return string
     */
    protected function get_current_collection() {
        return $this->_current_collection;
    }

        /**
     * Display the list of views available on this table.
     */
    public function get_views() {

        
    }

    /**
     * Render columns.
     * Fallback if function column_{name} does not exist
     * @param  WPChaosObject    $item
     * @param  string           $column_name
     * @return string
     */
    protected function column_default($item, $column_name) {
        return $item->$column_name;
    }

    /**
     * Render title column
     * @param  WPChaosObject    $item
     * @return string
     */
    protected function column_title($item) {
        
        //Build row actions
        $actions = array(
            'remove' => '<a class="submitdelete" href="'.add_query_arg(array('page' => $_REQUEST['page'], 'subpage'=> 'wpdkacollection-objects', 'action' => 'remove', $this->_args['singular'] => $this->_current_collection->GUID, 'dka-material' => $item->GUID),  'admin.php').'">'.__('Remove','wpdkacollections').'</a>'
        );

        //Return the title contents
        return sprintf('<strong><a href="%1$s">%2$s</a></strong>%3$s',
            $item->url,
            $item->title,
            $this->row_actions($actions)
        );
    }

    /**
     * Render checkbox column
     * @param  WPChaosObject    $item
     * @return string
     */
    protected function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item->GUID                //The value of the checkbox should be the record's id
        );
    }

    /**
     * Get list of registered columns
     * @return array
     */
    public function get_columns() {
        $columns = array(
            'cb'        => '<input type="checkbox" />',
            'title'     => __('Title'),
            'organization'      => __('Organization', 'wpdkacollections')
        );
        return $columns;
    }

    /**
     * Get list of registered sortable columns
     * @return array
     */
    public function get_sortable_columns() {
        $sortable_columns = array(
            'title'     => array('title',false), //true means it's already sorted
            'date'      => array('date',true)
        );
        return $sortable_columns;
    }
    
    
	/**
	 * Get list of bulk actions
	 * @return array
	 */
    public function get_bulk_actions() {
        $actions = array(
            'remove' => __('Remove', 'wpdkacollections')
        );
        return $actions;
    }

	function extra_tablenav( $which ) {
		if ( $which == "top" ){
			echo '<div class="alignleft actions"><input type="button" id="wpdkacollections-sort" class="button-primary button" value="' . __('Save new sorting', 'wpdkacollections') . '" /></div>';
		}
	}
	
    /**
     * Prepare table with columns, data, pagination etc.
     * @return void
     */
    public function prepare_items() {

        //$per_page = $this->get_items_per_page( 'edit_wpdkacollections_per_page');
        $per_page = 1000;

        //Set column headers
        $hidden = array();
        $this->_column_headers = array($this->get_columns(), $hidden, $this->get_sortable_columns());      

        $this->_current_collection = WPDKACollections::get_current_collection();

        $this->title = '<a href="'.add_query_arg('page',WPDKACollections::DOMAIN,'admin.php').'">'.__('DKA Collections', WPDKACollections::DOMAIN).'</a> &raquo; '.$this->_current_collection->title;

      	$relation_guids = $this->_current_collection->playlist_raw;

        //Get the related objects to the collection.
        $serviceResult2 = WPChaosClient::instance()->Object()->Get(
            "(GUID:(".implode(" OR ", $relation_guids)."))",   // Search query
            null,   // Sort
            null,   // AP injected
            0,      // pageIndex
            count($relation_guids), // pageSize
            true,   // includeMetadata
            false,   // includeFiles
            false    // includeObjectRelations
        );
        $result3 = array();
        foreach($serviceResult2->MCM()->Results() as $result) {
        	$result3[$result->GUID] = new WPChaosObject($result);
        }

        //Set items in proper order
        foreach($relation_guids as $guid) {
        	$this->items[] = $result3[(string)$guid];
        }

        //Set pagination
        //$serviceResult->MCM()->TotalPages() cannot be trusted here!
        $this->set_pagination_args( array(
            'total_items' => $serviceResult2->MCM()->TotalCount(),
            'per_page'    => $per_page,
            'total_pages' => ceil($serviceResult2->MCM()->TotalCount()/$per_page)
        ) );
    }
    
}