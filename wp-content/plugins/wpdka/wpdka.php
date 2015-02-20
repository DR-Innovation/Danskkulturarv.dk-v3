<?php
/**
 * @package WP DKA
 * @version 1.0
 */
/*
Plugin Name: WordPress DKA
Plugin URI: 
Description: Module applying functionality to manipulate CHAOS material specific for DKA. Depends on WordPress Chaos Client.
Author: Joachim Jensen <joachim@opensourceshift.com>
Version: 1.0
Author URI: 
*/

/**
 * Class that manages CHAOS data specific to
 * Dansk Kulturarv and registers attributes
 * for WPChaosObject
 */
class WPDKA {

	/**
	 * Name for setting page
	 * @var string
	 */
	const MENU_PAGE = 'wpdka-administration';

	/**
	 * Capability requirement to change publish state
	 */
	const PUBLISH_STATE_CAPABILITY = 'moderate_comments';
	const TOKEN_PREFIX = 'somestring';
	
	const RESET_CROWD_METADATA_START_BTN = 'Start Resetting Crowd Metadata';
	const RESET_CROWD_METADATA_PAUSE_BTN = 'Pause';
	const RESET_CROWD_METADATA_STOP_BTN = 'Stop';
	const REMOVE_DUPLICATE_SLUGS_BTN = 'Remove duplicate slugs';
	const RESET_CROWD_METADATA_AJAX = 'wpdka_reset_crowd_metadata';
	const REMOVE_DUPLICATE_SLUGS_AJAX = 'wpdka_remove_duplicate_slugs';
	const SET_PUBLISH_STATE_AJAX = 'wpdka_set_publish_state';
	const SOCIAL_COUNTS_AJAX = 'wpdka_social_counts';
	const RESET_CROWD_METADATA_PAGE_INDEX_OPTION = 'wp-dka-rcm-pageIndex';
	const RESET_CROWD_METADATA_PAGE_SIZE_OPTION = 'wp-dka-rcm-pageSize';

	//List of plugins depending on
	private static $plugin_dependencies = array(
		'wpchaosclient/wpchaosclient.php' => 'WordPress Chaos Client',
		'wpchaossearch/wpchaossearch.php' => 'WordPress Chaos Search'
	);

	/**
	 * Construct
	 */
	public function __construct() {
		if(self::check_chaosclient()) {

			self::load_dependencies();

			if(is_admin()) {

				add_action('admin_menu', array(&$this, 'create_menu'));
				add_action('admin_init', array(&$this, 'reset_crowd_metadata'));
				add_action('right_now_content_table_end', array(&$this,'add_chaos_material_counts'));
				add_action('wp_dashboard_setup', array(&$this,'remove_dashboard_widgets'));
				add_action('wp_ajax_' . self::RESET_CROWD_METADATA_AJAX, array(&$this, 'ajax_reset_crowd_metadata'));
				add_action('wp_ajax_' . self::REMOVE_DUPLICATE_SLUGS_AJAX, array(&$this, 'ajax_remove_duplicate_slugs'));
				
				add_filter('wpchaos-config', array(&$this, 'settings'));
			}
			add_action('wp_enqueue_scripts', array(&$this, 'loadJsCss'));

			add_filter('widgets_init',array(&$this,'register_widgets'));
			
			// Set publish state
			add_action('wp_ajax_' . self::SET_PUBLISH_STATE_AJAX, array(&$this, 'ajax_set_publish_state'));

			// Social stuff
			add_action('wp_ajax_' . self::SOCIAL_COUNTS_AJAX, array(&$this, 'ajax_social_counts'));
			add_action('wp_ajax_nopriv_' . self::SOCIAL_COUNTS_AJAX, array(&$this, 'ajax_social_counts'));
			
			add_action('dashboard_glance_items', array(&$this,'add_chaos_material_counts'));
			add_action('plugins_loaded',array(&$this,'load_textdomain'));

		}

	}

