<?php

// Registering namespaces.
\CHAOS\Portal\Client\Data\Object::registerXMLNamespace('dka', 'http://www.danskkulturarv.dk/DKA.xsd');
\CHAOS\Portal\Client\Data\Object::registerXMLNamespace('dka2', 'http://www.danskkulturarv.dk/DKA2.xsd');
\CHAOS\Portal\Client\Data\Object::registerXMLNamespace('dkac', 'http://www.danskkulturarv.dk/DKA.Crowd.xsd');
\CHAOS\Portal\Client\Data\Object::registerXMLNamespace('xhtml1-transitional.xsd', 'http://www.w3.org/1999/xhtml', 'schemas/xhtml1-transitional.xsd');
\CHAOS\Portal\Client\Data\Object::registerXMLNamespace('http://www.w3.org/2001/xml.xsd', 'http://www.w3.org/2001/xml.xsd', 'schemas/xml.xsd');


//object->title
add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'title', function($value, \WPCHAOSObject $object) {
  $value .= $object->metadata(
    array(WPDKAObject::DKA2_SCHEMA_GUID, WPDKAObject::DKA_SCHEMA_GUID),
    array('/dka2:DKA/dka2:Title/text()', '/DKA/Title/text()')
    );
        // If we have no title at all.
  if($value == "") {
    $typeTitle = $object->type_title;
        // if($typeTitle == WPDKAObject::TYPE_UNKNOWN) {
        // 	$typeTitle = __('Material','wpdka');
        // }
    $value = $typeTitle . __(' without title','wpdka');
  }
  return $value;
}, 10, 2);

    //object->tags_raw
add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'tags_raw', function($value, \WPCHAOSObject $object) {
  $tags = $object->metadata(
    array(WPDKAObject::DKA2_SCHEMA_GUID, WPDKAObject::DKA_SCHEMA_GUID),
    array('/dka2:DKA/dka2:Tags/dka2:Tag','/DKA/Tags/Tag'),
    null
    );
      //If there are no tags, null is returned above, we need an array
  return ($tags?:array());
}, 10, 2);

//object->tags
add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'tags', function($value, \WPCHAOSObject $object) {
  $tags = $object->tags_raw;
  foreach($tags as $key => &$tag) {
        //Remove tag if empty
    if(!$tag) {
      unset($tags[$key]);
      continue;
    }

    $link = WPChaosSearch::generate_pretty_search_url(array(WPChaosSearch::QUERY_KEY_FREETEXT => $tag));
    $value .= '<a class="tag" href="'.$link.'" title="'.esc_attr($tag).'">'.$tag.'</a>'."\n";
  }
  if(empty($tags)) {
    $value = "";
  }
  return $value;
}, 10, 2);

//object->creator
add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'creator', function($value, \WPCHAOSObject $object) {
  $creators = $object->metadata(
    array(WPDKAObject::DKA2_SCHEMA_GUID, WPDKAObject::DKA_SCHEMA_GUID),
    array('/dka2:DKA/dka2:Creators/dka2:Creator','/DKA/Creator/Person'),
    null
    );
  return $value . WPDKAObject::get_creator_attributes($creators);
}, 10, 2);

//object->contributor
add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'contributor', function($value, \WPCHAOSObject $object) {
  $contributors = $object->metadata(
    array(WPDKAObject::DKA2_SCHEMA_GUID, WPDKAObject::DKA_SCHEMA_GUID),
    array('/dka2:DKA/dka2:Contributors/dka2:Contributor','/DKA/Contributor/Person'),
    null
    );
  return $value . WPDKAObject::get_creator_attributes($contributors);
}, 10, 2);

//object->organization_raw
add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'organization_raw', function($value, \WPCHAOSObject $object) {
  $organization = $object->metadata(
    array(WPDKAObject::DKA2_SCHEMA_GUID, WPDKAObject::DKA_SCHEMA_GUID),
    array('/dka2:DKA/dka2:Organization/text()', '/DKA/Organization/text()')
    );
  return $value . $organization;
}, 10, 2);

