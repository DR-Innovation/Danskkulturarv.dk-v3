<?php
/*
Plugin Name: WP DKA Program Listings
Plugin URI:
Description: Program listings
Version: 1.0
Author: Mads Lundt
Author URI:
License:
*/
class WPDKAProgramListings
{
  const DOMAIN = 'wpdkaprogramlistings';
  const ES_INDEX = 'programoversigter-updated';
  const ES_TYPE = 'programoversigt';
  const ES_URL = 'files.danskkulturarv.dk:80/api'; // previously: 54.72.221.2:80/api (404)
  const ES_UUID = 'universally-unique-identifier';
  const START_YEAR = 1925;
  const END_YEAR = 1984;
  const DATE_FORMAT = 'd-m-Y';
  const FLUSH_REWRITE_RULES_OPTION_KEY = 'wpprogramlisting-flush-rewrite-rules';
  const QUERY_KEY_FREETEXT = 'pl-text';
  const QUERY_KEY_DAY = 'pl-day';
  const QUERY_KEY_MONTH = 'pl-month';
  const QUERY_KEY_YEAR = 'pl-year'; // day, month, year are reserved to Wordpress
  const QUERY_KEY_PAGE = 'side';
  const QUERY_DEFAULT_POST_SEPERATOR = '/';
  const QUERY_PREFIX_CHAR = '/';

  const DEFAULT_PAGE_COUNT = 50;

