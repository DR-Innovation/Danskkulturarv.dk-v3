<?php
/**
 * Plugin Name: WPDKA Frontpage Featured Widget
 **/

class WPDKAFrontpageFeaturedWidget extends WP_Widget {
  function __construct() {
    parent::__construct(
      'dka_frontpage_featured_widget',
      __('DKA Frontpage Featured', 'wpdka'),
      array( 'description' => __( 'Featured assets for the frontpage', 'wpdka' ), )
    );

    $this->fields = array(
      array(
        'query' => 'm5906a41b-feae-48db-bfb7-714b3e105396_da_all:"JULETRÆET"',
        'max_results' => '10'
      )
    );
  }

  protected function get_featured($query, $max) {
    $response = WPChaosClient::instance()->Object()->Get(
      $query, // Search query
      null,   // Sort
      null,
      0,      // pageIndex
      $max,   // pageSize
      true,   // includeMetadata
      true,  // includeFiles
      false   // includeObjectRelations
    );

    $return = array();
    $items = WPChaosObject::parseResponse($response);
    foreach($items as $item) {
      $return[$item->GUID] = $item;
    }

    return $return;

  }

  // Creating widget front-end
  // This is where the action happens
  public function widget( $args, $instance ) {
    echo $args['before_widget'];
    $query = trim($instance['query']);
    $max_results = intval(trim($instance['max_results']));
    $result = $this->get_featured($query, $max_results);
    echo '<div data-columns>';
    foreach( $result as $item ) {
      $thumbnail = ($item->thumbnail ? '<img class="column-item__img" src="'.$item->thumbnail.'" />' : '');
      $linkUrl = '';
      echo '<a class="column-item" href="' . $linkUrl . '">';
      echo $thumbnail;
      echo '<h2 class="column-item__title">' . $item->title . '</h2>';
      echo '</a>';
    }
    echo '</div>';

    echo $args['after_widget'];
  }

  // Widget Backend
  public function form( $instance ) {
    $query = '';
    if ( isset( $instance[ 'query' ] ) ) {
      $query = $instance[ 'query' ];
    }

    $max_results = '';
    if ( isset( $instance[ 'max_results' ] ) ) {
      $max_results = $instance[ 'max_results' ];
    }
    // Widget admin form
      ?>
      <p>
        <label for="<?php echo $this->get_field_id( 'query' ); ?>"><?php _e('Query:'); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'query' ); ?>" name="<?php echo $this->get_field_name( 'query' ); ?>" type="text" value="<?php echo esc_attr( $query ); ?>" />
      </p>
      <p>
        <label for="<?php echo $this->get_field_id( 'max_results' ); ?>"><?php _e('Max results:'); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'max_results' ); ?>" name="<?php echo $this->get_field_name( 'max_results' ); ?>" type="text" value="<?php echo esc_attr( $max_results ); ?>" />
      </p>
    <?php
  }

  // Updating widget replacing old instances with new
  public function update( $new_instance, $old_instance ) {
    $updated_instance = $new_instance;
    return $updated_instance;
  }
}
