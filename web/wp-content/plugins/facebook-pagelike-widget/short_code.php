<?php
function fb_plugin_shortcode($atts) {

	$atts = shortcode_atts(array('fb_url' => 'http://facebook.com/WordPress', 'width' => '400', 'height' => '500', 'data_small_header' => 'false', 'data_small_header' => 'false', 'data_adapt_container_width' => 'true', 'data_hide_cover' => 'false', 'data_show_facepile' => 'true', 'data_tabs' => 'timeline', 'data_lazy'=> 'false'), $atts, 'fb_widget');
	
	$feeds = '<iframe src="https://www.facebook.com/plugins/page.php?href='.esc_html($atts['fb_url']).'&tabs='.esc_html($atts['data_tabs']).'&width='.esc_html($atts['width']).'&height='.esc_html($atts['height']).'&small_header='.esc_html($atts['data_small_header']).'&adapt_container_width='.esc_html($atts['data_adapt_container_width']).'&hide_cover='.esc_html($atts['data_hide_cover']).'&show_facepile='.esc_html($atts['data_show_facepile']).'&data_lazy='.esc_html($atts['data_lazy']).'" width="'.esc_html($atts['width']).'" height="'.esc_html($atts['height']).'" scrolling="no" frameborder="0" allowfullscreen="true" allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"></iframe>';

	return $feeds;
}

add_shortcode('fb_widget', 'fb_plugin_shortcode');


?>