//object->organization
add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'organization', function($value, \WPCHAOSObject $object) {
  $organizations = WPDKASearch::get_organizations();
  $organization = $object->organization_raw;

  if(isset($organizations[$organization]))
    $organization = $organizations[$organization]['title'];

  return $value . $organization;
}, 10, 2);

//object->organization_link
add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'organization_link', function($value, \WPCHAOSObject $object) {
  $organizations = WPDKASearch::get_organizations();
  $organization = $object->organization_raw;

  if(isset($organizations[$organization])) {
    $value .= get_permalink($organizations[$organization]['id']);
  } else {
    $value .= get_permalink(get_option('wpdka-default-organization-page'));
  }

  return $value;
}, 10, 2);

//object->description
add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'description', function($value, \WPCHAOSObject $object) {
  require('htmlcleaner.php');

  $value .= $object->metadata(
    array(WPDKAObject::DKA2_SCHEMA_GUID, WPDKAObject::DKA_SCHEMA_GUID),
    array('/dka2:DKA/dka2:Description', '/DKA/Description/text()')
    );
  // Does $value contain any html
  if (!strcmp( $value, strip_tags($value))) {
    $doc = new DOMDocument();
    $doc->loadHTML('<?xml encoding="UTF-8">' . $value);
    $doc->encoding = 'UTF-8';
    return htmlcleaner_clean($doc);
  }

  return $value;
}, 10, 2);

//object->unpublishedByCurator
add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'unpublishedByCurator', function($value, \WPCHAOSObject $object) {
  return $object->metadata(
    array(WPDKAObject::DKA2_SCHEMA_GUID),
    array('/dka2:DKA/dka2:unpublishedByCurator')
  );
}, 10, 2);

//object->isPublished
add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'isPublished', function($value, \WPCHAOSObject $object) {
  foreach ($object->AccessPoints as $a) {
    if ($a->AccessPointGUID == strtolower(get_option('wpchaos-accesspoint-guid')) && !empty($a->StartDate)) {
      return true;
    }
  }
  return false;
}, 10, 2);

//object->hasDKA2MetaDataSchema
add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'hasDKA2MetaDataSchema', function($value, \WPCHAOSObject $object) {
  return $object->has_metadata(WPDKAObject::DKA2_SCHEMA_GUID);
}, 10, 2);

//object->published
add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'published', function($value, \WPCHAOSObject $object) {
  $time = $object->metadata(WPDKAObject::DKA2_SCHEMA_GUID, '/dka2:DKA/dka2:FirstPublishedDate/text()');

  if($time) {
    $time = strtotime($time);
    // Add the gmt offeset - this is what get_date_from_gmt does anyway
    // @see https://codex.wordpress.org/Function_Reference/get_date_from_gmt
    $time += get_option('gmt_offset') * 60 * 60; // * 60 * 60 sec/hour
    //If january 1st, only print year, else get format from WordPress
    if(date("d-m", $time) == "01-01") {
      $time = __('Year ', 'wpdka').date_i18n('Y', $time);
    } else {
      $time = date_i18n(get_option('date_format'), $time);
    }
  }

  return $value . $time;
}, 10, 2);

//object->rights
add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'rights', function($value, $object) {
  $value .= $object->metadata(
    array(WPDKAObject::DKA2_SCHEMA_GUID, WPDKAObject::DKA_SCHEMA_GUID),
    array('/dka2:DKA/dka2:RightsDescription/text()', '/DKA/RightsDescription/text()')
    );
  return WPDKAObject::replace_url_with_link($value);
}, 10, 2);

//object->type
add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'type', function($value, \WPCHAOSObject $object) {
  $value .= WPDKAObject::determine_type($object);
  return $value;
}, 10, 2);

//object->type_class
add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'type_class', function($value, \WPCHAOSObject $object) {
  $type = $object->type;
  return $value . (isset(WPDKAObject::$format_types[$type]) ? WPDKAObject::$format_types[$type]['class'] : $type);
}, 10, 2);

