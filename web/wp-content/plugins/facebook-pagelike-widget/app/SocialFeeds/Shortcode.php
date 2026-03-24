<?php
/**
 * Facebook Page Plugin Shortcode (Standard XFBML Version)
 */
function bzsf_fb_plugin_shortcode( $atts ) {

    // 1. Define defaults
    $atts = shortcode_atts( array(
        'fb_url'                     => 'https://www.facebook.com/WordPress',
        'width'                      => '340',
        'height'                     => '500',
        'data_tabs'                  => 'timeline',
        'data_small_header'          => 'false',
        'data_adapt_container_width' => 'true',
        'data_hide_cover'            => 'false',
        'data_show_facepile'         => 'true',
        'data_lazy'                  => 'false',
        'lang'                       => 'en_US'
    ), $atts, 'fb_widget' );

    // 2. Enqueue the Facebook SDK and local scripts
    wp_enqueue_style( 'fb-widget-frontend-style', FB_WIDGET_PLUGIN_URL . 'assets/css/style.css' );
    wp_enqueue_script( 'scfbwidgetscript', FB_WIDGET_PLUGIN_URL . 'assets/js/fb.js', array( 'jquery' ), '3.0', true );
    wp_enqueue_script( 'scfbexternalscript', 'https://connect.facebook.net/' . esc_attr($atts['lang']) . '/sdk.js#xfbml=1&version=v25.0', array(), '1.0', true );

    // 3. Determine container width
    $style_width = ( $atts['data_adapt_container_width'] === 'true' ) ? '100%' : absint( $atts['width'] ) . 'px';

    ob_start(); ?>

    <div class="fb-shortcode-container" style="width: <?php echo $style_width; ?>; position: relative; min-height: <?php echo absint($atts['height']); ?>px;">

        <div class="fb_loader" style="text-align: center !important;">
            <img src="<?php echo esc_url( FB_WIDGET_PLUGIN_URL . 'assets/images/loader.gif' ); ?>" alt="Loading..." />
        </div>

        <div id="fb-root"></div>

        <div class="fb-page"
            data-href="<?php echo esc_url( $atts['fb_url'] ); ?>"
            data-tabs="<?php echo esc_attr( $atts['data_tabs'] ); ?>"
            data-width="<?php echo absint( $atts['width'] ); ?>"
            data-height="<?php echo absint( $atts['height'] ); ?>"
            data-small-header="<?php echo ( $atts['data_small_header'] === 'true' ) ? 'true' : 'false'; ?>"
            data-adapt-container-width="<?php echo ( $atts['data_adapt_container_width'] === 'true' ) ? 'true' : 'false'; ?>"
            data-hide-cover="<?php echo ( $atts['data_hide_cover'] === 'true' ) ? 'true' : 'false'; ?>"
            data-show-facepile="<?php echo ( $atts['data_show_facepile'] === 'true' ) ? 'true' : 'false'; ?>"
            data-lazy="<?php echo ( $atts['data_lazy'] === 'true' ) ? 'true' : 'false'; ?>"
            data-xfbml-parse-ignore="false">
        </div>
    </div>

    <?php
    return ob_get_clean();
}
add_shortcode( 'fb_widget', 'bzsf_fb_plugin_shortcode' );
