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

	}

	/**
	 * GUI for widget content
	 * 
	 * @param  array $args Sidebar arguments
	 * @param  array $instance Widget values from database
	 * @return void 
	 */
	public function widget( $args, $instance ) {
		if(WPChaosClient::get_object()) {
			echo $args['before_widget'];

			//var_dump(WPChaosClient::get_object()->Files);
			
			$type = WPChaosClient::get_object()->type;
			
			//Look in theme dir and include if found
			if(locate_template('chaos-player-'.$type, true) == "") {
				include(dirname(__FILE__)."/../templates/player-".$type.".php");
			}

			echo $args['after_widget'];
		}
	}

}

//eol