//object->type_title
add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'type_title', function($value, \WPCHAOSObject $object) {
  $type = $object->type;
  return $value . (isset(WPDKAObject::$format_types[$type]) ? WPDKAObject::$format_types[$type]['title'] : $type);
}, 10, 2);

//object->thumbnail
add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'thumbnail', function($value, \WPCHAOSObject $object) {
  foreach($object->Files as $file) {
    // FormatID = 10 is thumbnail format. This is what we want here
    if($file->FormatID == 10) {
      return $value . htmlspecialchars($file->URL);
    }
  }

  //Fallback to theme images
  if($object->type != WPDKAObject::TYPE_UNKNOWN) {
    return get_stylesheet_directory_uri().'/img/format-'.$object->type.'.png';
  }
      // Try another image - any image will do.
      // TODO: Consider using a serverside cache and downscaling service.
      /*
      foreach($object->Files as $file) {
        // FormatID = 10 is thumbnail format. This is what we want here
        if($file->FormatType == "Image") {
          return $value . htmlspecialchars($file->URL);
        }
      }
      */
  // Fallback to nothing
  return null;
}, 10, 2);

//object->slug
add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'slug', function($value, \WPCHAOSObject $object) {
  return $value . $object->metadata(WPDKAObject::DKA_CROWD_SCHEMA_GUID, '/dkac:DKACrowd/dkac:Slug/text()');
}, 10, 2);

//object->url
add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'url', function($value, \WPCHAOSObject $object) {
  if($object->slug) {
    return $object->organization_link . $object->slug . '/' . $value;
  } else {
    return $object->organization_link . $value . '?guid=' . $object->GUID;
  }
}, 10, 2);

//object->externalurl
add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'externalurl', function($value, \WPCHAOSObject $object) {
  return $value . $object->metadata(
    array(WPDKAObject::DKA2_SCHEMA_GUID),
    array('/dka2:DKA/dka2:ExternalURL/text()')
    );
}, 10, 2);

//object->views
add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'views', function($value, $object) {
  return $value . $object->metadata(WPDKAObject::DKA_CROWD_SCHEMA_GUID, '/dkac:DKACrowd/dkac:Views/text()');
}, 10, 2);

//object->shares
add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'shares', function($value, $object) {
  return $value . $object->metadata(WPDKAObject::DKA_CROWD_SCHEMA_GUID, '/dkac:DKACrowd/dkac:Shares/text()');
}, 10, 2);

//object->likes
add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'likes', function($value, $object) {
  return $value . $object->metadata(WPDKAObject::DKA_CROWD_SCHEMA_GUID, '/dkac:DKACrowd/dkac:Likes/text()');
}, 10, 2);

//object->ratings
add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'ratings', function($value, $object) {
  return $value . $object->metadata(WPDKAObject::DKA_CROWD_SCHEMA_GUID, '/dkac:DKACrowd/dkac:Ratings/text()');
}, 10, 2);

//object->accumulatedrate
add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'accumulatedrate', function($value, $object) {
  return $value . $object->metadata(WPDKAObject::DKA_CROWD_SCHEMA_GUID, '/dkac:DKACrowd/dkac:AccumulatedRate/text()');
}, 10, 2);

//object->caption
add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'caption', function($value, $object) {
  if($object->type == WPDKAObject::TYPE_IMAGE || $object->type == WPDKAObject::TYPE_IMAGE_AUDIO) {
    $realImages = 0;
    foreach(WPChaosClient::get_object()->Files as $file) {
      if($file->FormatType == 'Image' && $file->FormatCategory == 'Image Source') {
        $realImages++;
      }
    }
    return $value . sprintf(_n('%s image', '%s images', $realImages,'wpdka'),$realImages);
  } else {
    return $value;
  }
}, 10, 2);

