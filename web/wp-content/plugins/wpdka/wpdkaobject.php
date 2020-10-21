<?php
/**
 * @package WP DKA
 * @version 1.0
 */

/**
 * Class that manages CHAOS data specific to
 * Dansk Kulturarv
 */
class WPDKAObject {

  // TODO: Consider making this an option.
  static $OBJECT_TYPE_IDS = array(36, 41);
  const DKA_SCHEMA_GUID = '00000000-0000-0000-0000-000063c30000';
  const DKA2_SCHEMA_GUID = '5906a41b-feae-48db-bfb7-714b3e105396';
  const DKA_CROWD_SCHEMA_GUID = 'a37167e0-e13b-4d29-8a41-b0ffbaa1fe5f';
  const DKA_CROWD_TAGS_SCHEMA_GUID = '00000000-0000-0000-0000-000067c30000';
  const METADATA_LANGUAGE = 'da';
  const SESSION_PREFIX = __CLASS__;

  const DKA_CROWD_SLUG_SOLR_FIELD = 'DKA-Crowd-Slug_string';

  public static $FREETEXT_SCHEMA_GUIDS = array(self::DKA_SCHEMA_GUID, self::DKA2_SCHEMA_GUID);
  public static $FREETEXT_LANGUAGE = array(self::METADATA_LANGUAGE);

  // TODO: Consider moving this back into the harvester.
  public static $DERIVED_FILES = array(
    '|^(?P<streamer>rtmp://vod-bonanza\.gss\.dr\.dk/bonanza)/mp4:bonanza/(?P<filename>.+\.mp4)$|i' => 'https://vod-kulturarv.dr.dk/bonanza/mp4:bonanza/bonanza/{$matches["filename"]}/Playlist.m3u8',
    '|^(?P<streamer>rtmp://vod-kulturarv\.dr\.dk/bonanza)/mp4:bonanza/(?P<filename>.+\.mp4)$|i' => 'https://vod-kulturarv.dr.dk/bonanza/mp4:bonanza/bonanza/{$matches["filename"]}/Playlist.m3u8'
  );

  public static $KNOWN_STREAMERS = array(
    'rtmp://vod-bonanza.gss.dr.dk/bonanza/',
    'rtmp://vod-kulturarv.dr.dk/bonanza/',
    'http://om.gss.dr.dk/MediaCache/_definst_/'
  );

  /**
   * Construct
   */
  public function __construct() {

    // Define a filter for object creation.
    $this->define_object_construction_filters();

    $this->define_single_object_page();

    // Restrict chaos query to this object type.
    // $objectTypeConstraints = array();
    // foreach(self::$OBJECT_TYPE_IDS as $id) {
    // 	$objectTypeConstraints[] = "ObjectTypeID:$id";
    // }
    // WPChaosClient::instance()->addGlobalConstraint(implode('+OR+', $objectTypeConstraints));
  }

  const TYPE_VIDEO = 'video';
  const TYPE_AUDIO = 'lyd';
  const TYPE_IMAGE = 'billede';
  const TYPE_IMAGE_AUDIO = 'billede-lyd';
  const TYPE_UNKNOWN = 'unknown';

  const IMAGE_MAX_WIDTH = 800;
  const IMAGE_MAX_HEIGHT = 400;

  /**
   * How many seconds should we wait for the CHAOS service to realize the slug has changed?
   * @var integer
   */
  const RESET_TIMEOUT_S = 30; // 10 seconds.

  /**
   * How many milliseconds delay between checking the service for the object to become searchable on the slug.
   * @var integer
   */
  const RESET_DELAY_MS = 100; // 0.1 seconds.

