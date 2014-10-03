<?php
function htmlCleaner_clean($node) {
	if (htmlCleaner_isNodeAllowed($node)) {
		$result = '<' . $node->tagName . ' ';
		foreach ($node->attributes as $attribute) {
			if (htmlCleaner_isAttributeAllowed($attribute)) {
				$result .= $attribute . '="' . $node->getAttribute($attribute) . '"';
			}
		}
		foreach ($node->children as $c) {
			$result .= clean($c);
		}

		$result = '</' . $node->tagName . '>';
	} else if (!isset($node->tagName)) {
		$result = $node->textContent;
	}
	return $result;
}

function htmlCleaner_isNodeAllowed($node) {
	$whitelist = array('p', 'a', 'i', 'strong', 'b', 'em', 'u', 'br');
	return isset($node->tagName) && in_array($node->tagName, $whitelist);
}

function htmlCleaner_isAttributeAllowed($node, $attribute) {
	$whitelist = array(
		'href' => function($value) { return strpos('http', $value); }
	);
	return in_array($attribute, array_keys($whitelist)) && $whitelist[$attribute]($node->getAttribute($attribute));
}
?>