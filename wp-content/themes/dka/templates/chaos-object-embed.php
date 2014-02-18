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

	wp_dequeue_script( 'respond-js' );
	wp_dequeue_script( 'html5shiv' );
	wp_dequeue_script( 'dka-collections' );
	do_action('dequeue_all_styles');
	wp_enqueue_style( 'dka-embed-style' );
} );

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head prefix="og: http://ogp.me/ns#">
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title><?php wp_title( '|', true, 'right' ); ?></title>
	<link rel="profile" href="http://gmpg.org/xfn/11" />
	<?php wp_head(); ?>
</head>
<body>
<?php the_widget('WPDKAObjectPlayerWidget'); ?>
<?php wp_footer(); ?>
</body>
</html>

<?php else :

status_header(404);
exit();

endif; ?>