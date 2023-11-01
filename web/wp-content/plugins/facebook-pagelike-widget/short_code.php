<?php
function fb_plugin_shortcode($atts, $content = null) {

	$atts = shortcode_atts(array('title' => 'Like Us On Facebook', 'app_id' => '503595753002055', 'fb_url' => 'http://facebook.com/WordPress', 'width' => '400', 'height' => '500', 'data_small_header' => 'false', 'select_lng' => 'en_US', 'data_small_header' => 'false', 'data_adapt_container_width' => 'true', 'data_hide_cover' => 'false', 'data_show_facepile' => 'true', 'custom_css' => '', 'data_tabs' => 'timeline', 'data_lazy'=> 'false'), $atts, 'fb_widget');
	
	wp_register_script('milapfbwidgetscriptsc', FB_WIDGET_PLUGIN_URL . 'fb_sc.js', array('jquery'));
	wp_enqueue_script('milapfbwidgetscriptsc');
	
	$local_variables = array('app_id' => esc_html($atts['app_id']), 'select_lng' => esc_html($atts['select_lng']));
	
	wp_localize_script('milapfbwidgetscriptsc', 'milapfbwidgetvarssc', $local_variables);
	
	$feeds = '<div class="fb_loader" style="text-align: center !important;"><img src="' . plugins_url() . '/facebook-pagelike-widget/loader.gif" /></div><div id="fb-root"></div><div style="display:inherit;" class="fb-page" data-href="' . esc_html($atts['fb_url']) . '" data-width="' . esc_html($atts['width']) . '" data-height="' . esc_html($atts['height']) . '" data-small-header="' . esc_html($atts['data_small_header']) . '" data-adapt-container-width="' . esc_html($atts['data_adapt_container_width']) . '" data-hide-cover="' . esc_html($atts['data_hide_cover']) . '" data-show-facepile="' . esc_html($atts['data_show_facepile']) . '" style="' . esc_html($atts['custom_css']) . '" data-tabs="'. esc_html($atts['data_tabs']) .'" data-lazy="'.esc_html($atts['data_lazy']).'"></div>';

	return $feeds;
}
add_shortcode('fb_widget', 'fb_plugin_shortcode');
?>