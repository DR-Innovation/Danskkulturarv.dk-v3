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
			array( 'description' => __( 'Facebook Page Like Widget' , 'facebook-pagelike-widget' ) )
		);

        add_action( 'admin_enqueue_scripts', [ $this, 'load_custom_js' ] );
        
    }

    function load_custom_js(){
        wp_enqueue_script( 'load-custom-js', plugin_dir_url(__FILE__) . 'admin/assets/js/custom.js' );
    }

    /** @see WP_Widget::widget */
    function widget( $args , $instance ) {
        
        global $select_lng;
        extract( $args );
        
        $title                          =   apply_filters( 'widget_title' , $instance['title'] );
        $fb_url                         =   $instance['fb_url'];
        $width                          =   $instance['width'];
        $height                         =   $instance['height'];
        $data_small_header              =   isset( $instance['data_small_header'] ) && $instance['data_small_header'] != '' ? 'true' : 'false';
        $data_adapt_container_width     =   isset( $instance['data_adapt_container_width'] ) && $instance['data_adapt_container_width'] != '' ? 'true' : 'false';
        $data_hide_cover                =   isset( $instance['data_hide_cover']) && $instance['data_hide_cover'] != '' ? 'true' : 'false';
        $data_show_facepile             =   isset( $instance['data_show_facepile']) && $instance['data_show_facepile'] != '' ? 'true' : 'false';
        $select_lng                     =   $instance['select_lng'];
        $data_tabs                      =   'timeline';
        $data_lazy                      =   isset( $instance['data_lazy'] ) && $instance['data_lazy'] != '' ? 'true' : 'false';
        
        if (array_key_exists('data_tabs', $instance) && $instance['data_tabs'] !== '') {
            $data_tabs = implode(",", $instance['data_tabs']);
        }

        echo $before_widget;
        if ( $title ) echo $before_title . $title . $after_title;

        wp_register_script( 'scfbwidgetscript' , FB_WIDGET_PLUGIN_URL . 'fb.js', array( 'jquery' ), '1.0' );
        wp_enqueue_script( 'scfbwidgetscript' );

        wp_register_script( 'scfbexternalscript', 'https://connect.facebook.net/'.$select_lng.'/sdk.js#xfbml=1&version=v18.0', "", '2.0', true );
        wp_enqueue_script( 'scfbexternalscript' );
        
        echo '<div class="fb_loader" style="text-align: center !important;"><img src="' . plugins_url() . '/facebook-pagelike-widget/loader.gif" alt="Facebook Pagelike Widget" /></div>';
        echo '<div id="fb-root"></div>
        <div class="fb-page" data-href="' . $fb_url . '" data-width="' . $width . '" data-height="' . $height . '" data-small-header="' . $data_small_header . '" data-adapt-container-width="' . $data_adapt_container_width . '" data-hide-cover="' . $data_hide_cover . '" data-show-facepile="' . $data_show_facepile . '" hide_cta="false" data-tabs="'. $data_tabs .'" data-lazy="'.$data_lazy.'"></div>';
        echo $after_widget; ?>
        <!-- A WordPress plugin developed by Milap Patel -->
    <?php }

    /** @see WP_Widget::update */
    function update( $new_instance, $old_instance ) {
        
        $instance   =   $old_instance;
        $instance   =   array( 'data_small_header' => 'false', 'data_adapt_container_width' => 'true', 'data_hide_cover' => 'false', 'data_show_facepile' => 'false', 'data_tabs' => 'timeline' );
        
        foreach ( $instance as $field => $val ) {
            if ( isset( $new_instance[$field] ) )
                $instance[$field] = 'true';
        }
        
        $instance['title']                          =   strip_tags( $new_instance['title'] );
        $instance['fb_url']                         =   strip_tags( htmlspecialchars($new_instance['fb_url']) );
        $instance['width']                          =   strip_tags( $new_instance['width'] );
        $instance['height']                         =   strip_tags( $new_instance['height'] );
        $instance['data_small_header']              =   strip_tags( $new_instance['data_small_header'] );
        $instance['data_adapt_container_width']     =   strip_tags( $new_instance['data_adapt_container_width'] );
        $instance['data_hide_cover']                =   strip_tags( $new_instance['data_hide_cover'] );
        $instance['data_show_facepile']             =   strip_tags( $new_instance['data_show_facepile'] );
        $instance['select_lng']                     =   strip_tags( $new_instance['select_lng'] );
        $instance['data_tabs']                      =   esc_sql( $new_instance['data_tabs'] );
        $instance['data_lazy']                      =   strip_tags( $new_instance['data_lazy'] );
        
        return $instance;

    }

    /** @see WP_Widget::form */
    function form( $instance ) {
        
        /**
         * Set Default Value for widget form
         */
        $defaults       =   array( 'title' => 'Like Us On Facebook', 'fb_url' => 'https://www.facebook.com/WordPress', 'width' => '300', 'height' => '500', 'data_small_header' => 'false', 'select_lng' => 'en_US', 'data_adapt_container_width' => 'on', 'data_hide_cover' => 'false', 'data_show_facepile' => 'on', 'data_tabs' => 'timeline', 'data_lazy'=> 'false');
        
        $instance       =   wp_parse_args( ( array ) $instance, $defaults );
        $title          =   esc_attr( $instance['title'] );
        $fb_url         =   isset( $instance['fb_url'] ) ? esc_attr( $instance['fb_url'] ) : "http://www.facebook.com/WordPress";
        $width          =   esc_attr( $instance['width'] );
        $height         =   esc_attr( $instance['height'] );
        $data_tabs      =   isset( $instance['data_tabs'] ) ? ( $instance['data_tabs'] ) : "timeline";
        ?>
        
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'facebook-pagelike-widget' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'fb_url' ); ?>"><?php _e( 'Facebook Page Url:', 'facebook-pagelike-widget' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'fb_url' ); ?>" name="<?php echo $this->get_field_name( 'fb_url' ); ?>" type="text" value="<?php echo $fb_url; ?>" />
            <small style="font-size: 0.6em;">
                <?php _e( 'Works with only' ); ?>
                <a href="http://www.facebook.com/help/?faq=174987089221178" target="_blank">
                    <?php _e( 'Valid Facebook Pages!' ); ?>
                </a>
            </small>
        </p>
        <p>
            <?php
                if( $instance ) {
                    $select =   $instance['data_tabs'];
                }
                else {
                    $select =   "timeline";
                }
                if(is_string($select)) {
                    $select = array($select);
                }
            ?>
            <label for="<?php echo $this->get_field_id( 'data_tabs' ); ?>"><?php _e( 'Tabs:', 'facebook-pagelike-widget' ); ?></label>
            <?php

            printf(
                '<select multiple="multiple" name="%s[]" id="%s">',
                $this->get_field_name('data_tabs'),
                $this->get_field_id('data_tabs')
            );
            $tabs = array( 'timeline','events','messages' );
            
            foreach( $tabs as $tab )
            {
                printf(
                    '<option value="%s" class="hot-topic" %s style="margin-bottom:3px;">%s</option>',
                    $tab,
                    in_array( $tab, $select) ? 'selected="selected"' : '',
                    $tab
                );
            }
            echo '</select>';
            ?>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked( $instance['data_hide_cover'], "on" ) ?> id="<?php echo $this->get_field_id( 'data_hide_cover' ); ?>" name="<?php echo $this->get_field_name( 'data_hide_cover' ); ?>" />
            <label for="<?php echo $this->get_field_id( 'data_hide_cover' ); ?>" title="Hide the cover photo in the header"><?php _e( 'Hide Cover Photo', 'facebook-pagelike-widget' ); ?></label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked( $instance['data_show_facepile'], "on" ) ?> id="<?php echo $this->get_field_id( 'data_show_facepile' ); ?>" name="<?php echo $this->get_field_name( 'data_show_facepile' ); ?>" />
            <label for="<?php echo $this->get_field_id( 'data_show_facepile' ); ?>" title="Show profile photos when friends like this"><?php _e( "Show Friend's Faces", 'facebook-pagelike-widget' ); ?></label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked( $instance['data_small_header'], "on" ) ?> id="<?php echo $this->get_field_id( 'data_small_header' ); ?>" name="<?php echo $this->get_field_name( 'data_small_header' ); ?>" />
            <label for="<?php echo $this->get_field_id( 'data_small_header' ); ?>" title="Uses a smaller version of the page header"><?php _e( 'Show Small Header', 'facebook-pagelike-widget' ); ?></label>
        </p>
        <p>
            <input onclick="showWidth();" class="checkbox" type="checkbox" <?php checked( $instance['data_adapt_container_width'], "on" ) ?> id="<?php echo $this->get_field_id( 'data_adapt_container_width' ); ?>" name="<?php echo $this->get_field_name( 'data_adapt_container_width' ); ?>" />
            <label for="<?php echo $this->get_field_id( 'data_adapt_container_width' ); ?>" title="Plugin will try to fit inside the container"><?php _e( 'Adapt To Plugin Container Width', 'facebook-pagelike-widget' ); ?></label>
        </p>
        <p class="width_option <?php echo $instance['data_adapt_container_width'] == 'on' ? 'hideme' : ''; ?>">
            <label for="<?php echo $this->get_field_id( 'width' ); ?>"><?php _e( 'Set Width:', 'facebook-pagelike-widget' ); ?></label>
            <input size="19" id="<?php echo $this->get_field_id( 'width' ); ?>" name="<?php echo $this->get_field_name( 'width' ); ?>" type="text" value="<?php echo $width; ?>" placeholder="Min. 180 to Max. 500" />
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked( $instance['data_lazy'], "on" ) ?> id="<?php echo $this->get_field_id( 'data_lazy' ); ?>" name="<?php echo $this->get_field_name( 'data_lazy' ); ?>" />
            <label for="<?php echo $this->get_field_id( 'data_lazy' ); ?>" title="true means use the browser's lazy-loading mechanism by setting the loading=lazy iframe attribute. The effect is that the browser does not render the plugin if it's not close to the viewport and might never be seen."><?php _e( 'Enable Lazy Loading', 'facebook-pagelike-widget' ); ?></label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'height' ); ?>"><?php _e( 'Set Height:', 'facebook-pagelike-widget' ); ?></label>
            <input size="19" id="<?php echo $this->get_field_id( 'height' ); ?>" name="<?php echo $this->get_field_name( 'height' ); ?>" type="text" value="<?php echo $height; ?>" placeholder="Min. 70" />
        </p>
        
        <?php
        $filename = __DIR__.'/FacebookLocales.json';
        if (ini_get( 'allow_url_fopen') ) {
            if(file_exists( $filename) ) {
                $langs      = file_get_contents( $filename );
                $jsoncont   = json_decode( $langs );
                ?>
                <p>
                    <label for="<?php echo $this->get_field_id( 'select_lng' ); ?>"><?php _e( 'Language:', 'facebook-pagelike-widget' ); ?></label>
                    <select name="<?php echo $this->get_field_name( 'select_lng' ); ?>" id="<?php echo $this->get_field_id( 'select_lng' ); ?>">
                        <?php
                        if ( !empty( $jsoncont ) ) {
                            foreach ( $jsoncont as $languages => $short_name ) { ?>
                                <option value="<?php echo $short_name; ?>"<?php selected( $instance['select_lng'], $short_name ); ?>><?php _e( $languages ); ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                </p>
                <?php
            }
        } else {
            ?>
            <p>Your PHP configuration does not allow to read <a href="<?php echo plugin_dir_url( __FILE__ ).'FacebookLocales.json';?>" target="_blank">this</a> file.
                To unable language option, enable <a href="http://php.net/manual/en/filesystem.configuration.php#ini.allow-url-fopen" target="_blank"><b>allow_url_fopen</b></a> in your server configuration.
            </p>
            <?php
        }
        ?>
        <script type="text/javascript">
            function showWidth() {
                if (jQuery( ".width_option" ).hasClass( 'hideme' ) )
                    jQuery( ".width_option" ).removeClass( 'hideme' );
                else
                    jQuery( ".width_option" ).addClass( 'hideme' );
            }
        </script>
        
        <style type="text/css">.hideme {display: none;}</style>
        <?php
    }
}

add_action( 'widgets_init', function() {
    return register_widget( "facebook_widget" );
});

?>