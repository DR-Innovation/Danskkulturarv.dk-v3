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
		'alert',
		'button',
		'carousel',
		'collapse', //search
		'dropdown', //menu
		'modal', //used by collection and tags
		'scrollspy',
		'tab',
		'tooltip', // Used by the /api page.
		'popover', // Used by the /api page.
		'affix'
	);
	foreach($bootstrap_scripts as $bootscript) {
		wp_dequeue_script( $bootscript );
	}

	// Dequeue scripts that are not necessary.
	wp_dequeue_script( 'respond-js' );
	wp_dequeue_script( 'html5shiv' );
	wp_dequeue_script( 'dka-collections' );


	wp_enqueue_script( 'embed-custom-functions', get_template_directory_uri() . '/js/embed-custom-functions.js', array('jquery'), '1', true );
	wp_localize_script( 'embed-custom-functions', 'embed', 
		array( 'html' 			=> esc_html(WPChaosClient::get_object()->embed), 
			'blogname' 			=> '<a href="' . site_url() .'">' . get_bloginfo('name') . '</a>', 
			'type' 				=> WPChaosClient::get_object()->type, 
			'submit_string' 	=> __('Update', 'dka'),
			'start_string'		=> __('Start playback', 'dka'),
			'size_string'		=> __('Size', 'dka'),
			'width_string'		=> __('Width', 'dka'),
			'height_string'		=> __('Height', 'dka'),
			'autoplay_string' 	=> __('Autoplay', 'dka'),
			'sizes' 			=> 	array(
										array('label' => __('Default', 'dka'), 'width' => '480', 'height' => '360'), 
										array('label' => __('Small', 'dka'), 'width' => '640', 'height' => '360'), 
										array('label' => __('Medium', 'dka'), 'width' => '853', 'height' => '480'),
										array('label' => __('Full', 'dka'), 'width' => '100', 'height' => '100'),
										array('label' => __('Custom', 'dka'), 'width' => '0', 'height' => '0')
									)
		)
	);

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
	<?php
		$autoplay = false;
		$start = 0;
		if (isset($_GET['autoplay']) && $_GET['autoplay']) {
			$autoplay = true;
		}
		if (isset($_GET['start'])) {
			$start = $_GET['start'];
		}
	?>
	<?php echo WPDKA::get_object_player(null, $autoplay, '', true, $start); ?>
</div>
<div class="nav">

<?php
if(WPChaosClient::get_object()->rights) :
	echo '<div title="'.esc_attr(WPChaosClient::get_object()->rights).'" class="copyright pull-left">'.WPChaosClient::get_object()->rights.'</div>';
endif;

echo '<div class="title pull-right"><a title="' . get_bloginfo('name') . '" href="'.WPChaosClient::get_object()->url.'" target="_blank" rel="bookmark">Fra '.get_bloginfo('name').'</a></div>';

?>
	<?php if (!isset($_SERVER['HTTP_REFERER'])): ?>
		<script>
		    try {
		    	// If page is loading inside an iFrame.
		        if (window.self !== window.top) {
		   			// var meta = document.createElement('meta');
					// meta.httpEquiv = "X-Frame-Options";
					// meta.content = "deny";
					// document.getElementsByTagName('head')[0].appendChild(meta);
					document.getElementsByTagName('body')[0].innerHTML = '<div class="noembed">' + 
					'<a href="<?php echo WPChaosClient::get_object()->url; ?>" target="_blank" rel="bookmark">Dette materiale fra <?php bloginfo('name'); ?> kan ikke embeddes.</a>' + 
					'<p>Det lader til at du ikke har tilladelse til at embedde materialer fra <a href="<?php bloginfo('url'); ?>"><?php bloginfo('name'); ?></a>.</p>' + 
					'<p><a href="mailto:drexdibe@dr.dk">Kontakt os venligst </a> hvis du mener der er sket en fejl.</p>' + 
					'</div>';
		        }
		    } catch (e) {
		    }
		</script>
	<?php endif; ?>
</div>

<?php wp_footer(); ?>
</body>
</html>

<?php else:

status_header(404); ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
	</head>
	<body>
		<div class="noembed" style="background-color: #fff;">
			<a href="<?php echo WPChaosClient::get_object()->url; ?>" target="_blank" rel="bookmark">Dette materiale fra <?php bloginfo('name'); ?> kan ikke embeddes.</a>
			<p>Det lader til at du ikke har tilladelse til at embedde materialer fra <a href="<?php bloginfo('url'); ?>"><?php bloginfo('name'); ?></a>.</p>
			<p><a href="mailto:drexdibe@dr.dk">Kontakt os venligst </a> hvis du mener der er sket en fejl.</p>
		</div>
	</body>
</html>
<?php
exit();
endif;
?>