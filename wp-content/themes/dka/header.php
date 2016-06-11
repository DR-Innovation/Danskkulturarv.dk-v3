<?php
/**
 */
?><!DOCTYPE html>
<!--[if IE 7 ]><html class="ie7" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 8 ]><html class="ie8" <?php language_attributes(); ?>> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html <?php language_attributes(); ?>><!--<![endif]-->
<head prefix="og: http://ogp.me/ns#">
  <meta charset="<?php bloginfo('charset'); ?>" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="apple-mobile-web-app-capable" content="yes"/>
  <title><?php wp_title('|', true, 'right'); ?></title>
  <link rel="profile" href="http://gmpg.org/xfn/11" />
  <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
  <?php wp_head(); ?>
</head>
<body <?php body_class() ?>>
  <!-- start wrapper (for page content to push down sticky footer) -->
  <div id="wrap">

    <!-- start navigation -->
    <nav class="navbar navbar-fixed-top">
      <div class="container-fluid">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-responsive-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="<?php echo esc_url(home_url('/')); ?>" title="<?php echo esc_attr(get_bloginfo('name', 'display')); ?>" rel="home">Dansk Kulturarv</a>
        </div>
        <div class="collapse navbar-collapse navbar-responsive-collapse">
          <?php
              wp_nav_menu(array(
                  'theme_location' => 'primary',
                  'depth' => 3,
                  'container' => false,
                  'menu_class' => 'nav navbar-nav navbar-right',
                  'fallback_cb' => false,
                  'walker' => new wp_bootstrap_navwalker(), )
              );
          ?>
        </div><!--/.nav-collapse -->
      </div>
      <!--[if IE]>
        <div class="alert alert-warning alert-dismissible text-center" id="old-browser-btn" role="alert">
          <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          Din gamle browser kan desværre ikke understøttes. <a href="<?php echo esc_url(home_url('/understottelse')); ?>">Mere information.</a>
        </div>
      <![endif]-->
    </nav><!-- end navigation -->