  public static $format_types = array(
    WPDKAObject::TYPE_AUDIO => array(
      'class' => 'icon-volume-up',
      'title' => 'Lyd',
      'chaos-value' => 'audio',
      'chaos-filter' => '(FormatTypeName:audio)'
      ),
    WPDKAObject::TYPE_IMAGE_AUDIO => array(
      'class' => 'icon-picture-sound',
      'title' => 'Billeder og lyd',
      'chaos-value' => 'unknown',
    ),
    WPDKAObject::TYPE_VIDEO => array(
      'class' => 'icon-film',
      'title' => 'Video',
      'chaos-value' => 'video',
      'chaos-filter' => '(FormatTypeName:video)'
    ),
    // This is not yet supported by the metadata.
    //WPDKAObject::TYPE_UNKNOWN => array(
    //	'class' => 'icon-file-text',
    //	'title' => 'Dokumenter',
    //),
    WPDKAObject::TYPE_IMAGE => array(
      'class' => 'icon-picture',
      'title' => 'Billeder',
      'chaos-value' => 'image',
      // 'chaos-filter' => '(FormatTypeName:image)'
      'chaos-filter' => '(FormatTypeName:(image AND -video))'
    ),
    WPDKAObject::TYPE_UNKNOWN => array(
      'class' => 'icon-circle-blank',
      'title' => 'Materiale',
      'chaos-value' => '',
      ),
  );

  /**
   * Limit length of string
   * @author Joachim Jensen <jv@intox.dk>
   * @param  string    $string
   * @param  integer   $length
   * @param  string    $ellipsis
   * @return string
   */
  public static function word_limit($string, $length = 30, $ellipsis = " [...]") {
    $words = explode(' ', $string);
    if (count($words) > $length)
      $string = implode(' ', array_slice($words, 0, $length)) . $ellipsis;
    return $string;
  }

  /**
   * Resize image server side
   * @author Mads Lundt <mads@socialsquare.dk>
   * @param 	string 	$url
   * @return 	string 	link to resized image
   */
  public static function resizeImage($id) {
    return sprintf('%s?guid=%s&w=%s&h=%s', plugins_url( 'cimage/webroot/img.php' , __FILE__ ), $id, self::IMAGE_MAX_WIDTH, self::IMAGE_MAX_HEIGHT);
  }

  /**
   * Determine type of a CHAOS object based
   * on the included file formats
   * @param  WPChaosDataObject $object
   * @return string
   */
  public static function determine_type($object) {

    $format_types = array();

    foreach($object->Files as $file) {
      //FormatID = 10 is thumbnai format. We do not want that here.
      if($file->FormatID != 10) {
        $format_types[$file->FormatType] = 1;
      }

    }

    //Video format
    if(isset($format_types['Video']))
      return self::TYPE_VIDEO;

    if(isset($format_types['Audio'])) {
      //Image audio format
      if(isset($format_types['Image']))
        return self::TYPE_IMAGE_AUDIO;
      //Audio format
      return self::TYPE_AUDIO;
    }

    //Image format
    if(isset($format_types['Image']))
      return self::TYPE_IMAGE;

    //Fallback
    return self::TYPE_UNKNOWN;
  }

  public static function get_creator_attributes($creators) {
    $value = "";
    if($creators) {
      //Some roles are in English, gettext cannot translate variables, thus this whitelist
      $role_i18n = array(
        'actors' => __('Actors','wpdka'),
        'cinematography' => __('Cinematography','wpdka'),
        'creator' => __('Creator','wpdka'),
        'direction' => __('Direction','wpdka'),
        'directors' => __('Directors','wpdka'),
        'production' => __('Production','wpdka'),
        'script' => __('Script','wpdka'),

      );

      $value .= "<dl>\n";
      foreach($creators as $creator) {
        $role = strtolower(strval($creator['Role']));
        $role = (isset($role_i18n[$role]) ? $role_i18n[$role] : ucfirst($creator['Role']));
        $value .= "<dt>".$role."</dt>\n";
        if(strval($creator) == "") {
          $value .= "<dd>".$creator['Name']."</dd>\n";
        } else {
          $value .= "<dd>".strval($creator)."</dd>\n";
        }
      }
      $value .= "</dl>\n";
    } else {
      $value .= "";
    }
    return $value;
  }

