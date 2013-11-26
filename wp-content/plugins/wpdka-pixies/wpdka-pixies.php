<?php
/*
 Plugin Name: WP DKA Pixies
Plugin URI:
Description: Inserts a pixie in the left hand side of the page.
Version: 1.0
Author: Kræn Hansen <kh@bitlueprint.com>
Author URI:
License:
*/
class WPDKAPixies {
	
	// TODO: Consider making this a selection on the admin interface.
	const SELECTED_PIXIE = 'magnus-tagmus-red.png';

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp_footer', array(&$this, 'print_pixie') );
		add_action( 'wp_enqueue_scripts', array(&$this, 'enqueue_scripts') );
	}
	
	public function enqueue_scripts() {
		wp_enqueue_style( 'wpdka-pixies', plugins_url('css/style.css', __FILE__) );
	}
	
	protected $pixies;
	
	public function get_pixie_url() {
		$pixies = $this->get_pixies();
		return $pixies[self::SELECTED_PIXIE];
	}
	
	public function get_pixies() {
		if($this->pixies == null) {
			$this->pixies = array();
			$dir = 'pixies';
			// Load the pixies from the directory.
			if ($dh = opendir(__DIR__ . DIRECTORY_SEPARATOR . $dir)) {
				while (($file = readdir($dh)) !== false) {
					if($file !== '.' && $file !== '..') {
						$this->pixies[$file] = plugins_url($dir . DIRECTORY_SEPARATOR . $file, __FILE__);
					}
				}
				closedir($dh);
			}
		}
		return $this->pixies;
	}
	
	public function print_pixie() {
		$image_url = $this->get_pixie_url();
		$link_url = site_url('jul');
		echo "<a href='$link_url' class='dka-pixie' title='$link_title'><img src='$image_url'></a>";
	}

}

new WPDKAPixies();
