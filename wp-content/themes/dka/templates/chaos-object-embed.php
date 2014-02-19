<?php
/**
 * @package WP Chaos Client
 * @version 1.0
 */
?>
<?php

if(WPChaosClient::get_object()->is_embeddable) :

//Remove scripts not needed
add_action( 'wp_enqueue_scripts', function() {

	$bootstrap_scripts = array(
		'transition', //modal
		//'alert',
		//'button',
		//'carousel',
		'collapse', //search
		'dropdown', //menu
		'modal', //used by collection and tags
		//'scrollspy',
		//'tab',
		'tooltip', // Used by the /api page.
		'popover', // Used by the /api page.
		//'affix'
	);
	foreach($bootstrap_scripts as $bootscript) {
		wp_dequeue_script( $bootscript );
	}

	// Dequeue scripts that are not necessary.
	wp_dequeue_script( 'respond-js' );
	wp_dequeue_script( 'html5shiv' );
	wp_dequeue_script( 'dka-collections' );

	// Dequeue all styles
	do_action('dequeue_all_styles');
	wp_enqueue_style( 'dka-embed-style' );

	// Remove admin bar at the top.
    show_admin_bar(false);
} );

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> style="margin-top: 0 !important;">
<head prefix="og: http://ogp.me/ns#">
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title><?php wp_title( '|', true, 'right' ); ?></title>
	<link rel="profile" href="http://gmpg.org/xfn/11" />
	<?php wp_head(); ?>
</head>
<body>
<div class="player">
	<?php echo WPDKA::get_object_player(); ?>
</div>
<div class="nav">

<?php

if(WPChaosClient::get_object()->rights) :
echo '<div title="'.WPChaosClient::get_object()->rights.'" class="copyright pull-left">'.WPChaosClient::get_object()->rights.'</div>';
endif;

echo '<div class="title pull-right"><a href="'.WPChaosClient::get_object()->url.'" target="_blank" rel="bookmark">Fra '.get_bloginfo('name').'</a></div>';

?>

</div>

<?php wp_footer(); ?>
</body>
</html>

<?php else :

status_header(404); ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<?php
echo '<head></head><body>';
echo '<a href="'.WPChaosClient::get_object()->url.'" target="_blank" rel="bookmark">Dette materiale fra '.get_bloginfo('name').' kan ikke indlejres.</a>';
echo '</body></html>';
exit();

endif; ?>