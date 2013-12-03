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

//collection->playlist
add_filter(WPDKACollections::OBJECT_FILTER_PREFIX.'playlist_raw', function($value, \WPCHAOSObject $object) {
	$value = $object->metadata(
		array(WPDKACollections::METADATA_SCHEMA_GUID),
		array('/dkac:Collection/dkac:Playlist/dkac:Object'),
		null
	);
	return $value?:array();
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
		$return .= '<div id="collection-'.$collection->GUID.'" class="collapse in">';
		$return .= 'Hello';
		$return .= '</div>';
	}
	$return .= '</div></div>';

	return $return;
}, 10, 2);

//