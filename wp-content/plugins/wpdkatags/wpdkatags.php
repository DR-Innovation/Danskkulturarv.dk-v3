<?php
/*
Plugin Name: WP DKA Tags
Plugin URI: 
Description: Manage user generated tags for CHAOS
Version: 1.0
Author: Joachim Jensen
Author URI: 
License: 
*/
final class WPDKATags {

	const DOMAIN = 'wpdkatags';

	const METADATA_SCHEMA_GUID = '00000000-0000-0000-0000-000067c30000';

	/**
	 * ID = 12 is "DKA Crowd Tag"
	 */
	const TAG_TYPE_ID = 12;

	/**
	 * ID = 11 is "Is related to"
	 */
	const TAG_RELATION_ID = 11;

	/**
	 * ID = 470 is "DKA/DKA/Tags"
	 */
	const TAGS_FOLDER_ID = 470;

	/**
	 * States
	 */
	const TAG_STATE_APPROVED = 'Approved';
	const TAG_STATE_UNAPPROVED = 'Unapproved';
	const TAG_STATE_FLAGGED = 'Flagged';

	/**
	 * Facets
	 */
	const FACET_KEY_VALUE = 'DKA-Crowd-Tag-Value_string';
	const FACET_KEY_STATUS = 'DKA-Crowd-Tag-Status_string';
	const FACET_KEY_CREATED = 'DKA-Crowd-Tag-Created_date';

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

				add_action('admin_menu', array(&$this,'add_menu_items'));
				add_filter('wpchaos-config',array(&$this,'add_chaos_settings'));

				// Rename tag
				add_action('wp_ajax_wpdkatags_rename_tag', array(&$this,'ajax_admin_rename_tag') );

				// Submit tag
				add_action('wp_ajax_wpdkatags_submit_tag', array(&$this,'ajax_submit_tag') );
				add_action('wp_ajax_nopriv_wpdkatags_submit_tag', array(&$this,'ajax_submit_tag') );

				// Flag tag
				add_action('wp_ajax_wpdkatags_flag_tag', array(&$this,'ajax_flag_tag') );
				add_action('wp_ajax_nopriv_wpdkatags_flag_tag', array(&$this,'ajax_flag_tag') );

