<?php
/**
 * @package WP DKA Object
 * @version 1.0
 */

$object = WPChaosClient::get_object();

if (isset($only_thumbnail) && $only_thumbnail): // Only show thumbnail - we do not need to start the player yet.
	$collection_obj = null;
	if(class_exists('WPDKACollections') && $object->ObjectTypeID == WPDKACollections::COLLECTIONS_TYPE_ID) :
		$collection_obj = new WPChaosObject($object,WPDKACollections::OBJECT_FILTER_PREFIX);
		//Safety. Should never happen
		if(!isset(WPDKACollections::$collection_relations[$object->GUID])) {
			continue;
		}
		$object = WPDKACollections::$collection_relations[$object->GUID];

	endif;
    $thumbnail = (WPChaosClient::get_object()->thumbnail ? ' style="background-image: url(\''.WPChaosClient::get_object()->thumbnail.'\')!important;"' : '');
    $url = WPChaosClient::get_object()->url;
    $caption = WPChaosClient::get_object()->caption;
    $title = WPChaosClient::get_object()->title;
    $organization = WPChaosClient::get_object()->organization;
    $views = number_format_i18n((double)WPChaosClient::get_object()->views);
    $class = '';
    if($collection_obj) {
        $url .= '#'.$collection_obj->GUID;
        $caption = count($collection_obj->ObjectRelations) . ' ' . _n( 'material', 'materials', count($collection_obj->ObjectRelations), 'dka' );
        $title = $collection_obj->title;
        $organization = "Samling";
        $views = null;
        $class = ' collection-result';
    }
    ?>
    <div class="search-results">
	    <div class="search-object<?php echo $class ?>">
	        <a class="thumbnail" href="<?php echo $url; ?>" id="<?php echo WPChaosClient::get_object()->GUID; ?>">

	            <div class="thumb format-<?php echo WPChaosClient::get_object()->type; ?>"<?php echo $thumbnail; ?>>
	                <?php  if($caption):?>
	                <div class="caption"><?php echo $caption ?></div>
	            <?php endif;?>
	            <?php if(class_exists('WPDKACollections') && current_user_can(WPDKACollections::CAPABILITY) && !$collection_obj) : ?>
	                <button type="button" class="add-to-collection btn"><span class="icon-plus"></span></button>
	            <?php endif; ?>
	            </div>
	            <h2 class="title"><strong><?php echo $title; ?></strong></h2>
	            <p class="organization orange"><strong><?php echo $organization; ?></strong></p>
	            <?php if(WPChaosClient::get_object()->published && $collection_obj == null) : ?>
	                <p class="date"><i class="icon-calendar"></i> <?php echo WPChaosClient::get_object()->published; ?></p>
	            <?php endif; ?>
	            <hr>
	            <div class="media-type-container">
	                <i title="<?php echo WPChaosClient::get_object()->type_title; ?>" class="<?php echo WPChaosClient::get_object()->type_class; ?>"></i>
	                <?php if(0 && $views) : ?>
	                <i class="icon-eye-open"> <?php echo $views; ?></i>
	                <?php endif; ?>
	            </div>
	        </a>
	    </div>
	</div>
<?php
else: // Normal behavior (no thumbnail) - start player

//Must be called within wp_head or wp_footer
//to work with Player Widget
add_action( 'wp_footer', function() {
    wp_enqueue_script( 'jwplayer' );
});
add_action( 'wp_footer', function() {
    //echo '<script type="text/javascript">jwplayer.key="'. get_option('wpdka-jwplayer-api-key') .'";</script>';
}, 99);

$playlist_sources = array();
foreach($object->Files as $file) {
    if($file->FormatType == "Audio") {
        $playlist_sources[] = array(
            "file" => $file->URL,
            "label" => WPDKA::generate_file_label($file)
        );
    }
}

$sharing_link = site_url($_SERVER["REQUEST_URI"]);

$options = array(
    "skin" => get_template_directory_uri() . '/lib/jwplayer/dka.xml',
    "skin_embed" => get_template_directory_uri() . '/lib/jwplayer/dka.embed.xml',
    "width" => "100%",
    "height" => 24,
    /*"logo" => array(
        "file" => get_template_directory_uri() . '/img/dka-logo-jwplayer.png',
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
    "autostart" => $jwplayer_autostart,
    "ga" => array(),
);

if (isset($embed) && $embed) {
    if (isset($start) && $start > 0) {
        $options['startoffset'] = $start;
    }

    if (isset($stoptime) && $stoptime > 0) {
        $options['stoptime'] = $stoptime;
    }
}

WPDKA::print_jwplayer($options, 'jwplayer-'.uniqid());
endif;