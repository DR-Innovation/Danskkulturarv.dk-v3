<?php

class WPDKACollections_List_Table extends WP_List_Table {

	const NAME_SINGULAR = 'dka-collection';
	const NAME_PLURAL = 'dka-collections';

	protected $title;
	protected $states;

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

		$this->title = __('DKA Collections', WPDKACollections::DOMAIN);
		$this->states = array(
			'publish' => array(
				'title' => __('Published',WPDKACollections::DOMAIN),
				'count' => 0,
				'action' => __('Publish',WPDKACollections::DOMAIN)
			),
			'draft' => array(
				'title' => __('Draft',WPDKACollections::DOMAIN),
				'count' => 0,
				'action' => __('Draft',WPDKACollections::DOMAIN)
			)
		);
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
		$facetsResponse = WPChaosClient::instance()->Index()->Search(WPChaosClient::generate_facet_query(array(WPDKACollections::FACET_KEY_STATUS)), "(FolderID:".WPDKACollections::COLLECTIONS_FOLDER_ID.")", false);

		foreach($facetsResponse->Index()->Results() as $facetResult) {
			foreach($facetResult->FacetFieldsResult as $fieldResult) {
				foreach($fieldResult->Facets as $facet) {
					$facets[$facet->Value] = $facet->Count;
					$total_count += $facet->Count;
				}
			}
		}

		$status_links = array();

		$class = empty($_REQUEST['tag_status']) ? ' class="current"' : '';
		$status_links['all'] = '<a href="admin.php?page='.$this->screen->parent_base.'"'.$class.'>' . sprintf( _nx( 'All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', $total_count, 'posts' ), number_format_i18n($total_count) ) . '</a>';

		foreach($this->states as $status_key => $status) {
			$class = '';
			$count = (isset($facets[$status_key]) ? $facets[$status_key] : 0);
			if(isset($_REQUEST['tag_status']) && $_REQUEST['tag_status'] == $status_key)
				$class = ' class="current"';
			$status_links[$status_key] = '<a href="admin.php?page='.$this->screen->parent_base.'&amp;tag_status='.$status_key.'"'.$class.'>'. sprintf( '%s <span class="count">(%s)</span>', $status['title'], number_format_i18n( $count ) ) . '</a>';
		}

		return $status_links;
	}


	/**
	 * Render columns.
	 * Fallback if function column_{name} does not exist
	 * @param  WPChaosDataObject    $item
	 * @param  string           $column_name
	 * @return string
	 */
	protected function column_default($item, $column_name){
		return $item->$column_name;
	}


	/**
	 * Render title column
	 * @param  WPChaosDataObject    $item
	 * @return string
	 */
	protected function column_title($item){

		$current_page = "admin.php?".http_build_query(array('page' => $_REQUEST['page'], $this->_args['singular'] => $item->GUID));

		$actions = array();

		$actions['quickedit'] = '<a class="wpdkacollections-quickedit" href="#" id="'.$item->GUID.'">'.__('Quick Edit').'</a>';
		$actions['delete'] = '<a class="submitdelete" href="'.add_query_arg(array('action' => 'delete'), $current_page).'">'.__('Delete').'</a>';

		//Return the title contents
		return sprintf('<strong><a href="%1$s">%2$s</a></strong>%3$s',
			add_query_arg(array('page' => $_REQUEST['page'], 'subpage' => 'wpdkacollection-objects', $this->_args['singular'] => $item->GUID), 'admin.php'),
			$item->title,
			$this->row_actions($actions)
		);
	}

	/**
	 * Render playlist column
	 * @param  WPChaosDataObject    $item
	 * @return string
	 */
	protected function column_playlist($item){
		return count($item->ObjectRelations);
	}

	/**
	 * Render checkbox column
	 * @param  WPChaosDataObject    $item
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
			'status' => __('Status', WPDKACollections::DOMAIN),
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
			'title'    => array(WPDKACollections::FACET_KEY_TITLE,false),
			'type'      => array(WPDKACollections::FACET_KEY_TYPE,false),
			'status'      => array(WPDKACollections::FACET_KEY_STATUS,false)
		);
		return $sortable_columns;
	}


	/**
	 * Get list of bulk actions
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'delete' => __('Delete', WPDKACollections::DOMAIN)
		);
		return $actions;
	}

	public function extra_tablenav( $which ) {
	}

	/**
	 * Prepare table with columns, data, pagination etc.
	 * @return void
	 */
	public function prepare_items() {
		$per_page = $this->get_items_per_page('edit_wpdkacollections_per_page');
		//$per_page = 60;

		$hidden = array();
		$this->_column_headers = array($this->get_columns(), $hidden, $this->get_sortable_columns());

		//Append status query if present
		$query = '(FolderID:'.WPDKACollections::COLLECTIONS_FOLDER_ID.')';
		if(isset($_GET['tag_status'])) {
			$query .= " AND (".WPDKACollections::FACET_KEY_STATUS.":".$_GET['tag_status'].")";
		}

		//Sort query
		$sort = WPDKACollections::FACET_KEY_TITLE.'+asc';
		if(isset($_REQUEST['orderby']) && isset($_REQUEST['order'])) {
			$orderby = (in_array($_REQUEST['orderby'],array(WPDKACollections::FACET_KEY_TITLE,WPDKACollections::FACET_KEY_TYPE,WPDKACollections::FACET_KEY_STATUS)) ? $_REQUEST['orderby'] : WPDKACollections::FACET_KEY_TITLE);
			$order = (in_array($_REQUEST['order'],array('asc','desc')) ? $_REQUEST['order'] : 'asc');
			$sort = $orderby."+".$order;
		}

		$response = WPChaosClient::instance()->Object()->Get(
				$query,   // Search query
				$sort,   // Sort
				null,
				$this->get_pagenum()-1,      // pageIndex
				$per_page,      // pageSize
				true,   // includeMetadata
				false,   // includeFiles
				true    // includeObjectRelations
			);

		$this->items = WPChaosDataObject::parseResponse($response,WPDKACollections::OBJECT_FILTER_PREFIX);
		$this->set_pagination_args( array(
			'total_items' => $response->MCM()->TotalCount(),
			'per_page'    => $per_page,
			'total_pages' => ceil($response->MCM()->TotalCount()/$per_page)
		) );
	}

}