				add_action('right_now_content_table_end', array(&$this,'add_usertag_counts'));

			} else {

				add_filter('wpchaos-solr-query',array(&$this,'add_tag_search_to_query'),20,2);

			}

			add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'usertags', array(&$this,'define_usertags_filter'),10,2);
			add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'usertags_raw', array(&$this,'define_usertags_raw_filter'),10,2);

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
				'name'      => 'wpdkatags',
				'title'     => __('User Tags',self::DOMAIN),
				'fields'    => array(
					/*Section fields*/
					array(
						'name' => 'wpdkatags-status',
						'title' => __('Sitewide Status',self::DOMAIN),
						'type' => 'select',
						'list' => array(
							__('Hidden',self::DOMAIN),
							__('Frozen',self::DOMAIN),
							__('Active',self::DOMAIN)
							)
						)
					)
				)
			);
		return array_merge($settings,$new_settings);
	}

	/**
	 * Add menu to adminisration
	 */
	public function add_menu_items(){
		global $submenu;
		$page = add_menu_page(
			'WP DKA Tags',
			'User Tags',
			'activate_plugins',
			'wpdkatags',
			array(&$this,'render_tags_page')
			);
		add_action( 'load-' . $page , array(&$this,'load_tags_page'));
	}

	public function load_tags_page() {

		$action = (isset($_REQUEST['action']) ? $_REQUEST['action'] :(isset($_REQUEST['action2']) ? $_REQUEST['action'] : false));

		if($action && $action != -1 && isset($_REQUEST[WPDKATagObjects_List_Table::NAME_SINGULAR])) {
			//nonce check here
			
			$tags = $_REQUEST[WPDKATagObjects_List_Table::NAME_SINGULAR];
			if(!is_array($tags)) {
				$tags = array($tags);
			}

			$current_page = remove_query_arg(array('_wpnonce','action','action2',WPDKATagObjects_List_Table::NAME_SINGULAR,'_wp_http_referer','dka-tag-new'));
			$count = 0;

			//TODO: instead of lazy loading objects, load em all at once
			switch($action) {
					case 'flagged':
						foreach($tags as $tag) {
							$tag_object = WPDKATags::get_object_by_guid(esc_html($tag),false);
							if(WPDKATags::change_tag_state($tag_object,WPDKATags::TAG_STATE_FLAGGED)) {
								$count++;
							}
						}
						$current_page = add_query_arg('flagged',count($tags),$current_page);
						break;
					case 'approved':
						foreach($tags as $tag) {
							$tag_object = WPDKATags::get_object_by_guid(esc_html($tag),false);
							if(WPDKATags::change_tag_state($tag_object,WPDKATags::TAG_STATE_APPROVED)) {
								$count++;
							}
						}
						$current_page = add_query_arg('approved',count($tags),$current_page);
						break;
					case 'unapproved':
						foreach($tags as $tag) {
							$tag_object = WPDKATags::get_object_by_guid(esc_html($tag),false);
							if(WPDKATags::change_tag_state($tag_object,WPDKATags::TAG_STATE_UNAPPROVED)) {
								$count++;
							}
						}
						
						break;
					case 'rename':
						if(isset($_REQUEST['dka-tag-new'])) {
							$new_tag = $this->_escape_tag_value($_REQUEST['dka-tag-new']);
							if($new_tag) {
								foreach($tags as $tag) {
									$tag_object = WPDKATags::get_object_by_guid(esc_html($tag),false);
									if(WPDKATags::change_tag_value($tag_object,$new_tag)) {
										$count++;
									}
								}
							}
						}
						break;
			}
			$current_page = add_query_arg($action,$count,$current_page);

			wp_safe_redirect($current_page);

		} else if(isset($_REQUEST['flagged'])) {
			$count = intval($_REQUEST['flagged']);
			add_action( 'admin_notices', function() use ($count) {
				echo '<div class="updated"><p>'.sprintf(_n('%d tag flagged','%d tags flagged', $count,'wpdkatags'),$count).'</p></div>';
			} );
		} else if(isset($_REQUEST['approved'])) {
			$count = intval($_REQUEST['approved']);
			add_action( 'admin_notices', function() use ($count) {
				echo '<div class="updated"><p>'.sprintf(_n('%d tag approved','%d tags approved', $count,'wpdkatags'),$count).'</p></div>';
			} );
		} else if(isset($_REQUEST['unapproved'])) {
			$count = intval($_REQUEST['unapproved']);
			add_action( 'admin_notices', function() use ($count) {
				echo '<div class="updated"><p>'.sprintf(_n('%d tag unapproved','%d tags unapproved', $count,'wpdkatags'),$count).'</p></div>';
			} );
		} else if(isset($_REQUEST['rename'])) {
			$count = intval($_REQUEST['rename']);
			add_action( 'admin_notices', function() use ($count) {
				echo '<div class="updated"><p>'.sprintf(_n('%d tag renamed','%d tags renamed', $count,'wpdkatags'),$count).'</p></div>';
			} );
		}

	}

	/**
	 * Render page added in menu
	 * @author Joachim Jensen <jv@intox.dk>
	 * @return void
	 */
	public function render_tags_page() {

			$page = (isset($_GET['subpage']) ? $_GET['subpage'] : "");
			$renderTable;
			switch($page) {
				case 'wpdkatag-objects' :
				$renderTable = new WPDKATagObjects_List_Table();
				break;
				default :
				$renderTable = new WPDKATags_List_Table();
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

	/**
	 * Render page for a given list table
	 * @param  WPDKATags_List_Table $table
	 * @return WPDKATags_List_Table
	 */
	private function render_list_table(WPDKATags_List_Table $table) {
		$table->prepare_items();   
		?>
		<h2><?php $table->get_title(); ?></h2>

		<form id="movies-filter" method="get">
			<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
			<?php if(isset($_REQUEST['subpage'])) : ?>
			<input type="hidden" name="subpage" value="<?php echo $_REQUEST['subpage']; ?>" />
			<?php endif; ?>
			<?php if(isset($_REQUEST['dka-tag'])) : ?>
			<input type="hidden" name="dka-tag" value="<?php echo $_REQUEST['dka-tag']; ?>" />
			<?php endif; ?>
			<?php $table->views(); ?>
			<?php $table->display(); ?>
		</form>
		
		<?php
		return $table;
	}

	/** ************************************************************************
	 * Ajax calls
	 **************************************************************************/

	public function ajax_admin_rename_tag() {

		if(!isset($_POST['tag_guid'])) {
			echo "Missing tag guid";
			throw new \RuntimeException("Missing tag guid");
		}

		if(!isset($_POST['tag'])) {
			echo "Invalid tag input";
			throw new \RuntimeException("Invalid tag input");
		}

		if(!check_ajax_referer('bulk-'.WPDKATagObjects_List_Table::NAME_PLURAL, 'nonce', false)) {
			echo "Nonce not valid";
			throw new \RuntimeException("Nonce not valid");
		}

		$tag_string = $this->_escape_tag_value($_POST['tag']);

		if($tag_string == "") {
			echo "Invalid tag input";
			throw new \RuntimeException("Invalid tag input");
		}

		$tag = self::get_object_by_guid(esc_html($_POST['tag_guid']),false);

		if(!$tag) {
			echo "Invalid tag";
			throw new \RuntimeException("Invalid tag");
		}

		if(self::change_tag_value($tag, $tag_string)) {
			$response = array(
				'tag' => $tag_string
				);
		} else {
			echo "Tag could not be renamed";
			throw new \RuntimeException("Tag could not be flagged");  
		}

		echo json_encode($response);
		die();

	}

	/**
	 * Handle AJAX request to flag a tag from user TODO
	 * @return void
	 */
	public function ajax_flag_tag() {
		
		//iff status == active
		if(get_option('wpdkatags-status') != '2') {
			echo "Cheating uh?";
			throw new \RuntimeException("Cheating uh?");
		}

		if(!isset($_POST['tag_guid'])) {
			echo "Missing tag guid";
			throw new \RuntimeException("Missing tag guid");
		}

		if(!isset($_POST['object_guid']) || !check_ajax_referer(self::TOKEN_PREFIX.$_POST['object_guid'], 'token', false)) {
			echo "Object GUID not valid";
			throw new \RuntimeException("Object GUID not valid");
		}

		$tag = self::get_object_by_guid(esc_html($_POST['tag_guid']),false);

		if(!$tag) {
			echo "Invalid tag";
			throw new \RuntimeException("Invalid tag");
		}
		
		if(self::change_tag_state($tag, self::TAG_STATE_FLAGGED)) {
			$response = array(
				'Tag flagged successfully!'
				);
		} else {
			echo "Tag could not be flagged";
			throw new \RuntimeException("Tag could not be flagged");  
		}

		echo json_encode($response);
		die();
		
	}

	/**
	 * Handle AJAX request and response of (frontend) tag submission
	 * @return void
	 */
	public function ajax_submit_tag() {

		//iff status == active
		if(get_option('wpdkatags-status') != '2') {
			echo "Cheating uh?";
			throw new \RuntimeException("Cheating uh?");
		}

		if(!isset($_POST['tag'])) {
			echo "Invalid tag input";
			throw new \RuntimeException("Invalid tag input");
		}

		$tag = $this->_escape_tag_value($_POST['tag']);

		if($tag == "") {
			echo "Invalid tag input";
			throw new \RuntimeException("Invalid tag input");
		}

		if(!isset($_POST['object_guid']) || !check_ajax_referer(self::TOKEN_PREFIX.$_POST['object_guid'], 'token', false)) {
			echo "GUID not valid";
			throw new \RuntimeException("GUID not valid");
		}

		$object = self::get_object_by_guid($_POST['object_guid']);
		
		if($object == null) {
			echo "Object could not be found";
			throw new \RuntimeException("Object could not be found");
		}

		if($this->_tag_exists($object,$tag)) {
			echo "Tag already exists";
			throw new \RuntimeException("Tag already exists");
		}

		$tag_object = $this->_add_tag($_POST['object_guid'],$tag);

		if($tag_object) {
			$response = array(
				'title' => $tag,
				'guid' => $tag_object->GUID,
				'link' => WPChaosSearch::generate_pretty_search_url(array(WPChaosSearch::QUERY_KEY_FREETEXT => $tag))
				);
		} else {
			echo "Tag could not be added";
			throw new \RuntimeException("Tag could not be added to CHAOS");
		}
		
		// $tag_input = esc_html($_POST['tag']);
		// $response = array(
		//     'title' => $tag_input,
		//     'link' => WPChaosSearch::generate_pretty_search_url(array(WPChaosSearch::QUERY_KEY_FREETEXT => $tag_input))
		// );
		
		echo json_encode($response);
		die();
	}

	private function _escape_tag_value($string) {
		// Strip HTML Tags
		$string = strip_tags($string);
		// Clean up things like &amp;
		$string = html_entity_decode($string);
		// Strip out any url-encoded stuff
		$string = urldecode($string);
		// Replace non-AlNum characters with space
		//$string = preg_replace('/[^A-Za-z0-9]/', ' ', $string);
		// Replace Multiple spaces with single space
		$string = preg_replace('/ +/', ' ', $string);
		// Trim the string of leading/trailing space
		$string = trim($string);

		return $string;
	}

	/**
	 * Adds a new tag object to CHAOS and relates it to material object
	 * @param  string    $object_guid
	 * @param  string    $tag_input
	 * @return boolean
	 */
	private function _add_tag($object_guid, $tag_input) {

		try {
			$serviceResult = WPChaosClient::instance()->Object()->Create(self::TAG_TYPE_ID,self::TAGS_FOLDER_ID);
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

			$tags = WPChaosObject::parseResponse($serviceResult);
			$tag = $tags[0];

			//Create XML and set it to tag
			$metadataXML = new SimpleXMLElement("<?xml version='1.0' encoding='UTF-8' standalone='yes'?><dkact:Tag xmlns:dkact='http://www.danskkulturarv.dk/DKA-Crowd-Tag.xsd'></dkact:Tag>");

			$metadataXML[0] = esc_html($tag_input);
			//date seems 2 hours behind gmt1 and daylight saving time. using gmt0?
			$metadataXML->addAttribute('created', date('c', time()));
			$metadataXML->addAttribute('status', self::TAG_STATE_UNAPPROVED);
			
			$tag->set_metadata(WPChaosClient::instance(),self::METADATA_SCHEMA_GUID,$metadataXML,WPDKAObject::METADATA_LANGUAGE);

			//Set relation between object and tag
			WPChaosClient::instance()->ObjectRelation()->Create(esc_html($object_guid),$tag->GUID,self::TAG_RELATION_ID);

			return $tag;

		} catch(\Exception $e) {
			error_log('CHAOS Error when adding tag: '.$e->getMessage());
			return false;
		}
		return true;
	}

	/**
	 * Change state on a given tag object
	 * @param  WPChaosObject $tag_object
	 * @param  string        $new_state
	 * @return boolean
	 */
	public static function change_tag_state(WPChaosObject $tag_object,$new_state) {
		if(in_array($new_state,array(self::TAG_STATE_UNAPPROVED,self::TAG_STATE_APPROVED,self::TAG_STATE_FLAGGED))) {

			try {

				$metadataXML = $tag_object->get_metadata(self::METADATA_SCHEMA_GUID);
				
				if($metadataXML['status'] != $new_state) {
					
					$metadataXML['status'] = $new_state;
					$tag_object->set_metadata(WPChaosClient::instance(),self::METADATA_SCHEMA_GUID,$metadataXML,WPDKAObject::METADATA_LANGUAGE);
					return true;

				}

				return false;
				
			} catch(\Exception $e) {
				error_log('CHAOS Error when changing tag state: '.$e->getMessage());
			}
		}
		return false;
	}

	/**
	 * Change state on a given tag object
	 * @param  WPChaosObject $tag_object
	 * @param  string        $new_state
	 * @return boolean
	 */
	public static function change_tag_value(WPChaosObject $tag_object,$new_value) {
		
		try {

			$metadataXML = $tag_object->get_metadata(self::METADATA_SCHEMA_GUID);
			if($metadataXML[0] != $new_value) {
				$metadataXML[0] = $new_value;
				$tag_object->set_metadata(WPChaosClient::instance(),self::METADATA_SCHEMA_GUID,$metadataXML,WPDKAObject::METADATA_LANGUAGE);
				return true;
			}
			
			
			return false;
		} catch(\Exception $e) {
			error_log('CHAOS Error when changing tag value: '.$e->getMessage());
		}

		return false;
	}

	/**
	 * Check if given tag exists as relation to object
	 * @param  WPChaosObject $object
	 * @param  string        $tag_input
	 * @return boolean
	 */
	private function _tag_exists(WPChaosObject $object,$tag_input) {
		$tag_input = esc_html($tag_input);
		foreach($object->usertags_raw as $tag) {
			$tag = $tag->metadata(
				array(WPDKATags::METADATA_SCHEMA_GUID),
				array(''),
				null
				);
			if((string)$tag == $tag_input) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Get a single WPChaosObject
	 * @param  string            $guid
	 * @param  string|boolean    $accesspoint
	 * @return WPChaosObject
	 */
	public static function get_object_by_guid($guid,$accesspoint = null) {
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
	 * Create usertags_raw property for WPChaosObject
	 * @param  mixed            $value
	 * @param  WPChaosObject    $object
	 * @return array
	 */
	public function define_usertags_raw_filter($value,WPChaosObject $object) {
		$relation_guids = array();
		foreach($object->ObjectRelations as $relation) {
			$guid_property = "Object2GUID"; //tag is always saved here.
			//Because we know which relation is tag, we can safely
			//skip the relations where GUID == Object2GUID
			if($object->GUID == $relation->{$guid_property}) {
				continue;
			}
			$relation_guids[] = $relation->{$guid_property};
		}
		$tags = array();
		if(!empty($relation_guids)) {
			try {
				//+AND+(!".self::FACET_KEY_STATUS.":".self::TAG_STATE_FLAGGED.")
				$serviceResult = WPChaosClient::instance()->Object()->Get(
					"(GUID:(".implode("+OR+", $relation_guids).")+AND+(ObjectTypeID:".self::TAG_TYPE_ID."))",   // Search query
					null,   // Sort
					false,   // Use session instead of AP
					0,      // pageIndex
					count($relation_guids),      // pageSize
					true,   // includeMetadata
					false,   // includeFiles
					false    // includeObjectRelations
				);
				$tags = WPChaosObject::parseResponse($serviceResult);
			} catch(\CHAOSException $e) {
				error_log('CHAOS Error when getting user tags for object: '.$e->getMessage());
			}			
		}

		return $tags;
	}

	/**
	 * Create usertags property for WPChaosObject
	 * @param  mixed            $value
	 * @param  WPChaosObject    $object
	 * @return string
	 */
	public function define_usertags_filter($value, $object) {

		$status = intval(get_option('wpdkatags-status'));

		//iff status == active or frozen
		if($status > 0) {
			$tags = $object->usertags_raw;
			
			$value .= '<div class="usertags">';
			foreach($tags as $tag) {
				//Get tag XML meta
				$tag_meta = $tag->metadata(
					array(WPDKATags::METADATA_SCHEMA_GUID),
					array(''),
					null
					);
				//We do not want flagged tags
				if($tag_meta['status'] == self::TAG_STATE_FLAGGED) {
					continue;
				}
				$link = WPChaosSearch::generate_pretty_search_url(array(WPChaosSearch::QUERY_KEY_FREETEXT => $tag_meta));
				$flag = ($tag_meta['status'] == self::TAG_STATE_UNAPPROVED ? '<i class="icon-remove flag-tag" id="'.$tag->GUID.'"></i>' : '');
				$value .= '<a class="usertag tag" href="'.$link.'" title="'.esc_attr($tag_meta).'">'.$tag_meta.$flag.'</a>'."\n";
			}
			if(empty($tags)) {
				$value .= '<span class="no-tag">'.__('No tags','wpdka').'</span>'."\n";
			}
			$value .= '</div>';

			//Iff status == active
			if($status == 2) {

				$value .= '<div class="input-group">';
				$value .= '<input type="text" class="form-control" id="usertag-add" value="">';
				$value .= '<span class="input-group-btn"><button class="btn btn-default" type="button" id="usertag-submit">Add tag</button></span>';
				$value .= '</div>';

				wp_enqueue_script('dka-usertags',plugins_url( 'js/functions.js' , __FILE__ ),array('jquery'),'1.0',true);
				$translation_array = array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'token' => wp_create_nonce(self::TOKEN_PREFIX.$object->GUID)
					);
				wp_localize_script( 'dka-usertags', 'WPDKATags', $translation_array );
			}
		}
		return $value;
	}

	public function add_usertag_counts() {

		$facetsResponse = WPChaosClient::instance()->Index()->Search(WPChaosClient::generate_facet_query(array(self::FACET_KEY_STATUS)), null, false);
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
		$text = _n('User tag', 'User tags', intval($num_posts),'wpdka');

		echo '<tr>';
		echo '<td class="first b b-chaos-material">'.$num.'</td>';
		echo '<td class="t chaos-material">'.$text.'</td>';
		echo '</tr>';

		$num_posts = isset($facets['approved']) ? $facets['approved'] : 0;
		$num = number_format_i18n($num_posts);
		$text = _n('Approved user tag', 'Approved user tags', intval($num_posts),'wpdka');

		echo '<tr>';
		echo '<td class="first b b-chaos-material">'.$num.'</td>';
		echo '<td class="t chaos-material">'.$text.'</td>';
		echo '</tr>';

		$num_posts = isset($facets['unapproved']) ? $facets['unapproved'] : 0;
		$num = number_format_i18n($num_posts);
		$text = _n('Unapproved user tag', 'Unapproved user tags', intval($num_posts),'wpdka');

		echo '<tr>';
		echo '<td class="first b b-chaos-material">'.$num.'</td>';
		echo '<td class="t chaos-material">'.$text.'</td>';
		echo '</tr>';

		$num_posts = isset($facets['flagged']) ? $facets['flagged'] : 0;
		$num = number_format_i18n($num_posts);
		$text = _n('Flagged user tag', 'Flagged user tags', intval($num_posts),'wpdka');

		echo '<tr>';
		echo '<td class="first b b-chaos-material">'.$num.'</td>';
		echo '<td class="t chaos-material">'.$text.'</td>';
		echo '</tr>';

	}

	/**
	 * Find tags from substrings in search
	 * Append the guid of their relations to search query
	 * @param  string    $query
	 * @param  array    $query_vars
	 */
	public function add_tag_search_to_query($query, $query_vars) {
		//We use an OR, so encapsulate everything prior
		if($query) {
			$query = array("(".$query.")");
		} else {
			$query = array();
		}

		//For each substring in search, look for matching tags
		if(array_key_exists(WPChaosSearch::QUERY_KEY_FREETEXT, $query_vars)) {
			$freetext = $query_vars[WPChaosSearch::QUERY_KEY_FREETEXT];
			//$freetext = preg_replace('/\s+/', ' ', $query_vars[WPChaosSearch::QUERY_KEY_FREETEXT]);
			if($freetext) {
				$freetext = explode(" ", $freetext);
				$tags = array();

				//Prepare relevant strings for query (avoid empty ones)
				foreach($freetext as $tag) {
					if($tag != "") {
						$tags[] = sprintf("%s:%s*",self::FACET_KEY_VALUE,$tag);
					}
					
				}

				if(!empty($tags)) {
					$tag_query = '((' . implode("+OR+", $tags) . ')+AND+ObjectTypeID:'.self::TAG_TYPE_ID.')';
					try {
						$relation_guids = array();

						$page_index = (WPChaosSearch::get_search_var(WPChaosSearch::QUERY_KEY_PAGE) ?: 0);

						//Get user tags based on substrings
						$response = WPChaosClient::instance()->Object()->Get(
							$tag_query,   // Search query
							null,   // Sort
							false, //tags use sessionid
							$page_index,      // pageIndex
							get_option("wpchaos-searchsize",20),      // pageSize
							false,   // includeMetadata
							false,   // includeFiles
							true    // includeObjectRelations
							);
						$objects = WPChaosObject::parseResponse($response);

						//Get related object to each string. Avoid dupes
						foreach($objects as $object) {
							foreach($object->ObjectRelations as $relation) {
								$relation_guids[$relation->Object1GUID] = "GUID:".$relation->Object1GUID;
							}
						}
						
						//Append found object guids to search query
						if(!empty($relation_guids)) {
							$query[] = "(".implode("+OR+", $relation_guids).")";
						}

					} catch(\CHAOSException $e) {
						error_log('CHAOS Error when getting user tags for search: '.$e->getMessage());
					}
				}

			}
		}
		
		return implode("+OR+", $query);        
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
		require_once("wpdkatags-list-table.php");
		require_once("wpdkatagobjects-list-table.php");
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
				echo '<div class="error"><p><strong>'.__('WordPress DKA Tags','wpdka').'</strong> '.sprintf(__('needs %s to be activated.','wpdka'),'<strong>'.implode('</strong>, </strong>',$dep).'</strong>').'</p></div>';
			},10);
			return false;
		}
		//}
		return true;
	}

}

new WPDKATags();

/**/