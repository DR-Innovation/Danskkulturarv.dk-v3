<?php

// Registering namespaces.
\CHAOS\Portal\Client\Data\Object::registerXMLNamespace('dkac', 'http://www.danskkulturarv.dk/DKA-Collection.xsd');

//object->title
add_filter(WPDKACollections::OBJECT_FILTER_PREFIX.'title', function($value, \WPCHAOSObject $object) {
	$value .= $object->metadata(
		array(WPDKACollections::METADATA_SCHEMA_GUID),
		array('/Collection/Title/text()')
	);
	if($value == "") {
		$value = __('No title',WPDKACollections::DOMAIN);
	}
	return $value;
}, 10, 2);

//object->description
add_filter(WPDKACollections::OBJECT_FILTER_PREFIX.'description', function($value, \WPCHAOSObject $object) {
	$value .= $object->metadata(
		array(WPDKACollections::METADATA_SCHEMA_GUID),
		array('/Collection/Description/text()')
	);
	return $value;
}, 10, 2);

//object->rights
add_filter(WPDKACollections::OBJECT_FILTER_PREFIX.'rights', function($value, \WPCHAOSObject $object) {
	$value .= $object->metadata(
		array(WPDKACollections::METADATA_SCHEMA_GUID),
		array('/Collection/Rights/text()')
	);
	return $value;
}, 10, 2);

//object->type
add_filter(WPDKACollections::OBJECT_FILTER_PREFIX.'type', function($value, \WPCHAOSObject $object) {
	$value .= $object->metadata(
		array(WPDKACollections::METADATA_SCHEMA_GUID),
		array('/Collection/Type/text()')
	);
	return $value;
}, 10, 2);

//object->playlist
add_filter(WPDKACollections::OBJECT_FILTER_PREFIX.'playlist', function($value, \WPCHAOSObject $object) {
	$value .= $object->metadata(
		array(WPDKACollections::METADATA_SCHEMA_GUID),
		array('/Collection/Playlist/text()')
	);
	return $value;
}, 10, 2);

//object->status
add_filter(WPDKACollections::OBJECT_FILTER_PREFIX.'status', function($value, \WPCHAOSObject $object) {
	$value .= $object->metadata(
		array(WPDKACollections::METADATA_SCHEMA_GUID),
		array('/Collection/Status/text()')
	);
	return $value;
}, 10, 2);

//