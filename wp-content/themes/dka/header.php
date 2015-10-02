<?php
/**
 * @package WordPress
 * @subpackage DKA
 */
?><!DOCTYPE html>
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
  <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
  <?php wp_head(); ?>
</head>
<body>
  <!-- start wrapper (for page content to push down sticky footer) -->
  <div id="wrap">

    <!-- start navigation -->
    <nav class="navbar navbar-fixed-top">
      <div class="container-fluid">

        <!-- .navbar-toggle is used as the toggle for collapsed navbar content -->
            <div class="navbar-header">
              <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-responsive-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
              </button>

              <a class="navbar-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home">Dansk Kulturarv</a>

            </div>
        <div class="collapse navbar-collapse navbar-responsive-collapse">
<?php
    wp_nav_menu( array(
        'theme_location'       => 'primary',
        'depth'      => 3,
        'container'  => false,
        'menu_class' => 'nav navbar-nav navbar-right',
        'fallback_cb' => false,
        'walker' => new wp_bootstrap_navwalker())
    );
?>
        </div><!--/.nav-collapse -->
      </div>
      <!--[if IE]>
        <a class="btn btn-lg btn-warning" id="old-browser-btn" href="<?php echo esc_url( home_url( '/understottelse' ) ); ?>">Din gamle browser kan desværre ikke understøttes: Klik her for mere information.</a>
      <![endif]-->
    </nav><!-- end navigation -->