	public function loadJsCss() {
		add_action( 'wp_footer', function() {
			echo '<script type="text/javascript">jwplayer.key="'. get_option('wpdka-jwplayer-api-key') .'";</script>';
		}, 99);
		wp_enqueue_script('wpdka-publish',plugins_url( 'js/publish.js' , __FILE__ ),array('jquery'),'1.0',true);
		$translation_array = array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'token' => wp_create_nonce(self::TOKEN_PREFIX),
			'error' => '<div class="alert alert-warning">' . __('Metadata schema is not valid for this object.', 'wpdka') . '</div>'
			);
		wp_localize_script( 'wpdka-publish', 'WPDKAPublish', $translation_array );
		wp_enqueue_script('wpdka-player', plugins_url( 'js/player.js', __FILE__),array('jquery'), '1.0',true);
		wp_localize_script( 'wpdka-player', 'WPDKAPlayer', array('goToOriginalPage' => __("Go to original page", "wpdka")));
	}

	public function load_textdomain() {
		load_plugin_textdomain( 'wpdka', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/');
	}
	
	public static function install() {
		if(self::check_chaosclient()) {
			self::load_dependencies();
			WPChaosSearch::flush_rewrite_rules_soon();
		}
	}
	
	public static function uninstall() {
		if(self::check_chaosclient()) {
			WPChaosSearch::flush_rewrite_rules_soon();
		}
	}

	/**
	 * CHAOS settings for this module
	 * 
	 * @param  array $settings Other CHAOS settings
	 * @return array           Merged CHAOS settings
	 */
	public function settings($settings) {
		$pages = array(); 
		foreach(get_pages() as $page) {
			$pages[$page->ID] = $page->post_title;
		}
		
		$new_settings = array(array(
			'name'      => 'dka',
			'title'     => __('Dansk Kulturarv', 'wpdka'),
			'fields'    => array(
				array(
					'name' => 'wpdka-default-organization-page',
					'title' => __('Page for objects with an unknown organization.','wpdka'),
					'type' => 'select',
					'list' => $pages,
				),
			)
		),array(
			/*Sections*/
			'name'		=> 'jwplayer',
			'title'		=> __('JW Player Settings','wpdka'),
			'fields'	=> array(
				/*Section fields*/
				array(
					'name' => 'wpdka-jwplayer-api-key',
					'title' => __('JW Player API key','wpdka'),
					'type' => 'text',
					'val' => '',
					'class' => 'regular-text'
				)
			)
		));
		return array_merge($settings,$new_settings);
	}

	/**
	 * Create submenu and call page for settings
	 * @return void 
	 */
	public function create_menu() {
		add_menu_page(
			'Dansk Kulturarv',
			'DKA',
			'manage_options',
			self::MENU_PAGE,
			array(&$this, 'create_menu_page'),
			'none',
			81
		); 
	}

	/**
	 * Create page for settings
	 * @return void
	 */
	public function create_menu_page() {
		$defaultPageSize = array_key_exists('pageSize', $_GET) ? intval($_GET['pageSize']) : 3;
		$pageIndex = intval(get_option(self::RESET_CROWD_METADATA_PAGE_INDEX_OPTION, 0));
		$pageSize = intval(get_option(self::RESET_CROWD_METADATA_PAGE_SIZE_OPTION, $defaultPageSize));
		if($pageIndex == 0) {
			$start_btn_text = "";
		} else {
			$start_btn_text = " (from page $pageIndex)";
		}
		?>
		<div class="wrap"><h2><?php echo get_admin_page_title() ?></h2>
		<h3>Resetting Dansk Kulturarv's Crowd Metadata</h3>
		<p>When installing the website or when experiencing inconsitent crowd metadata it might make sence to reset the crowd metadata.</p>
		<p><strong>Warning:</strong> This will <strong>remove all</strong> counts on views, shares, likes, ratings and user tags.</p>
		<style type='text/css'>
		#reset-crowd-metadata-start-button, #reset-crowd-metadata-pause-button, #reset-crowd-metadata-stop-button, #progress-objects { float:left; margin: 0px 2px; }
		.media-item .progress { position:relative; float:left; margin: 0px 10px; }
		.media-item .progress .state { display:block; position:absolute; top:0px; right:7px; color: rgba(0, 0, 0, 0.6); padding: 0 8px; text-shadow: 0 1px 0 rgba(255, 255, 255, 0.4); z-index: 10; }
		.media-item .eta { float:right; line-height:24px; }
		#ajax-messages {clear:both;max-height:200px;overflow:auto;padding:10px;}
		</style>
		<button class="button button-primary" id="reset-crowd-metadata-start-button"><?php echo self::RESET_CROWD_METADATA_START_BTN ?><?php echo $start_btn_text ?></button>
		<button class="button button-primary" id="reset-crowd-metadata-pause-button" disabled><?php echo self::RESET_CROWD_METADATA_PAUSE_BTN ?></button>
		<button class="button button-primary" id="reset-crowd-metadata-stop-button"><?php echo self::RESET_CROWD_METADATA_STOP_BTN ?></button>
		<div class='media-item' id='progress-objects' style='display:none;'>
			<div class='progress'><div class='percent'>0%</div><div class='state'><span class='d'>0</span> of <span class='t'>?</span> objects</div><div class='bar'></div></div>
			<div class='eta'>ETA: <span></span></div>
		</div>
		<pre id="ajax-messages"></pre>
		<script>
		jQuery(document).ready(function($) {
			var pageIndex = <?php echo $pageIndex ?>;
			var pageSize = <?php echo $pageSize ?>;
			var startDate = null;
			var objectsProcessedSinceStart = 0;

			// See: http://codeaid.net/javascript/convert-seconds-to-hours-minutes-and-seconds-(javascript)
			function secondsToTime(secs)
			{
			    var hours = Math.floor(secs / (60 * 60));
			   
			    var divisor_for_minutes = secs % (60 * 60);
			    var minutes = Math.floor(divisor_for_minutes / 60);
			 
			    var divisor_for_seconds = divisor_for_minutes % 60;
			    var seconds = Math.ceil(divisor_for_seconds);
			   
			    var obj = {
			        "h": hours,
			        "m": minutes,
			        "s": seconds
			    };
			    return obj;
			}

			function calculate_eta(time_elapsed, objects_processed, objects_left) {
				// time_elapsed in s.
				time_elapsed /= 1000;
				var rate = objects_processed / time_elapsed;
				var eta_seconds = Math.round(objects_left / rate);
				var eta = secondsToTime(eta_seconds);
				var result = ""
				if(eta.h > 0) {
					result += eta.h+" hours ";
				}
				if(eta.m > 0) {
					result += eta.m+" minutes ";
				}
				if(eta.s > 0) {
					result += eta.s+" seconds ";
				}
				return result;
			}
			
			function reset_crowd_metadata(data) {
				data['action'] = "<?php echo WPDKA::RESET_CROWD_METADATA_AJAX ?>";
				// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
				$.post(ajaxurl, data, function(response) {
					// Update the page index, used if the user presses pause.
					pageIndex = response.pageIndex;
					// Overall objects processed.
					var objectsProcessed = (response.pageIndex+1)*response.pageSize;
					// Update the number of objects processed since start.
					objectsProcessedSinceStart += response.pageSize;
					// The procentage.
					var progressProcentage = Math.round(objectsProcessed * 100.0 / response.totalCount);
					// Objects left to process
					var objectsLeft = response.totalCount - objectsProcessed;
					eta = calculate_eta(new Date() - startDate, objectsProcessedSinceStart, objectsLeft);
					$("#progress-objects")
						.find(".percent").text(progressProcentage+"%").end()
						.find(".state .d").text(objectsProcessed).end()
						.find(".state .t").text(response.totalCount).end()
						.find(".bar").css("width", progressProcentage+"%").end()
						.find(".eta span").text(eta).end()
					.fadeIn();
					// Get the next.
					data['pageIndex'] = response.nextPageIndex;
					for(var m = 0; m < response.messages.length; m++) {
						$("#ajax-messages").prepend(response.messages[m]+"\n");
					}
					reset_crowd_metadata(data);
				}, 'json');
			}

			function remove_duplicate_slugs(data) {
				data['action'] = "<?php echo WPDKA::REMOVE_DUPLICATE_SLUGS_AJAX ?>";
				$.post(ajaxurl, data, function(response) {
					removed_duplicate_slugs_count += response.removed;
					$("#remove-duplicate-slugs-status").text('Removed '+removed_duplicate_slugs_count+' duplicate slugs, so far.');
					if(response.removed > 0) {
						remove_duplicate_slugs(data);
					}
				}, 'json');
			}
			
			$("#reset-crowd-metadata-start-button").click(function() {
				var data = { pageSize: pageSize, pageIndex: pageIndex };
				reset_crowd_metadata(data);
				startDate = new Date();
				$(this).attr('disabled', true);
				$("#reset-crowd-metadata-pause-button").attr('disabled', false);
			});
			$("#reset-crowd-metadata-pause-button").click(function() {
				location.reload();
			});
			$("#reset-crowd-metadata-stop-button").click(function() {
				location.href = location.search + "&action=stop";
			});
			var removed_duplicate_slugs_count = 0;
			$("#remove-duplicate-slugs-start-button").click(function() {
				remove_duplicate_slugs({});
				$(this).attr('disabled', true);
			});
		});
		</script>
		<button class="button button-primary" id="remove-duplicate-slugs-start-button"><?php echo self::REMOVE_DUPLICATE_SLUGS_BTN ?></button> <span id="remove-duplicate-slugs-status" />
		<?php
	}
	
	public function reset_crowd_metadata() {
		$action = array_key_exists('action', $_GET) ? $_GET['action'] : null;
		if($action == 'stop') {
			delete_option(self::RESET_CROWD_METADATA_PAGE_INDEX_OPTION);
			delete_option(self::RESET_CROWD_METADATA_PAGE_SIZE_OPTION);
			wp_redirect(admin_url('admin.php?page='.self::MENU_PAGE));
		}
	}
	
	const DUPLICATE_SLUGS_REMOVED_PR_REQUEST = 5;
	
	public function ajax_remove_duplicate_slugs() {
		$result = array('removed' => 0);
		$chaos_slug_field = 'DKA-Crowd-Slug_string';
		$action = array_key_exists('action', $_GET) ? $_GET['action'] : null;
		$facets = WPChaosClient::index_search(array($chaos_slug_field));
		foreach($facets[$chaos_slug_field] as $slug => $count) {
			if($count > 1) {
				// We need to reset something
				$objectResponse = WPChaosClient::instance()->Object()->Get($chaos_slug_field . ':' . $slug, 'GUID+asc', null, 0, $count, true, false, false);
				$objects = WPChaosObject::parseResponse($objectResponse);
				foreach($objects as $object) {
					$new_slug = WPDKAObject::reset_crowd_metadata($object)->slug;
					$result['removed']++;
				}
				if($result['removed'] >= self::DUPLICATE_SLUGS_REMOVED_PR_REQUEST) {
					break;
				}
			}
		}
		echo json_encode($result);
		die();
	}
	
	public function ajax_reset_crowd_metadata () {
		$result = array();
		$result['messages'] = array();
		
		$timeBefore = microtime(true);
		$responseTimeBefore = WPChaosClient::instance()->getAccumulatedResponseTime();
		
		if(!array_key_exists('pageSize', $_POST)) {
			status_header(500);
			echo "pageSize must be specified.";
			die();
		} else {
			$result['pageSize'] = intval($_POST['pageSize']);
		}
		
		// Calculate the pageIndex, defaults to 0.
		$result['pageIndex'] = array_key_exists('pageIndex', $_POST) ? intval($_POST['pageIndex']) : 0;
		
		// Ask chaos for all interesting objects.
		$query = apply_filters('wpchaos-solr-query', '', array());
		$response = WPChaosClient::instance()->Object()->Get($query, "GUID+asc", null, $result['pageIndex'], $result['pageSize'], true);
		
		$result['totalCount'] = $response->MCM()->TotalCount();
		
		$objects = WPChaosObject::parseResponse($response);
		// Process the objects
		foreach($objects as $object) {
			$slug = WPDKAObject::reset_crowd_metadata($object)->slug;
			$result['messages'][] = $object->GUID .' is now reacheable with slug: '. $slug;
			// Ensure its crowd metadata.
			// Make sure the object is reachable on its slug - if not, reset its metadata.
			// $result['messages'][] = "";
		}
		
		$responseTimeAfter = WPChaosClient::instance()->getAccumulatedResponseTime();
		$timeAfter = microtime(true);
		
		$result['messages'][] = round($responseTimeAfter - $responseTimeBefore, 2) . ' of ' . round($timeAfter - $timeBefore, 2) .' seconds spent in CHAOS land.';

		update_option(self::RESET_CROWD_METADATA_PAGE_INDEX_OPTION, $result['pageIndex']);
		update_option(self::RESET_CROWD_METADATA_PAGE_SIZE_OPTION, $result['pageSize']);
		
		$result['nextPageIndex'] = $result['pageIndex'] + 1;
		echo json_encode($result);
		die();
	}


	public function ajax_set_publish_state () {
		if(!array_key_exists('object_guid', $_POST) || !check_ajax_referer(self::TOKEN_PREFIX, 'token', false)) {
			status_header(500);
			echo "Object GUID and URL must be specified.";
			die();
		}

		$objects = WPChaosObject::parseResponse(WPChaosClient::instance()->Object()->Get($_POST['object_guid'], null, false, 0, 1, true, true, true));
		$publish = isset($_POST['publishState']) ? $_POST['publishState'] : true;
		if (count($objects) != 1) {
			echo "Object didn't exist or too many objects returned from service.";
			die();
		}
		$object = $objects[0];
		if ($publish) {
			// Only publish if curator was the one unpublishing in the first place.
			if ($object->unpublishedByCurator) {
				// Removing unpublishedByCurator
				if ($object->unpublishedByCurator) {
					try {
						$metadataXML = $object->get_metadata(WPDKAObject::DKA2_SCHEMA_GUID);
						$unpublishedByCurator = $metadataXML->xpath('/dka2:DKA');
						unset($unpublishedByCurator[0]->unpublishedByCurator);
						
						// Do not refresh client
						if ($object->set_metadata(WPChaosClient::instance(),WPDKAObject::DKA2_SCHEMA_GUID,$metadataXML,WPDKAObject::METADATA_LANGUAGE,null,false)) {
							// Sets accesspoint (publish)
							$serviceResult = WPChaosClient::instance()->Object()->SetPublishSettings(
								$_POST['object_guid'], // objectGUID
								get_option('wpchaos-accesspoint-guid'), // accessPointGUID
								new DateTime('NOW'), // startDate
								null // endDate
							);
						}
					} catch(\Exception $e) {
						error_log('CHAOS Error when changing unpublishedByCurator state: '.$e->getMessage());
						echo 'CHAOS Error when changing unpublishedByCurator state: '.$e->getMessage();
						die();
					}
				}

				$response = array(
					'<div class="alert alert-success">' . __('Object is now republished and is visible for other viewers again.', 'wpdka') . '</div>'
				);
			} else {
				$response = array(
					'<div class="alert alert-warning">' . __('Object is unpublished by the institution. You have no permission to publish this.', 'wpdka') . '</div>'
				);
			}
		} else { 
			// Setting unpublishedByCurator to true.
			try {
				$metadataXML = $object->get_metadata(WPDKAObject::DKA2_SCHEMA_GUID);
				$unpublishedByCurator = $metadataXML->xpath('/dka2:DKA');
				$unpublishedByCurator[0]->unpublishedByCurator = 'true';
				if ($object->set_metadata(WPChaosClient::instance(),WPDKAObject::DKA2_SCHEMA_GUID,$metadataXML,WPDKAObject::METADATA_LANGUAGE)) {
					// Sets accesspoint, but removing startDate (unpublish)
					$serviceResult = WPChaosClient::instance()->Object()->SetPublishSettings(
						$_POST['object_guid'], // objectGUID
						get_option('wpchaos-accesspoint-guid'), // accessPointGUID
						null, // startDate
						null // endDate
					);
				}
			} catch(\Exception $e) {
				error_log('CHAOS Error when changing unpublishedByCurator state: '.$e->getMessage());
				echo 'CHAOS Error when changing unpublishedByCurator state: '.$e->getMessage();
				die();
			}

			$response = array(
				'<div class="alert alert-danger">' . __('Object is now unpublished and is <strong>not</strong> visible for other viewers.', 'wpdka') . '</div>'
			);
		}
		echo json_encode($response);
		die();

	}
	
	public function ajax_social_counts() {
		if(!array_key_exists('object_guid', $_POST)) {
			status_header(500);
			echo "Object GUID and URL must be specified.";
			die();
		}
		
		$objectGUID = strval($_POST['object_guid']);
		
		// Get the object from the CHAOS service.
		$objects = WPChaosObject::parseResponse(WPChaosClient::instance()->Object()->Get($objectGUID, null, null, 0, 1, true));
		
		if(count($objects) != 1) {
			status_header(500);
			echo "Object didn't exist or too many objects returned from service.";
			die();
		}
		
		echo json_encode(WPDKAObject::fetch_social_counts($objects[0], true));
		
		die();
	}

	/**
	 * Add numbers to At A Glance dashboard widget
	 */
	function add_chaos_material_counts() {
		//Even though this function only is in this scope
		//another function in another scope (wpdkatags.php)
		//causes a redeclaration. Suffixing with 2.
		function dashboard_entry2($text,$num) {
			$num = number_format_i18n($num);
			echo '<li class="page-count"><a href="">'.$num.' '.$text.'</a></li>'."\n";
		}

		$sum = WPChaosClient::instance()->Object()->Get('', null, null, 0, 0)->MCM()->TotalCount();

		dashboard_entry2(_n('CHAOS material', 'CHAOS materials', intval($sum),'wpdka'),$sum);

		$facetFields = array('DKA-Crowd-Views_int', 'DKA-Crowd-Likes_int', 'DKA-Crowd-Shares_int');
		$sum = WPChaosClient::summed_index_search($facetFields);

		dashboard_entry2(_n('CHAOS material view', 'CHAOS material views', intval($sum['DKA-Crowd-Views_int']),'wpdka'),$sum['DKA-Crowd-Views_int']);
		//dashboard_entry2(_n('CHAOS material like', 'CHAOS material likes', intval($sum['DKA-Crowd-Likes_int']),'wpdka'),$sum['DKA-Crowd-Likes_int']);
		dashboard_entry2(_n('CHAOS material share', 'CHAOS material shares', intval($sum['DKA-Crowd-Shares_int']),'wpdka'),$sum['DKA-Crowd-Shares_int']);

	}

	/**
	 * Remove clutter from dashboard
	 * @return void
	 */
	public function remove_dashboard_widgets() {
		remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
		remove_meta_box( 'dashboard_secondary', 'dashboard', 'side' );
		remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );
	} 

	/**
	 * Print a jwplayer
	 * @param  array    $options
	 * @param  string    $player_id
	 * @return void
	 */
	public static function print_jwplayer($options, $player_id = 'main-jwplayer') {
		echo '<div id="'.$player_id.'"><p style="text-align:center;">'.__('Loading the player ...','wpdka').'</p></div>';
		echo '<script type="text/javascript">';
		echo 'jQuery(document).ready(function() {';
		echo 'initPlayer(\'' . $player_id . '\', ' . json_encode($options) . ');';
		echo '});';
		echo '</script>';
	}

	public static function generate_file_label($file) {
		$quality_matches = array();
		if(preg_match('/[\d]+k/i', $file->URL, $quality_matches)) {
			$quality = ' ('. strtoupper($quality_matches[0]) .')';
		} else {
			$quality = '';
		}
		return $file->Token . $quality;
	}

	/**
	 * Get a player according to its format type
	 * @param  WPChaosObject    $object
	 * @param  boolean   $autoplay
	 * @param  string    $title
	 * @return string
	 */
	public static function get_object_player(WPChaosObject $object = null, $autoplay = false, $title = '', $embed = null, $start = 0, $stoptime = false, $link = false, $only_thumbnail = false) {
		$return = "";

		if($object == null && WPChaosClient::get_object()) {
			$object = WPChaosClient::get_object();
		} 

		if($object) {
				
			$type = $object->type;
			
			$jwplayer_autostart = $autoplay;
			
			//Look in theme dir and include if found
			ob_start();
			if(locate_template('chaos-player-'.$type, true) == "") {
				include(dirname(__FILE__)."/templates/player-".$type.".php");
			}
			$return = ob_get_contents();
			ob_end_clean();			
		}
		return $return;
	}

	/**
	 * Register widgets in WordPress
	 * @return  void
	 */
	public function register_widgets() {
		register_widget( 'WPDKAObjectPlayerWidget' );
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
					echo '<div class="error"><p><strong>'.__('WordPress DKA','wpdka').'</strong> '.sprintf(__('needs %s to be activated.','wpdka'),'<strong>'.implode('</strong>, </strong>',$dep).'</strong>').'</p></div>';
				},10);
				return false;
			}
		//}
		return true;
	}

	/**
	 * Load files and libraries
	 * @return void 
	 */
	protected static function load_dependencies() {
		require_once('wpdkaobject.php');
		require_once('wpdkasearch.php');
		require_once('wpdkasitemap.php');
		require_once('widgets/player.php');
		require("shortcodes.php");
		require("wpchaosobject-filters.php");
	}

}

register_activation_hook(__FILE__, array('WPDKA', 'install'));
register_deactivation_hook(__FILE__, array('WPDKA', 'uninstall'));

//Instantiate
new WPDKA();

//eol