<?php
/**
 * @package WP DKA Object
 * @version 1.0
 */
?>
<?php

//Must be called within wp_head or wp_footer
//to work with Player Widget
add_action( 'wp_footer', function() {
	wp_enqueue_script( 'flexslider' );
});

//Loop through each file and skip those whose format is not image ?>
<div class="flexslider">
	<a href="<?php echo WPChaosClient::get_object()->url; ?>" class="image_logo" title="<?php printf(__('Go to %s', 'wpdka'), WPChaosClient::get_object()->title); ?>" target="_top"><img src="<?php echo get_template_directory_uri() . '/img/dka-logo-top.png'; ?>" alt="<?php bloginfo('name'); ?>" /></a>
	<ul class="slides">
<?php foreach(WPChaosClient::get_object()->Files as $file) :
	if($file->FormatType != 'Image' || $file->FormatCategory != 'Image Source') continue;
	if(!isset($title) || empty($title)) {
		$title = sprintf(esc_attr__('Image %s for %s','wpdka'),$file->Filename,WPChaosClient::get_object()->title);
	}
	
?>
		<li>
			<img src="<?php echo htmlspecialchars($file->URL); ?>" alt="<?php bloginfo('name'); ?>">
		</li>
<?php ;endforeach; ?>
	</ul>
</div>