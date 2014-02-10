<?php
/**
 * @package WP Chaos Client
 * @version 1.0
 */
?>
<!DOCTYPE html>
<!--[if lt IE 7 ]><html class="ie6" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 7 ]><html class="ie7" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 8 ]><html class="ie8" <?php language_attributes(); ?>> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html <?php language_attributes(); ?>><!--<![endif]-->
<head prefix="og: http://ogp.me/ns#">
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta name="apple-mobile-web-app-capable" content="yes"/>
	<title><?php wp_title( '|', true, 'right' ); ?></title>
	<link rel="profile" href="http://gmpg.org/xfn/11" />
	<?php wp_head(); ?>
</head>
<body>
<?php the_widget('WPDKAObjectPlayerWidget'); ?>
<?php wp_footer(); ?>
</body>
</html>
