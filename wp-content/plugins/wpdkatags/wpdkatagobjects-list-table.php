<?php

class WPDKATagObjects_List_Table extends WPDKATags_List_Table {

	const NAME_SINGULAR = 'dka-tag-object';
	const NAME_PLURAL = 'dka-tag-objects';

	protected $_tags_related_item = array();
	protected $_tags_metadata = array();
	protected $_current_tag;

	/**
	 * Constructor
	 */
	public function __construct($args = array()){
		global $status, $page;
				
		//Set parent defaults
		parent::__construct( array(
			'singular'  => self::NAME_SINGULAR,
			'plural'    => self::NAME_PLURAL,
			'ajax'      => false        //does this table support ajax?
		) );

		$this->_current_tag = esc_html($_GET[parent::NAME_SINGULAR]);

		$this->title = '<a href="'.add_query_arg('page','wpdkatags','admin.php').'">'.__('User Tags', 'wpdkatags').'</a> &raquo; '.$this->get_current_tag();

		wp_enqueue_script('dka-usertags-admin',plugins_url( 'js/admin_functions.js' , __FILE__ ),array('jquery'),'1.0',true);
	}

	/**
	 * Get current tag
	 * @return string
	 */
	protected function get_current_tag() {
		return $this->_current_tag;
	}

		/**
	 * Display the list of views available on this table.
	 */
	public function get_views() {

		$facets = array();
		$query = "(".WPDKATags::FACET_KEY_VALUE.":(".WPChaosClient::escapeSolrValue($this->get_current_tag())."))";
		$facetsResponse = WPChaosClient::instance()->Index()->Search(WPChaosClient::generate_facet_query(array(WPDKATags::FACET_KEY_STATUS)), $query, false);

		foreach($facetsResponse->Index()->Results() as $facetResult) {
			foreach($facetResult->FacetFieldsResult as $fieldResult) {
				foreach($fieldResult->Facets as $facet) {
					$facets[$facet->Value] = $facet->Count;
				}
			}
		}

		$status_links = array();

		$class = empty($_REQUEST['tag_status']) ? ' class="current"' : '';
		$status_links['all'] = '<a href="admin.php?page='.$this->screen->parent_base.'&amp;subpage=wpdkatag-objects&amp;dka-tag='.$this->get_current_tag().'"'.$class.'>' . sprintf( _nx( 'All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', $this->get_pagination_arg('total_items'), 'posts' ), number_format_i18n( $this->get_pagination_arg('total_items') ) ) . '</a>';

		foreach($this->states as $status_key => $status) {
			$class = '';
			$count = (isset($facets[$status_key]) ? $facets[$status_key] : 0);
			if(isset($_REQUEST['tag_status']) && $_REQUEST['tag_status'] == $status_key)
				$class = ' class="current"';
			$status_links[$status_key] = '<a href="admin.php?page='.$this->screen->parent_base.'&amp;subpage=wpdkatag-objects&amp;dka-tag='.$this->get_current_tag().'&amp;tag_status='.$status_key.'"'.$class.'>'. sprintf( '%s <span class="count">(%s)</span>', $status['title'], number_format_i18n( $count ) ) . '</a>';
		}

		return $status_links;
	}

	/**
	 * Render columns.
	 * Fallback if function column_{name} does not exist
	 * @param  WPChaosObject    $item
	 * @param  string           $column_name
	 * @return string
	 */
	protected function column_default($item, $column_name) {

		switch($column_name) {
			case 'material_title':
				$material = $this->_tags_related_item[$item->ObjectRelations[0]->Object1GUID];
				return '<a href="'.$material->url.'" target="_blank">'.$material->title.'</a>';
			case 'status':

				$status = $this->_tags_metadata[$item->GUID]['status'];

				switch($status) {
					case 'Approved':
						$status = '<span style="color:green">'.$status.'</span>';
						break;
					case 'Flagged':
						$status = '<span style="color:red">'.$status.'</span>';
						break;
					case 'Unapproved':
						$status = '<span style="color:orange">'.$status.'</span>';
				}

				return $status;
			case 'date':
				$time = strtotime($this->_tags_metadata[$item->GUID]['created']);
				$time_diff = time() - $time;
				if ($time_diff > 0 && $time_diff < WEEK_IN_SECONDS )
					$time = sprintf( __( '%s ago' ), human_time_diff( $time ) );
				else
					$time = date_i18n(get_option('date_format'),$time);
				return $time;
			default:
				return print_r($item,true); //Show the whole array for troubleshooting purposes
		}
	}

