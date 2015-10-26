<?php
/**
 * @package WP DKA Program Listings
 * @version 1.0
 */

/**
 * WordPress Widget that makes it possible to style
 * and display a search tool for program listings using elasticsearch
 */
class WPDKAProgramListingsFeaturedWidget extends WP_Widget {
  /**
   * Constructor
   */
  public function __construct() {

    parent::__construct(
      'dka-program-listing-featured-widget',
      __('DKA Program Listings',WPDKAProgramListings::DOMAIN),
      array( 'description' => __('Search for program listings',WPDKAProgramListings::DOMAIN) )
    );

    $this->fields = array(
      array(
        'title' => __('Program Listings',WPDKAProgramListings::DOMAIN),
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
    // if (empty(get_option('wpdkaprogramlistings-page')) || !is_page(get_option('wpdkaprogramlistings-page'))) {
    // 	exit();
    // }

    $title = apply_filters( 'widget_title', $instance['title'] );
    $image = $instance['image'];
    echo $args['before_widget'];

    echo '<div class="programlisting-search">';
    // echo $args['before_title'];
    // echo '<img src="' . plugins_url( '../images/logo.png' , __FILE__ ) . '" alt="' . $title . '" />';
    echo '<h3 class="widget-title">TV og radio gennem tiden</h3>';
    // echo $args['after_title'];
    echo '<p>' . $instance['description'] . '</p>';
    echo '<form method="GET" action="' . get_permalink(get_option('wpdkaprogramlistings-page')) . '" class="row">';
    echo '<div class="col-xs-12 col-sm-4"><div class="programlisting-year"><select name="' . WPDKAProgramListings::QUERY_KEY_YEAR . '"><option value="" disabled selected>' . __('Year',WPDKAProgramListings::DOMAIN) . '</option>';
    for ($y = WPDKAProgramListings::START_YEAR; $y <= WPDKAProgramListings::END_YEAR; $y++) {
      echo '<option value="' . $y . '">' . $y . '</option>';
    }
    echo '</select></div></div>';

    echo '<div class="col-xs-12 col-sm-4"><div class="programlisting-month"><select name="' . WPDKAProgramListings::QUERY_KEY_MONTH . '"><option value="" disabled selected>' . __('Month',WPDKAProgramListings::DOMAIN) . '</option>';
    for ($m = 1; $m <= 12; $m++) {
      echo '<option value="' . $m . '">' . ucfirst(__(date('F', mktime(0,0,0,$m,1)))) . '</option>';
    }
    echo '</select></div></div>';
    echo '<div class="col-xs-12 col-sm-4"><div class="programlisting-day"><select name="' . WPDKAProgramListings::QUERY_KEY_DAY . '"><option value="" disabled selected>' . __('Day',WPDKAProgramListings::DOMAIN) . '</option>';
    for ($d = 1; $d <= 31; $d++) {
      echo '<option value="' . $d . '">' . $d . '</option>';
    }
    echo '</select></div></div>';
    echo '<div class="col-xs-12 col-sm-12"><button type="submit" class="btn btn-primary btn-block">' . __('Search program schedule', WPDKAProgramListings::DOMAIN) . '</button></div>';
    echo '</form>';
    echo '<div class="widget-promo-image">
            <a href="http://www.danskkulturarv.dk/udstilling/postkort-og-plakater/"><img src="' . plugins_url( '../images/promo.jpg' , __FILE__ ) . '" alt="'. __('Poster and postcard promo image', WPDKAProgramListings::DOMAIN) .'" /></a>
          </div>';

    echo '</div>';

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
