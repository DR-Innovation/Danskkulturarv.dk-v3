<?php
/*
Plugin Name: WP DKA Collections
Plugin URI: 
Description: Manage generated collections for CHAOS
Version: 1.0
Author: Mads Lundt
Author URI: 
License: 
*/
final class WPDKACollections {

    const DOMAIN = 'wpdkacollections';

    const METADATA_SCHEMA_GUID = '00000000-0000-0000-0000-000065c30000';

    /**
     * ID = X is ""
     */
    const COLLECTIONS_TYPE_ID = 12; // CHANGE

    /**
     * ID = X is ""
     */
    const COLLECTIONS_FOLDER_ID = 470; // CHANGE

    /**
     * Token prefix for frontend AJAX submissions
     * Appended with object guid
     */
    const TOKEN_PREFIX = 'somestring';

    /**
     * Plugin dependencies
     * @var array
     */
    private static $plugin_dependencies = array(
        'wpchaosclient/wpchaosclient.php' => 'WordPress Chaos Client',
        'wpdka/wpdka.php' => 'WordPress DKA'
    );

    /**
     * Constructor
     */
    public function __construct() {
        if(self::check_chaosclient()) {
            if(is_admin()) {
                $this->load_dependencies();
                add_action('admin_menu', array(&$this, 'loadJsCss'));
                add_action('admin_menu', array(&$this,'add_menu_items'));
                add_filter('wpchaos-config',array(&$this,'add_chaos_settings'));

                // Add collection
                add_action('wp_ajax_wpdkacollections_add_collection', array(&$this,'ajax_add_collection') );
                add_action('wp_ajax_nopriv_wpdkacollections_add_collection', array(&$this,'ajax_add_collection') );
            }

            add_action('plugins_loaded',array(&$this,'load_textdomain'));
        }
    }

    /**
     * Load textdomain for i18n
     * @return void
     */
    public function load_textdomain() {
        load_plugin_textdomain(self::DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/lang/');
    }

    /**
     * Add some setting keys to CHAOS settings
     * @param  array    $settings
     * @return array 
     */
    public function add_chaos_settings($settings) {
        // $new_settings = array(
        //     array(
        //         /*Sections*/
        //         'name'      => 'wpdkacollections',
        //         'title'     => __('Collections',self::DOMAIN),
        //         'fields'    => array()
        //         )
        //     );
        // return array_merge($settings,$new_settings);
        
        return $settings;
    }

    public function loadJsCss() {
        wp_enqueue_script('bootstrapjs',plugins_url( 'js/bootstrap.min.js' , __FILE__ ),array('jquery'),'1.0',true);
        wp_enqueue_script('dka-collections',plugins_url( 'js/functions.js' , __FILE__ ),array('jquery', 'bootstrapjs'),'1.0',true);
        $translation_array = array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'token' => wp_create_nonce(self::TOKEN_PREFIX)
        );
        wp_localize_script( 'dka-collections', 'WPDKACollections', $translation_array );
        wp_enqueue_style('bootstrapcss', plugins_url( 'css/bootstrap.min.css' , __FILE__ ),true);
    }

    /** ************************************************************************
     * Ajax calls
     **************************************************************************/
    public function ajax_add_collection() {
        if (!isset($_POST['collectiontitle'])) {
            echo "Missing title";
            throw new \RuntimeException("Missing title");
        }

        if (!$this->_add_collection($_POST['collectiontitle'], $_POST['collectionDescription'], $_POST['collectionRights'], $_POST['collectionCategory'])) {
            echo "Collection could not be added";
            throw new \RuntimeException("Collection could not be added to CHAOS");
        }
    }

    public function ajax_add_material_to_collection() {

    }


    /**
     * Add menu to adminisration
     */
    public function add_menu_items() {
        global $submenu;
        add_menu_page(
            'WP DKA Collections',
            'Collections',
            'activate_plugins',
            'wpdkacollections',
            array(&$this,'render_collections_page')
        );
    }

    /**
     * Render page added in menu
     * @author Joachim Jensen <jv@intox.dk>
     * @return void
     */
    public function render_collections_page() {

?>
        <div class="wrap">
            <div id="icon-users" class="icon32"><br/></div>
<?php
            $page = (isset($_GET['subpage']) ? $_GET['subpage'] : "");
            $renderTable;
            switch($page) {
                case 'wpdkacollection-objects' :
                    $renderTable = new WPDKACollectionObjects_List_Table();
                    break;
                default :
                    $renderTable = new WPDKACollections_List_Table();
            }
            $this->render_list_table($renderTable);
            
?>
        </div>
<?php
    }

    /**
     * Render page for a given list table
     * @param  WPDKACollections_List_Table $table
     * @return WPDKACollections_List_Table
     */
    private function render_list_table(WPDKACollections_List_Table $table) {
        $table->prepare_items();
?>
    <h2><?php $table->get_title(); ?></h2>
    <form id="movies-filter" method="get">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
        <?php $table->views(); ?>
        <?php $table->display(); ?>
    </form>
    
<?php
        return $table;
    }

    private function render_edit_collection() {
?>
        <h2><?php printf(__('Edit %s', self::DOMAIN), $_GET['dka-collection']); ?></h2>

        <form method="post">
            <label for="collection"><?php _e('Collection', self::DOMAIN)?></label>
            <input id="collection" name="collection" type="text" value="<?php echo $_GET['dka-collection']?>"/>
            <input type="submit" value="<?php _e('Save', self::DOMAIN)?>" id="submit" class="button-primary" name="submit"/>
        </form>
    <?php
        if (isset($_POST['submit'])) {
            if (!empty($_POST['collection'])) {
                // Change collection name.
                _e('Collection was updated.', self::DOMAIN);
            }
        }
    }

