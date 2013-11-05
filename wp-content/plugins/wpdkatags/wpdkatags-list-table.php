<?php
/************************** CREATE A PACKAGE CLASS *****************************
 *******************************************************************************
 * Create a new list table package that extends the core WP_List_Table class.
 * WP_List_Table contains most of the framework for generating the table, but we
 * need to define and override some methods so that our data can be displayed
 * exactly the way we need it to be.
 * 
 * To display this example on a page, you will first need to instantiate the class,
 * then call $yourInstance->prepare_items() to handle any data manipulation, then
 * finally call $yourInstance->display() to render the table to the page.
 * 
 * Our theme for this list table is going to be movies.
 */
class WPDKATags_List_Table extends WP_List_Table {

	const NAME_SINGULAR = 'dka-tag';
	const NAME_PLURAL = 'dka-tags';

	protected $title;
	protected $states;
	
	public function __construct($args = array()){
		global $status, $page;

		$args = wp_parse_args( $args, array(
			'singular'  => self::NAME_SINGULAR,
			'plural'    => self::NAME_PLURAL,
			'ajax'      => false        //does this table support ajax?
		) );
				
		//Set parent defaults
		parent::__construct( $args );

		$this->title = __('DKA User Tags',WPDKATags::DOMAIN);
		$this->states = array(
			'unapproved' => array(
				'title' => __('Unapproved',WPDKATags::DOMAIN),
				'count' => 0,
				'action' => __('Unapprove',WPDKATags::DOMAIN)
			),
			'flagged' => array(
				'title' => __('Flagged',WPDKATags::DOMAIN),
				'count' => 0,
				'action' => __('Flag',WPDKATags::DOMAIN)
			),
			'approved' => array(
				'title' => __('Approved',WPDKATags::DOMAIN),
				'count' => 0,
				'action' => __('Approve',WPDKATags::DOMAIN)
			),
		);
	}

	public function get_title() {
		echo $this->title;
	}

	public function extra_tablenav($which) {

		// $states = array(
		//     1 => 'Active',
		//     2 => 'Frozen',
		//     3 => 'Hidden'
		// );

		// $current_status = isset($_GET['dka-tag-status']) ? intval($_GET['dka-tag-status']) : 0;

		// echo '<div class="alignleft actions">';
		// echo '<select name="dka-tag-status">';
		// echo '<option value="0"'.selected($current_status,0,false).'>All</option>';

		// foreach($states as $state_key => $state_title) {
		//     echo '<option value="'.$state_key.'"'.selected($current_status,$state_key,false).'>'.$state_title.'</option>';
		// }

		// echo '</select>';
		// submit_button( __( 'Filter' ), 'button', false, false, array( 'id' => 'post-query-submit' ) );
		// echo '</div>';
	}

	/**
	 * Display the list of views available on this table.
	 */
	public function get_views() {

		$total_count = 0;
		$facets = array();
		$facetsResponse = WPChaosClient::instance()->Index()->Search(WPChaosClient::generate_facet_query(array(WPDKATags::FACET_KEY_STATUS)), "(FolderID:".WPDKATags::TAGS_FOLDER_ID.")", false);

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
		// $status_links['all'] = '<a href="admin.php?page='.$this->screen->parent_base.'"'.$class.'>' . sprintf( _nx( 'All <span class="count">(%s, %s unique)</span>', 'All <span class="count">(%s, %s unique)</span>', $total_count, 'posts' ), $total_count, number_format_i18n( $this->get_pagination_arg('total_items') ) ) . '</a>';

		foreach($this->states as $status_key => $status) {
			$class = '';
			$count = (isset($facets[$status_key]) ? $facets[$status_key] : 0);
			if(isset($_REQUEST['tag_status']) && $_REQUEST['tag_status'] == $status_key)
				$class = ' class="current"';
			$status_links[$status_key] = '<a href="admin.php?page='.$this->screen->parent_base.'&amp;tag_status='.$status_key.'"'.$class.'>'. sprintf( '%s <span class="count">(%s)</span>', $status['title'], number_format_i18n( $count ) ) . '</a>';
		}

		return $status_links;
	}
	
