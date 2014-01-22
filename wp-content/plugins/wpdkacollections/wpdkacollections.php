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
	const COLLECTIONS_FOLDER_ID = 468;

	/**
	 * ID = 16 is "Part of"
	 */
	const COLLECTIONS_RELATION_ID = 16;

	/**
	 * ID = 467 is "DKA/DKA/delete"
	 */
	const COLLECTIONS_FOLDER_DELETE_ID = 467;

	/**
	 * Token for frontend AJAX submissions
	 */
	const TOKEN_PREFIX = 'somestring';

	/**
	 * Collection types
	 */
	const TYPE_THEME = 'Theme';
	const TYPE_EXHIBITION = 'Exhibition';
	const TYPE_SERIES = 'Series';

	/**
	 * Collection states
	 */
	const STATUS_PUBLISH = 'Publish';
	const STATUS_DRAFT = 'Draft';

	/**
	 * Filter prefix for collection objects
	 */
	const OBJECT_FILTER_PREFIX = 'wpchaos-object-collection-';

	/**
	 * Collection indexes
	 */
	const FACET_KEY_TITLE = 'DKA-Collection-Title_string';
	const FACET_KEY_STATUS = 'DKA-Collection-Status_string';
	const FACET_KEY_TYPE = 'DKA-Collection-Type_string';

	/**
	 * Capability to manage collections
	 */
	const CAPABILITY = 'moderate_comments';

	/**
	 * Plugin dependencies
	 * @var array
	 */
	private static $plugin_dependencies = array(
		'wpchaosclient/wpchaosclient.php' => 'WordPress Chaos Client',
		'wpdka/wpdka.php' => 'WordPress DKA'
	);

	/**
	 * Objects related to collections
	 * Used to avoid lazy loading
	 * @var array
	 */
	public static $collection_relations = array();

	public static $types = array();
	public static $states = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		if(self::check_chaosclient()) {

			$this->load_dependencies();

			if(is_admin()) {
	
				add_action('admin_menu', array(&$this,'add_menu_items'));
				
				// Get collections
				add_action('wp_ajax_wpdkacollections_get_collections', array(&$this,'ajax_get_collections') );
				add_action('wp_ajax_nopriv_wpdkacollections_get_collections', array(&$this,'ajax_get_collections') );

				// Add collection
				add_action('wp_ajax_wpdkacollections_add_collection', array(&$this,'ajax_add_collection') );
				add_action('wp_ajax_nopriv_wpdkacollections_add_collection', array(&$this,'ajax_add_collection') );

				// Add object to collection
				add_action('wp_ajax_wpdkacollections_add_relation', array(&$this,'ajax_add_relation') );
				add_action('wp_ajax_nopriv_wpdkacollections_add_relation', array(&$this,'ajax_add_relation') );

				// Edit collection
				add_action('wp_ajax_wpdkacollections_edit_collection', array(&$this,'ajax_edit_collection') );

				// Sort collection objects
				add_action('wp_ajax_wpdkacollections_sortable', array(&$this,'ajax_sort_collection_objects') );

			}

			add_filter('wpchaos-solr-query',array(&$this,'add_collection_search_to_query'),30,2);
			add_filter(WPChaosSearch::FILTER_PREPARE_RESULTS,array(&$this,'prepare_search_results'));

			wp_enqueue_style('dka-collections-style-bootstrap',plugins_url( 'css/bootstrap.min.css' , __FILE__ ));
			wp_enqueue_style('dka-collections-style',plugins_url( 'css/style.css' , __FILE__ ));

			add_shortcode( 'collection_slider', array( &$this, 'collection_slider_shortcode' ) );
			add_shortcode( 'general_information', array( &$this, 'general_information_shortcode' ) );

			add_action('wp_enqueue_scripts', array(&$this, 'loadJsCss'));

			add_action('plugins_loaded',array(&$this,'load_textdomain'));
		}
	}

	/**
	 * Load textdomain for i18n
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain(self::DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/lang/');
		self::$types = array(
			self::TYPE_THEME => __('Theme',self::DOMAIN),
			//self::TYPE_EXHIBITION => __('Exhibition',self::DOMAIN),
			self::TYPE_SERIES => __('Series',self::DOMAIN)
		);
		self::$states = array(
			self::STATUS_PUBLISH => __('Published',self::DOMAIN),
			self::STATUS_DRAFT => __('Draft',self::DOMAIN)
		);
	}

	/**
	 * Handle requests on administration page
	 * @return void
	 */
	public function load_collections_page() {

		function add_admin_notice($key, $verb) {
			$count = intval($_REQUEST[$key]);
			add_action( 'admin_notices', function() use ($count,$verb) {
				echo '<div class="updated"><p>'.sprintf(_n('%d collection %s successfully!','%d collections %s successfully!', $count,WPDKACollections::DOMAIN),$count,$verb).'</p></div>';
			} );
		}

		function add_admin_notice_object($key, $verb) {
			$count = intval($_REQUEST[$key]);
			add_action( 'admin_notices', function() use ($count,$verb) {
				echo '<div class="updated"><p>'.sprintf(_n('%d object %s successfully!','%d objects %s successfully!', $count,WPDKACollections::DOMAIN),$count,$verb).'</p></div>';
			} );
		}

		//wp_enqueue_style('wpdkatags-style',plugins_url('css/style.css', __FILE__ ));

		$action = (isset($_REQUEST['action']) && $_REQUEST['action'] != -1 ? $_REQUEST['action'] : (isset($_REQUEST['action2']) && $_REQUEST['action2'] != -1 ? $_REQUEST['action2'] : false));

		if($action && isset($_REQUEST[WPDKACollections_List_Table::NAME_SINGULAR])) {

			if(!current_user_can(self::CAPABILITY)) {
				wp_die("Unauthorized request");
			}

			//TODO: nonce check here
			
			$collections = $_REQUEST[WPDKACollections_List_Table::NAME_SINGULAR];
			if(!is_array($collections)) {
				$collections = array($collections);
			}

			$current_page = remove_query_arg(array('_wpnonce','action','action2',WPDKACollections_List_Table::NAME_SINGULAR,'_wp_http_referer','dka-material'));
			$count = 0;

			//TODO: instead of lazy loading objects, load em all at once
			switch($action) {
					case 'delete':
						//When we delete, we actually move the tag to a specific folder.
						//Consider it a trash can
						foreach($collections as $collection) {
							
							$collection_obj = $this->get_collection_by_guid($collection);
							$this->_remove_materials_from_collection($collection_obj);
							$serviceResult = WPChaosClient::instance()->Link()->Update($collection, self::COLLECTIONS_FOLDER_ID, self::COLLECTIONS_FOLDER_DELETE_ID);
							$count++;
						}
						break;
					case 'remove':
						$materials = $_REQUEST['dka-material'];
						if(!is_array($materials)) {
							$materials = array($materials);
						}
						$collection_obj = $this->get_collection_by_guid($collections[0]);
						if($collection_obj) {
							if($this->_remove_materials_from_collection($collection_obj,$materials)) {
								$count = count($materials);
							}							
						}
						$current_page = add_query_arg(WPDKACollections_List_Table::NAME_SINGULAR,$collections[0],$current_page);
						break;
			}
			$current_page = add_query_arg($action,$count,$current_page);

			wp_safe_redirect($current_page);

		} if(isset($_REQUEST['delete'])) {
			add_admin_notice('delete',__('deleted',self::DOMAIN));
		} elseif(isset($_REQUEST['remove'])) {
			add_admin_notice_object('remove',__('removed',self::DOMAIN));
		}

	}

	/**
	 * Load necessary CSS and JS to visualize collections on the site.
	 */
	public function loadJsCss() {
		if(!is_admin() && current_user_can('edit_posts')) {
			//if(current_user_can('edit_posts')) {
				wp_enqueue_script('dka-collections-carousel',plugins_url( 'js/carousel.js' , __FILE__ ),array('jquery'),'3.0.0',true);
				wp_enqueue_script('dka-collections',plugins_url( 'js/functions.js' , __FILE__ ),array('jquery'),'1.0',true);
				$translation_array = array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'token' => wp_create_nonce(self::TOKEN_PREFIX),
					'types' => self::$types,
					'collection' => __('Collection',self::DOMAIN),
					'material' => __('Material',self::DOMAIN),
					'addMaterial' => __('Add',self::DOMAIN),
					'createCollection' => __('Create',self::DOMAIN),
					'newCollection' => _x('Add New','page'),
					'cancel' => __('Cancel'),
					'addMaterialHeader' => __('Add material to collection',self::DOMAIN),
					'createCollectionHeader' => __('Create collection',self::DOMAIN),
					'rights' => __('Rights',self::DOMAIN),
					'type' => __('Type',self::DOMAIN),
					'name' => __('Title'),
					'description' => __('Description',self::DOMAIN)
				);
				wp_localize_script( 'dka-collections', 'WPDKACollections', $translation_array );				
			//}
			
			wp_enqueue_style('dka-collections-style',plugins_url( 'css/style.css' , __FILE__ ));
		}
	}

	/**
	 * Get collections for a specific material.
	 * @param  WPCHAOSObject $object
	 * @return array of collection objects
	 */
	public static function get_material_collections($object) {
		try {
			//Get relations
			$relation_guids = array();
			foreach($object->ObjectRelations as $relation) {
				if($object->GUID != $relation->Object1GUID) {
					$relation_guids[] = $relation->Object1GUID;
				} else {
					$relation_guids[] = $relation->Object2GUID;
				}
			}
			$serviceResult = WPChaosClient::instance()->Object()->Get(
				'(GUID:('.implode(' OR ',$relation_guids).')) AND (ObjectTypeID:'.WPDKACollections::COLLECTIONS_TYPE_ID.') AND ('.WPDKACollections::FACET_KEY_STATUS.':'.WPDKACollections::STATUS_PUBLISH.')',   // Search query
				null,   // Sort
				null, 
				0,      // pageIndex
				50,      // pageSize
				true,   // includeMetadata
				false,   // includeFiles
				false    // includeObjectRelations
			);
			return WPChaosObject::parseResponse($serviceResult,self::OBJECT_FILTER_PREFIX);
			
		} catch(\Exception $e) {

		}
		return array();		
	}

	/** ************************************************************************
	 * Ajax calls
	 **************************************************************************/

	/**
	 * Get collections with ajax
	 * @return array of collection objects
	 */
	public function ajax_get_collections() {
		$response = array();
		try {
			$serviceResult = WPChaosClient::instance()->Object()->Get(
				'(FolderID:'.WPDKACollections::COLLECTIONS_FOLDER_ID.')',   // Search query
				null,   // Sort
				null, 
				0,      // pageIndex
				100,      // pageSize
				true,   // includeMetadata
				false,   // includeFiles
				false    // includeObjectRelations
			);
			$collections = WPChaosObject::parseResponse($serviceResult,WPDKACollections::OBJECT_FILTER_PREFIX);
			foreach($collections as $collection) {
				$response[] = array(
					'title' => $collection->title,
					'guid' => $collection->GUID
				);

			}
		} catch(\Exception $e) {

		}

		echo json_encode($response);
		die();
	}

	/**
	 * Get collection by guid with ajax
	 * @return collection object
	 */
	public function ajax_get_collection() {
		try {
			$result = self::get_collection_by_guid($_POST['guid']);
			echo $this->list_collection_objects($result);
		} catch(\Exception $e) {

		}
		die();
	}

	/**
	 * Add new collection with ajax
	 * @return status string
	 */
	public function ajax_add_collection() {

		if(!current_user_can(self::CAPABILITY) || !check_ajax_referer(self::TOKEN_PREFIX, 'token', false)) {
			echo __("Unauthorized request",self::DOMAIN);
			throw new \RuntimeException("Unauthorized request");
		}

		if (!isset($_POST['collectionTitle']) || strlen(trim($_POST['collectionTitle'])) < 1) {
			echo __("Missing title",self::DOMAIN);
			throw new \RuntimeException("Missing title");
		}

		$title = esc_html($_POST['collectionTitle']);

		$collection = $this->_add_collection($title, $_POST['collectionDescription'], $_POST['collectionRights'], $_POST['collectionType']);

		if (!$collection) {
			echo __("Collection could not be added",self::DOMAIN);
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

	/**
	 * Add relations to collection
	 * @return status string
	 */
	public function ajax_add_relation() {
		if (!isset($_POST['collection_guid']) || !isset($_POST['object_guid'])) {
			echo "Missing ids";
			throw new \RuntimeException("Missing ids");
		}

		if(!current_user_can(self::CAPABILITY) || !check_ajax_referer(self::TOKEN_PREFIX, 'token', false)) {
			echo __("Unauthorized request",self::DOMAIN);
			throw new \RuntimeException("Unauthorized request");
		}

		$collection = $this->get_object_by_guid(esc_html($_POST['collection_guid']),false);

		if (empty($collection)) {
			echo "Couldn't find collection with that GUID";
			throw new \RuntimeException("Couldn't find collection");
		}

		$relation = $this->_add_material_to_collection($collection,$_POST['object_guid']);

		if (!$relation) {
			echo "Lol could not be added";
			throw new \RuntimeException("Collection could not be added to CHAOS");
		}

		//echo json_encode($response);
		die();
	}

	/**
	 * Edit collection with ajax from administration
	 * @return json object
	 */
	public function ajax_edit_collection() {
		if (!isset($_POST['object_guid'])) {
			echo "Missing guid";
			throw new \RuntimeException("Missing guid for collection");
		}

		if (!isset($_POST['title']) || strlen($_POST['title']) < 1) {
			echo "No title";
			throw new \RuntimeException("No title");
		}

		if(!isset($_POST['description']) || !isset($_POST['rights']) || !isset($_POST['type']) || !isset($_POST['status'])) {
			echo "Missing information";
			throw new \RuntimeException("Missing information");
		}

		if(!current_user_can(self::CAPABILITY)) {
			echo __("Unauthorized request",self::DOMAIN);
			throw new \RuntimeException("Unauthorized request");
		}

		$collection = $this->get_collection_by_guid(esc_html($_POST['object_guid']),false);

		if (empty($collection)) {
			echo "Couldn't find collection with that GUID";
			throw new \RuntimeException("Couldn't find collection");
		}

		$title = esc_html($_POST['title']);
		$description = esc_html($_POST['description']);
		$rights = esc_html($_POST['rights']);
		$type = esc_html($_POST['type']);
		$status = esc_html($_POST['status']);

		$updated_obj = $this->_edit_collection($collection, $title, $description, $rights, $type, $status);

		if($updated_obj) {
			$response = array(
				'title' => $updated_obj->title,
				'description' => $updated_obj->description,
				'rights' => $updated_obj->rights,
				'type' => $updated_obj->type,
				'status' => $updated_obj->status
			);
		} else {
			echo "Couldn't change collection";
			throw new \RuntimeException("Couldn't change collection");
		}

		echo json_encode($response);
		die();
	}

	/**
	 * Get sorted collection objects with ajax
	 * @return json object
	 */
	public function ajax_sort_collection_objects() {
		if (!isset($_POST['collection_guid'])) {
			echo "Missing guid";
			throw new \RuntimeException("Missing guid for collection");
		}

		if (!isset($_POST['guids']) || !is_array($_POST['guids']) || count($_POST['guids']) < 2) {
			echo "Not enough data";
			throw new \RuntimeException("No title");
		}

		if(!current_user_can(self::CAPABILITY)) {
			echo __("Unauthorized request",self::DOMAIN);
			throw new \RuntimeException("Unauthorized request");
		}

		$collection = $this->get_object_by_guid(esc_html($_POST['collection_guid']),false);

		if (empty($collection)) {
			echo "Couldn't find collection with that GUID";
			throw new \RuntimeException("Couldn't find collection");
		}

		if($this->_sort_collection_objects($collection, $_POST['guids'])) {
			$response = array();
		} else {
			echo "Couldn't change collection";
			throw new \RuntimeException("Couldn't change collection");
		}

		echo json_encode($response);
		die();
	}

	/**
	*	Get a specific collection
	*	@param $guid collection guid
	*
	*/
	public static function get_collection_by_guid($guid) {
		$objects = array();
		try {
			$response = WPChaosClient::instance()->Object()->Get(
				WPChaosClient::escapeSolrValue($guid),   // Search query
				null,   // Sort
				null, 
				0,      // pageIndex
				1,      // pageSize
				true,   // includeMetadata
				true,   // includeFiles
				true    // includeObjectRelations
			);
			$objects = WPChaosObject::parseResponse($response,WPDKACollections::OBJECT_FILTER_PREFIX);
		 } catch(\CHAOSException $e) {
			error_log('CHAOS Error when getting object by guid: '.$e->getMessage());
		}
		return empty($objects) ? null : $objects[0];
	}


	/**
	 * Add menu to adminisration
	 */
	public function add_menu_items() {
		$page = add_menu_page(
			__('DKA Collections',self::DOMAIN),
			__('Collections',self::DOMAIN),
			self::CAPABILITY,
			self::DOMAIN,
			array(&$this,'render_collections_page'),
			'div',
			27
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
			<?php $this->render_list_table($renderTable); ?>
			</div>
		<?php
	}

	/**
	 * Render page for a given list table
	 * @param  WPDKACollections_List_Table $table
	 * @return WPDKACollections_List_Table
	 */
	private function render_list_table(WPDKACollections_List_Table $table) {
		wp_enqueue_script('dka-collections-admin',plugins_url( 'js/admin_functions.js' , __FILE__ ),array('jquery'),'1.0',true);
		
		$args = array(
			'confirmDelete' => __('Are you sure you want to delete this item permanently?',self::DOMAIN),
			'update' => __('Update'),
			'cancel' => __('Cancel'),
			'types' => self::$types,
			'states' => self::$states
			);
		wp_localize_script('dka-collections-admin', 'WPDKACollections', $args);

		$table->prepare_items();  
		?>
		<h2><?php $table->get_title(); ?></h2>
		<p class="alignright"><?php echo $table->guid; ?></p>
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

			$yday = new DateTime();
			$yday->sub(new DateInterval('P1D'));
			WPChaosClient::instance()->Object()->SetPublishSettings($collection->GUID, null,$yday);

			return $collection;
		} catch(\Exception $e) {
			error_log('CHAOS Error when adding collection: '.$e->getMessage());
			return false;
		}
	}

	/**
	 * Edit collection
	 * @param  WPChaosObject    $collection
	 * @param  string           $new_title
	 * @param  string           $new_description
	 * @param  string           $new_rights
	 * @param  string           $new_type
	 * @param  string           $new_status
	 * @return boolean
	 */
	private function _edit_collection($collection, $new_title = null, $new_description = null, $new_rights = null, $new_type = null, $new_status = null) {
		
		if($new_type != null && !in_array($new_type,array(self::TYPE_SERIES,self::TYPE_EXHIBITION,self::TYPE_THEME)))
			return false;

		if($new_status != null && !in_array($new_status,array(self::STATUS_PUBLISH,self::STATUS_DRAFT)))
			return false;

		if($new_status == self::STATUS_PUBLISH && count($collection->ObjectRelations) == 0)
			return false;

		try {

			$metadataXML = $collection->get_metadata(self::METADATA_SCHEMA_GUID);
			$title = $metadataXML->xpath('/dkac:Collection');
			if(count($title) !== 1) {
				throw new \RuntimeException("Malformed XML");
			}

			if($new_title) {
				$title[0]->Title = $new_title;
			} 
			if($new_description) {
				$title[0]->Description = $new_description;
			}
			if($new_rights) {
				$title[0]->Rights = $new_rights;
			}
			if($new_status) {
				$title[0]->Status = $new_status;
			}
			if($new_type) {
				$title[0]->Type = $new_type;
			}
			
			$collection->set_metadata(WPChaosClient::instance(),self::METADATA_SCHEMA_GUID,$metadataXML,WPDKAObject::METADATA_LANGUAGE);

			return $collection;
		} catch(\Exception $e) {


			error_log('CHAOS Error when editing collection: '.$e->getMessage());
			return false;
		}
	}

	/**
	 * Sort objects in collection playlist
	 * @param  WPChaosObject    $collection
	 * @param  array    $guids
	 * @return boolean
	 */
	private function _sort_collection_objects($collection, $guids) {

		try {

			$metadataXML = $collection->get_metadata(self::METADATA_SCHEMA_GUID);
			$item = $metadataXML->xpath('/dkac:Collection');

			if(count($item) !== 1) {
				throw new \RuntimeException("Malformed XML");
			}

			//Remove all objects in playlist
			$item[0]->Playlist = '';

			$item = $metadataXML->xpath('/dkac:Collection/dkac:Playlist');

			if(count($item) !== 1) {
				throw new \RuntimeException("Malformed XML");
			}

			//Add objects in new order
			foreach($guids as $guid) {
				$item[0]->addChild('Object',$guid);
			}
			
			$collection->set_metadata(WPChaosClient::instance(),self::METADATA_SCHEMA_GUID,$metadataXML,WPDKAObject::METADATA_LANGUAGE);

			return $collection;
		} catch(\Exception $e) {
			error_log('CHAOS Error when sorting collection objects: '.$e->getMessage());
			return false;
		}
	}

	/**
	 * Remove materials from collection playlist
	 * @param  WPChaosObject    $collection_object
	 * @param  array     $material_guids
	 * @return boolean
	 */
	private function _remove_materials_from_collection($collection_object,$material_guids = array()) {
		try {
			$metadataXML = $collection_object->get_metadata(self::METADATA_SCHEMA_GUID);

			if(empty($material_guids)) {
				foreach($collection_object->ObjectRelations as $relation) {
					if($collection_object->GUID != $relation->Object1GUID) {
						$material_guids[] = $relation->Object1GUID;
					} else {
						$material_guids[] = $relation->Object2GUID;
					}
				}
			}

			foreach($material_guids as $material_guid) {
				$item = $metadataXML->xpath('/dkac:Collection/dkac:Playlist/dkac:Object/text()[.="'.$material_guid.'"]');

				if(count($item) < 1) {
					throw new \RuntimeException("Malformed XML");
				}

				//Remove element
				$dom = dom_import_simplexml($item[0]);
				$dom->parentNode->removeChild($dom);	

				//Remove relation between object and collection
				WPChaosClient::instance()->ObjectRelation()->Delete($material_guid,$collection_object->GUID,self::COLLECTIONS_RELATION_ID);
			}

			//Set to draft if last object is removed
			$item = $metadataXML->xpath('/dkac:Collection/dkac:Playlist/dkac:Object');
			if(count($item) == 0) {
				$this->_edit_collection($collection_object,null,null,null,null,self::STATUS_DRAFT);
			}

			// Update playlist.
			$collection_object->set_metadata(WPChaosClient::instance(),self::METADATA_SCHEMA_GUID,$metadataXML,WPDKAObject::METADATA_LANGUAGE);

			return true;
		} catch(\Exception $e) {
			error_log('CHAOS Error when removing object from collection: '.$e->getMessage());
		}
		return false;
	}

	/**
	 * Add material to collection
	 * @param  WPChaosObject    $collection_object
	 * @param  string    $material_guid
	 */
	private function _add_material_to_collection($collection_object, $material_guid) {
		try {
			$metadataXML = $collection_object->get_metadata(self::METADATA_SCHEMA_GUID);

			$item = $metadataXML->xpath('/dkac:Collection/dkac:Playlist');

			if(count($item) !== 1) {
				throw new \RuntimeException("Malformed XML");
			}

			$item[0]->addChild('Object',$material_guid);

			// Add object to playlist.
			$collection_object->set_metadata(WPChaosClient::instance(),self::METADATA_SCHEMA_GUID,$metadataXML,WPDKAObject::METADATA_LANGUAGE);

			//Set relation between object and tag
			WPChaosClient::instance()->ObjectRelation()->Create($material_guid,$collection_object->GUID,self::COLLECTIONS_RELATION_ID);
			return true;
		} catch(\Exception $e) {
			error_log('CHAOS Error when changing collection state: '.$e->getMessage());
		}
		return false;
	}
	
	/**
	 * Get object by guid
	 * @param  string    $guid
	 * @param  boolean|null    $accesspoint
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
	 * Add possibility to search for collections
	 * @param  string    $query
	 * @param  array    $query_vars
	 */
	public function add_collection_search_to_query($query, $query_vars) {
		//We use an OR, so encapsulate everything prior
		if($query) {
			$query = array("(".$query.")");
		} else {
			$query = array();
		}

		//For each substring in search, look for matching tags
		if(array_key_exists(WPChaosSearch::QUERY_KEY_FREETEXT, $query_vars)) {
			$freetext = $query_vars[WPChaosSearch::QUERY_KEY_FREETEXT];
			if($freetext) {

				//Get collections by typeid, folderid and status publish
				$query[] = '(m'.self::METADATA_SCHEMA_GUID.'_da_all:(' . $freetext . ') AND (ObjectTypeID:'.self::COLLECTIONS_TYPE_ID.') AND (FolderID:'.self::COLLECTIONS_FOLDER_ID.') AND ('.self::FACET_KEY_STATUS.':'.self::STATUS_PUBLISH.'))';

			}
		}
		
		return implode(" OR ", $query);        
	}

	/**
	 * When searching for a collection, get first object in playlist
	 * and show this
	 * Avoids lazy loading with self::$collection_relations
	 * @param  [type]    $search_results
	 * @return [type]
	 */
	public function prepare_search_results($search_results) {
		$collection_relations = array();

		//Get collection objects and map first object from playlist
		foreach($search_results->MCM()->Results() as $collection) {
			if($collection->ObjectTypeID == self::COLLECTIONS_TYPE_ID) {
				$collection = new WPChaosObject($collection,self::OBJECT_FILTER_PREFIX);
				if(count($collection->playlist_raw) == 0)
					continue;
				//An object might occur first in several collections
				$collection_relations[$collection->GUID] = (string)$collection->playlist_raw[0];
			}
		}

		if(count($collection_relations) > 0) {
			self::$collection_relations = array();
			try {
				$response = WPChaosClient::instance()->Object()->Get(
					'(GUID:('.implode(' OR ',$collection_relations).'))',   // Search query
					null,   // Sort
					null, 
					0,      // pageIndex
					count($collection_relations),      // pageSize
					true,   // includeMetadata
					true,   // includeFiles
					true    // includeObjectRelations
				);
				//Flip to get O(1) lookup
				$collection_relations = array_flip($collection_relations);
				foreach($response->MCM()->Results() as $material) {
					$collection_guid = $collection_relations[$material->GUID];
					self::$collection_relations[$collection_guid] = $material;				
				}
			} catch(\CHAOSException $e) {
				error_log('CHAOS error when getting collection relations: '.$e->getMessage());
			}
		}

		return $search_results;
	}

	/**
	 * Shortcode for collection slider
	 */
	public function collection_slider_shortcode($atts, $content) {
		$collections = explode(',', $content);
		$thumbnails = array();
		$count = 0;
		echo '<div id="frontpage_carousel" class="carousel slide" data-ride="carousel">
    	<div class="carousel-inner">';
		foreach ($collections as $c) {
			$c = trim($c);
			$collection = WPDKACollections::get_collection_by_guid($c);
			$firstObject = WPChaosClient::instance()->Object()->Get(
				"(GUID:(".implode(" OR ", $collection->playlist_raw)."))",   // Search query
				null,   // Sort
				null,   // AP injected
				0,      // pageIndex
				10, // pageSize
				true,   // includeMetadata
				true,   // includeFiles
				false    // includeObjectRelations
			);

			//If the materials from collection playlist exist in CHAOS
			if($firstObject->MCM()->Count() > 0) {
				foreach($firstObject->MCM()->Results() as $result) {
					$firstObject = new WPChaosObject($result);
					if ($firstObject->thumbnail != null) {
						WPChaosClient::set_object($firstObject);
						break;
					}
				}
			}
			WPChaosClient::set_object($firstObject);
			$thumbnail = WPChaosClient::get_object()->thumbnail;
			$url = $firstObject->url . '#' . $c;
			$description = $collection->description;
			$title = $collection->title;

			$thumbnails[] = $thumbnail;
			echo '<div class="item' . (($count == 0) ? ' active' : '') . '">
        		<img src="' . $thumbnail . '" alt="' . $title . '">
        		<a href="' . $url . '">
    			<div class="carousel-caption">
      				<h3>' . $title . '</h3>
      				<p>' . $description . '</p>
    			</div>
    			</a>
      		</div>';
			$count++;
		}
		WPChaosClient::reset_object();
		echo '</div>
		<ul class="carousel-indicators">';
		$count = 0;
		foreach ($thumbnails as $thumb) {
			echo '<li data-target="#frontpage_carousel" data-slide-to="' . $count . '"'. (($count == 0) ? ' class="active" ' : '') . 'style="background-image: url(\'' . $thumb . '\');"></li>';
    		$count++;
		}
		echo '</ul>
		</div>';
	}

	public function general_information_shortcode($atts) {
		echo '<div class="info_right">
		<h3>Velkommen til danskkulturarv.dk</h3>';
		foreach (WPDKAObject::$format_types as $format_type => $args) {
			if($format_type == WPDKAObject::TYPE_IMAGE_AUDIO || $format_type == WPDKAObject::TYPE_UNKNOWN) continue;
			echo '<a class="media_info" href="' . WPChaosSearch::generate_pretty_search_url(array(WPChaosSearch::QUERY_KEY_FREETEXT => (WPDKASearch::QUERY_KEY_TYPE . '-' . $format_type))) . '">
			<i class="' . $args['class'] . '"></i>
			<p>' . number_format_i18n($this->get_facet_count(WPDKASearch::QUERY_KEY_TYPE, $args['chaos-value'])) . ' ' . $args['title'] . '</p>
			</a>';
		}
    	echo '<h2>Find masser af spændende indhold</h2>';
    	$tags = $this->get_random_tags_from_results_raw(array('number_of_tags' => 21));
    	foreach ($tags as $tag) {
    		echo '<a class="media_info" href="' . $tag['href'] . '">
    			<p>' . $tag['title'] . '</p>
    			</a>';
    	}
  		echo '</div>';
	}

	private function get_facet_count($field, $values) {
		if(is_string($values)) {
			$values = array($values);
		}
		global $facets;
		$sum = 0;
		if(array_key_exists($field, $facets)) {
			foreach($values as $value) {
				if(array_key_exists($value, $facets[$field])) {
					$sum += intval($facets[$field][$value]);
				}
			}
		}
		return $sum;
	}

	private function get_random_tags_from_results_raw($args = null) {
		$args = wp_parse_args($args, array(
			'query' => '',
			'number_of_tags' => 10,
			'pageindex' => 0,
			'pagesize' => get_option("wpchaos-searchsize"),
			'sort' => 'visninger',
			'accesspoint' => null
		));
		extract($args, EXTR_SKIP);	

		WPChaosSearch::generate_searchresults($args);
		$tags = array();
		foreach(WPChaosSearch::get_search_results()->MCM()->Results() as $object) {
			WPChaosClient::set_object($object);
			$tags = array_merge($tags,WPChaosClient::get_object()->tags_raw);

		}
		WPChaosClient::reset_object();

		//$tags = array_map('strval',$tags);
		$tags = array_unique($tags);

		$sep = '';
		$result = array();
		while($number_of_tags > 0 && $tags) {

			$tag = array_splice($tags, rand(0,count($tags)-1), 1);
			$tag = $tag[0];

			$link = WPChaosSearch::generate_pretty_search_url(array(WPChaosSearch::QUERY_KEY_FREETEXT => $tag, WPChaosSearch::QUERY_KEY_SORT => $sort));
			$result[] = array('href' => $link, 'title' => esc_attr($tag), 'tag' => $tag);
			$number_of_tags--;
		}
		return $result;
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