<?php
/**
 * @package WordPress
 * @subpackage DKA
 */

require('wp-bootstrap-navwalker/wp_bootstrap_navwalker.php');

//Diasable Core Updates
add_filter( 'pre_site_transient_update_core', function($a) { return null; } );
//wp_clear_scheduled_hook( 'wp_version_check' );


/**
 * Make post class compatible with Bootstrap
 */
add_filter('post_class', function($classes) {
  if(is_singular(array('post','page')) && is_active_sidebar('sidebar-1')) {
    $classes[] = 'col-lg-9';
  } else {
    $classes[] = 'col-xs-12';
  }

  return $classes;
});

/**
 * Gallery shortcode overwrite for flexslider
 * Uses code from WordPress Core
 * @author Joachim Jensen <jv@intox.dk>
 * @param  string          $output
 * @param  array|string    $attr
 * @see    wp-includes/media.php - gallery_shortcode()
 * @return string
 */
function dka_gallery_markup($output, $attr) {

  //Only continue on type flexslider
  if(!(isset($attr['type']) && $attr['type'] == 'flexslider'))
    return $output;

  $post = get_post();

  if ( ! empty( $attr['ids'] ) ) {
    // 'ids' is explicitly ordered, unless you specify otherwise.
    if ( empty( $attr['orderby'] ) )
      $attr['orderby'] = 'post__in';
    $attr['include'] = $attr['ids'];
  }

  // We're trusting author input, so let's at least make sure it looks like a valid orderby statement
  if ( isset( $attr['orderby'] ) ) {
    $attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
    if ( !$attr['orderby'] )
      unset( $attr['orderby'] );
  }

  extract(shortcode_atts(array(
    'order'      => 'ASC',
    'orderby'    => 'menu_order ID',
    'id'         => $post ? $post->ID : 0,
    'itemtag'    => 'li',
    'icontag'    => 'div',
    'captiontag' => 'div',
    'columns'    => 0,
    'size'       => 'full',
    'include'    => '',
    'exclude'    => '',
    'link'       => ''
  ), $attr, 'gallery'));

  $id = intval($id);
  if ( 'RAND' == $order )
    $orderby = 'none';

  if ( !empty($include) ) {
    $_attachments = get_posts( array('include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );

    $attachments = array();
    foreach ( $_attachments as $key => $val ) {
      $attachments[$val->ID] = $_attachments[$key];
    }
  } elseif ( !empty($exclude) ) {
    $attachments = get_children( array('post_parent' => $id, 'exclude' => $exclude, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
  } else {
    $attachments = get_children( array('post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
  }

  if ( empty($attachments) )
    return '';

  if ( is_feed() ) {
    $output = "\n";
    foreach ( $attachments as $att_id => $attachment )
      $output .= wp_get_attachment_link($att_id, $size, true) . "\n";
    return $output;
  }

  $itemtag = tag_escape($itemtag);
  $captiontag = tag_escape($captiontag);
  $icontag = tag_escape($icontag);
  $valid_tags = wp_kses_allowed_html( 'post' );
  if ( ! isset( $valid_tags[ $itemtag ] ) )
    $itemtag = 'dl';
  if ( ! isset( $valid_tags[ $captiontag ] ) )
    $captiontag = 'dd';
  if ( ! isset( $valid_tags[ $icontag ] ) )
    $icontag = 'dt';

  $columns = intval($columns);

  $size_class = sanitize_html_class( $size );
  $gallery_div = "<div class='flexslider gallery galleryid-{$id} gallery-size-{$size_class}'><ul class='slides'>";
  $output = $gallery_div;
  $i = 0;
  foreach ( $attachments as $id => $attachment ) {
    if ( ! empty( $link ) && 'file' === $link )
      $image_output = wp_get_attachment_link( $id, $size, false, false );
    elseif ( ! empty( $link ) && 'none' === $link )
      $image_output = wp_get_attachment_image( $id, $size, false );
    else
      $image_output = wp_get_attachment_link( $id, $size, true, false );

    $image_meta  = wp_get_attachment_metadata( $id );

    $orientation = '';
    if ( isset( $image_meta['height'], $image_meta['width'] ) )
      $orientation = ( $image_meta['height'] > $image_meta['width'] ) ? 'portrait' : 'landscape';

    $output .= "<{$itemtag} class='gallery-item'>";
    $output .= $image_output;
    // $output .= "
    // 	<{$icontag} class='gallery-icon {$orientation}'>
    // 		$image_output
    // 	</{$icontag}>";
    // if ( $captiontag && trim($attachment->post_excerpt) ) {
    // 	$output .= "
    // 		<{$captiontag} class='wp-caption-text gallery-caption'>
    // 		" . wptexturize($attachment->post_excerpt) . "
    // 		</{$captiontag}>";
    // }
    $output .= "</{$itemtag}>";
  }

  $output .= "</ul></div>\n";

  add_action( 'wp_footer', function() {
    wp_enqueue_script( 'flexslider' );
  });

  return $output;
}

add_filter( 'post_gallery', 'dka_gallery_markup', 11, 2 );

function cookie_policy_popup() {
  if (!is_user_logged_in() && !isset($_COOKIE['cookie_policy_seen'])):
    ?>

      <div class="footer_cookie_policy row">
        <div class="col-xs-12 col-sm-offset-2 col-sm-8">
          <h4><img class="pull-left" src="http://www.dr.dk/assets/img/cookie-icon.png">Cookie- og privatlivspolitik på <?php bloginfo( 'name' ); ?>.</h4>
          <p>
            Vi bruger cookies for at forbedre din oplevelse, vurdere brugen af de enkelte elementer på <?php bloginfo( 'name' ); ?> og til at støtte markedsføringen af vores services. Ved at klikke videre på <?php bloginfo( 'name' ); ?> accepterer du vores brug af cookies.
            <a class="dr-link" href="http://www.dr.dk/service/privatlivspolitik/">Læs mere</a>
          </p>
          <button type="button" class="exit exitlg btn btn-primary hidden-xs">OK</button>
        </div>
        <div class="col-xs-12 visible-xs">
          <button type="button" class="exit btn btn-primary btn-block">OK</button>
        </div>
      </div>
    <?php
  endif;
}
add_action('wp_footer', 'cookie_policy_popup');

/**
 * Add responsive with 2 columns pr. row for page builder
 */
function col2_style_page_builder($styles) {
  $styles['2col_res'] = __('2 columns responsive', 'dka');
  $styles['lgscreen'] = __('Only larger screens', 'dka');
  $styles['smscreen'] = __('Only smaller screens', 'dka');
  return $styles;
}
add_filter('siteorigin_panels_row_styles', 'col2_style_page_builder');

/**
 * Paragraphs in editor
 */
add_filter('tiny_mce_before_init', function($arr) {
  $arr['theme_advanced_blockformats'] = 'p,h2,h3,h4,h5,h6,address,pre';
  $style_formats = array(
    array(
      'title' => __('Text-box', 'dka'),
      'block' => 'div',
      'classes' => 'colorbox',
      'wrapper' => true
    ),
    array(
      'title' => __('Image-box', 'dka'),
      'block' => 'div',
      'classes' => 'imgbox',
      'wrapper' => true
    ),
    array(
      'title' => __('Box', 'dka'),
      'block' => 'div',
      'classes' => 'frontbox',
      'wrapper' => true
    )
  );
  $arr['style_formats'] = json_encode( $style_formats );
  return $arr;
});

/**
 * Add "Styles" drop-down
 */
add_filter( 'mce_buttons_2', 'tuts_mce_editor_buttons' );
function tuts_mce_editor_buttons( $buttons ) {
  array_unshift( $buttons, 'styleselect' );
  return $buttons;
}


function dka_setup() {

  add_editor_style();

  //add_theme_support( 'automatic-feed-links' );

  //add_theme_support( 'post-formats', array( 'aside', 'image', 'link', 'quote', 'status' ) );

  register_nav_menu( 'primary','Primary');
  register_nav_menu( 'secondary','Secondary');

  remove_action( 'wp_head','rsd_link',10);
  remove_action( 'wp_head','wlwmanifest_link',10);

  add_theme_support( 'post-thumbnails' );
  //set_post_thumbnail_size( 624, 9999 ); // Unlimited height, soft crop
  add_filter('wp_mail_from', function($old) { return get_bloginfo('admin_email'); });
  add_filter('wp_mail_from_name', function($old) { return get_bloginfo('name'); });

  load_theme_textdomain('dka', get_template_directory() . '/lang');

}
add_action( 'after_setup_theme', 'dka_setup' );

// Dequeue all styles loaded in DKA.
function dka_dequeue_all_styles() {
  wp_dequeue_style('dka-style');
  wp_dequeue_style('dka-collections-style');
}
add_action('dequeue_all_styles', 'dka_dequeue_all_styles');

function dka_scripts_styles() {

  wp_register_style( 'font-awesome', '//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.min.css' );
  //wp_register_style('asap', 'http://fonts.googleapis.com/css?family=Asap:400,700,400italic');
  wp_register_style( 'dka-style', get_template_directory_uri() . '/css/styles.css', array('font-awesome'/*,'asap'*/) );
  wp_register_style( 'dka-embed-style', get_template_directory_uri() . '/css/embed-style.css');

  wp_enqueue_style( 'dka-style' );

  //Use Google CDN instead
  wp_deregister_script('jquery');
  wp_register_script('jquery', '//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js', false, '1.11.0', false);

  wp_register_script( 'html5shiv', get_template_directory_uri() . '/js/html5shiv.js', array(), ' 1.0', true );
  wp_register_script( 'respond-js', get_template_directory_uri() . '/js/respond.min.js', array(), ' 1.0', true );
  wp_register_script( 'jwplayer', get_template_directory_uri() . '/lib/jwplayer/jwplayer.js', array('jquery'), '1', true );
  wp_register_script( 'flexslider', get_template_directory_uri() . '/js/jquery.flexslider-min.js', array('jquery'), '2.1', true );

  wp_enqueue_script('html5shiv' );
  wp_enqueue_script('respond-js' );

  $bootstrap_scripts = array(
    'transition', //modal
    //'alert',
    'button',
    //'carousel',
    'collapse', //search
    'dropdown', //menu
    'modal', //used by collection and tags
    //'scrollspy',
    //'tab',
    'tooltip', // Used by the /api page.
    'popover', // Used by the /api page.
    //'affix'
  );
  foreach($bootstrap_scripts as $bootscript) {
    wp_register_script( $bootscript, get_template_directory_uri() . '/js/bootstrap/'.$bootscript.'.js', array('jquery'), '3.0.0', true );
    wp_enqueue_script( $bootscript );
  }

  wp_enqueue_script( 'custom-functions', get_template_directory_uri() . '/js/custom-functions.js', array('jquery'), '1', true );
  wp_localize_script( 'custom-functions', 'dka', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'query_key_freetext' => WPChaosSearch::QUERY_KEY_FREETEXT ) );

}
add_action( 'wp_enqueue_scripts', 'dka_scripts_styles' );

add_action('wp_head',function() {
  if(is_user_logged_in()) {
    echo '<style>.navbar-fixed-top {margin-top:32px;} @media screen and ( max-width: 782px ) { .navbar-fixed-top {margin-top:46px;}}</style>';
  }
});

function dka_widgets_init() {

  register_sidebar( array(
    'id' => 'sidebar-1',
    'name' => 'Sidebar',
    'before_widget' => '<aside id="%1$s" class="widget %2$s">',
    'after_widget' => '</aside>',
    'before_title' => '<h3 class="widget-title">',
    'after_title' => '</h3>',
  ) );

  register_sidebar( array(
    'id' => 'sidebar-2',
    'name' => 'Top',
    'before_widget' => '<div id="%1$s" class="widget %2$s">',
    'after_widget' => '</div>',
    'before_title' => '<h3 class="widget-title">',
    'after_title' => '</h3>',
  ) );

  //Remove some widgets not needed that clutter the screen
  unregister_widget('WP_Widget_Pages');
  unregister_widget('WP_Widget_Calendar');
  unregister_widget('WP_Widget_Archives');
  unregister_widget('WP_Widget_Links');
  unregister_widget('WP_Widget_Meta');
  //unregister_widget('WP_Widget_Search');
  //unregister_widget('WP_Widget_Text');
  unregister_widget('WP_Widget_Categories');
  unregister_widget('WP_Widget_Recent_Posts');
  unregister_widget('WP_Widget_Recent_Comments');
  unregister_widget('WP_Widget_RSS');
  unregister_widget('WP_Widget_Tag_Cloud');
  //unregister_widget('WP_Nav_Menu_Widget');
}

add_action( 'widgets_init', 'dka_widgets_init' );

function dka_content_nav( $html_id ) {
  global $wp_query;

  $html_id = esc_attr( $html_id );

  if ( $wp_query->max_num_pages > 1 ) : ?>
    <nav id="<?php echo $html_id; ?>" class="navigation" role="navigation">
      <h3 class="assistive-text"><?php _e( 'Post navigation', 'dka' ); ?></h3>
      <div class="nav-previous alignleft"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'dka' ) ); ?></div>
      <div class="nav-next alignright"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'dka' ) ); ?></div>
    </nav><!-- #<?php echo $html_id; ?> .navigation -->
  <?php endif;
}

if ( ! function_exists( 'dka_entry_meta' ) ) :
/**
 * Prints HTML with meta information for current post: categories, tags, permalink, author, and date.
 *
 * Create your own dka_entry_meta() to override in a child theme.
 *
 * @since Twenty Twelve 1.0
 */
function dka_entry_meta() {
  // Translators: used between list items, there is a space after the comma.
  $categories_list = get_the_category_list( __( ', ', 'dka' ) );

  // Translators: used between list items, there is a space after the comma.
  $tag_list = get_the_tag_list( '', __( ', ', 'dka' ) );

  $date = sprintf( '<a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s">%4$s</time></a>',
    esc_url( get_permalink() ),
    esc_attr( get_the_time() ),
    esc_attr( get_the_date( 'c' ) ),
    esc_html( get_the_date() )
  );

  $author = sprintf( '<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s" rel="author">%3$s</a></span>',
    esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
    esc_attr( sprintf( __( 'View all posts by %s', 'dka' ), get_the_author() ) ),
    get_the_author()
  );

  // Translators: 1 is category, 2 is tag, 3 is the date and 4 is the author's name.
  if ( $tag_list ) {
    $utility_text = __( 'This entry was posted in %1$s and tagged %2$s on %3$s<span class="by-author"> by %4$s</span>.', 'dka' );
  } elseif ( $categories_list ) {
    $utility_text = __( 'This entry was posted in %1$s on %3$s<span class="by-author"> by %4$s</span>.', 'dka' );
  } else {
    $utility_text = __( 'This entry was posted on %3$s<span class="by-author"> by %4$s</span>.', 'dka' );
  }

  printf(
    $utility_text,
    $categories_list,
    $tag_list,
    $date,
    $author
  );
}
endif;

function dka_wp_title( $title, $sep ) {
  global $paged, $page;

  if ( is_feed() )
    return $title;

  // Add the site name.
  $title .= get_bloginfo( 'name' );

  // Add the site description for the home/front page.
  $site_description = get_bloginfo( 'description', 'display' );
  if ( $site_description && ( is_home() || is_front_page() ) )
    $title = "$title $sep $site_description";

  //Add title from CHAOS object
  if(WPChaosClient::get_object()) {
    $title = WPChaosClient::get_object()->title. " $sep $title";
  }

  // Add a page number if necessary.
  if ( $paged >= 2 || $page >= 2 )
    $title = "$title $sep " . sprintf( __( 'Page %s', 'dka' ), max( $paged, $page ) );

  return $title;
}
add_filter( 'wp_title', 'dka_wp_title', 10, 2 );


function dka_wp_head() {

  $metadatas = array();

  $metadatas['og:site_name'] = array(
    'property' => 'og:site_name',
    'content' => get_bloginfo('title')
  );
  $metadatas['og:locale'] = array(
    'property' => 'og:locale',
    'content' => get_locale()
  );
  $metadatas['og:type'] = array(
    'property' => 'og:type',
    'content' => 'website'
  );
  $metadatas['twitter:site'] = array(
    'name' => 'twitter:site',
    'content' => '@danskkulturarv'
  );

  if(is_singular()) {
    global $post;
    setup_postdata($post);

    $excerpt = dka_custom_excerpt(20);

    $metadatas['description'] = array(
      'name' => 'description',
      'content' => $excerpt
    );
    $metadatas['og:title'] = array(
      'property' => 'og:title',
      'content' => get_the_title()
    );
    $metadatas['og:description'] = array(
      'property' => 'og:description',
      'content' => $excerpt
    );

    wp_reset_postdata();
  }

  if(WPChaosClient::get_object()) {
    $metadatas = array_merge($metadatas,WPChaosClient::get_object()->og_tags);
  }

  $metadatas = apply_filters('wpchaos-head-meta',$metadatas);
  ksort($metadatas);
  //Loop over metadata
  foreach($metadatas as $metadata) {
    $fields = array();
    //Loop over each metadata attribute
    foreach($metadata as $key => $value) {
      $fields[] = $key.'="'.esc_attr(strip_tags($value)).'"';
    }
    //Insert attributes in meta node and print
    echo "<meta ".implode(" ", $fields).">\n";
  }

}

function dka_gemius_tracking() {
  echo <<<HTML
  <!-- (C)2000-2013 Gemius SA - gemiusAudience / danskkulturarv.dk / Main Page -->
  <script type="text/javascript">
  <!--//--><![CDATA[//><!--
  var pp_gemius_identifier = 'zIg70.vylD2c3AOnUAlzzZbxXtcUzeL2JV319kvG4RL.w7';
  // lines below shouldn't be edited
  function gemius_pending(i) { window[i] = window[i] || function() {var x = window[i+'_pdata'] = window[i+'_pdata'] || []; x[x.length]=arguments;};};
  gemius_pending('gemius_hit'); gemius_pending('gemius_event'); gemius_pending('pp_gemius_hit'); gemius_pending('pp_gemius_event');
  (function(d,t) {try {var gt=d.createElement(t),s=d.getElementsByTagName(t)[0]; gt.setAttribute('async','async'); gt.setAttribute('defer','defer');
   gt.src='http://gadk.hit.gemius.pl/xgemius.js'; s.parentNode.insertBefore(gt,s);} catch (e) {}})(document,'script');
  //--><!]]>
  </script>
HTML;

}

//Only track in production
if(!WP_DEBUG) {
  add_action('wp_head','dka_gemius_tracking',98);
}

add_action('wp_head','dka_wp_head',99);

function dka_custom_excerpt($new_length = 30) {
  add_filter('excerpt_length', create_function('$new_length',"return $new_length;"), 999);
  $output = get_the_excerpt();
  return $output;
}

function dka_word_limit($string, $length = 30, $ellipsis = " [...]") {

  $words = explode(' ', $string);
  if (count($words) > $length)
    $string = implode(' ', array_slice($words, 0, $length)) . $ellipsis;
  return $string;
}


function dka_social_share($args = array()) {
  // Grab args or defaults
  $args = wp_parse_args($args, array(
    'link' => '',
    'title' => '',
  ));
  extract($args, EXTR_SKIP);

  echo '<a class="social-share icon-facebook-sign" target="_blank" rel="nofollow" href="https://www.facebook.com/sharer.php?u='.$link.'" title="'.sprintf(__('Share on %s','dka'),'Facebook').'"><i class=""></i></a>'."\n";
  echo '<a class="social-share icon-twitter-sign" target="_blank" rel="nofollow" href="https://twitter.com/home?status='.$link.'+%23kulturarv" title="'.sprintf(__('Share on %s','dka'),'Twitter').'"></a>'."\n";
  echo '<a class="social-share icon-google-plus-sign" target="_blank" rel="nofollow" href="https://plus.google.com/share?url='.$link.'" title="'.sprintf(__('Share on %s','dka'),'Google Plus').'"></a>'."\n";
  echo '<a class="social-share icon-envelope" target="_blank" rel="nofollow" href="mailto:?subject='.rawurlencode(get_bloginfo('title')).'&amp;body='.$link.'" title="'.__('Send as e-mail','dka').'"></a>'."\n";

}

function echo_chaos($chaosObjectOne)
{
    echo WPChaosClient::get_object()->$chaosObjectOne;
}


function get_chaos($chaosObjectTwo)
{
    return WPChaosClient::get_object()->$chaosObjectTwo;
}


//eol
