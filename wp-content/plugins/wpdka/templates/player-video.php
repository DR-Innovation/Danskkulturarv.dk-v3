<?php
/**
 * @package WP DKA Object
 * @version 1.0
 */

//Must be called within wp_head or wp_footer
//to work with Player Widget
add_action( 'wp_footer', function() {
	wp_enqueue_script( 'jwplayer' );
});
add_action( 'wp_footer', function() {
	echo '<script type="text/javascript">jwplayer.key="'. get_option('wpdka-jwplayer-api-key') .'";</script>';
}, 99);

$object = WPChaosClient::get_object();

$playlist_sources = array();
foreach($object->Files as $file) {
	if($file->FormatType == "Video") {
		$playlist_sources[] = array(
			"file" => $file->URL,
			"label" => WPDKA::generate_file_label($file)
		);
	}
}

$sharing_link = site_url($_SERVER["REQUEST_URI"]);
$options = array(
	"skin" => get_template_directory_uri() . '/lib/jwplayer/dka.xml',
	"width" => "100%",
	"aspectratio" => "4:3",
	/*"logo" => array(
		/*"file" => get_template_directory_uri() . '/img/dka-logo-jwplayer.png',
		"hide" => true,
		"link" => /*site_url()*//*$object->url,
		"margin" => 20
	),*/
	"abouttext" => sprintf(__("About %s",'wpdka'),get_bloginfo('title')),
	"aboutlink" => site_url('om'),
	"playlist" => array(array(
		"image" => $object->thumbnail,
		"mediaid" => $object->GUID,
		"sources" => $playlist_sources,
		"title" => htmlspecialchars_decode($object->title)
	)),
	"sharing" => array(
		"link" => $sharing_link
	),
	"autostart" => false,
	"ga" => array()
);
if (isset($embed)) {
	$options['logo'] = array(
		"file" => get_template_directory_uri() . '/img/dka-logo-jwplayer.png',
		"hide" => true,
		"link" => /*site_url()*/$object->url,
		"margin" => 20
	);
	$options['autostart'] = (isset($jwplayer_autostart) && $jwplayer_autostart) ? $jwplayer_autostart : false;
	if (isset($start) && $start > 0) {
		$options['startoffset'] = $start;
	}
}


WPDKA::print_jwplayer($options, 'jwplayer-'.uniqid());