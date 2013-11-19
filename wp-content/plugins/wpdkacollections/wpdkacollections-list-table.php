<?php

class WPDKACollections_List_Table extends WP_List_Table {

	const NAME_SINGULAR = 'dka-collection';
	const NAME_PLURAL = 'dka-collections';

	// Needs to be changed to collection.
	const FACET_KEY_VALUE = 'DKA-Collection-Value_string';
	const FACET_KEY_STATUS = 'DKA-Collection-Status_string';
	const FACET_KEY_CREATED = 'DKA-Collection-Created_date';

	protected $title;
	
	public function __construct(){
		global $status, $page;
				
		//Set parent defaults
		parent::__construct( array(
			'singular'  => self::NAME_SINGULAR,
			'plural'    => self::NAME_PLURAL,
			'ajax'      => false        //does this table support ajax?
		) );

		$this->title = __('Collections', 'wpdkacollections');
	}

	public function get_title() {
		echo $this->title;
	}

	/**
	 * Display the list of views available on this table.
	 */
	public function get_views() {

		$total_count = 0;
		$facets = array();
		$facetsResponse = WPChaosClient::instance()->Index()->Search(WPChaosClient::generate_facet_query(array(self::FACET_KEY_STATUS)), null, false);

		foreach($facetsResponse->Index()->Results() as $facetResult) {
			foreach($facetResult->FacetFieldsResult as $fieldResult) {
				foreach($fieldResult->Facets as $facet) {
					$facets[$facet->Value] = $facet->Count;
					$total_count += $facet->Count;
				}
			}
		}

		$status_links = array();
		$status_links['all'] = '<a href="admin.php?page='.$this->screen->parent_base.'" class="current">' . sprintf( _nx( 'All <span class="count">(%s, %s unique)</span>', 'All <span class="count">(%s, %s unique)</span>', $total_count, 'posts' ), $total_count, number_format_i18n( $this->get_pagination_arg('total_items') ) ) . '</a>';
		
		$status_links['add'] = '<a href="admin.php?page='.$this->screen->parent_base.'&amp;subpage=wpdkacollection-objects" class="addCollection">' . __('Add new collection','wpdkacollections') . '</a>';
		wp_enqueue_script('bootstrapjs',plugins_url( 'js/bootstrap.min.js' , __FILE__ ),array('jquery'),'1.0',true);
		wp_enqueue_style('bootstrapcss', plugins_url( 'css/bootstrap.min.css' , __FILE__ ),true);
		return $status_links;
	}
	
	
	/**
	 * Render columns.
	 * Fallback if function column_{name} does not exist
	 * @param  WPChaosObject    $item
	 * @param  string           $column_name
	 * @return string
	 */
	protected function column_default($item, $column_name){
		return $item->$column_name;
	}
	
		
	/**
	 * Render title column
	 * @param  WPChaosObject    $item
	 * @return string
	 */
	protected function column_title($item){

		$actions = array();
		
		//Return the title contents
		return sprintf('<strong><a href="%1$s">%2$s</a></strong>%3$s',
			add_query_arg(array('page' => $_REQUEST['page'], 'subpage' => 'wpdkacollection-objects', $this->_args['singular'] => $item->GUID), 'admin.php'),
			$item->title,
			$this->row_actions($actions)
		);
	}

	/**
	 * Render playlist column
	 * @param  WPChaosObject    $item
	 * @return string
	 */
	protected function column_playlist($item){
		return count($item->ObjectRelations);
	}
	
	/**
	 * Render checkbox column
	 * @param  WPChaosObject    $item
	 * @return string
	 */
	protected function column_cb($item){
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
	public function get_columns(){
		$columns = array(
			'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
			'title'     => __('Title', WPDKACollections::DOMAIN),
			'description' => __('Description', WPDKACollections::DOMAIN),
			'rights' => __('Rights', WPDKACollections::DOMAIN),
			'type' => __('Type', WPDKACollections::DOMAIN),
			'playlist' => __('Materials', WPDKACollections::DOMAIN)
		);
		return $columns;
	}
	
    /**
     * Get list of registered sortable columns
     * @return array
     */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'title'     => array('title',false),     //true means it's already sorted
			'quantity'    => array('quantity',true)
		);
		return $sortable_columns;
	}
	
	
	/**
	 * Get list of bulk actions
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'delete' => __('Delete', 'wpdkacollections')
		);
		return $actions;
	}
	
	/**
	 * Prepare table with columns, data, pagination etc.
	 * @return void
	 */
	public function prepare_items() { 
		$per_page = $this->get_items_per_page('edit_wpdkacollections_per_page');
		//$per_page = 5;
		
		$hidden = array();
		$this->_column_headers = array($this->get_columns(), $hidden, $this->get_sortable_columns());

		$response = WPChaosClient::instance()->Object()->Get(
				'(FolderID:'.WPDKACollections::COLLECTIONS_FOLDER_ID.')',   // Search query
				null,   // Sort
				false, 
				0,      // pageIndex
				$per_page,      // pageSize
				true,   // includeMetadata
				false,   // includeFiles
				true    // includeObjectRelations
			);

		$this->items = WPChaosObject::parseResponse($response,WPDKACollections::OBJECT_FILTER_PREFIX);
		
		$this->set_pagination_args( array(
			'total_items' => $response->MCM()->TotalCount(),
			'per_page'    => $per_page,
			'total_pages' => ceil($response->MCM()->TotalCount()/$per_page)
		) );
	}
	
}
