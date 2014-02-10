<?php
/**
 * @package WP DKA Object
 * @version 1.0
 */

/**
 * WordPress Widget that makes it possible to style
 * and display one data attribute from a CHAOs object
 */
class WPDKACollectionFeaturedWidget extends WP_Widget {

	/**
	 * Fields in widget. Defines keys for values
	 * @var array
	 */
	private $fields;

	private $collection_materials;

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
				'type' => 'checkbox',
				'list' => array($this,'get_collections'),
				'val' => '',
			),
		);

	}

	private function get_collections() {
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

	private function get_collection_objects($collections) {
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
		if(isset($instance['collections'])) {
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

	/**
	 * GUI for widget form in the administration
	 * 
	 * @param  array $instance Widget values from database
	 * @return void           
	 */
	public function form( $instance ) {

		//Print each field based on its type
		foreach($this->fields as $field) {
			$value = isset( $instance[ $field['name'] ]) ? $instance[ $field['name'] ] : $field['val'];
			$name = $this->get_field_name( $field['name'] );
			$title = $field['title'];
			$id = $this->get_field_id( $field['name'] );

			//Populate list with callback
			if(isset($field['list']) && !empty($field['list']) && $field['list'][0] == $this) {
				$field['list'] = call_user_func($field['list']);
			}

			echo '<p>';
			echo '<label for="'.$name.'">'.$title.'</label>';
			switch($field['type']) {
				case 'textarea':
					echo '<textarea class="widefat" name="'.$name.'" >'.$value.'</textarea>';
					break;
				case 'select':
					echo '<select class="widefat" name="'.$name.'">';
					foreach((array)$field['list'] as $opt_key => $opt_value) {
						echo '<option value="'.$opt_key.'" '.selected( $value, $opt_key, false).'>'.$opt_value.'</option>';
					}
					echo '</select>';
					break;
				case 'checkbox':
					foreach((array)$field['list'] as $opt_key => $opt_value) {
						echo '<input type="checkbox" name="'.$name.'[]" value="'.$opt_key.'" '.checked( in_array($opt_key,$value), true, false).'> '.$opt_value.'';
					}
					break;
				case 'text':
				default:
					echo '<input class="widefat" id="'.$id.'" name="'.$name.'" type="text" value="'.esc_attr( $value ).'" />';
			}
			echo '</p>';

		}
	}

	/**
	 * Callback for whenever the widget values should be saved
	 * 
	 * @param  array $new_instance New values from the form
	 * @param  array $old_instance Previously saved values
	 * @return array               Values to be saved
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = array();
		
		foreach($this->fields as $field) {
			$instance[$field['name']] = ( ! empty( $new_instance[$field['name']] ) ) ? $new_instance[$field['name']]  : $field['val'];
		}
		
		return $instance;
	}

}

//eol