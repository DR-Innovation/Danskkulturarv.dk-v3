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
	<ul class="slides">
<?php foreach(WPChaosClient::get_object()->Files as $file) :
	if($file->FormatType != 'Image' || $file->FormatCategory != 'Image Source') continue;
	if(!isset($title) || empty($title)) {
		$title = sprintf(esc_attr__('Image %s for %s'),$file->Filename,WPChaosClient::get_object()->title);
	}
	
?>
		<li>
			<img src="<?php echo htmlspecialchars($file->URL); ?>" title="<?php echo $title; ?>" alt="<?php echo $title; ?>">
		</li>
<?php ;endforeach; ?>
	</ul>
</div>