  public static $search_results;
  public static $search_total;
  public static $page_size = self::DEFAULT_PAGE_COUNT;
  /**
   * Constructor
   */
  public function __construct()
  {
    $this->load_dependencies();
    if (is_admin()) {
      add_filter('wpchaos-config', array(
        &$this,
        'add_chaos_settings'
      ));
      add_action('programlisting-settings-updated', array(
        'WPDKAProgramListings',
        'flush_rewrite_rules_soon'
      ));
    }
    add_action('plugins_loaded', array(
      &$this,
      'load_textdomain'
    ));
    add_filter('widgets_init', array(
      &$this,
      'register_widgets'
    ));
    add_action('template_redirect', array(
      &$this,
      'get_programlisting_page'
    ));
    add_action('wp_enqueue_scripts', array(
      &$this,
      'loadJsCss'
    ));
    self::register_search_query_variable(
      1,
      self::QUERY_KEY_YEAR,
      '\d{4}',
      true,
      null,
      ''
    );
    self::register_search_query_variable(
      2,
      self::QUERY_KEY_MONTH,
      '\d{1,2}',
      true,
      null,
      ''
    );
    self::register_search_query_variable(
      3,
      self::QUERY_KEY_DAY,
      '\d{1,2}',
      true,
      null,
      ''
    );
    self::register_search_query_variable(
      4,
      self::QUERY_KEY_FREETEXT,
      '[^/&]+?',
      false,
      null,
      ''
    );
    self::register_search_query_variable(
      5,
      self::QUERY_KEY_PAGE,
      '\d+?',
      true,
      null,
      '1',
      '-'
    );

    self::$page_size = self::DEFAULT_PAGE_COUNT;

    add_action('init', array(
      'WPDKAProgramListings',
      'handle_rewrite_rules'
    ));
  }
  /**
   * Load necessary CSS and JS to visualize program listings on the site.
   */
  public function loadJsCss()
  {
    wp_enqueue_style('dka-programlisting-style', plugins_url('css/style.css', __FILE__));
    wp_enqueue_script('dka-programlisting-script', plugins_url('js/functions.js', __FILE__), array(
      'jquery'
    ));
  }
  /**
   * Load file dependencies
   * @return void
   */
  private function load_dependencies()
  {
    require_once('widgets/featured.php');
    require_once('widgets/search.php');
    require_once('pdfjs/pdfjs-viewer.php');
  }
  public function load_textdomain()
  {
    load_plugin_textdomain(self::DOMAIN, false, dirname(plugin_basename(__FILE__)) . '/lang/');
  }
  /**
   * Register widgets in WordPress
   * @return  void
   */
  public function register_widgets()
  {
    register_widget('WPDKAProgramListingsFeaturedWidget');
    register_widget('WPDKAProgramListingsSearchWidget');
  }
  public static function flush_rewrite_rules_soon()
  {
    update_option(self::FLUSH_REWRITE_RULES_OPTION_KEY, true);
  }
  /**
   * Flush rewrite rules hard
   * @return void
   */
  public static function handle_rewrite_rules()
  {
    self::add_rewrite_tags();
    self::add_rewrite_rules();
    if (get_option(self::FLUSH_REWRITE_RULES_OPTION_KEY)) {
      delete_option(self::FLUSH_REWRITE_RULES_OPTION_KEY);
      if (WP_DEBUG) {
        add_action('admin_notices', function()
        {
          echo '<div class="updated"><p><strong>' . __('WordPress program listing', self::DOMAIN) . '</strong> ' . __('Rewrite rules flushed ..', self::DOMAIN) . '</p></div>';
        }, 10);
      }
      flush_rewrite_rules();
    }
  }
  public static function install()
  {
    self::flush_rewrite_rules_soon();
  }
  public static function uninstall()
  {
    flush_rewrite_rules();
  }
  public function add_chaos_settings($settings)
  {
    $pages = array();
    foreach (get_pages() as $page) {
      $pages[$page->ID] = $page->post_title;
    }
    $new_settings = array(
      array(
        /*Sections*/
        'name' => 'wpdkaprogramlistings',
        'title' => __('Program Listings', self::DOMAIN),
        'fields' => array(
          /*Section fields*/
          array(
            'name' => 'wpdkaprogramlistings-page',
            'title' => __('Page for program listing results', self::DOMAIN),
            'type' => 'select',
            'list' => $pages,
            'precond' => array(
              array(
                'cond' => (get_option('permalink_structure') != ''),
                'message' => __('Permalinks must be enabled for program listings search to work properly', self::DOMAIN)
              )
            )
          )
        )
      )
    );
    return array_merge($settings, $new_settings);
  }
  /**
   * Get search parameters
   * @return array
   */
  public static function get_programlisting_vars($urldecode = true)
  {
    global $wp_query;
    $variables = array();
    foreach (self::$search_query_variables as $variable) {
      if (array_key_exists($variable['key'], $wp_query->query_vars)) {
        $value = $wp_query->query_vars[$variable['key']];
        if (gettype($value) == 'string') {
          if ($urldecode) {
            $value = urldecode($value);
          }
          // Wordpress is replacing this for us .. Thanks - but no thanks.
          $value = str_replace("\\\"", "\"", $value); // Replace \" with "
          $value = str_replace("\\'", "\'", $value); // Replace \' with '
          if (isset($variable['multivalue-seperator'])) {
            if ($value == '') {
              $value = array();
            } else {
              $value = explode($variable['multivalue-seperator'], $value);
            }
          }
        }
        //echo $variable['key']. ": '$value'\n";
        $variables[$variable['key']] = $value;
      }
      if ($variable['default_value'] !== null && empty($variables[$variable['key']])) {
        $variables[$variable['key']] = $variable['default_value'];
      }
    }
    return $variables;
  }
  public static function get_programlisting_var($query_key, $escape = false, $urldecode = true, $default = '')
  {
    $query_vars = self::get_programlisting_vars($urldecode);
    if (array_key_exists($query_key, $query_vars)) {
      if ($escape !== false) {
        $escape = explode(',', $escape);
        $result = $query_vars[$query_key];
        foreach ($escape as $e) {
          if (function_exists($e)) {
            $result = $e($result);
          } else {
            throw new InvalidArgumentException('The $escape argument must be false or a 1-argument function.');
          }
        }
        return $result;
      } else {
        return $query_vars[$query_key];
      }
    } else {
      return $default;
    }
  }
  /**
   * Get (and print) template for a program listing page
   * @return void
   */
  public function get_programlisting_page()
  {
    //Include template for program listing results
    if (get_option('wpdkaprogramlistings-page') && is_page(get_option('wpdkaprogramlistings-page'))) {
      //Change GET params to nice url
      $this->programlisting_query_prettify();
      $this->generate_programlisting_results();

      //set title and meta
      global $wp_query;
      $day   = self::get_programlisting_var(self::QUERY_KEY_DAY, 'esc_html');
      $month = self::get_programlisting_var(self::QUERY_KEY_MONTH, 'esc_html');
      $year  = self::get_programlisting_var(self::QUERY_KEY_YEAR, 'esc_html');
      $date  = '';
      $date .= $day ? $day . '/' : '';
      $date .= $month ? $month . '/' : '';
      $date .= $year ? $year : '';
      if (!empty($date)) {
        $wp_query->queried_object->post_title = sprintf(__('%s program listing %s', self::DOMAIN), get_bloginfo('title'), $date);
      } else {
        $wp_query->queried_object->post_title = sprintf(__('%s program listing search results %s', self::DOMAIN), get_bloginfo('title'), self::get_programlisting_var(self::QUERY_KEY_FREETEXT));
      }
      add_filter('wpchaos-head-meta', function($metadatas) use ($wp_query)
      {
        $metadatas['og:title']['content'] = $wp_query->queried_object->post_title;
        return $metadatas;
      });
      //Remove meta and add a dynamic ones for better seo
      remove_action('wp_head', 'rel_canonical');
      remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
      //Look in theme dir and include if found
      $include = locate_template('templates/programlisting-search-results.php', false);
      if ($include == "") {
        //Include from plugin template
        $include = plugin_dir_path(__FILE__) . "/templates/programlisting-search-results.php";
      }
      $year  = intval($year);
      $month = intval($month);
      $day   = intval($day);
      require($include);
      exit();
    }
  }
  public static $search_query_variables = array();
  public static function register_search_query_variable($position, $key, $regexp,
    $prefix_key = false, $multivalue_seperator = null, $default_value = null,
    $post_seperator = self::QUERY_DEFAULT_POST_SEPERATOR)
  {
    self::$search_query_variables[$position] = array(
      'key' => $key,
      'regexp' => $regexp,
      'prefix-key' => $prefix_key,
      'multivalue-seperator' => $multivalue_seperator,
      'default_value' => $default_value,
      'post-seperator' => $post_seperator
    );
    ksort(self::$search_query_variables);
  }
  /**
   * Add rewrite tags to WordPress installation
   */
  public static function add_rewrite_tags()
  {
    foreach (self::$search_query_variables as $variable) {
      // If prefix-key is set - the
      if (isset($variable['prefix-key'])) {
        add_rewrite_tag('%' . $variable['key'] . '%', $variable['key'] . self::QUERY_PREFIX_CHAR . '(' . $variable['regexp'] . ')');
      } else {
        add_rewrite_tag('%' . $variable['key'] . '%', '(' . $variable['regexp'] . ')');
      }
    }
  }
  /**
   * Add rewrite rules to WordPress installation
   */
  public static function add_rewrite_rules()
  {
    if (get_option('wpdkaprogramlistings-page')) {
      $searchPageID   = intval(get_option('wpdkaprogramlistings-page'));
      $searchPageName = get_page_uri($searchPageID);
      $regex          = $searchPageName . '/';
      foreach (self::$search_query_variables as $variable) {
        // An optional non-capturing group wrapped around the $regexp.
        if ($variable['prefix-key'] == true) {
          $regex .= sprintf('(?:%s(%s)%s?)?', $variable['key'] . self::QUERY_PREFIX_CHAR, $variable['regexp'], $variable['post-seperator']);
        } else {
          $regex .= sprintf('(?:(%s)%s?)?', $variable['regexp'], $variable['post-seperator']);
        }
      }
      $regex .= '$';
      $redirect = "index.php?pagename=$searchPageName";
      $v        = 1;
      foreach (self::$search_query_variables as $variable) {
        // An optional non-capturing group wrapped around the $regexp.
        $redirect .= sprintf('&%s=$matches[%u]', $variable['key'], $v);
        $v++;
      }
      add_rewrite_rule($regex, $redirect, 'top');
    }
  }

