<?php
/**
 * @package WP DKA
 * @version 1.0
 */

/**
 * Class that manages CHAOS data specific to
 * Dansk Kulturarv and registers attributes
 * for WPChaosObject
 */

class WPDKASearch {

  const QUERY_KEY_TYPE = 'med';
  const QUERY_KEY_ORGANIZATION = 'fra';
  const QUERY_KEY_DATE_RANGE = 'mellem';

  /**
   * List of organizations from the WordPress site
   * @var array
   */
  public static $organizations = [];

  public static $sorts = [
    null => [
      'title' => 'Relevans',
      'link' => null,
      'chaos-value' => null
    ],
    'titel' => [
      'title' => 'Titel',
      'link' => 'titel',
      'chaos-value' => 'DKA-Title_string+asc'
    ],
    'visninger' => [
      'title' => 'Visninger',
      'link' => 'visninger',
      'chaos-value' => 'DKA-Crowd-Views_int+desc'
    ],
    'udgivelse' => [
      'title' => 'Udgivelsestid',
      'link' => 'udgivelse',
      'chaos-value' => 'DKA-FirstPublishedDate_date+asc'
    ]
  ];

  /**
   * Construct
   */
  public function __construct() {
    self::$sorts['nyt-paa-dka'] = [
      'title' => 'Nyt på sitet',
      'link'  => 'nyt-paa-dka',
      'chaos-value' => 'ap' . strtolower(get_option('wpchaos-accesspoint-guid')) . '_PubStart+desc'
    ];

    add_filter('wpchaos-head-meta', [&$this, 'set_search_meta'], 99);

    WPChaosSearch::register_search_query_variable(2, WPDKASearch::QUERY_KEY_ORGANIZATION, '[\w-]+?', true, '-');
    WPChaosSearch::register_search_query_variable(3, WPDKASearch::QUERY_KEY_TYPE, '[\w-]+?', true, '-');

    // Define the free-text search filter.
    $this->define_search_filters();

    add_filter('wpchaos-solr-sort', [&$this, 'map_chaos_sorting'], 10, 2);

  }

  /**
   * Set title and meta nodes for search results
   */
  public function set_search_meta($metadatas) {

    if(get_option('wpchaos-searchpage') && is_page(get_option('wpchaos-searchpage'))) {

      $extra_description = '';

      // Fetch titles from the organizations searched in
      if(WPChaosSearch::get_search_var(WPDKASearch::QUERY_KEY_ORGANIZATION)) {
        $organizations = WPDKASearch::get_organizations();
        $temp = [];
        foreach($organizations as $organization) {
          if (in_array($organization['slug'], WPChaosSearch::get_search_var(WPDKASearch::QUERY_KEY_ORGANIZATION))) {
            $temp[] = $organization['title'];
          }
        }

        if ($temp) {
          $extra_description .= sprintf(__(' The material is from %s.', 'wpdka'), preg_replace('/(.*),/', '$1 ' . __('and', 'wpdka'), implode(", ", $temp)));
        }

        unset($temp);
      }

      //Fetch the titles from the formats searched in
      if (WPChaosSearch::get_search_var(WPDKASearch::QUERY_KEY_TYPE)) {
        $temp = [];
        foreach (WPChaosSearch::get_search_var(WPDKASearch::QUERY_KEY_TYPE) as $format) {
          if (isset(WPDKAObject::$format_types[$format])) {
            $temp[] = strtolower(WPDKAObject::$format_types[$format]['title']);
          }
        }

        if ($temp) {
          $extra_description .= sprintf(__(' The format is %s.', 'wpdka'), preg_replace('/(.*),/', '$1 ' . __('and', 'wpdka'), implode(", ", $temp)));
        }

        unset($temp);
      }

      $search_results = WPChaosSearch::get_search_results();

      if($search_results) {
        $title = get_bloginfo('title');
        $count = $search_results->MCM()->TotalCount();
        $freetext = WPChaosSearch::get_search_var(WPChaosSearch::QUERY_KEY_FREETEXT, 'esc_html');

        $content = sprintf(__('%s contains %s materials about %s.','wpdka'), $title, $count, $freetext) . $extra_description;

        $metadatas['description']['content'] = $content;
        $metadatas['og:description']['content'] = $content;
      }
    }

    return $metadatas;
  }

