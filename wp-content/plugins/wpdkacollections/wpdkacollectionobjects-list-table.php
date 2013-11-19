<?php

class WPDKACollectionObjects_List_Table extends WPDKACollections_List_Table {

    const NAME_SINGULAR = 'dka-collection-object';
    const NAME_PLURAL = 'dka-collection-objects';

    protected $_current_collection;

    /**
     * Constructor
     */
    public function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => self::NAME_SINGULAR,
            'plural'    => self::NAME_PLURAL,
            'ajax'      => false        //does this table support ajax?
        ) );

        $this->title = sprintf(__('Collection: %s', 'wpdkacollections'), $this->get_current_collection());
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
        // $actions = array(
        //     'edit' => '<a href="'.add_query_arg(array('page' => $_REQUEST['page'], 'action' => 'edit', $this->_args['singular'] => $item->GUID), 'admin.php').'">'.__('Edit','wpdkacollections').'</a>',
        //     'remove' => '<a class="submitdelete" href="'.add_query_arg(array('page' => $_REQUEST['page'], 'action' => 'remove', $this->_args['singular'] => $item->GUID), 'admin.php').'">'.__('Remove','wpdkacollections').'</a>',
        //     'show' => '<a href="'.$this->_collections_related_item[$item->ObjectRelations[0]->Object1GUID]->url.'" target="_blank">'.__('Show material').'</a>'
        // );
        $actions = array();

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

    /**
     * Prepare table with columns, data, pagination etc.
     * @return void
     */
    public function prepare_items() {

        $per_page = $this->get_items_per_page( 'edit_wpdkacollections_per_page');

        //Set column headers
        $hidden = array();
        $this->_column_headers = array($this->get_columns(), $hidden, $this->get_sortable_columns());      

        $query = "GUID:".$_GET[parent::NAME_SINGULAR];

        //Get collection object
        $serviceResult = WPChaosClient::instance()->Object()->Get(
            $query,   // Search query
            null,   // Sort
            false,   // Use session instead of AP
            0,      // pageIndex
            1,      // pageSize
            true,   // includeMetadata
            false,   // includeFiles
            true    // includeObjectRelations
        );

        //Instantiate collection
        $collection = WPChaosObject::parseResponse($serviceResult,WPDKACollections::OBJECT_FILTER_PREFIX);
        $this->_current_collection = $collection[0];

        $this->title = '<a href="'.add_query_arg('page',WPDKACollections::DOMAIN,'admin.php').'">'.__('DKA Collections', WPDKACollections::DOMAIN).'</a> &raquo; '.$this->_current_collection->title;

        //Get relations
        $relation_guids = array();
        foreach($this->_current_collection->ObjectRelations as $relation) {
        	if($this->_current_collection->GUID != $relation->Object1GUID) {
        		$relation_guids[] = $relation->Object1GUID;
        	} else {
        		$relation_guids[] = $relation->Object2GUID;
        	}
        }

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
        
        //Set items
        $this->items = WPChaosObject::parseResponse($serviceResult2);
        
        //Set pagination
        //$serviceResult->MCM()->TotalPages() cannot be trusted here!
        $this->set_pagination_args( array(
            'total_items' => $serviceResult2->MCM()->TotalCount(),
            'per_page'    => $per_page,
            'total_pages' => ceil($serviceResult2->MCM()->TotalCount()/$per_page)
        ) );
    }
    
}