  public static function programlisting_query_prettify()
  {
    foreach (self::$search_query_variables as $variable) {
      if (array_key_exists($variable['key'], $_GET)) {
        $redirection = self::generate_pretty_search_url(self::get_programlisting_vars(false));
        wp_redirect($redirection);
        exit();
      }
    }
  }

  public static function generate_pretty_search_url($variables = array())
  {
    $variables           = array_merge(self::get_programlisting_vars(), $variables);
    // Start with the search page uri.
    $result              = get_page_uri(get_option('wpdkaprogramlistings-page')) . '/';
    $last_post_seperator = '';
    foreach (self::$search_query_variables as $variable) {
      if (!array_key_exists($variable['key'], $variables)) {
        $variables[$variable['key']] = "";
      }
      $value = $variables[$variable['key']];
      if (empty($value) && $variable['default_value'] != null) {
        $value = $variable['default_value'];
      }
      if ($value) {
        if (is_array($value)) {
          $value = implode($variable['multivalue-seperator'], $value);
        }
        $value = urlencode($value);
        if ($variable['prefix-key']) {
          $result .= $variable['key'] . self::QUERY_PREFIX_CHAR . $value . $variable['post-seperator'];
        } else {
          $result .= $value . $variable['post-seperator'];
        }
      }
      $last_variable = $variable;
    }
    if (substr($result, -1) === $last_variable['post-seperator']) {
      $result = substr($result, 0, strlen($result) - 1) . "/";
    }
    // Fixing postfix issues, removing the last post-seperator.
    return site_url($result);
  }
  public static function print_search_info_text()
  {
    printf(__('Use %s, %s, or %s keywords.', self::DOMAIN), '<strong>AND</strong>', '<strong>OR</strong>', '<strong>NOT</strong>');
    echo '<br />';
    printf(__('%s is used to include both words.', self::DOMAIN), '<strong>AND</strong>');
    echo '<br />';
    printf(__('%s is used to include atleast one of the words.', self::DOMAIN), '<strong>OR</strong>');
    echo '<br />';
    printf(__('%s is used if it should not include the following word.', self::DOMAIN), '<strong>NOT</strong>');
    echo '<br /><br /><i>';
    printf(__('If nothing has been specified it is by default %s.', self::DOMAIN), '<strong>AND</strong>');
    echo '</i><br /><br />';
    _e('Put words or phrase in quotes to get more specific search results.', self::DOMAIN);
    echo '<br /><br />';
    printf(__('Use %s to replace a single character.', self::DOMAIN), '<strong>?</strong>');
    echo '<br />';
    printf(__('Use %s to replace zero or more characters.', self::DOMAIN), '<strong>*</strong>');
  }
  public static function escapeSearchValue($string)
  {
    $match   = array(
      '"'
    );
    $replace = array(
      '\\\\"'
    );
    $string  = str_replace($match, $replace, $string);
    return $string;
  }

