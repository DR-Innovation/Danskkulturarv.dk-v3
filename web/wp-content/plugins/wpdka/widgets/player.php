<?php
/**
 * @package WP DKA Object
 * @version 1.0
 */

/**
 * WordPress Widget that makes it possible to style
 * and display one data attribute from a CHAOs object
 */
class WPDKAObjectPlayerWidget extends WPChaosWidget {

	/**
	 * Constructor
	 */
	public function __construct() {

		parent::__construct(
			'dka-object-player-widget',
			__('DKA Object Player','wpdka'),
			array( 'description' => __('Display a player according to the material format','wpdka') )
		);

		$this->fields = array(
			array(
				'title' => __('Slug/GUID','wpdka'),
				'name' => 'id',
				'type' => 'text',
				'val' => '',
			),
			array(
				'title' => __('Title/Alternate text','wpdka'),
				'name' => 'alt',
				'type' => 'text',
				'val' => '',
			),
			array(
				'title' => __('Autoplay','wpdka'),
				'name' => 'autoplay',
				'type' => 'checkbox',
				'val' => 0,
			),
			array(
				'title' => __('Link to object','wpdka'),
				'name' => 'link',
				'type' => 'checkbox',
				'val' => 0,
			),
			array(
				'title' => __('Show thumbnail','wpdka'),
				'name' => 'thumbnail',
				'type' => 'checkbox',
				'val' => 0,
			)
		);

	}

	/**
	 * GUI for widget content
	 *
	 * @param  array $args Sidebar arguments
	 * @param  array $instance Widget values from database
	 * @return void
	 */
	public function widget( $args, $instance ) {

		$object = '';
		if(isset($instance['id']) && $instance['id']) {
			//Does id match guid pattern?
			if (preg_match('/^\{?[a-f\d]{8}-(?:[a-f\d]{4}-){3}[a-f\d]{12}\}?$/i', $instance['id'])) {
				$query = 'GUID:'.$instance['id'];
			} else {
				$query = WPDKAObject::DKA_CROWD_SLUG_SOLR_FIELD. ':'. $instance['id'];
			}

			try {
				$serviceResult = WPChaosClient::instance()->Object()->Get(
					"(".$query.")",   // Search query
					null,   // Sort
					null,
					0,      // pageIndex
					1,      // pageSize
					true,   // includeMetadata
					true,   // includeFiles
					true    // includeObjectRelations
				);
			} catch(\Exception $e) {
				echo "Error in CHAOS.";
			}

			if(isset($serviceResult) && $serviceResult->MCM()->Count() > 0) {
				$object = WPChaosDataObject::parseResponse($serviceResult);
				//Set global obj to use templates
				WPChaosClient::set_object($object[0]);
			} else {
				echo "Could not find any object with ID ".$id;
			}

		}

		if(WPChaosClient::get_object()) {

			$autoplay = (isset($instance['autoplay']) ? $instance['autoplay'] : false);
			$alt = (isset($instance['alt']) ? $instance['alt'] : '');
			$link = (isset($instance['link']) ? $instance['link'] : false);
			$thumbnail = (isset($instance['thumbnail']) ? $instance['thumbnail'] : false);

			echo $args['before_widget'];
			echo WPDKA::get_object_player(WPChaosClient::get_object(),$autoplay,$alt,null,0,false,$link,$thumbnail);
			// if($link) {
			// 	echo '<a href="'.WPChaosClient::get_object()->url.'">'.__('Read more','wpdka').'</a>';
			// }
			echo $args['after_widget'];

			if($object) {
				WPChaosClient::reset_object();
			}
		}
	}

}

//eol
