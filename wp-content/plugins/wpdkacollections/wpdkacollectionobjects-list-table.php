<?php

class WPDKACollectionObjects_List_Table extends WPDKACollections_List_Table {

    const NAME_SINGULAR = 'dka-collection-object';
    const NAME_PLURAL = 'dka-collection-objects';

    protected $_collections_related_item = array();
    protected $_collections_metadata = array();
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

        if (isset($_GET[self::NAME_SINGULAR]))
            $this->_current_collection = $_GET[self::NAME_SINGULAR];

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
        switch($column_name) {
            case 'date':
                $time = strtotime($this->_collections_metadata[$item->GUID]['created']);
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
        
        //Build row actions
        $actions = array(
            'edit' => '<a href="'.add_query_arg(array('page' => $_REQUEST['page'], 'action' => 'edit', $this->_args['singular'] => $item->GUID), 'admin.php').'">'.__('Edit','wpdkacollections').'</a>',
            'remove' => '<a class="submitdelete" href="'.add_query_arg(array('page' => $_REQUEST['page'], 'action' => 'remove', $this->_args['singular'] => $item->GUID), 'admin.php').'">'.__('Remove','wpdkacollections').'</a>',
            'show' => '<a href="'.$this->_collections_related_item[$item->ObjectRelations[0]->Object1GUID]->url.'" target="_blank">'.__('Show material').'</a>'
        );

        //Return the title contents
        return sprintf('<strong><a href="%1$s">%2$s</a></strong>%3$s',
            "#",
            $this->_collections_related_item[$item->ObjectRelations[0]->Object1GUID]->title,
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
            'title'     => __('Material Title','wpdkacollections'),
            'date'      => __('Date', 'wpdkacollections')
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
            'remove' => __('Remove', 'wpdkacollections')
        );
        return $actions;
    }
    
    
    /** ************************************************************************
     * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
     * For this example package, we will handle it in the class to keep things
     * clean and organized.
     * 
     * @see $this->prepare_items()
     **************************************************************************/
    protected function process_bulk_action() {
        
        //Detect when a bulk action is being triggered...
        switch ($this->current_action()) {
            case 'remove':
                // Delete tags TODO
                wp_die('Items removed from collection (or they would be if we had items to remove)!');
        }
        
    }

    /**
     * Prepare table with columns, data, pagination etc.
     * @return void
     */
    public function prepare_items() {

        // New collection
        if (!isset($this->_current_collection)) {
            // TODO load 
            return;
        }

        $per_page = $this->get_items_per_page( 'edit_wpdkacollections_per_page');

        //Set column headers
        $hidden = array();
        $this->_column_headers = array($this->get_columns(), $hidden, $this->get_sortable_columns());
        
        //Process actions
        $this->process_bulk_action();

        // $query = self::FACET_KEY_VALUE.":".$this->get_current_collection();"+AND+ObjectTypeID:".WPDKACollections::TAG_TYPE_ID;

        $query = '';

        //Get tag objects by name
        //A tag is NOT unique by name, as the object<->tag relation is 1:1
        $serviceResult = WPChaosClient::instance()->Object()->Get(
            $query,   // Search query
            null,   // Sort
            false,   // Use session instead of AP
            $this->get_pagenum()-1,      // pageIndex
            $per_page,      // pageSize
            true,   // includeMetadata
            false,   // includeFiles
            true    // includeObjectRelations
        );

        //Instantiate collections from serviceResult
        $collections = WPChaosObject::parseResponse($serviceResult);

        //Loop through collections to get and cache metadata and get relations
        $relation_guids = array();
        foreach($collections as $object) {
            $this->_tags_metadata[$object->GUID] = $object->metadata(
                array(WPDKACollections::METADATA_SCHEMA_GUID),
                array(''),
                null
            );
            foreach($object->ObjectRelations as $relation) {
                $relation_guids[] = "GUID:".$relation->Object1GUID;
                $relation_guids_map[$relation->Object1GUID] = $object->GUID;
            }
        }

        //Get the related objects to the collections.
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
            $this->_collections_related_item[$object->GUID] = new WPChaosObject($object);
        }
        
        //Set items
        $this->items = $collections;
        
        //Set pagination
        //$serviceResult->MCM()->TotalPages() cannot be trusted here!
        $this->set_pagination_args( array(
            'total_items' => $serviceResult->MCM()->TotalCount(),
            'per_page'    => $per_page,
            'total_pages' => ceil($serviceResult->MCM()->TotalCount()/$per_page)
        ) );
    }
    
}