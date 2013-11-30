<?php
/**
 * @package WP DKA
 * @version 1.0
 */


/**
 * Player shortcode
 * @param string $id The slug/GUID of the wanted material
 */
add_shortcode( 'chaos-player', function($atts, $content = null) {
	extract(shortcode_atts( array(
			'id' => '',
			'autoplay' => false
	), $atts ));

	if($id) {
		//Does id match guid pattern?
		if (preg_match('/^\{?[a-f\d]{8}-(?:[a-f\d]{4}-){3}[a-f\d]{12}\}?$/i', $id)) {
			$query = 'GUID:'.$id;
		} else {
			$query = WPDKAObject::DKA_CROWD_SLUG_SOLR_FIELD. ':'. $id;
		}
	} else {
		return "Shortcode chaos-player needs an id!";
	}

	try {
		$serviceResult = WPChaosClient::instance()->Object()->Get(
			"(".$query.")",   // Search query
			null,   // Sort
			null,   
			0,      // pageIndex
			1,      // pageSize
			true,   // includeMetadata
			true,   // includeFiles
			true    // includeObjectRelations
		);
	} catch(\Exception $e) {
		return "Error in CHAOS.";
	}

	if($serviceResult->MCM()->Count() > 0) {
		$object = WPChaosObject::parseResponse($serviceResult);
		//Set global obj to use templates
		WPChaosClient::set_object($object[0]);		
	} else {
		return "Could not find any object with ID ".$id;
	}

	$type = WPChaosClient::get_object()->type;
	
	//Look in theme dir and include if found
	$jwplayer_autostart = $autoplay;
	ob_start();
	if(locate_template('chaos-player-'.$type, true) == "") {
		include(dirname(__FILE__)."/templates/player-".$type.".php");
	}

	$return = ob_get_contents();

	WPChaosClient::reset_object();
	ob_end_clean();

	return $return;

} );

/**
 * Date-constraint shortcode
 * @param string $at Specific date
 * @param string $from From date
 * @param string $to To date
 */
add_shortcode( 'date-constraint', function($atts, $content = null) {
	extract(shortcode_atts( array(
			'at' => '',
			'from' => '',
			'to' => ''
	), $atts ));

	//Check for constraints. If one is true, go on
	if(($constraint = $from || $to || $at)) {
		//If specific date
		if($at) {
			$constraint = $constraint && date('Ymd') == date('Ymd', strtotime($at));
		}
		//If from date
		if($from) {
			$constraint = $constraint && time() >= strtotime($from);
		}
		//If to date
		if($to) {
			$constraint = $constraint && time() <= strtotime($to);
		}
		//Check constraints
		if($constraint) {
			return do_shortcode($content);
		}
	} else {
		return "Shortcode date-constraint need constraints!";
	}

} );

//