  /**
   * Generate data and include template for search results.
   * There are three search modes: text only, date only, text with date filter.
   *
   * @param  array $args
   * @return string The markup generated.
   */
  public function generate_programlisting_results($args = array())
  {
    $search_vars = self::get_programlisting_vars();
    $year        = $search_vars[self::QUERY_KEY_YEAR];
    $month       = $search_vars[self::QUERY_KEY_MONTH];
    $day         = $search_vars[self::QUERY_KEY_DAY];
    $page        = $search_vars[self::QUERY_KEY_PAGE];
    $text        = self::get_programlisting_var(self::QUERY_KEY_FREETEXT, 'trim');

    $day = empty($day) ? "*" : str_pad($day, 2, '0', STR_PAD_LEFT);
    $month = empty($month) ? "*" : str_pad($month, 2, '0', STR_PAD_LEFT);
    $year = empty($year) ? "*" : $year;
    $text = empty($text) ? "*" : $text;

    // Calculate search offset from page number. Default is offset 0.
    $from = empty($page) ? 0 : (($page-1) * self::$page_size);

    $queryString = array(
      "default_field" => "allText",
      "default_operator" => "AND",
      "query" => $text
    );

    $query = array(
      "bool" => array(
        "must" => array(
          array(
            "query_string" => $queryString
          ),
          array(
            "wildcard" => array(
              "date.verbatim" => "$year-$month-$day"
            )
          )
        )
      )
    );

    $searchParams = array(
      "index" => self::ES_INDEX,
      "type" => self::ES_TYPE,
      "body" => array(
        "size" => self::$page_size,
        "from" => $from,
        "sort" => array(
          "date" => array(
            "order" => "ASC"
          )
        ),
        "query" => $query
      )
    );

    require(plugin_dir_path(__FILE__) . '/elasticsearch/autoload.php');
    $params = array(
      "hosts" => array(
        self::ES_URL
      )
    );
    $client = new Elasticsearch\Client($params);

    try {
      $queryResponse = $client->search($searchParams);
    } catch (Exception $e) {
      #error_log("generate_programlisting_results() Exception: $e");
      return;
    }


    // Sort by filename - elasticsearch can't do this when it is in _source
    $total = $queryResponse['hits']['total'];
    $ret_hits = $hits = $queryResponse['hits']['hits'];
    $ret_hits = array();
    foreach ($hits as $hit) {
      $filename            = $hit['_source']['filename'];
      $ret_hits[$filename] = $hit;
    }
    ksort($ret_hits);
    self::$search_results = $ret_hits;
    self::$search_total = $total;
  }