    public function remove_collection() {

    }

    public function rename_collection() {

    }

    /**
     * Adds a new collection object to CHAOS
     * @param  string    $title
     * @param  string    $description (optional)
     * @param  string    $rights      (optional)
     * @param  string    $category    (optional)
     * @return boolean
     */
    private function _add_collection($title, $description = '', $rights = '', $category = '') {
        try {
            $serviceResult = WPChaosClient::instance()->Object()->Create(self::COLLECTIONS_TYPE_ID,self::COLLECTIONS_FOLDER_ID);
            // $serviceResult = WPChaosClient::instance()->Object()->Get(
            //             "GUID:d96cbd3a-766d-6d42-888d-cbcfa3592ca3",   // Search query
            //             null,   // Sort
            //             false,   // Use session instead of AP.
            //             0,      // pageIndex
            //             1,      // pageSize
            //             true,   // includeMetadata
            //             false,   // includeFiles
            //             false    // includeObjectRelations
            // ); //debug purpose. using created guid

            $collections = WPChaosObject::parseResponse($serviceResult);
            $collection = $collections[0];

            //Create XML and set it to collection
            $metadataXML = new SimpleXMLElement("<?xml version='1.0' encoding='UTF-8' standalone='yes'?><dkact:Collection xmlns:dkact='http://www.danskkulturarv.dk/DKA-Collection.xsd'></dkact:Collection>");

            $metadataXML[0] = esc_html($title);
            //date seems 2 hours behind gmt1 and daylight saving time. using gmt0?
            $metadataXML->addChild('title', $title);
            $metadataXML->addChild('description', $description);
            $metadataXML->addChild('rights', $rights);
            $metadataXML->addChild('category', $category);
            
            $collection->set_metadata(WPChaosClient::instance(),self::METADATA_SCHEMA_GUID,$metadataXML,WPDKAObject::METADATA_LANGUAGE);

        } catch(\Exception $e) {
            error_log('CHAOS Error when adding collection: '.$e->getMessage());
            return false;
        }
        return true;
    }

    private function _add_materials_to_collection() {
        
        // TODO
        return true;
    }

    /**
     * Get a single WPChaosObject
     * @param  string            $guid
     * @param  string|boolean    $accesspoint
     * @return WPChaosObject
     */
    private function get_object_by_guid($guid,$accesspoint = null) {
        $objects = array();
        try {
            $response = WPChaosClient::instance()->Object()->Get(
                WPChaosClient::escapeSolrValue($guid),   // Search query
                null,   // Sort
                $accesspoint, 
                0,      // pageIndex
                1,      // pageSize
                true,   // includeMetadata
                true,   // includeFiles
                true    // includeObjectRelations
            );
            $objects = WPChaosObject::parseResponse($response);
         } catch(\CHAOSException $e) {
            error_log('CHAOS Error when getting object by guid: '.$e->getMessage());
        }
        return empty($objects) ? null : $objects[0];
    }

    /**
     * Create collections property for WPChaosObject
     * @param  mixed            $value
     * @param  WPChaosObject    $object
     * @return string
     */

    function add_collection_counts() {

        $facetsResponse = WPChaosClient::instance()->Index()->Search(WPChaosClient::generate_facet_query(array(WPDKACollections_List_Table::FACET_KEY_STATUS)), null, false);
        $total_count = 0;
        $facets = array();

        foreach($facetsResponse->Index()->Results() as $facetResult) {
            foreach($facetResult->FacetFieldsResult as $fieldResult) {
                foreach($fieldResult->Facets as $facet) {
                    $facets[$facet->Value] = $facet->Count; 
                    $total_count += $facet->Count;
                }
            }
        }

        $num_posts = $total_count;
        $num = number_format_i18n($num_posts);
        $text = _n('Collection', 'Collections', intval($num_posts),self::DOMAIN);

        echo '<tr>';
        echo '<td class="first b b-chaos-material">'.$num.'</td>';
        echo '<td class="t chaos-material">'.$text.'</td>';
        echo '</tr>';

    }

    /**
     * Load file dependencies
     * @return void
     */
    private function load_dependencies() {
        //WP_List_Table might not be available automatically
        if(!class_exists('WP_List_Table')){
            require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
        }
        require_once("wpdkacollections-list-table.php");
        require_once("wpdkacollectionobjects-list-table.php");
    }


    /**
     * Check if dependent plugins are active
     * 
     * @return void 
     */
    public static function check_chaosclient() {
        //$plugin = plugin_basename( __FILE__ );
        $dep = array();
        //if(is_plugin_active($plugin)) {
        foreach(self::$plugin_dependencies as $class => $name) {
            if(!in_array($class, get_option('active_plugins'))) {
                $dep[] = $name;
            }
        }
        if(!empty($dep)) {
                //deactivate_plugins(array($plugin));
            add_action( 'admin_notices', function() use (&$dep) { 
                echo '<div class="error"><p><strong>'.__('WordPress DKA Collections',self::DOMAIN).'</strong> '.sprintf(__('needs %s to be activated.',self::DOMAIN),'<strong>'.implode('</strong>, </strong>',$dep).'</strong>').'</p></div>';
            },10);
            return false;
        }
        //}
        return true;
    }

}

new WPDKACollections();

/**/