  /**
   * Convert search parameters to SOLR query
   * @return string
   */
  public function define_search_filters() {

    add_filter('wpchaos-solr-query', function ($query, $query_vars) {
      if ($query) {
        $query = [$query];
      } else {
        $query = [];
      }

      $query[] = '(ObjectTypeID:(' . implode(" OR ", WPDKAObject::$OBJECT_TYPE_IDS) . '))';
      return '(' . implode(" AND ", $query) . ')';
    }, 9, 2);


    // Free text search.
    add_filter('wpchaos-solr-query', function ($query, $query_vars) {
      if ($query) {
        $query = [$query];
      } else {
        $query = [];
      }

      if (array_key_exists(WPChaosSearch::QUERY_KEY_FREETEXT, $query_vars)) {
        // For each known metadata schema, loop and add freetext search on this.
        $freetext = $query_vars[WPChaosSearch::QUERY_KEY_FREETEXT];
        if (!empty($freetext)) {
          $freetext = WPChaosClient::escapeSolrValue($freetext);
          $searches = [];
          foreach (WPDKAObject::$FREETEXT_SCHEMA_GUIDS as $schemaGUID) {
            foreach (WPDKAObject::$FREETEXT_LANGUAGE as $language) {
              $searches[] = sprintf("(m%s_%s_all:(%s))", $schemaGUID, $language, $freetext);
            }
          }
          $query[] = '(' . implode(" OR ", $searches) . ')';
        }
      }
      return '(' . implode(" AND ", $query) . ')';
    }, 10, 2);

    // File format types
    add_filter('wpchaos-solr-query', function ($query, $query_vars) {
      if ($query) {
        $query = [$query];
      } else {
        $query = [];
      }

      if (array_key_exists(WPDKASearch::QUERY_KEY_TYPE, $query_vars)) {
        // Loop through requested types and append valid ones to query
        $types = $query_vars[WPDKASearch::QUERY_KEY_TYPE];
        $filters = [];
        foreach ($types as $type) {
          if (isset(WPDKAObject::$format_types[$type])) {
            if (WPDKAObject::$format_types[$type]['chaos-filter']) {
              $filters[] = WPDKAObject::$format_types[$type]['chaos-filter'];
            }
          }
        }
        if (count($filters) > 0) {
          $query[] = '(' . implode(" OR ", $filters) . ')';
        }
      }

      return '(' . implode(" AND ", $query) . ')';
    }, 11, 2);

    // Organizations
    add_filter('wpchaos-solr-query', function ($query, $query_vars) {
      if ($query) {
        $query = [$query];
      } else {
        $query = [];
      }

      // Force query to only search in DR organizations. (hasse@ramlev.dk - 20231116)
      $query[] = '(DKA-Organization:(DR))';

      return '(' . implode(" AND ", $query) . ')';
    }, 21, 2);

    /* Add Date filtering to the search criteria */
    add_filter('wpchaos-solr-query', function($query, $query_vars) {
      if($query) {
        $query = [$query];
      } else {
        $query = [];
      }


      if(array_key_exists(WPDKASearch::QUERY_KEY_DATE_RANGE, $query_vars) &&
        count($query_vars[WPDKASearch::QUERY_KEY_DATE_RANGE]) === 2) {
        $dates = $query_vars[WPDKASearch::QUERY_KEY_DATE_RANGE];
        $date_from = ensure_ymd_format($dates[0]) . 'T00:00:00Z';
        $date_to = ensure_ymd_format($dates[1]) . 'T00:00:00Z';
        $query[] = '(DKA-FirstPublishedDate_date:['. $date_from . ' TO ' . $date_to . '])';
      }

      return '(' . implode(" AND ", $query) . ')';
    }, 22, 2); // Has to be exercuted after tags are added.
  }

  public function map_chaos_sorting($sort,$query_vars) {
    return (isset(WPDKASearch::$sorts[$sort]) ? WPDKASearch::$sorts[$sort]['chaos-value'] : null);
  }

  /**
   * Fetch organization title and slug from pages using the "chaos_organization" custom field
   * That custom field value should correspond to the title given in CHAOS
   * @return array
   */
  public static function get_organizations() {
    if(empty(self::$organizations)) {
      $key = 'chaos_organization';
      $posts = new WP_Query([
        'meta_key' => $key ,
        'post_type' => 'page',
        'post_status' => 'publish,future',
        'orderby' => 'title',
        'order' => 'ASC'
      ]);

      foreach($posts->posts as $post) {
        $post_organizations = get_post_custom_values($key, $post->ID);
        foreach($post_organizations as $organization) {
          self::$organizations[$organization] = [
            'title' => $post->post_title,
            'slug' => $post->post_name,
            'id' => $post->ID
          ];
        }
      }
    }

    return self::$organizations;
  }

  public static function get_organizations_merged() {
    $result = [];
    $organizations = self::get_organizations();
    foreach($organizations as $title => $organization) {
      if(!array_key_exists($organization['id'], $result)) {
        $result[$organization['id']] = $organization;
        $result[$organization['id']]['chaos_titles'] = [$title];
      } else {
        $result[$organization['id']]['chaos_titles'][] = $title;
      }
    }

    return $result;
  }

}

//Instantiate
new WPDKASearch();

//eol