//object->is_embeddable
add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'is_embeddable', function($value, $object) {
	// Pages who are allowed to embed.
	$pages = array('danskkulturarv.dk', 'dr.dk', 'lilleverden.dk', 'lazyspinningsparrow.dk');

	if (isset($_SERVER['HTTP_REFERER'])) {
	    $ar = parse_url($_SERVER['HTTP_REFERER']);
	    // Remove http(s)://www. from urls to compare easier.
	    $ar = preg_replace('/(?:https?:\/\/)?(?:www\.)?(.*)\/?$/i', '$1', $ar);
	    if (isset($ar['host']) && in_array($ar['host'], $pages)) {
	    	return true;
	    } else {
	    	return false;
	    }
	}

	return true; // If HTTP_REFERER is not set, it will return true and let JavaScript handle an iFrame error.
}, 10, 2);

//object->embed
add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'embed', function($value, $object) {
  // Replace æøå when printing html iFrame.
  $url = str_replace('ø', '%C3%B8', str_replace('æ', '%C3%A6', str_replace('å', '%C3%A5', $object->url)));
  return '<iframe src="'.rtrim($url, '/').'/embed" frameborder="0" allowfullscreen width="480" height="360"></iframe>';
}, 10, 2);

//object->og_tags
add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'og_tags', function($value, $object) {

  $metadatas = array();

  $description = WPDKAObject::word_limit($object->description);

  $metadatas['fb:app_id'] = array(
      'name' => 'fb:app_id',
      'content' => '503595753002055',
  );
  
  $metadatas['description'] = array(
    'name' => 'description',
    'content' => $description
  );
  $metadatas['og:title'] = array(
    'property' => 'og:title',
    'content' => $object->title
  );
  $metadatas['og:description'] = array(
    'property' => 'og:description',
    'content' => $description
  );
  $metadatas['og:type'] = array(
    'property' => 'og:type',
    'content' => WPDKAObject::$format_types[$object->type]['chaos-value']
  );
  $metadatas['og:url'] = array(
    'property' => 'og:url',
    'content' => $object->url
  );
  $metadatas['og:image'] = array(
    'property' => 'og:image',
    'content' => $object->thumbnail
  );

  if ($object->type === WPDKAObject::TYPE_VIDEO) {
  	$metadatas['og:video'] = array(
  		'property' => 'og:video',
  		'content' => $object->Files[0]->URL
  	);
  	$metadatas['og:video:secure_url'] = array(
  		'property' => 'og:video:secure_url',
  		'content' => $object->Files[0]->URL
  	);
  }
  // } elseif($object->type == WPDKAObject::TYPE_AUDIO) {
  // 	$metadatas['og:audio'] = array(
  // 		'property' => 'og:image',
  // 		'content' => $object->thumbnail
  // 	);
  // }

  return $metadatas;
}, 10, 2);

//object->metafield_creators_raw
add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'metafield_creators_raw', function($value, \WPCHAOSObject $object) {
  $metafields = $object->metadata(
    array(WPDKAObject::DKA2_SCHEMA_GUID, WPDKAObject::DKA_SCHEMA_GUID),
    array('/dka2:DKA/dka2:Metafield','/DKA/Metafield'),
    null
  );

  if (!$metafields) { return null; }

  foreach($metafields as $field) {
    if ($field->Key == "CreatorsRaw") {
      return $field->Value;
    }
  }
  return null;
}, 10, 2);

//object->metafield_contributor_raw
add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'metafield_contributor_raw', function($value, \WPCHAOSObject $object) {
  $metafields = $object->metadata(
    array(WPDKAObject::DKA2_SCHEMA_GUID, WPDKAObject::DKA_SCHEMA_GUID),
    array('/dka2:DKA/dka2:Metafield','/DKA/Metafield'),
    null
  );

  if (!$metafields) { return null; }

  foreach($metafields as $field) {
    if ($field->Key == "ActorsRaw") {
      return $field->Value;
    }
  }
  return null;
}, 10, 2);

//
