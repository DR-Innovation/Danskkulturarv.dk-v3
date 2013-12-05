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

	$query = "GUID:".$value[0];

		//Get collection object
		$serviceResult = WPChaosClient::instance()->Object()->Get(
			$query,   // Search query
			null,   // Sort
			null,   // Use AP
			0,      // pageIndex
			1,      // pageSize
			true,   // includeMetadata
			false,   // includeFiles
			true    // includeObjectRelations
		);

		//Instantiate collection
		$collection = WPChaosObject::parseResponse($serviceResult,WPDKACollections::OBJECT_FILTER_PREFIX);
		$cur_collection = $collection[0];
		/*
		$relation_guids = $cur_collection->playlist_raw;
		var_dump($relation_guids);
        //Get the related objects to the collection.
        $serviceResult2 = WPChaosClient::instance()->Object()->Get(
            "(GUID:(".implode(" OR ", $relation_guids)."))",   // Search query
            null,   // Sort
            null,   // AP injected
            0,      // pageIndex
            count($relation_guids), // pageSize
            true,   // includeMetadata
            false,   // includeFiles
            false    // includeObjectRelations
        );

        $result3 = array();
        foreach($serviceResult2->MCM()->Results() as $result) {
        	$result3[$result->GUID] = new WPChaosObject($result);
        }

        //Set items in proper order
        foreach($relation_guids as $guid) {
        	$items[] = $result3[(string)$guid];
        }

        return $items;
        */
       
       return array();
}, 10, 2);

//collection->status
add_filter(WPDKACollections::OBJECT_FILTER_PREFIX.'status', function($value, \WPCHAOSObject $object) {
	$value .= $object->metadata(
		array(WPDKACollections::METADATA_SCHEMA_GUID),
		array('/dkac:Collection/dkac:Status/text()')
	);
	if($value == 'Draft') {
		$value = __('Draft',WPDKACollections::DOMAIN);
	} else if($value == 'Publish') {
		$value = __('Published',WPDKACollections::DOMAIN);
	}
	return $value;
}, 10, 2);

//object->collections
add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'collections', function($value, \WPCHAOSObject $object) {

	$collections = WPDKACollections::get_material_collections($object);
	$return = '<div class="panel-group"><div class="panel panel-default">';
	foreach($collections as $collection) {
		$return .= '<h4><a data-toggle="collapse" data-target="#collection-'.$collection->GUID.'">'.$collection->title.'</a></h4>';
		$return .= '<div id="collection-'.$collection->GUID.'" class="collapse">';
		$return .= '<ul class="media-list">';
		foreach ($collection->playlist as $material) {
			$thumbnail = ($material->thumbnail ? ' style="background-image: url(\'' . $material->thumbnail . '\')!important;"' : '');
			$return .= '<li class="media' . ($object->GUID == $material->GUID ? ' active' : '') . '">';
			$return .= '<div class="pull-left">';
			$return .= '<div class="media-object" id="collection_object"' . $thumbnail . '></div>';
			$return .= '</div><div class="media-body">';
			$return .= '<h4 class="media-heading">' . $material->title . '</h4>';
			$return .= $material->description;
			$return .= '</div>';
			$return .= '</li>';
		}
		$return .= '</ul>';
		$return .= '</div>';
	}
	$return .= '</div></div>';

	return $return;
}, 10, 2);

//