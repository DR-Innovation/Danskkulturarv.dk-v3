<?php
	require('/Users/madslundt/Library/Containers/com.bitnami.wordpress/Data/app-3_8_1/apache2/htdocs/BitBlueprint/DKA/wp-content/plugins/wpdka/htmlcleaner.php');
	$doc = new DOMDocument();
	$doc->loadHTML('<h1>This is a test</h1><br />Search with <a href="http://google.dk">Google</a>View the picture <img src="http://farm6.staticflickr.com/5453/7392699682_9210fe6a80_b.jpg" /><strong>Strong text</strong>');
	var_dump(htmlcleaner_clean($doc));
?>