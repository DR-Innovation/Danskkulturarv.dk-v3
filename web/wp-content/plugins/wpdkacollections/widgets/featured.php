<?php
/**
 * @package WP DKA Collections
 * @version 1.0
 */

/**
 * WordPress Widget that makes it possible to style
 * and display one data attribute from a CHAOs object
 */
class WPDKACollectionFeaturedWidget extends WPChaosWidget {

	protected $collection_materials;

	/**
	 * Constructor
	 */
	public function __construct() {
		
		parent::__construct(
			'dka-collection-featured-widget',
			__('DKA Featured Collections',WPDKACollections::DOMAIN),
			array( 'description' => __('Display selected collections',WPDKACollections::DOMAIN) )
		);

		$this->fields = array(
			array(
				'title' => __('Collections',WPDKACollections::DOMAIN),
				'name' => 'collections',
				'type' => 'checkbox-multi',
				'list' => array($this,'get_collections'),
				'val' => '',
			),
		);

	}

	protected function get_collections() {
		$query = '(FolderID:'.WPDKACollections::COLLECTIONS_FOLDER_ID.') AND ('.WPDKACollections::FACET_KEY_STATUS.':'.WPDKACollections::STATUS_PUBLISH .')';

		$response = WPChaosClient::instance()->Object()->Get(
			$query,   // Search query
			null,   // Sort
			null, 
			0,      // pageIndex
			100,      // pageSize
			true,   // includeMetadata
			false,   // includeFiles
			false    // includeObjectRelations
		);

		$return = array();
		$items = WPChaosObject::parseResponse($response,WPDKACollections::OBJECT_FILTER_PREFIX);
		foreach($items as $item) {
			$return[$item->GUID] = $item->title;
		}

		return $return;

	}

	protected function get_collection_objects($collections) {
		$response = WPChaosClient::instance()->Object()->Get(
			"(GUID: ".implode(" ", $collections).")",   // Search query
			null,   // Sort
			null, 
			0,      // pageIndex
			count($collections),      // pageSize
			true,   // includeMetadata
			false,   // includeFiles
			false    // includeObjectRelations
		);
		$return = array();
		$relations = array();
		foreach($response->MCM()->Results() as $collection) {
			$collection_object = new WPChaosObject($collection,WPDKACollections::OBJECT_FILTER_PREFIX);
			if(count($collection_object ->playlist_raw) == 0)
				continue;
			//An object might occur first in several collections
			$relations[$collection_object ->GUID] = (string)$collection_object ->playlist_raw[0];
			$return[] = $collection_object;
		}

		WPDKACollections::map_collections_to_material($relations,$this->collection_materials);

		return $return;
	}

	/**
	 * GUI for widget content
	 * 
	 * @param  array $args Sidebar arguments
	 * @param  array $instance Widget values from database
	 * @return void 
	 */
	public function widget( $args, $instance ) {
		if(isset($instance['collections']) && $instance['collections']) {
			echo $args['before_widget'];

			echo '<div id="frontpage_carousel" class="carousel slide" data-ride="carousel">';
			echo '<div class="carousel-inner">';

			$count = 0;
			foreach($this->get_collection_objects($instance['collections']) as $collection) {
				//Safety. Should never happen
				if(!isset($this->collection_materials[$collection->GUID])) {
					continue;
				}
				$material = $this->collection_materials[$collection->GUID];

				$url = $material->url . '#' . $collection->GUID;

				echo '<div class="item' . (($count == 0) ? ' active' : '') . '">';
	        	echo '<img src="' . $material->thumbnail . '" alt="' . $collection->title . '">';
	        	echo '<a href="' . $url . '">';
	    		echo '<div class="carousel-caption">';
	      		echo '<h3>'.$collection->type.': ' . $collection->title . '</h3>';
	      		echo '<p>' . $collection->description . '</p>';
	    		echo '</div>';
	    		echo '</a>';
	      		echo '</div>';
				$count++;
			}

			echo '</div>';
			echo '</div>';

			//var_dump($this->collection_materials);

			echo $args['after_widget'];			
		}

	}

}

//eol