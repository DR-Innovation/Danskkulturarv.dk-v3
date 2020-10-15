<?php

//object->usertags_raw
add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'usertags_raw', function($value, \WPChaosDataObject $object) {
		$relation_guids = array();
		foreach($object->ObjectRelations as $relation) {
			$guid_property = "Object2GUID"; //tag is always saved here.
			//Because we know which relation is tag, we can safely
			//skip the relations where GUID == Object2GUID
			if($object->GUID == $relation->{$guid_property}) {
				continue;
			}
			$relation_guids[] = $relation->{$guid_property};
		}
		$tags = array();
		if(!empty($relation_guids)) {
			try {
				//+AND+(!".WPDKATags::FACET_KEY_STATUS.":".WPDKATags::TAG_STATE_FLAGGED.")
				// $serviceResult = WPChaosClient::instance()->Object()->Get(
				// 	"(GUID:(".implode(" OR ", $relation_guids).") AND (ObjectTypeID:".WPDKATags::TAG_TYPE_ID."))",   // Search query
				// 	null,   // Sort
				// 	false,   // Use session instead of AP
				// 	0,      // pageIndex
				// 	count($relation_guids),      // pageSize
				// 	true,   // includeMetadata
				// 	false,   // includeFiles
				// 	false    // includeObjectRelations
				// );

				//Query will quickly become too long for GET. Using POST instead to handle more data
				$serviceResult = WPChaosClient::instance()->CallService("Object/Get", CHAOS\Portal\Client\IServiceCaller::POST, array(
					"query" => "(GUID:(".implode(" OR ", $relation_guids).") AND (ObjectTypeID:".WPDKATags::TAG_TYPE_ID.") AND (FolderID:".WPDKATags::TAGS_FOLDER_ID."))",
					"sort" => null,
					"accessPointGUID" => false,
					"includeMetadata" => true,
					"includeFiles" => false,
					"includeObjectRelations" => false,
					"includeAccessPoints" => false,
					"pageIndex" => 0,
					"pageSize" => count($relation_guids)));
				$tags = WPChaosDataObject::parseResponse($serviceResult);
			} catch(\CHAOSException $e) {
				error_log('CHAOS Error when getting user tags for object: '.$e->getMessage());
			}
		}

		return $tags;
	},10,2);

//object->usertags
add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'usertags', function ($value, \WPChaosDataObject $object) {

		$status = intval(get_option('wpdkatags-status',0));

		//if(current_user_can(WPDKATags::CAPABILITY)) {
			wp_enqueue_script('dka-usertags-taggable',plugins_url( 'js/taggable.js' , __FILE__ ),array('jquery'),'1.0',true);
			$translation_array = array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'token' => wp_create_nonce(WPDKATags::TOKEN_PREFIX.$object->GUID)
				);
			wp_localize_script( 'dka-usertags-taggable', 'WPDKATags_taggable', $translation_array );
		//}

		//iff status == active or frozen
		if($status > 0 && $object->taggable) {
			$tags = $object->usertags_raw;

			$value .= '<div class="usertags">';
			foreach($tags as $key => $tag) {
				//Get tag XML meta
				$tag_meta = $tag->metadata(
					array(WPDKATags::METADATA_SCHEMA_GUID),
					array(''),
					null
				);
				//We do not want flagged tags
				if($tag_meta['status'] == WPDKATags::TAG_STATE_FLAGGED) {
					unset($tags[$key]);
					continue;
				}
				$link = WPChaosSearch::generate_pretty_search_url(array(WPChaosSearch::QUERY_KEY_FREETEXT => $tag_meta));
				$flag = ($tag_meta['status'] == WPDKATags::TAG_STATE_UNAPPROVED ? '<i class="icon-remove flag-tag" id="'.$tag->GUID.'"></i>' : '');
				$value .= '<a class="usertag tag" href="'.$link.'" title="'.esc_attr($tag_meta).'">'.$tag_meta.$flag.'</a>'."\n";
			}
			if(empty($tags)) {
				$value .= '<div class="alert alert-info">'.__('No user tags',WPDKATags::DOMAIN).'</div>'."\n";
			}
			$value .= '</div>';

			//Iff status == active
			if($status == 2) {

				$value .= '<div class="input-group">';
				$value .= '<input type="text" class="form-control" id="usertag-add" value="">';
				$value .= '<span class="input-group-btn"><button class="btn btn-primary" type="button" id="usertag-submit">'.__('Add tag',WPDKATags::DOMAIN).'</button></span>';
				$value .= '</div>';

				wp_enqueue_script('dka-usertags',plugins_url( 'js/functions.js' , __FILE__ ),array('jquery'),'1.0',true);
				$translation_array = array(
					'confirmTitle' => __('Confirm flagging',WPDKATags::DOMAIN),
					'confirmBody' => __('Are you sure you want to flag this tag?',WPDKATags::DOMAIN),
					'yes' => __('Yes'),
					'no' => __('No'),
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'token' => wp_create_nonce(WPDKATags::TOKEN_PREFIX.$object->GUID)
					);
				wp_localize_script( 'dka-usertags', 'WPDKATags', $translation_array );
			}
		} else {
			$value = '<div class="usertags"><div class="alert alert-warning">'.__('User tagging has been disabled for this material',WPDKATags::DOMAIN).'</div></div>'."\n";
		}
		return $value;
	},10,2);

//object->taggable
add_filter(WPChaosClient::OBJECT_FILTER_PREFIX.'taggable', function ($value, \WPChaosDataObject $object) {

	$value = $object->metadata(WPDKAObject::DKA_CROWD_SCHEMA_GUID, '/dkac:DKACrowd/dkac:Taggable/text()');

	//Taggable if 'true' or 'null'
	if($value != 'false') {
		$value = true;
	} else {
		$value = false;
	}

	return $value;
},10,2);

/**/
