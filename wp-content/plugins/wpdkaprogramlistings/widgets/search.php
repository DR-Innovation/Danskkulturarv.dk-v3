<?php
/**
 * @package WP DKA Program Listings
 * @version 1.0
 */

/**
 * WordPress Widget that makes it possible to style
 * and display a search tool for program listings using elasticsearch
 */
class WPDKAProgramListingsSearchWidget extends WP_Widget {
	/**
	 * Constructor
	 */
	public function __construct() {
		
		parent::__construct(
			'dka-program-listing-featured-widget',
			__('DKA Search program schedule',WPDKAProgramListings::DOMAIN),
			array( 'description' => __('Search program schedule',WPDKAProgramListings::DOMAIN) )
		);

		$this->fields = array(
			array(
				'title' => __('Search program schedule',WPDKAProgramListings::DOMAIN),
				'description' => __('Search description',WPDKAProgramListings::DOMAIN)
			),
		);

		add_action('admin_enqueue_scripts', array($this, 'upload_scripts'));
        add_action('admin_enqueue_styles', array($this, 'upload_styles'));
	}

	public function upload_scripts() {
		wp_enqueue_script('media-upload');
        wp_enqueue_script('thickbox');
        wp_enqueue_script('upload_media_widget', plugin_dir_url(__FILE__) . '../js/upload-media.js', array('jquery'));
	}

	public function upload_styles() {
		wp_enqueue_style('thickbox');
	}

	/**
	 * GUI for widget content
	 * 
	 * @param  array $args Sidebar arguments
	 * @param  array $instance Widget values from database
	 * @return void 
     */
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );
		$image = $instance['image'];
		echo $args['before_widget'];

        echo '<div class="schedule-free-text-search ">';
        echo '<div class="js-free-text-search-content">';
        echo '<form method="GET" action="' . get_permalink(get_option('wpdkaprogramlistings-page'))  . '">';
        echo '<div class="col-xs-9 col-lg-10 col-sm-9">';
        echo '<div class="input-group">';
        echo '<input type="text" name="' . WPDKAProgramListings::QUERY_KEY_FREETEXT . '" class="form-control programlistings-search-text" placeholder="' . __('Search program schedule', WPDKAProgramListings::DOMAIN) . '" value="' . WPDKAProgramListings::get_programlisting_var(WPDKAProgramListings::QUERY_KEY_FREETEXT, 'esc_attr,trim') . '" data-original-title="" title="" />';
        echo '<div class="input-group-addon hover-info" data-html="true" data-container="body" data-toggle="popover" data-placement="bottom" data-trigger="hover" data-content="';
        WPDKAProgramListings::print_search_info_text();
        echo '"><i class="icon icon-info-sign"></i>';
        echo '</div></div></div>';
        echo '<div class="col-xs-3 col-lg-2 col-sm-3" style="padding-bottom: 5px; padding-left: 0;">';
        echo '<button type="submit" class="btn btn-primary btn-search btn-block" id="searchsubmit">' . __('Search', WPDKAProgramListings::DOMAIN) . '</button>';
        echo '</div></form>';
        echo '</div></div>';

		echo $args['after_widget'];			
	}

	// Widget Backend 
	public function form($instance) {
		$title = '';
		if (isset($instance['title'])) {
			$title = $instance['title'];
		}

		$description = '';
		if (isset($instance['description'])) {
			$description = $instance['description'];
		}

		$image = '';
		if (isset( $instance['image'])) {
			$image = $instance['image'];
		}
	?>
		<p>
			<?php if (isset($image)): ?>
			<img src="<?php echo esc_url( $image ); ?>" style="display: block; max-width: 200px;" />
			<?php endif; ?>
			<input class="upload_image_button button button-primary" type="button" value="<?php isset($image) ? _e('Upload new image', WPDKAProgramListings::DOMAIN) : _e('Upload Image', WPDKAProgramListings::DOMAIN); ?>" />
			<?php if (isset($image)): ?>
			<input class="remove_image_button button button-danger" type="button" value="&times; <?php _e('Remove image', WPDKAProgramListings::DOMAIN); ?>" />
			<?php endif; ?>
            <input name="<?php echo $this->get_field_name( 'image' ); ?>" id="<?php echo $this->get_field_id( 'image' ); ?>" class="widefat" type="hidden" size="36"  value="<?php echo esc_url( $image ); ?>" />
        </p>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:'); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'description' ); ?>"><?php _e('Description:'); ?></label> 
			<textarea class="widefat" id="<?php echo $this->get_field_id( 'description' ); ?>" name="<?php echo $this->get_field_name( 'description' ); ?>"><?php echo esc_attr( $description ); ?></textarea>
		</p>
	<?php 
	}
		
	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$updated_instance = $new_instance;
        return $updated_instance;
	}
}
