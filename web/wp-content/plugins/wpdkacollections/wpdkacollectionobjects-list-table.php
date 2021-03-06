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

		$this->title = '<a href="'.add_query_arg('page',WPDKACollections::DOMAIN,'admin.php').'">'.__('DKA Collections', WPDKACollections::DOMAIN).'</a>';

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
						forcePlaceholderSize: true,
						axis: "y"
					}).disableSelection();

					var wpdkaSortGuids = [];

					$('#wpdkacollections-sort').click(function(e) {
						e.preventDefault();
						wpdkaSortGuids = [];
						$('.dka-materials tbody input:checkbox').each( function() {
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
	 * @param  WPChaosDataObject    $item
	 * @param  string           $column_name
	 * @return string
	 */
	protected function column_default($item, $column_name) {
		return $item->$column_name;
	}

	/**
	 * Render title column
	 * @param  WPChaosDataObject    $item
	 * @return string
	 */
	protected function column_title($item) {

		//Build row actions
		$actions = array(
			'remove' => '<a class="submitdelete" href="'.add_query_arg(array('page' => $_REQUEST['page'], 'subpage'=> 'wpdkacollection-objects', 'action' => 'remove', 'dka-collection' => $this->_current_collection->GUID, 'dka-material' => $item->GUID),  'admin.php').'">'.__('Remove',WPDKACollections::DOMAIN).'</a>'
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
	 * @param  WPChaosDataObject    $item
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
			'organization'      => __('Organization', WPDKACollections::DOMAIN)
		);
		return $columns;
	}

	/**
	 * Get list of registered sortable columns
	 * @return array
	 */
	public function get_sortable_columns() {
		return array();
	}


	/**
	 * Get list of bulk actions
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'remove' => __('Remove', WPDKACollections::DOMAIN)
		);
		return $actions;
	}

	/**
	 * Add sort button to table
	 * @param  string    $which
	 * @return void
	 */
	public function extra_tablenav( $which ) {
		if ( $which == "top" ){
			echo '<div class="alignleft actions"><input type="button" id="wpdkacollections-sort" class="button-primary button" value="' . __('Save new sorting', WPDKACollections::DOMAIN) . '" /></div>';
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

		if(isset($_GET[parent::NAME_SINGULAR])) {
			$current_collection = esc_html($_GET[parent::NAME_SINGULAR]);
		} else {
			$current_collection = " ";
		}

		$this->_current_collection = WPDKACollections::get_collection_by_guid($current_collection);

		//If current collection exists
		if($this->_current_collection) {
			$pagesize = count($this->_current_collection->playlist_raw);

			//If there are some materials in collection
			if($pagesize > 0) {

				$this->title .= ' &raquo; '.$this->_current_collection->title;
				$this->guid = $current_collection;
				//Get the related objects to the collection.
				$serviceResult2 = WPChaosClient::instance()->Object()->Get(
					"(GUID:(".implode(" OR ", $this->_current_collection->playlist_raw)."))",   // Search query
					null,   // Sort
					null,   // AP injected
					0,      // pageIndex
					$pagesize, // pageSize
					true,   // includeMetadata
					false,   // includeFiles
					false    // includeObjectRelations
				);

				//If the materials from collection playlist exist in CHAOS
				if($serviceResult2->MCM()->Count() > 0) {
					$result3 = array();
					foreach($serviceResult2->MCM()->Results() as $result) {
						$result3[$result->GUID] = new WPChaosDataObject($result);
					}

					//Set items in proper order
					foreach($this->_current_collection->playlist_raw as $guid) {
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



		}

	}

}