  /**
   * Turns a URLs in a text string into links.
   * @see http://stackoverflow.com/questions/206059/php-validation-regex-for-url
   * @param string $text Text string to replace in.
   * @return string
   */
  public static function replace_url_with_link($text) {
    return preg_replace("#((http|https|ftp)://(\S*?\.\S*?))(\s|\;|\)|\]|\[|\{|\}|,|\"|'|:|\<|$|\.\s)#i", '<a href="$1" target="_blank">$3</a>$4', $text);
  }

  public function define_single_object_page() {
    // Ensure the DKA Crowd metadata schema is present, and redirect to the slug URL if needed.
    add_action(WPChaosClient::GET_OBJECT_PAGE_BEFORE_TEMPLATE_ACTION, function(\WPChaosDataObject $object) {
      // If a guid was used to retreive the object, this might not have the crowd metadata connected to it.
      // Only for DKA objects
      if(array_key_exists('guid', $_GET) && in_array($object->ObjectTypeID, WPDKAObject::$OBJECT_TYPE_IDS)) {
        try {
          // Don't wait for it to become available with a nice URL, just show the darn thing.
          $object = WPDKAObject::ensure_crowd_metadata($object, true);
          // Prepend the GUID, just in case the metadata is not ready.
          $redirection = $object->url;
          // Add URL parameters
          unset($_GET['guid']);
          if(count($_GET) > 0) {
            $redirection .= '?' . http_build_query($_GET);
          }
          status_header(301);
          header("Location: $redirection");
        } catch(\CHAOSException $e) {
          error_log($e->getMessage());
          wp_die($e->getMessage());
        }
        exit;
      }
    });

    add_action(WPChaosClient::GET_OBJECT_PAGE_BEFORE_TEMPLATE_ACTION, function(\WPChaosDataObject $object) {
      // Make sure no tags is present in the metadata.
      $crowd_metadata = $object->get_metadata(WPDKAObject::DKA_CROWD_SCHEMA_GUID);
      if($crowd_metadata instanceof \SimpleXMLElement) {
        $crowd_metadata_dom = dom_import_simplexml($crowd_metadata);
        // Remove all Tags elements.
        foreach($crowd_metadata_dom->getElementsByTagName('Tags') as $tag) {
          // If we've got a tag
          $tag->parentNode->removeChild($tag);
        }
        $object->set_metadata(WPChaosClient::instance(), WPDKAObject::DKA_CROWD_SCHEMA_GUID, $crowd_metadata, WPDKAObject::METADATA_LANGUAGE);
      }
    }, 11);

    //Only increment views in production
    if(!WP_DEBUG) {
      add_action(WPChaosClient::GET_OBJECT_PAGE_BEFORE_TEMPLATE_ACTION, function(\WPChaosDataObject $object) {
        // TODO: Restrict on session data.
        if(!session_id()){
          session_start();
        }
        // Increment the views counter
        $viewed_session_name = WPDKAObject::SESSION_PREFIX . '_viewed_' . $object->GUID;
        if(!array_key_exists($viewed_session_name, $_SESSION)) {
          $object->increment_metadata_field(WPDKAObject::DKA_CROWD_SCHEMA_GUID, WPDKAObject::METADATA_LANGUAGE, '/dkac:DKACrowd/dkac:Views/text()', array('views'));
          // Use the session to make sure that the same user is not just refreshing the view counter by refreshing.
          $_SESSION[$viewed_session_name] = "viewed";
        }
      }, 12);
    }

    // Make sure objects are identified if they are there.
    add_filter(WPChaosClient::GENERATE_SINGLE_OBJECT_SOLR_QUERY, function($query) {
      if(is_string($query)) {
        $query = array($query);
      } else {
        $query = array();
      }

      $chaos_material_slug = false;

      //Case for <organisation>/integer
      //when a chaos material slug consists
      //of an integer
      if(is_page() && get_query_var('page')) {
        global $wpdb;
        $result = $wpdb->get_col("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'chaos_organization'");
        $result[] = get_option('wpdka-default-organization-page');
        $array = array_unique($result);
        if(in_array(get_the_ID(),$result)) {
          $chaos_material_slug = get_query_var('page');
        }
      }
      //Case for <organisation>/<404 page>(/embed)
      //when a chaos material slug triggers
      //a 404 in WordPress
      //WordPress pages will therefore get priority over
      //chaos pages
      elseif(is_404()) {
        $http = strstr(get_home_url(),'://',true) ?: 'http';

        //<blog url>/<institution>/<slug>(/<embed>)
        //We need to use blog url as suffix, because it might not be root and therefore a part of request_uri
        preg_match('|^'.get_home_url().'/([^/]+)/([^/]+)(?:/(embed)?)?(/\?.*)?$|', $http.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], $matches);

        //$matches[2] is slug
        if(isset($matches[2])) {

          $chaos_material_slug = $matches[2];

          $query[] = WPDKAObject::DKA_CROWD_SLUG_SOLR_FIELD. ':"'. $matches[2] .'"';

          //Get relevant template
          add_filter('chaos-object-template', function($template) use ($matches) {
            if(isset($matches[3]) && $matches[3] == 'embed') {
              $template = 'chaos-object-embed';
            }
            return $template;
          });
        }
      }

      if($chaos_material_slug) {
        $query[] = WPDKAObject::DKA_CROWD_SLUG_SOLR_FIELD. ':"'. $chaos_material_slug .'"';
      }



      return implode("+OR+", $query);
    });
  }


  public function define_object_construction_filters() {
    /*
    add_action(WPChaosObject::CHAOS_OBJECT_CONSTRUCTION_ACTION, function(WPChaosObject $object) {
      WPDKAObject::ensure_crowd_metadata($object);
    }, 10, 1);
    */
    // Hack: Adding a HLS version of videos from DR
    // Make this change on the metadata instead.
    add_action(WPChaosDataObject::CHAOS_OBJECT_CONSTRUCTION_ACTION, function(WPChaosDataObject $object) {
      $originalObject = $object->getObject();
      foreach($originalObject->Files as $file) {
        foreach(WPDKAObject::$DERIVED_FILES as $regexp => $transformation) {
          //		  http://om.gss.dr.dk/MediaCache/_definst_/mp4:content/bonanza/02-03-2008/25781_720x540x1400K.mp4/Playlist.m3u8
          //		  http://om.gss.dr.dk/MediaCache/_definst_/mp4:content/bonanza/2008/3/2/25781_720x540x1400K.mp4/Playlist.m3u8
          // FIXME: http://om.gss.dr.dk/MediaCache/_definst_/mp4:content/bonanza/2012/6/14/49531_720x540x1400k.mp4/Playlist.m3u8
          $matches = null;
          if($file->Token == "RTMP Streaming" && preg_match($regexp, $file->URL, $matches)) {
            // Perform the transformation.
            eval('$url = "'.$transformation.'";');
            $newFile = (object) array_merge((array) $file, array(
              'URL' => $url,
              'Token' => 'HLS Streaming',
              'Streamer' => $matches['streamer']
            ));
            // $originalObject->Files[] = $newFile;
            array_unshift($originalObject->Files, $newFile);
          }
        }
      }
      foreach($originalObject->Files as &$file) {
        // We're not interested in anything but a video.
        if($file->FormatType != 'Video') continue;

        $file->Streamer = null;
        foreach(WPDKAObject::$KNOWN_STREAMERS as $streamer) {
          if(strstr($file->URL, $streamer) !== false) {
            $file->Streamer = $streamer;

            // Check if the file URL contains the (flv|mp4|mp3): part, just after the streamers URL.
            $matches = array();
            $streamer_regexp_escaped = $streamer;
            $streamer_regexp_escaped = str_replace('.', '\.', $streamer_regexp_escaped);
            if(preg_match("~^$streamer_regexp_escaped(((?:flv|mp4|mp3):)?.*?.([\w]+))$~", $file->URL, $matches) > 0) {
              $filename = $matches[1];
              $rtmpPathSeperator = $matches[2];
              $extension = $matches[3];
              if(empty($rtmpPathSeperator)) {
                // No RTMP seperator found, using the extension.
                $file->URL = $streamer . $extension . ':' . $filename;
              }
            }
          }
        }
      }
      return $object;
    }, 10, 1);
  }

  public static function ensure_crowd_metadata(\WPChaosDataObject $object, $ensureObjectIsReachableFromSlug = false) {
    // Is this the admin force resetting from URL?
    $forceReset = WP_DEBUG && array_key_exists('reset-crowd-metadata', $_GET) && current_user_can('edit_posts');

    if($forceReset || !$object->has_metadata(WPDKAObject::DKA_CROWD_SCHEMA_GUID)) {
      $object = self::reset_crowd_metadata($object, true);
      $slug = $object->slug;
    } else {
      // If the metadata is present, we can extract the slug from there.
      $slug = $object->slug;
    }

    // If needed, a loop is performed until the object is reachable or a timeout is reached and an exception is thrown.
    if($ensureObjectIsReachableFromSlug) {
      // Make sure the object is reachable on the slug, by performing multiple requests for the object until its returned.
      $start = time(); // Time in milliseconds
      while(($object = self::getObjectFromSlug($slug)) == null) {
        $now = time();
        if($now > $start + self::RESET_TIMEOUT_S) {
          // Timeout was reached.
          throw new \CHAOSException("reset_crowd_metadata loop failed to find the CHAOS object within the timeout (".self::RESET_TIMEOUT_S."s)");
        }
        usleep(self::RESET_DELAY_MS * 1000);
      };
    }

    if($forceReset) {
      $link = $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
      $link = str_replace('reset-crowd-metadata', '', $link);
      $link = "<a href='$link'>Click to get back</a>";
      wp_die("Crowd Metadata was reset: $link");
    }

    return $object;
  }

  public static function reset_crowd_metadata(\WPChaosDataObject $object, $forceNewSlug = false, $fetchSocialCounts = false) {
    $existingMetadata = $object->has_metadata(WPDKAObject::DKA_CROWD_SCHEMA_GUID);
    $revisionID = $existingMetadata != false ? $existingMetadata->RevisionID : null;

    // The object has not been extended with the crowd matadata schema.
    $objectGUID = $object->GUID;
    $metadataXML = new SimpleXMLElement("<?xml version='1.0' encoding='UTF-8' standalone='yes'?><dkac:DKACrowd xmlns:dkac='http://www.danskkulturarv.dk/DKA-Crowd.xsd'></dkac:DKACrowd>");
    $metadataXML->addChild('Views', WPDKAObject::restore_views($object->GUID));
    if($fetchSocialCounts) {
      $shares = array_sum(self::fetch_social_counts($object));
    } else {
      $shares = 0;
    }
    $metadataXML->addChild('Shares', $shares);
    $metadataXML->addChild('Likes', '0');
    $metadataXML->addChild('Ratings', '0');
    $metadataXML->addChild('AccumulatedRate', '0');
    $slug = WPDKAObject::generateSlug($object, $forceNewSlug);
    $metadataXML->addChild('Slug', $slug);

    //Taggable by default
    $metadataXML->addChild('Taggable', 'true');

    $successfulValidation = $object->set_metadata(WPChaosClient::instance(), WPDKAObject::DKA_CROWD_SCHEMA_GUID, $metadataXML, WPDKAObject::METADATA_LANGUAGE, $revisionID);

    if($successfulValidation === false) {
      wp_die("Error validating the Crowd Schema");
    }
    $object->clear_cache();

    return $object;
  }

  /**
   * Generate a slug from a chaos object.
   * Using a bisection algorithm, first determining an upper bound and then bisecting until the next free postfix is found.
   * @param \CHAOS\Portal\Client\Data\DataObject $object The object to generate the slug from.
   * @return string The slug generated - prepended with a nummeric postfix to prevent douplicates.
   */
  public static function generateSlug(\WPChaosDataObject $object, $forceNew = false) {
    // Check if the object is reachable on its exsisting slug.
    if($object->slug && !$forceNew) {
      $exsistingSlugObjects = self::getObjectFromSlug($object->slug, true);
      if(count($exsistingSlugObjects) == 1 && $exsistingSlugObjects[0]->GUID == $object->GUID) {
        // There is only a single object with this slug, and its the same object.
        return $object->slug;
      }
    }

    // If not - lets generate another one.
    $title = $object->title;
    // We need to urldecode, as special chars are encoded to octets.
    $slug_base = urldecode(sanitize_title_with_dashes($title));
    if(strlen($slug_base) == 0) {
      $slug_base = 'materiale-uden-titel';
    }
    // Is it free without a postfix?
    if(self::isSlugFree($slug_base)) {
      return $slug_base;
    }

    $postfix = null;
    $lower_postfix = 1;
    $upper_postfix = 1;
    // Find an upper-bound for the postfix - exponentially.
    while(self::isSlugFree("$slug_base-$upper_postfix") === false) {
      $upper_postfix *= 2;
    }

    while($upper_postfix - $lower_postfix > 1) {
      $middle_postfix = floor(($upper_postfix-$lower_postfix)/2) + $lower_postfix;
      $slug_candidate = "$slug_base-$middle_postfix";
      if(self::isSlugFree($slug_candidate)) {
        $upper_postfix = $middle_postfix;
      } else {
        $lower_postfix = $middle_postfix;
      }
    }
    // Return the slug of the upper_postfix.
    return "$slug_base-$upper_postfix";
  }

  /**
   * Tests if a slug is free.
   * @param string $slug_candidate
   * @return boolean True if no objects are associated with the slug, false otherwise.
   */
  public static function isSlugFree($slug_candidate) {
    $objects = self::getObjectFromSlug($slug_candidate, true);
    if(count($objects) == 0) {
      // This slug appears to be free.
      return true;
    } else {
      return false;
    }
  }

  const FACEBOOK_STATS_URL = 'https://graph.facebook.com/fql';

  public static function get_facebook_stats($url) {
    $fql_query = "SELECT total_count FROM link_stat WHERE url = '$url'";
    $response = json_decode(file_get_contents(self::FACEBOOK_STATS_URL . '?q=' . urlencode($fql_query)));
    return $response->data[0]->total_count;
  }

  const TWITTER_STATS_URL = 'https://cdn.api.twitter.com/1/urls/count.json';

  public static function get_twitter_stats($url) {
    $response = json_decode(file_get_contents(self::TWITTER_STATS_URL . '?url=' . urlencode($url)));
    return $response->count;
  }

  const GOOGLE_PLUS_STATS_URL = 'https://apis.google.com/u/0/_/+1/sharebutton';

  public static function get_google_plus_stats($url) {
    $query = http_build_query(array( 'url' => $url ));
    $url = self::GOOGLE_PLUS_STATS_URL . '?' . $query;

    $ch = curl_init();
    curl_setopt_array($ch, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTPHEADER => array( 'accept-language: en-GB,da;q=0.8,en-US;q=0.6' )
    ));

    $content = curl_exec($ch);
    curl_close($ch);

    if($content === false) {
      throw new \RuntimeException("Couldn't connect to the Google Plus API, it might have changed.");
    }

    $count_matches = array();
    if(preg_match('|id="aggregateCount".*?>([^<]+)<|', $content, $count_matches) == 1) {
      if(strstr($count_matches[1], 'K') !== false) {
        $multiplier = 1000;
      } elseif(strstr($count_matches[1], 'M') !== false) {
        $multiplier = 1000000;
      } else {
        $multiplier = 1;
      }
      return intval(floatval($count_matches[1]) * $multiplier);
    } else {
      throw new \RuntimeException("Couldn't find the aggregateCount to the Google Plus API, the markup might have changed, check: " . $base_url . $query);
    }
  }

  public static function fetch_social_counts(\WPChaosDataObject $object, $update_metadata = false) {
    $url = $object->url;
    // What would the legacy url be?
    $legacyURL = 'http://www.danskkulturarv.dk/chaos_post/' . $object->GUID;

    $facebook_total_count = self::get_facebook_stats($url);
    $facebook_total_count += self::get_facebook_stats($legacyURL);

    $twitter_total_count = self::get_twitter_stats($url);
    $twitter_total_count += self::get_twitter_stats($legacyURL);

    $google_plus_total_count = self::get_google_plus_stats($url);
    $google_plus_total_count = self::get_google_plus_stats($legacyURL);

    if($update_metadata) {
      // Update the metadata.
      $object->set_metadata_field(WPDKAObject::DKA_CROWD_SCHEMA_GUID, WPDKAObject::METADATA_LANGUAGE, '/dkac:DKACrowd/dkac:Shares/text()', $facebook_total_count + $twitter_total_count + $google_plus_total_count, array('shares'));
    }

    return array(
      'facebook_total_count' => $facebook_total_count,
      'twitter_total_count' => $twitter_total_count,
      'google_plus_total_count' => $google_plus_total_count
    );
  }

  /**
   * Gets an object from the CHAOS Service from an alphanummeric, lowercase slug.
   * @param string $slug The slug to search for.
   * @param boolean $returnMultiple Makes the function return an array of objects, a single element array if only one object is found.
   * @return NULL|\WPChaosDataObject|\WPChaosDataObject[] The object(s) matching the slug - multiple if
   * @throws \RuntimeException If an error occurs in the service.
   */
  public static function getObjectFromSlug($slug, $returnMultiple = false) {
    // TODO: Use this instead, when DKA-Slug is added to the index.
    // $response = WPChaosClient::instance()->Object()->Get("DKA-Slug:'$slug'");
    $query = WPDKAObject::DKA_CROWD_SLUG_SOLR_FIELD. ':"' . $slug . '"';
    // die($query);
    $response = WPChaosClient::instance()->Object()->Get($query, null, false, 0, 1, true);
    if(!$response->WasSuccess()) {
      throw new \RuntimeException("Couldn't get object from slug: ".$response->Error()->Message());
    } elseif (!$response->MCM()->WasSuccess()) {
      throw new \RuntimeException("Couldn't get object from slug: ".$response->MCM()->Error()->Message());
    } else {
      $count = $response->MCM()->TotalCount();
      if($count == 0) {
        return null;
      } elseif ($count > 1) {
        //if(WP_DEBUG) {
        //	throw new \CHAOSException("CHAOS returned more than one ($count) object for this slug: ". htmlentities($slug));
        //}
      }
      if($returnMultiple) {
        return WPChaosDataObject::parseResponse($response);
      } else {
        $results = $response->MCM()->Results();
        return new \WPChaosDataObject($results[0]);
      }
    }
  }

  protected static $legacy_views = array();
  protected static $legacy_views_file = 'views.csv';

  public static function restore_views($guid) {
    if(count(self::$legacy_views) == 0) {
      $legacy_views_file = realpath(__DIR__ . DIRECTORY_SEPARATOR . self::$legacy_views_file);
      if($legacy_views_file) {
        $legacy_views = file_get_contents($legacy_views_file);
        $legacy_views = explode("\n", $legacy_views);
        foreach($legacy_views as $row) {
          $row = explode(',', $row);
          if($row[0]) {
            self::$legacy_views[$row[0]] = intval($row[1]);
          }
        }
      }
    }
    if(array_key_exists($guid, self::$legacy_views)) {
      return self::$legacy_views[$guid];
    } else {
      return 0;
    }
  }

}
//Instantiate
new WPDKAObject();

//eol
