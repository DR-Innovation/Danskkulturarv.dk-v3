<?php

// Registering namespaces.
\CHAOS\Portal\Client\Data\Object::registerXMLNamespace('dkac', 'http://www.danskkulturarv.dk/DKA-Collection.xsd');

//Uses fallback to old scheme

//collection->title
add_filter(WPDKACollections::OBJECT_FILTER_PREFIX.'title', function($value, \WPCHAOSObject $object) {
	$value .= $object->metadata(
		array(WPDKACollections::METADATA_SCHEMA_GUID, WPDKACollections::METADATA_SCHEMA_GUID),
		array('/dkac:Collection/dkac:Title/text()','/Collection/Title/text()')
	);
	if($value == "") {
		$value = __('No title',WPDKACollections::DOMAIN);
	}
	return $value;
}, 10, 2);

//collection->description
add_filter(WPDKACollections::OBJECT_FILTER_PREFIX.'description', function($value, \WPCHAOSObject $object) {
	$value .= $object->metadata(
		array(WPDKACollections::METADATA_SCHEMA_GUID, WPDKACollections::METADATA_SCHEMA_GUID),
		array('/dkac:Collection/dkac:Description/text()','/Collection/Description/text()',)
	);
	return $value;
}, 10, 2);

//collection->rights
add_filter(WPDKACollections::OBJECT_FILTER_PREFIX.'rights', function($value, \WPCHAOSObject $object) {
	$value .= $object->metadata(
		array(WPDKACollections::METADATA_SCHEMA_GUID),
		array('/dkac:Collection/dkac:Rights/text()')
	);
	return $value;
}, 10, 2);

//collection->type
add_filter(WPDKACollections::OBJECT_FILTER_PREFIX.'type', function($value, \WPCHAOSObject $object) {
	$value .= $object->metadata(
		array(WPDKACollections::METADATA_SCHEMA_GUID),
		array('/dkac:Collection/dkac:Type/text()')
	);
	if(isset(WPDKACollections::$types[$value]))
		$value = WPDKACollections::$types[$value];
	return $value;
}, 10, 2);

//collection->playlist_raw
add_filter(WPDKACollections::OBJECT_FILTER_PREFIX.'playlist_raw', function($value, \WPCHAOSObject $object) {
	$value = $object->metadata(
		array(WPDKACollections::METADATA_SCHEMA_GUID),
		array('/dkac:Collection/dkac:Playlist/dkac:Object'),
		null
	);
	return $value?:array();
}, 10, 2);

//collection->playlist
add_filter(WPDKACollections::OBJECT_FILTER_PREFIX.'playlist', function($value, \WPCHAOSObject $object) {
	$value = $object->metadata(
		array(WPDKACollections::METADATA_SCHEMA_GUID),
		array('/dkac:Collection/dkac:Playlist/dkac:Object'),
		null
	);

    //Get the related objects to the current collection.
    $serviceResult2 = WPChaosClient::instance()->Object()->Get(
        "(GUID:(".implode(" OR ", $value)."))",   // Search query
        null,   // Sort
        null,   // AP injected
        0,      // pageIndex
        count($value), // pageSize
        true,   // includeMetadata
        false,   // includeFiles
        false    // includeObjectRelations
    );

    $result3 = array();
    foreach($serviceResult2->MCM()->Results() as $result) {
    	$result3[$result->GUID] = new WPChaosObject($result);
    }

    //Set items in proper order
    foreach($value as $guid) {
    	$items[] = $result3[(string)$guid];
    }

    return $items;
}, 10, 2);

//collection->status
add_filter(WPDKACollections::OBJECT_FILTER_PREFIX.'status', function($value, \WPCHAOSObject $object) {
	$value .= $object->metadata(
		array(WPDKACollections::METADATA_SCHEMA_GUID),
		array('/dkac:Collection/dkac:Status/text()')
	);
	if(isset(WPDKACollections::$states[$value]))
		$value = WPDKACollections::$states[$value];

	return $value;
}, 10, 2);

//object->collections
add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'collections', function($value, \WPCHAOSObject $object) {

	$collections = WPDKACollections::get_material_collections($object);
	$return = '<div class="panel-group" id="collectionDiv"><div class="panel panel-default">';
	foreach($collections as $collection) {
		$return .= '<h4><a data-toggle="collapse" data-target="#collection-'.$collection->GUID.'">'.$collection->title.'</a></h4>';
		$return .= '<div id="collection-'.$collection->GUID.'" class="collapse">';
		$return .= '<ul class="media-list"><hr>';
		$count = 0;
		foreach ($collection->playlist as $material) {
			$count++;
			$thumbnail = ($material->thumbnail ? ' style="background-image: url(\'' . $material->thumbnail . '\')!important;"' : '');
			$return .= '<li class="media" onClick="location.href=\'' . $material->url . '#' . $collection->GUID . '\'">';
			//$return .= '<a class="fill-div" href="#"></a>';
			// Have to focus this element in the collection. 
			if ($material->GUID == $object->GUID) {
				$return .= '<div class="media-body">';
				$return .= '<h4 class="media-heading"><span class="collectionCount">' . $count . '.</span> ' . $material->title . '</h4>';
				if (!empty($thumbnail))
					$return .= '<div class="media-object thumb format-' . $material->type . '" id="collection_object"' . $thumbnail . '></div>';
				$return .= $material->description; // Should be excerpt.
				$return .= '</div>';
			} else {
				$return .= '<div class="media-body">';
				$return .= '<h4 class="media-heading"><span class="collectionCount">' . $count . '.</span> ' . $material->title . '</h4>';
				$return .= '</div>';
			}
			$return .= '</li><hr>';
		}
		$return .= '</ul>';
		$return .= '</div>';
	}
	$return .= '</div></div>';

	return $return;
}, 10, 2);

//