	protected function column_default($item, $column_name){
		switch($column_name){
			case 'quantity':
				return $item->Count;
			default:
				return print_r($item,true); //Show the whole array for troubleshooting purposes
		}
	}
	
	protected function column_title($item){
		//Build row actions
		// $actions = array(
		// 	'edit'      => '<a href="'.add_query_arg(array('page' => $_REQUEST['page'], 'action' => 'edit', $this->_args['singular'] => $item->Value), 'admin.php').'">'.__('Edit').'</a>',
		// 	'delete'      => '<a class="submitdelete" href="'.add_query_arg(array('page' => $_REQUEST['page'], 'action' => 'delete', $this->_args['singular'] => $item->Value), 'admin.php').'">'.__('Delete').'</a>',
		// );

		//Return the title contents
		return sprintf('<p><strong><a href="%1$s">%2$s</a></strong></p>',
			add_query_arg(array('page' => $_REQUEST['page'], 'subpage' => 'wpdkatag-objects', $this->_args['singular'] => urlencode($item->Value)), 'admin.php'),
			esc_html($item->Value)
		);
		
		//Return the title contents
		// return sprintf('<strong><a href="%1$s">%2$s</a></strong>%3$s',
		// 	add_query_arg(array('page' => $_REQUEST['page'], 'subpage' => 'wpdkatag-objects', $this->_args['singular'] => $item->Value), 'admin.php'),
		// 	$item->Value,
		// 	$this->row_actions($actions)
		// );
	}
	
	protected function column_cb($item){
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
			/*$2%s*/ $item->Value                //The value of the checkbox should be the record's id
		);
	}

	public function get_columns(){
		$columns = array(
			//'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
			'title'     => __('Title', WPDKATags::DOMAIN),
			'quantity'    => __('Quantity',WPDKATags::DOMAIN),
		);
		return $columns;
	}
	
	public function get_sortable_columns() {
		$sortable_columns = array(
			'title'     => array('Value',false),     //true means it's already sorted
			'quantity'    => array('Count',true),
		);
		return $sortable_columns;
	}
	
	public function get_bulk_actions() {
		return array();
	}
	
	public function prepare_items() {

		$per_page = $this->get_items_per_page( 'edit_wpdkatags_per_page');
		//$per_page = 5;
		
		$hidden = array();
		$this->_column_headers = array($this->get_columns(), $hidden, $this->get_sortable_columns());
		
		//Append status query if present
		$query = "(FolderID:".WPDKATags::TAGS_FOLDER_ID.")";
		if(isset($_GET['tag_status'])) {
			$query = " AND (".WPDKATags::FACET_KEY_STATUS.":".$_GET['tag_status'].")";
		}
		
		//Get tags from index
		$tags = array();
		$facetsResponse = WPChaosClient::instance()->Index()->Search(WPChaosClient::generate_facet_query(array(WPDKATags::FACET_KEY_VALUE)), $query, false);
		foreach($facetsResponse->Index()->Results() as $facetResult) {
			foreach($facetResult->FacetFieldsResult as $fieldResult) {
				foreach($fieldResult->Facets as $facet) {
					if($facet->Count > 0) {
					   $tags[] = $facet; 
					}
				}
			}
		}

		//Order tags if requested
		if(isset($_REQUEST['orderby']) && isset($_REQUEST['order'])) {
			function usort_reorder($a,$b){
			    $orderby = in_array($_REQUEST['orderby'],array('Count','Value')) ? $_REQUEST['orderby'] : "Count";
			    $order = $_REQUEST['order'];
			    $result = strcmp($a->$orderby, $b->$orderby); //Determine sort order
			    return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
			}
			usort($tags, 'usort_reorder');			
		}

		$total_items = count($tags);
		$tags = array_slice($tags,(($this->get_pagenum()-1)*$per_page),$per_page);
		$this->items = $tags;
		
		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil($total_items/$per_page)
		) );
	}
	
}
