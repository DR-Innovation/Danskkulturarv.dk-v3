<?php
/**
 * Facebook Widget Class
 */
class facebook_widget extends WP_Widget {
    /** constructor */
    function __construct() {

        parent::__construct(
			'fbw_id', // Base ID
			'Facebook Page Like Widget', // Name
			array( 'description' => __( 'Facebook Page Like Widget' , 'social-feeds-for-wordpress' ) )
		);

        add_action( 'admin_enqueue_scripts', [ $this, 'load_custom_js' ] );
        add_action( 'admin_enqueue_scripts', [ $this,'enqueue_admin_widget_scripts']);

    }

    function load_custom_js(){
        wp_enqueue_script( 'load-custom-js', FB_WIDGET_PLUGIN_URL . 'app/SocialFeeds/Admin/custom.js' );
    }

    function enqueue_admin_widget_scripts($hook) {
        // Only load on the widgets or customizer pages
        if ( 'widgets.php' !== $hook && 'customize.php' !== $hook ) {
            return;
        }

        wp_enqueue_script('fb-widget-js',  FB_WIDGET_PLUGIN_URL . 'assets/js/fb.js', array('jquery'), '3.0', true );
    }

    public function widget( $args, $instance ) {
        // 1. Initialize variables
        $title     = ! empty( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'] ) : '';
        $fb_url    = $instance['fb_url'] ?? 'https://www.facebook.com/WordPress';
        $width     = $instance['width'] ?? '300';
        $height    = $instance['height'] ?? '500';
        $lang      = $instance['select_lng'] ?? 'en_US';

        $data_tabs_array = ! empty( $instance['data_tabs'] ) ? (array) $instance['data_tabs'] : array( 'timeline' );
        $data_tabs_string = implode( ',', $data_tabs_array );

        $data_small_header          = ( isset( $instance['data_small_header'] ) && $instance['data_small_header'] === 1 ) ? 'true' : 'false';
        $data_adapt_container_width  = ( isset( $instance['data_adapt_container_width'] ) && $instance['data_adapt_container_width'] === 1 ) ? 'true' : 'false';
        $data_hide_cover            = ( isset( $instance['data_hide_cover'] ) && $instance['data_hide_cover'] === 1 ) ? 'true' : 'false';
        $data_show_facepile         = ( isset( $instance['data_show_facepile'] ) && $instance['data_show_facepile'] === 1 ) ? 'true' : 'false';
        $data_lazy                  = ( isset( $instance['data_lazy'] ) && $instance['data_lazy'] === 1 ) ? 'true' : 'false';

        echo $args['before_widget'];

        if ( $title ) {
            echo $args['before_title'] . $title . $args['after_title'];
        }

        // 2. Consistent Script Enqueueing
        // Use the assets/js/ path consistently across the plugin
        wp_enqueue_script( 'scfbwidgetscript', FB_WIDGET_PLUGIN_URL . 'assets/js/fb.js', array( 'jquery' ), '3.0', true );
        wp_enqueue_script( 'scfbexternalscript', 'https://connect.facebook.net/' . $lang . '/sdk.js#xfbml=1&version=v25.0', array(), '1.0', true );

        // 3. Output HTML - Wrapped in .fb-widget-container for style.css compatibility
        ?>
        <div class="fb-widget-container" style="position: relative; min-height: <?php echo absint($height); ?>px;">
            <div class="fb_loader" style="text-align: center !important;">
                <img src="<?php echo esc_url( FB_WIDGET_PLUGIN_URL . 'assets/images/loader.gif' ); ?>" alt="Loading..." />
            </div>

            <div id="fb-root"></div>
            <div class="fb-page"
                data-href="<?php echo esc_url( $fb_url ); ?>"
                data-width="<?php echo esc_attr( $width ); ?>"
                data-height="<?php echo esc_attr( $height ); ?>"
                data-small-header="<?php echo $data_small_header; ?>"
                data-adapt-container-width="<?php echo $data_adapt_container_width; ?>"
                data-hide-cover="<?php echo $data_hide_cover; ?>"
                data-show-facepile="<?php echo $data_show_facepile; ?>"
                data-tabs="<?php echo esc_attr( $data_tabs_string ); ?>"
                data-lazy="<?php echo $data_lazy; ?>"
                data-xfbml-parse-ignore="false">
            </div>
        </div>
        <?php

        echo $args['after_widget'];
    }

    /**
     * @see WP_Widget::update Update widget options.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();

        $instance['title']      = !empty( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '';
        $instance['fb_url']     = !empty( $new_instance['fb_url'] ) ? esc_url_raw( $new_instance['fb_url'] ) : '';
        $instance['width']      = !empty( $new_instance['width'] ) ? sanitize_text_field( $new_instance['width'] ) : '';
        $instance['height']     = !empty( $new_instance['height'] ) ? sanitize_text_field( $new_instance['height'] ) : '';
        $instance['select_lng'] = !empty( $new_instance['select_lng'] ) ? sanitize_text_field( $new_instance['select_lng'] ) : 'en_US';

        // Handle Multiple Select for data_tabs
        if ( ! empty( $new_instance['data_tabs'] ) && is_array( $new_instance['data_tabs'] ) ) {
            $instance['data_tabs'] = array_map( 'sanitize_text_field', $new_instance['data_tabs'] );
        } else {
            $instance['data_tabs'] = array( 'timeline' );
        }

        // Booleans/Checkboxes
        $instance['data_small_header']           = !empty( $new_instance['data_small_header'] ) ? 1 : 0;
        $instance['data_adapt_container_width']  = !empty( $new_instance['data_adapt_container_width'] ) ? 1 : 0;
        $instance['data_hide_cover']             = !empty( $new_instance['data_hide_cover'] ) ? 1 : 0;
        $instance['data_show_facepile']          = !empty( $new_instance['data_show_facepile'] ) ? 1 : 0;
        $instance['data_lazy']                   = !empty( $new_instance['data_lazy'] ) ? 1 : 0;

        return $instance;
    }

    function form( $instance ) {
        $defaults = array(
            'title'                      => 'Like Us On Facebook',
            'fb_url'                     => 'https://www.facebook.com/WordPress',
            'width'                      => '300',
            'height'                     => '500',
            'data_small_header'          => 0,
            'select_lng'                 => 'en_US',
            'data_adapt_container_width' => 1,
            'data_hide_cover'            => 0,
            'data_show_facepile'         => 1,
            'data_tabs'                  => array('timeline'),
            'data_lazy'                  => 0
        );

        $instance = wp_parse_args( (array) $instance, $defaults );

        $title     = esc_attr( $instance['title'] );
        $fb_url    = esc_attr( $instance['fb_url'] );
        $width     = esc_attr( $instance['width'] );
        $height    = esc_attr( $instance['height'] );
        $data_tabs = $instance['data_tabs'];

        // Correctly handle tabs as an array
        $selected_tabs = is_array( $data_tabs ) ? $data_tabs : explode( ',', $data_tabs );
        ?>

        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'social-feeds-for-wordpress' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'fb_url' ); ?>"><?php _e( 'Facebook Page Url:', 'social-feeds-for-wordpress' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'fb_url' ); ?>" name="<?php echo $this->get_field_name( 'fb_url' ); ?>" type="text" value="<?php echo $fb_url; ?>" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'data_tabs' ); ?>"><?php _e( 'Tabs (Hold Ctrl/Cmd to select multiple):', 'social-feeds-for-wordpress' ); ?></label>
            <select class="widefat" multiple="multiple" name="<?php echo $this->get_field_name('data_tabs'); ?>[]" id="<?php echo $this->get_field_id('data_tabs'); ?>" style="height: 80px;">
                <?php
                $available_tabs = array( 'timeline', 'events', 'messages' );
                foreach ( $available_tabs as $tab ) {
                    $selected = in_array( $tab, $selected_tabs ) ? 'selected="selected"' : '';
                    echo '<option value="' . esc_attr( $tab ) . '" ' . $selected . '>' . ucfirst( $tab ) . '</option>';
                }
                ?>
            </select>
        </p>

        <p>
            <input class="checkbox" type="checkbox" <?php checked( $instance['data_hide_cover'], 1 ) ?> id="<?php echo $this->get_field_id( 'data_hide_cover' ); ?>" name="<?php echo $this->get_field_name( 'data_hide_cover' ); ?>" />
            <label for="<?php echo $this->get_field_id( 'data_hide_cover' ); ?>"><?php _e( 'Hide Cover Photo', 'social-feeds-for-wordpress' ); ?></label>
        </p>

        <p>
            <input class="checkbox" type="checkbox" <?php checked( $instance['data_show_facepile'], 1 ) ?> id="<?php echo $this->get_field_id( 'data_show_facepile' ); ?>" name="<?php echo $this->get_field_name( 'data_show_facepile' ); ?>" />
            <label for="<?php echo $this->get_field_id( 'data_show_facepile' ); ?>"><?php _e( "Show Friend's Faces", 'social-feeds-for-wordpress' ); ?></label>
        </p>

        <p>
            <input class="checkbox" type="checkbox" <?php checked( $instance['data_small_header'], 1 ) ?> id="<?php echo $this->get_field_id( 'data_small_header' ); ?>" name="<?php echo $this->get_field_name( 'data_small_header' ); ?>" />
            <label for="<?php echo $this->get_field_id( 'data_small_header' ); ?>"><?php _e( 'Show Small Header', 'social-feeds-for-wordpress' ); ?></label>
        </p>

        <p>
            <input class="checkbox" type="checkbox" <?php checked( $instance['data_lazy'], 1 ) ?> id="<?php echo $this->get_field_id( 'data_lazy' ); ?>" name="<?php echo $this->get_field_name( 'data_lazy' ); ?>" />
            <label for="<?php echo $this->get_field_id( 'data_lazy' ); ?>"><?php _e( 'Enable Lazy Loading', 'social-feeds-for-wordpress' ); ?></label>
        </p>

        <p>
            <input class="checkbox adapt-click" type="checkbox" <?php checked( $instance['data_adapt_container_width'], 1 ) ?> id="<?php echo $this->get_field_id( 'data_adapt_container_width' ); ?>" name="<?php echo $this->get_field_name( 'data_adapt_container_width' ); ?>" />
            <label for="<?php echo $this->get_field_id( 'data_adapt_container_width' ); ?>"><?php _e( 'Adapt To Container Width', 'social-feeds-for-wordpress' ); ?></label>
        </p>

        <p class="width_option" style="<?php echo ( $instance['data_adapt_container_width'] === 'true' ) ? 'display:none;' : ''; ?>">
            <label for="<?php echo $this->get_field_id( 'width' ); ?>"><?php _e( 'Set Width:', 'social-feeds-for-wordpress' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'width' ); ?>" name="<?php echo $this->get_field_name( 'width' ); ?>" type="text" value="<?php echo $width; ?>" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'height' ); ?>"><?php _e( 'Set Height:', 'social-feeds-for-wordpress' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'height' ); ?>" name="<?php echo $this->get_field_name( 'height' ); ?>" type="text" value="<?php echo $height; ?>" />
        </p>

        <?php
        $filename = BZ_SOCIAL_FEEDS_DIR . '/FacebookLocales.json';
        if ( file_exists( $filename ) ) {
            $langs = file_get_contents( $filename );
            $jsoncont = json_decode( $langs, true );
            if ( ! empty( $jsoncont ) ) { ?>
                <p>
                    <label for="<?php echo $this->get_field_id( 'select_lng' ); ?>"><?php _e( 'Language:', 'social-feeds-for-wordpress' ); ?></label>
                    <select class="widefat" name="<?php echo $this->get_field_name( 'select_lng' ); ?>" id="<?php echo $this->get_field_id( 'select_lng' ); ?>">
                        <?php foreach ( $jsoncont as $lang_name => $short_name ) : ?>
                            <option value="<?php echo esc_attr( $short_name ); ?>" <?php selected( $instance['select_lng'], $short_name ); ?>>
                                <?php echo esc_html( $lang_name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </p>
            <?php }
        }
    }
}

add_action( 'widgets_init', function() {
    return register_widget( "facebook_widget" );
});



