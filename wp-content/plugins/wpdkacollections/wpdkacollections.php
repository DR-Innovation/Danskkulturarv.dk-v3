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
            }

            add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'collections', array(&$this,'define_collections_filter'),10,2);
            add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'collections_raw', array(&$this,'define_collections_raw_filter'),10,2);
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
        $new_settings = array(
            array(
                /*Sections*/
                'name'      => 'wpdkacollections',
                'title'     => __('Collections',self::DOMAIN),
                'fields'    => array()
                )
            );
        return array_merge($settings,$new_settings);
    }

    public function loadJsCss() {
        wp_enqueue_script('bootstrapjs',plugins_url( 'js/bootstrap.min.js' , __FILE__ ),array('jquery'),'1.0',true);
        wp_enqueue_script('dka-collections',plugins_url( 'js/functions.js' , __FILE__ ),array('jquery', 'bootstrapjs'),'1.0',true);
        wp_enqueue_style( 'bootstrapcss', plugins_url( 'css/bootstrap.min.css' , __FILE__ ),true);
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
            array(&$this,'render_tags_page')
        );
    }

    /**
     * Render page added in menu
     * @author Joachim Jensen <jv@intox.dk>
     * @return void
     */
    public function render_tags_page() {

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

            if (isset($_GET['action'])) {
                switch ($_GET['action']) {
                    default: break;
                }
            } else {
                $this->render_list_table($renderTable);
            }
            
?>
        </div>
<?php
    }

    /**
     * Render page for a given list table
     * @param  WPDKATags_List_Table $table
     * @return WPDKATags_List_Table
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

    private function render_edit_tag() {
?>
        <h2><?php printf(__('Edit %s', self::DOMAIN), $_GET['dka-collection']); ?></h2>

        <form method="post">
            <label for="tag"><?php _e('Collection', self::DOMAIN)?></label>
            <input id="tag" name="tag" type="text" value="<?php echo $_GET['dka-collection']?>"/>
            <input type="submit" value="<?php _e('Save', self::DOMAIN)?>" id="submit" class="button-primary" name="submit"/>
        </form>
    <?php
        if (isset($_POST['submit'])) {
            if (!empty($_POST['collection'])) {
                // Change tag name.
                _e('Collection was updated.', self::DOMAIN);
            }
        }
    }

    /**
     * Adds a new collection object to CHAOS
     * @param  string    $object_guid
     * @param  string    $tag_input
     * @return boolean
     */
    private function _add_collection($object_guid, $tag_input) {

        // TODO
        return true;
    }

    private function _add_materials_to_collection() {
        
        // TODO
        return true;
    }

    /**
     * Check if given tag exists as relation to object
     * @param  WPChaosObject $object
     * @param  string        $tag_input
     * @return boolean
     */
    private function _collection_exists(WPChaosObject $object,$tag_input) {
        
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
     * Create collections_raw property for WPChaosObject
     * @param  mixed            $value
     * @param  WPChaosObject    $object
     * @return array
     */
    public function define_collections_raw_filter($value, $object) {
        // $relation_guids = array();
        // foreach($object->ObjectRelations as $relation) {
        //     $guid_property = "Object1GUID";
        //     if($object->GUID == $relation->{$guid_property}) {
        //         $guid_property = "Object2GUID";
        //     }
        //     $relation_guids[] = "GUID:".$relation->{$guid_property};
        // }
        // $serviceResult = WPChaosClient::instance()->Object()->Get(
        //     "(".implode("+OR+", $relation_guids).")+AND+ObjectTypeID:".self::TAG_TYPE_ID,   // Search query
        //     null,   // Sort
        //     false,   // Use session instead of AP
        //     0,      // pageIndex
        //     count($relation_guids),      // pageSize
        //     true,   // includeMetadata
        //     false,   // includeFiles
        //     false    // includeObjectRelations
        // );

        // return WPChaosObject::parseResponse($serviceResult);
        return false;
    }

    /**
     * Create collections property for WPChaosObject
     * @param  mixed            $value
     * @param  WPChaosObject    $object
     * @return string
     */
    public function define_collections_filter($value, $object) {

        // $status = intval(get_option('wpdkatags-status'));

        // //iff status == active or frozen
        // if($status > 0) {
        //     $tags = $object->usertags_raw;
        
        //     $value .= '<div class="usertags">';
        //     foreach($tags as $tag) {
        //         //Get tag XML meta
        //         $tag_meta = $tag->metadata(
        //             array(WPDKATags::METADATA_SCHEMA_GUID),
        //             array(''),
        //             null
        //         );
        //         //We do not want flagged tags
        //         if($tag_meta['status'] == self::TAG_STATE_FLAGGED) {
        //             continue;
        //         }
        //         $link = WPChaosSearch::generate_pretty_search_url(array(WPChaosSearch::QUERY_KEY_FREETEXT => $tag_meta));
        //         $value .= '<a class="usertag tag" href="'.$link.'" title="'.esc_attr($tag_meta).'">'.$tag_meta.'<i class="icon-remove flag-tag" id="'.$tag->GUID.'"></i></a>'."\n";
        //     }
        //     if(empty($tags)) {
        //         $value .= '<span class="no-tag">'.__('No tags','wpdka').'</span>'."\n";
        //     }
        //     $value .= '</div>';

        //     //Iff status == active
        //     if($status == 2) {
        //         $value .= '<input type="text" value="" id="usertag-add" class=""><button type="button" id="usertag-submit" class="btn">Add tag</button>';
        //         wp_enqueue_script('dka-usertags',plugins_url( 'js/functions.js' , __FILE__ ),array('jquery'),'1.0',true);
        //         $translation_array = array(
        //             'ajaxurl' => admin_url( 'admin-ajax.php' ),
        //             'token' => wp_create_nonce(self::TOKEN_PREFIX.$object->GUID)
        //         );
        //         wp_localize_script( 'dka-usertags', 'WPDKATags', $translation_array );
        //     }
        // }
        // return $value;
        return false;
    }

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