  public static function get_programlisting_search_type()
  {
    $search_vars = self::get_programlisting_vars();
    return $search_vars[self::QUERY_KEY_FREETEXT] ? self::QUERY_KEY_FREETEXT : 'date';
  }
  public static function get_programlisting_results()
  {
    return self::$search_results;
  }
  public static function set_search_results($search_results)
  {
    self::$search_results = apply_filters(self::FILTER_PREPARE_RESULTS, $search_results);
  }
  public static function get_programlisting_total() {
    return self::$search_total;
  }


  /**
   * Pagination for search results
   * @param  array  $args Arguments can be passed for specific behaviour
   * @return string
   **/
  public static function paginate($args = array()) {
    // Grab args or defaults
    $args = wp_parse_args($args, array(
      'before' 			=> '<ul>',
      'after' 			=> '</ul>',
      'before_link' 		=> '<li class="%s">',
      'after_link' 		=> '</li>',
      'link_class' 		=> '',
      'class_disabled' 	=> 'hidden',
      'class_active' 		=> 'active',
      'count' 			=> 5,
      'previous' 			=> '&laquo;',
      'next' 				=> '&raquo;',
      'echo' 				=> true
    ));
    extract($args, EXTR_SKIP);

    //Get current page number
    $page = intval(self::get_programlisting_var(self::QUERY_KEY_PAGE, false,
      true, '1'));
    //Get max page number
    $max_page = ceil(self::$search_total/self::$page_size);

    $result = $before;

    //Current page should optimally be in the center
    $start = $page-(ceil($count/2))+1;
    //When reaching the end, push start to the left such that current page is pushed to the right
    $start = min($start,($max_page+1)-$count);
    //Start can minimum be 1
    $start = max(1,$start);
    //Set end according to start
    $end = $start+$count;

    //Is prevous wanted
    if($previous) {
      $result .= self::paginate_page($before_link,$after_link,$page-1,$start,$max_page,$page,$link_class,$class_active,$class_disabled,$previous);
    }

    //Set enumeration
    for($i = $start; $i < $end; $i++) {
      $result .= self::paginate_page($before_link,$after_link,$i,$start,$max_page,$page,$link_class,$class_active,$class_disabled);
    }

    //Is next wanted
    if($next) {
      $result .= self::paginate_page($before_link,$after_link,$page+1,$start,$max_page,$page,$link_class,$class_active,$class_disabled,$next);
    }

    $result .= $after;

    //Is echo wanted automatically
    if($echo) {
      echo $result;
    }

    return $result;
  }

  /**
   * Helper function for pagination.
   * Sets the class, link and text for each element
   *
   * @param  string $before_link
   * @param  string $after_link
   * @param  int $page
   * @param  int $min
   * @param  int $max
   * @param  int $current
   * @param  string $link_class
   * @param  string $class_active
   * @param  string $class_disabled
   * @param  string $title
   * @return string
   */
  public static function paginate_page($before_link, $after_link, $page, $min,
    $max, $current, $link_class, $class_active, $class_disabled, $title = "") {
    if(!$title) {
      $link_class = ' class="'.$link_class.'"';
    } else {
      $link_class = '';
    }
    if($page > $max || $page < $min) {
      $class = $class_disabled;
      $result = '<span'.$link_class.'>'.($title?:$page).'</span>';
    } else if(!$title && $page == $current) {
      $class = $class_active;
      $result = '<span>'.$page.'</span>';
    } else {
      $class = "";
      $result = '<a'.$link_class.' href="'.
        self::generate_pretty_search_url(
          array(self::QUERY_KEY_PAGE => $page)) .'">'
        .($title?:$page).'</a>';
    }
    return sprintf($before_link,$class).$result.$after_link."\n";
  }

}
register_activation_hook(__FILE__, array(
  'WPDKAProgramListings',
  'install'
));
register_deactivation_hook(__FILE__, array(
  'WPDKAProgramListings',
  'uninstall'
));
new WPDKAProgramListings();
