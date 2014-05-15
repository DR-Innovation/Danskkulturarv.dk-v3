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
	<?php if (!isset($_SERVER['HTTP_REFERER'])): ?>
		<script>
		    try {
		        if (window.self !== window.top) {
		        	var meta = document.createElement('meta');
					meta.httpEquiv = "X-Frame-Options";
					meta.content = "deny";
					// document.getElementsByTagName('head')[0].appendChild(meta);
					document.getElementsByTagName('body')[0].innerHTML = '<p>test</p>';

		        }
		    } catch (e) {
		    }
		</script>
	<?php endif; ?>
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
echo '<div title="'.esc_attr(WPChaosClient::get_object()->rights).'" class="copyright pull-left">'.WPChaosClient::get_object()->rights.'</div>';
endif;

echo '<div class="title pull-right"><a href="'.WPChaosClient::get_object()->url.'" target="_blank" rel="bookmark">Fra '.get_bloginfo('name').'</a></div>';

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
					document.getElementsByTagName('body')[0].innerHTML = '<a href="<?php echo WPChaosClient::get_object()->url; ?>" target="_blank" rel="bookmark">Dette materiale fra <?php bloginfo('name'); ?> kan ikke embeddes.</a><p>Det lader til at du ikke har tilladelse til at embedde materialer fra <a href="<?php bloginfo('url'); ?>"><?php bloginfo('name'); ?></a></p>';
					var stylesheets = document.getElementsByTagName('link'), i, sheet;
					
					// Removing stylesheets
					for (i in stylesheets) {
					    sheet = stylesheets[i];
				        sheet.parentNode.removeChild(sheet);
					}
		        }
		    } catch (e) {
		    }
		</script>
	<?php endif; ?>
	<script>
		// If someone trying to access /embed without an iframe.
		// Shows an overlay with the html of how to implement the material in an iframe. 
		try {
			if (window.self === window.top) {
				console.log(document.querySelectorAll('.player'));
				document.querySelectorAll('.player')[0].outerHTML += '<div class="overlay"><div class="info"><h2>Hvordan embedder jeg?</h2><textarea onClick="this.select()" readonly><?php echo esc_html(WPChaosClient::get_object()->embed); ?></textarea><p><?php echo WPChaosClient::get_object()->rights; ?></p></div><a href="#" onClick="this.parentNode.parentNode.removeChild(this.parentNode);" class="exit">&times;</a></div>';
			}
		} catch (e) {
		}
	</script>
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
		<a href="<?php echo WPChaosClient::get_object()->url; ?>" target="_blank" rel="bookmark">Dette materiale fra <?php bloginfo('name'); ?> kan ikke embeddes.</a>
		<p>Det lader til at du ikke har tilladelse til at embedde materialer fra <a href="<?php bloginfo('url'); ?>"><?php bloginfo('name'); ?></a></p>
	</body>
</html>
<?php
exit();
endif;
?>