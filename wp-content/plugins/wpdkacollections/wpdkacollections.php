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
	 * ID = 10 is ""
	 */
	const COLLECTIONS_TYPE_ID = 10;

	/**
	 * ID = 468 is DKA/DKA/Samlinger
	 */
	const COLLECTIONS_FOLDER_ID = 468; // CHANGE

	/**
	 * ID = 467 is "DKA/DKA/delete"
	 */
	const COLLECTIONS_FOLDER_DELETE_ID = 467;

	/**
	 * Token prefix for frontend AJAX submissions
	 * Appended with object guid
	 */
	const TOKEN_PREFIX = 'somestring';

	const TYPE_THEME = 'Theme';
	const TYPE_EXHIBITION = 'Exhibition';
	const TYPE_SERIES = 'Series';

	const STATUS_PUBLISH = 'Publish';
	const STATUS_DRAFT = 'Draft';

	const OBJECT_FILTER_PREFIX = 'wpchaos-object-collection-';

	/**
	 * Plugin dependencies
	 * @var array
	 */
	private static $plugin_dependencies = array(
		'wpchaosclient/wpchaosclient.php' => 'WordPress Chaos Client',
		'wpdka/wpdka.php' => 'WordPress DKA'
	);

	protected $cur_collection_guid;

	/**
	 * Constructor
	 */
	public function __construct() {
		if(self::check_chaosclient()) {

			$this->load_dependencies();

			if(is_admin()) {
	
				add_action('admin_menu', array(&$this,'add_menu_items'));
				//add_filter('wpchaos-config',array(&$this,'add_chaos_settings'));
				//
				// Get collections
				add_action('wp_ajax_wpdkacollections_get_collections', array(&$this,'ajax_get_collections') );
				add_action('wp_ajax_nopriv_wpdkacollections_get_collections', array(&$this,'ajax_get_collections') );

				// Get specific collection
				add_action('wp_ajax_wpdkacollections_get_collection', array(&$this,'ajax_get_collection') );
				add_action('wp_ajax_nopriv_wpdkacollections_get_collection', array(&$this,'ajax_get_collection') );

				// Add collection
				add_action('wp_ajax_wpdkacollections_add_collection', array(&$this,'ajax_add_collection') );
				add_action('wp_ajax_nopriv_wpdkacollections_add_collection', array(&$this,'ajax_add_collection') );

				// Delete collection
				add_action('wp_ajax_wpdkacollections_delete_collection', array(&$this,'ajax_delete_collection') );
				add_action('wp_ajax_nopriv_wpdkacollections_delete_collection', array(&$this,'ajax_delete_collection') );

				// Edit collection
				add_action('wp_ajax_wpdkacollections_edit_collection', array(&$this,'ajax_edit_collection') );
				add_action('wp_ajax_nopriv_wpdkacollections_edit_collection', array(&$this,'ajax_edit_collection') );

				//$this->_add_collection("Title test", 'Description Test', 'Right test',  self::TYPE_SERIES);


			}

			add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'collections', array(&$this,'define_collections_filter'),10,2);
			add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'collections_raw', array(&$this,'define_collections_raw_filter'),10,2);

			add_action('wp_head', array(&$this, 'loadJsCss'));

			//add_action( 'admin_bar_menu', array(&$this,'make_parent_node'), 999 );

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

	public function load_collections_page() {

		function add_admin_notice($key, $verb) {
			$count = intval($_REQUEST[$key]);
			add_action( 'admin_notices', function() use ($count,$verb) {
				echo '<div class="updated"><p>'.sprintf(_n('%d collection %s successfully!','%d collections %s successfully!', $count,WPDKACollections::DOMAIN),$count,$verb).'</p></div>';
			} );
		}

		//wp_enqueue_style('wpdkatags-style',plugins_url('css/style.css', __FILE__ ));

		$action = (isset($_REQUEST['action']) && $_REQUEST['action'] != -1 ? $_REQUEST['action'] : (isset($_REQUEST['action2']) && $_REQUEST['action2'] != -1 ? $_REQUEST['action2'] : false));

		if($action && isset($_REQUEST[WPDKACollections_List_Table::NAME_SINGULAR])) {
			
			//TODO: nonce check here
			//TODO: perms check here
			
			$collections = $_REQUEST[WPDKACollections_List_Table::NAME_SINGULAR];
			if(!is_array($collections)) {
				$collections = array($collections);
			}

			$current_page = remove_query_arg(array('_wpnonce','action','action2',WPDKACollections_List_Table::NAME_SINGULAR,'_wp_http_referer'));
			$count = 0;

			//TODO: instead of lazy loading objects, load em all at once
			switch($action) {
					case 'delete':
						//When we delete, we actually move the tag to a specific folder.
						//Consider it a trash can
						foreach($collections as $collection) {
							$serviceResult = WPChaosClient::instance()->Link()->Update($collection, self::COLLECTIONS_FOLDER_ID, self::COLLECTIONS_FOLDER_DELETE_ID);
							$count++;
						}
						break;
			}
			$current_page = add_query_arg($action,$count,$current_page);

			wp_safe_redirect($current_page);

		} if(isset($_REQUEST['delete'])) {
			add_admin_notice('delete',__('deleted',self::DOMAIN));
		}

	}

	public function make_parent_node( $wp_admin_bar ) {
		$args = array(
			'id'     => 'new-dka-collection',     // id of the existing child node (New > Post)
			'title'  => __('Add new Collection'), // alter the title of existing node
			'href' => '#',
			'parent' => false,          // set parent to false to make it a top level (parent) node
		);
		$wp_admin_bar->add_node( $args );
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
		if(current_user_can('edit_posts')) {
			wp_enqueue_script('dka-collections',plugins_url( 'js/functions.js' , __FILE__ ),array('jquery'),'1.0',true);
			wp_enqueue_style('dka-collections-style',plugins_url( 'css/style.css' , __FILE__ ));
			$translation_array = array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'token' => wp_create_nonce(self::TOKEN_PREFIX)
			);
			$cur_collection = self::get_current_collection();
			$translation_array_edit = array();
			if (!empty($cur_collection)) {
				$translation_array_edit = array(
					'inputName' => $cur_collection->title,
		            'inputDescription' => $cur_collection->description,
		            'inputRights' => $cur_collection->rights,
		            'inputCategories' => $cur_collection->categories
				);
			}
			wp_localize_script( 'dka-collections', 'WPDKACollections', array_merge($translation_array, $translation_array_edit) );
		}
	}

	/** ************************************************************************
	 * Ajax calls
	 **************************************************************************/

	public function ajax_get_collections() {
		$response = array();
		try {
			$serviceResult = WPChaosClient::instance()->Object()->Get(
				'(FolderID:'.WPDKACollections::COLLECTIONS_FOLDER_ID.')',   // Search query
				null,   // Sort
				false, 
				0,      // pageIndex
				100,      // pageSize
				true,   // includeMetadata
				false,   // includeFiles
				false    // includeObjectRelations
			);
			$collections = WPChaosObject::parseResponse($serviceResult);
			foreach($collections as $collection) {
				$title = $collection->metadata(
					array(self::METADATA_SCHEMA_GUID),
					array('/dkac:DKA-Collection/dkac:Title/text()')
				);
				$response[] = array(
					'title' => $title,
					'guid' => $collection->GUID
				);

			}
		} catch(\Exception $e) {

		}

		echo json_encode($response);
		die();
	}

	public function ajax_get_collection() {
		try {
			$result = self::get_collection_by_guid($_POST['guid']);
			echo $this->list_collection_objects($result);
		} catch(\Exception $e) {

		}
		die();
	}

	public function ajax_add_collection() {
		if (!isset($_POST['collectionTitle'])) {
			echo "Missing title";
			throw new \RuntimeException("Missing title");
		}

		if (strlen(trim($_POST['collectionTitle'])) < 1) {
			echo "No title";
			throw new \RuntimeException("No title");
		}

		$title = esc_html($_POST['collectionTitle']);

		$collection = $this->_add_collection($title, $_POST['collectionDescription'], $_POST['collectionRights'], $_POST['collectionCategory']);

		if (!$collection) {
			echo "Collection could not be added";
			throw new \RuntimeException("Collection could not be added to CHAOS");
		} else {
			$response = array(
				'title' => $title,
				'guid' => $collection->GUID
			);
		}

		echo json_encode($response);
		die();
	}

	public function ajax_delete_collection() {
		if (!isset($_POST['object_guid'])) {
			echo "Missing guid";
			throw new \RuntimeException("Missing guid for collection");
		}

		$collection = $this->get_object_by_guid(esc_html($_POST['object_guid']),false);

		if (empty($collection)) {
			echo "Couldn't find collection with that GUID";
			throw new \RuntimeException("Couldn't find collection");
		}

		if (!$this->_remove_materials_from_collection($collection)) {
			echo "Couldn't remove materials from collection";
			throw new \RuntimeException("Couldn't remove materials from collection");
		}

		if (!$this->_delete_collection($collection)) {
			echo "Couldn't delete collection";
			throw new \RuntimeException("Couldn't delete collection");
		}

		echo 1;
		die();
	}

	public function ajax_edit_collection() {
		if (!isset($_POST['object_guid'])) {
			echo "Missing guid";
			throw new \RuntimeException("Missing guid for collection");
		}

		if (strlen($_POST['collectionTitle']) < 1) {
			echo "No title";
			throw new \RuntimeException("No title");
		}

		$collection = $this->get_object_by_guid(esc_html($_POST['object_guid']),false);

		if (empty($collection)) {
			echo "Couldn't find collection with that GUID";
			throw new \RuntimeException("Couldn't find collection");
		}

		if (!$this->_edit_collection($collection, $_POST['collectionTitle'], $_POST['collectionDescription'], $_POST['collectionRights'], $_POST['collectionCategory'])) {
			echo "Couldn't change collection";
			throw new \RuntimeException("Couldn't change collection");
		}

		echo 1;
		die();
	}

	public function material_get_collections($material_guid) {
		return array();
	}

	/**
	*	Get current collection
	*
	*/
	public static function get_current_collection() {
		if (!class_exists(WPDKACollections_List_Table) || !isset($_GET[WPDKACollections_List_Table::NAME_SINGULAR])) {
			return false;
		}

		$query = "GUID:".$_GET[WPDKACollections_List_Table::NAME_SINGULAR];

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
        return $collection[0];
	}

	/**
	*	Get a specific collection
	*	@param $guid collection guid
	*
	*/
	public static function get_collection_by_guid($guid) {
		$query = "GUID:".$guid;

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
        return $collection[0];
	}


	/**
	 * Add menu to adminisration
	 */
	public function add_menu_items() {
		$page = add_menu_page(
			'WP DKA Collections',
			'Collections',
			'activate_plugins',
			'wpdkacollections',
			array(&$this,'render_collections_page')
		);
		add_action( 'load-' . $page , array(&$this,'load_collections_page'));
	}

	/**
	 * Render page added in menu
	 * @author Joachim Jensen <jv@intox.dk>
	 * @return void
	 */
	public function render_collections_page() {

			$page = (isset($_GET['subpage']) ? $_GET['subpage'] : "");
			$renderTable;
			switch($page) {
				case 'wpdkacollection-objects' :
				$renderTable = new WPDKACollectionObjects_List_Table();
				break;
				default :
				$renderTable = new WPDKACollections_List_Table();
			}

			?>

			<div class="wrap">
			<div id="icon-edit" class="icon32 icon32-posts-post"><br /></div>

			<?php

			$this->render_list_table($renderTable);
			
			?>
		</div>
		<?php
	}

	private function list_collection_objects($collection_object) {
		$count = 1;
		$value = '';
		foreach ($collection_object->objects as $object) {
			if ($this->cur_collection_guid == $object->GUID) {
				$thumbnail = (WPChaosClient::get_object()->thumbnail ? ' style="background-image: url(\''.WPChaosClient::get_object()->thumbnail.'\')!important;"' : ''); // Should get current object thumbnail.
		 		$value .= 	'<li id="current_collection" class="list-group-item media' . $style . '" value="' . $object->guid . '">
		 				<a class="fill-collection" href="'. $object->url . '"></a>
		 				<h4 class="list-group-item-heading"><span class="collectionCount">' . $count++ . '</span> ' . $object->title . '</h4>
			 				<div class="pull-left">
			 					<div id="collection_image" class="thumb format"'. $thumbnail . '">
					   			</div>
			 				</div>
			 				<div class="media-body">
  							' . $object->description . '
						  	</div>
						</li>';
				continue;
			}
			$value .= 	'<li class="list-group-item media' . $style . '" value="' . $object->guid . '">
		 				<a class="fill-collection" href="'. $object->url . '"></a>
		 				<h4 class="list-group-item-heading"><span class="collectionCount">' . $count++ . '</span> ' . $object->title . '</h4>
						</li>';
		}
		return $value;
	}

	public function define_collections_raw_filter($value, WPChaosObject $object) {
		$collections = array();

		// $collections = $this->material_get_collections($object->GUID);
		// foreach ($collections as $collection) {

		// }

		// Testing design
		for ($i = 0; $i < 3; $i++) {
			$object = new stdClass();
			$object->title = 'Test ' . $i;
			$object->guid = $i;
			$new_objects = array();
			for ($j = 1; $j < 20; $j++) {
				$new_object = new stdClass();
				$new_object->title = 'Object ' . $i . $j;
				$new_object->GUID = $i . $j;
				$new_object->description = "Description...";
				$new_objects[] = $new_object;
			}
			$object->objects = $new_objects;
			$collections[] = $object;
		}

		return $collections;
	}

	public function define_collections_filter($value, $object) {
		// View collections
		$collections = $object->collections_raw;

		$this->cur_collection_guid = 12;//$object->GUID;

		if (count($collections) == 0) { return; }

		$cur_collection = $collections[0];

		if (count($collections) == 1) {
			$value .= '<h4>' . $cur_collection->title . '</h4>';
		}
		else {
			$value .= '<div class="btn-group listCollections">
					  <div class="dropdown-toggle" data-toggle="dropdown" value="' . $cur_collection->GUID . '">
						  <h4>' . sprintf('Part of the theme %s', '<strong><span>' . $cur_collection->title . '<span></strong>') . '</h4>
						  <div class="pull-right"><span class="caret"></span></div>
					  </div>
					  <ul class="dropdown-menu" role="menu">';
			foreach ($collections as $collection) {
				$value .= '<li value="' . $collection->GUID . '"><a href="">' . $collection->title . '</a></li>';
			}

	  		$value .= '</ul></div>';
		}
		$value .= '<hr><div class="collections media">';
		$value .= '<ul class="media-list">';
		$value .= $this->list_collection_objects($cur_collection);
		$value .= '</ul>
				</div>';
		return $value;
	}

	/**
	 * Render page for a given list table
	 * @param  WPDKACollections_List_Table $table
	 * @return WPDKACollections_List_Table
	 */
	private function render_list_table(WPDKACollections_List_Table $table) {
		$table->prepare_items();  
		wp_enqueue_script('bootstrapjs',plugins_url( 'js/bootstrap.min.js' , __FILE__ ),array('jquery'),'1.0',true); 
		wp_enqueue_style('bootstrapcss',plugins_url( 'css/bootstrap.min.css' , __FILE__ ));
		$this->loadJsCss();
		?>
		<h2><?php $table->get_title(); ?></h2>
		<form id="movies-filter" method="get">
			<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
			<?php if(isset($_REQUEST['subpage'])) : ?>
			<input type="hidden" name="subpage" value="<?php echo $_REQUEST['subpage']; ?>" />
			<?php endif; ?>
			<?php if(isset($_REQUEST['dka-collection'])) : ?>
			<input type="hidden" name="dka-collection" value="<?php echo $_REQUEST['dka-collection']; ?>" />
			<?php endif; ?>
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

	/**
	 * Adds a new collection object to CHAOS
	 * @param  string    $title
	 * @param  string    $description (optional)
	 * @param  string    $rights      (optional)
	 * @param  string    $type    (optional)
	 * @return boolean
	 */
	private function _add_collection($title, $description = '', $rights = '', $type = self::TYPE_SERIES) {

		if(!in_array($type,array(self::TYPE_SERIES,self::TYPE_EXHIBITION,self::TYPE_THEME)))
			return false;

		// $serviceResult = WPChaosClient::instance()->Object()->Get(
		//                 "GUID:ad8682b9-1fc0-6045-acad-347f16a41d12",   // Search query
		//                 null,   // Sort
		//                 false,   // Use session instead of AP.
		//                 0,      // pageIndex
		//                 1,      // pageSize
		//                 true,   // includeMetadata
		//                 false,   // includeFiles
		//                 false    // includeObjectRelations
		//     ); //debug purpose. using created guid
		// $collections = WPChaosObject::parseResponse($serviceResult);
		// 	$collection = $collections[0];
		//     return $collection;
		try {
			$serviceResult = WPChaosClient::instance()->Object()->Create(self::COLLECTIONS_TYPE_ID,self::COLLECTIONS_FOLDER_ID);

			$collections = WPChaosObject::parseResponse($serviceResult);
			$collection = $collections[0];

			//Create XML and set it to collection
			$metadataXML = new SimpleXMLElement("<?xml version='1.0' encoding='UTF-8' standalone='yes'?><dkac:Collection xmlns:dkac='http://www.danskkulturarv.dk/DKA-Collection.xsd'></dkac:Collection>");

			$metadataXML->addChild('Title', $title);
			$metadataXML->addChild('Description', $description);
			$metadataXML->addChild('Rights', $rights);
			$metadataXML->addChild('Type', $type);
			$metadataXML->addChild('Status', self::STATUS_DRAFT);
			$metadataXML->addChild('Playlist');
			
			$collection->set_metadata(WPChaosClient::instance(),self::METADATA_SCHEMA_GUID,$metadataXML,WPDKAObject::METADATA_LANGUAGE);

			return $collection;
		} catch(\Exception $e) {
			error_log('CHAOS Error when adding collection: '.$e->getMessage());
			return false;
		}
	}

	/**
	 * Delete collection
	 * @param  string           $object_guid
	 * @return boolean              success
	 */
	private function _delete_collection($collection_object) {
		// TODO
		// Check for no materials in collection.
		return false;
	}

	/**
	 * Change collection informtion
	 * @param  string $object_guid
	 * @param  string $title       
	 * @param  string $description
	 * @param  string $rights      
	 * @param  string $type    
	 * @return boolean          success
	 */
	private function _edit_collection($collection_object, $new_title, $new_description, $new_rights, $new_category) {
		// try {
		//     $metadataXML = $collection_object->get_metadata(self::METADATA_SCHEMA_GUID);

		//     // Getting elements from collection
		//     $title = $metadataXML->getElementsByTagName('title')->item(0);
		//     $description = $metadataXML->getElementsByTagName('title')->item(0);
		//     $rights = $metadataXML->getElementsByTagName('title')->item(0);
		//     $category = $metadataXML->getElementsByTagName('title')->item(0);

		//     // Replacing the elements with new information.
		//     $title->nodeValue = $new_title;
		//     $description->nodeValue = $new_description;
		//     $rights->nodeValue = $new_rights;
		//     $category->nodeValue = $new_category;

		//     $metadataXML->replaceChild($title, $title);
		//     $metadataXML->replaceChild($description, $description);
		//     $metadataXML->replaceChild($rights, $rights);
		//     $metadataXML->replaceChild($category, $category);


		//     $collection_object->set_metadata(WPChaosClient::instance(),self::METADATA_SCHEMA_GUID,$metadataXML,WPDKAObject::METADATA_LANGUAGE);
		//     return true;
		// } catch(\Exception $e) {
		//     error_log('CHAOS Error when changing collection state: '.$e->getMessage());
		// }
		return false;
	}

	/**
	 * Remove materials from collection
	 * @param  string $object_guid
	 * @return boolean           success
	 */
	private function _remove_materials_from_collection($collection_object) {
		// TODO
		return false;
	}

	/**
	 * Add materials to collection
	 * @param string $object_guid
	 * @return  boolean           success
	 */
	private function _add_material_to_collection($collection_object, $material_object) {
		// try {
		//     $metadataXML = $collection_object->get_metadata(self::METADATA_SCHEMA_GUID);

		//     // Add playlist.          

		//     $collection_object->set_metadata(WPChaosClient::instance(),self::METADATA_SCHEMA_GUID,$metadataXML,WPDKAObject::METADATA_LANGUAGE);
		//     return true;
		// } catch(\Exception $e) {
		//     error_log('CHAOS Error when changing collection state: '.$e->getMessage());
		// }
		return false;
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
		if(is_admin()) {
			//WP_List_Table might not be available automatically
			if(!class_exists('WP_List_Table')){
				require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
			}
			require_once("wpdkacollections-list-table.php");
			require_once("wpdkacollectionobjects-list-table.php");			
		}
		require("wpchaosobject-filters.php");
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