	/**
	 * Render title column
	 * @param  WPChaosObject    $item
	 * @return string
	 */
	protected function column_title($item) {

		$actions = array();
		$current_page = "admin.php?".http_build_query(array('page' => $_REQUEST['page'], 'subpage' => $_REQUEST['subpage'], parent::NAME_SINGULAR => $_REQUEST[parent::NAME_SINGULAR] /*, 'noheader' => true*/));

		$actions['rename'] = '<a class="wpdkatags-rename" href="#" id="'.$item->GUID.'">'.__('Rename','wpdkatags').'</a>';

		foreach($this->states as $state_k => $state) {
			if($this->_tags_metadata[$item->GUID]['status'] != ucfirst($state_k)) {
				$url = wp_nonce_url(add_query_arg(array('action' => $state_k, $this->_args['singular'] => $item->GUID), $current_page),$state_k.'_'.$item->GUID);
				$actions[$state_k] = '<a href="'.$url.'">'.$state['action'].'</a>';	
			}
		}

		$actions['delete'] = '<a class="submitdelete" href="'.add_query_arg(array('action' => 'delete', $this->_args['singular'] => $item->GUID), $current_page).'">'.__('Delete').'</a>';

		//Return the title contents
		return sprintf('<strong>%1$s</strong>%2$s',
			$this->_tags_metadata[$item->GUID],
			$this->row_actions($actions)
		);
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
			'cb'        => '<input type="checkbox" />',
			'title'     => __('Title','wpdkatags'),
			'status'    => __('Status', 'wpdkatags'),
			'material_title' => __('Material Title','wpdkatags'),
			'date'      => __('Date', 'wpdkatags')
		);
		return $columns;
	}

	/**
	 * Get list of registered sortable columns
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'status'    => array(WPDKATags::FACET_KEY_STATUS,false),
			'date'      => array(WPDKATags::FACET_KEY_CREATED,true) //true means it's already sorted
		);
		return $sortable_columns;
	}
	
	
	/** ************************************************************************
	 * Optional. If you need to include bulk actions in your list table, this is
	 * the place to define them. Bulk actions are an associative array in the format
	 * 'slug'=>'Visible Title'
	 * 
	 * If this method returns an empty value, no bulk action will be rendered. If
	 * you specify any bulk actions, the bulk actions box will be rendered with
	 * the table automatically on display().
	 * 
	 * Also note that list tables are not automatically wrapped in <form> elements,
	 * so you will need to create those manually in order for bulk actions to function.
	 * 
	 * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
	 **************************************************************************/
	public function get_bulk_actions() {
		$actions = array(
			'rename' => __('Rename','wpdkatags'),		
			'approved' => __('Approve', 'wpdkatags'),
			'unapproved' => __('Unapprove', 'wpdkatags'),
			'flagged' => __('Flag', 'wpdkatags'),
			'delete' => __('Delete', 'wpdkatags')
		);
		return $actions;
	}

	/**
	 * Prepare table with columns, data, pagination etc.
	 * @return void
	 */
	public function prepare_items() {

		$per_page = $this->get_items_per_page( 'edit_wpdkatags_per_page');
		//$per_page = 1;

		//Set column headers
		$hidden = array();
		$this->_column_headers = array($this->get_columns(), $hidden, $this->get_sortable_columns());

		//Get tags by name
		$query = "(".WPDKATags::FACET_KEY_VALUE.":(".WPChaosClient::escapeSolrValue($this->get_current_tag())."))+AND+ObjectTypeID:".WPDKATags::TAG_TYPE_ID;
		if(isset($_GET['tag_status'])) {
			$query .= "+AND+".WPDKATags::FACET_KEY_STATUS.":".$_GET['tag_status'];
		}

		$sort = null;
		if(isset($_REQUEST['orderby']) && isset($_REQUEST['order'])) {
			$orderby = (in_array($_REQUEST['orderby'],array(WPDKATags::FACET_KEY_STATUS,WPDKATags::FACET_KEY_CREATED)) ? $_REQUEST['orderby'] : WPDKATags::FACET_KEY_CREATED);
			$order = (in_array($_REQUEST['order'],array('asc','desc')) ? $_REQUEST['order'] : 'asc');
			$sort = $orderby."+".$order;
		}

		//Get tag objects by name
		//A tag is NOT unique by name, as the object<->tag relation is 1:1
		$serviceResult = WPChaosClient::instance()->Object()->Get(
			$query,   // Search query
			$sort,   // Sort
			false,   // Use session instead of AP
			$this->get_pagenum()-1,      // pageIndex
			$per_page,      // pageSize
			true,   // includeMetadata
			false,   // includeFiles
			true    // includeObjectRelations
		);

		//Instantiate tags from serviceResult
		$tags = WPChaosObject::parseResponse($serviceResult);

		if(!empty($tags)) {
			//Loop through tags to get and cache metadata and get relations
			$relation_guids = array();
			foreach($tags as $object) {
				$this->_tags_metadata[$object->GUID] = $object->metadata(
					array(WPDKATags::METADATA_SCHEMA_GUID),
					array(''),
					null
				);
				foreach($object->ObjectRelations as $relation) {
					$relation_guids[] = "GUID:".$relation->Object1GUID;
					$relation_guids_map[$relation->Object1GUID] = $object->GUID;
				}
			}

			//Get the related objects to the tags.
			//The quantity we get here should at most be the quantity we got in $serviceResult
			$serviceResult2 = WPChaosClient::instance()->Object()->Get(
				"(".implode("+OR+", $relation_guids).")",   // Search query
				null,   // Sort
				null,   // AP injected
				0,      // pageIndex
				$per_page,      // pageSize
				true,   // includeMetadata
				false,   // includeFiles
				false    // includeObjectRelations
			);

			//Loop through objects to make them available for later use
			foreach($serviceResult2->MCM()->Results() as $object) {
				$this->_tags_related_item[$object->GUID] = new WPChaosObject($object);
			}			
		}
		
		//Set items
		$this->items = $tags;
		
		//Set pagination
		//$serviceResult->MCM()->TotalPages() cannot be trusted here!
		$this->set_pagination_args( array(
			'total_items' => $serviceResult->MCM()->TotalCount(),
			'per_page'    => $per_page,
			'total_pages' => ceil($serviceResult->MCM()->TotalCount()/$per_page)
		) );

	}
	
}