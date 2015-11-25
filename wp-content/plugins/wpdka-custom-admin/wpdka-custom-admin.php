<?php
/*
Plugin Name: WP DKA Custom Dashboard
Description: Mere clean dashboard + theme instruktioner
Version: 1.0
Author: Andreas Larsen
Author URI: http://socialsquare.dk
License: MIT
*/


/************
Admin cleanup
************/

add_action('admin_head', 'custom_admin_style');
function custom_admin_style()
{
    echo '<style>
   #menu-comments, 
   #menu-posts {
     display: none;
   }
   #activity-widget #latest-comments {
     display: none;
   }
   #collapse-menu {
     display: none;
   }
   #adminmenu li.wp-menu-separator {
     height: 14px;
   }
 </style>';
}

// Admin bar cleanup
add_action('wp_before_admin_bar_render', 'remove_admin_bar_links');
function remove_admin_bar_links()
{
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu('wp-logo');          // Remove the WordPress logo
    $wp_admin_bar->remove_menu('about');            // Remove the about WordPress link
    $wp_admin_bar->remove_menu('wporg');            // Remove the WordPress.org link
    $wp_admin_bar->remove_menu('documentation');    // Remove the WordPress documentation link
    $wp_admin_bar->remove_menu('support-forums');   // Remove the support forums link
    $wp_admin_bar->remove_menu('feedback');         // Remove the feedback link
    $wp_admin_bar->remove_menu('updates');          // Remove the updates link
    $wp_admin_bar->remove_menu('comments');         // Remove the comments link
}

// Dashboard cleanup
remove_action('welcome_panel', 'wp_welcome_panel');
add_action('wp_dashboard_setup', 'my_custom_dashboard_widgets');
function my_custom_dashboard_widgets()
{
    global $wp_meta_boxes;
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_recent_drafts']);
}

/******************
Parallax Vejledning
******************/

function theme_instructions_dashboard_widgets() {

  wp_add_dashboard_widget(
                 'theme_instructions',
                 'Vejledning: Tilføj nyt parallax element til forside',
                 'theme_instructions_widget_function'
        );
}
add_action( 'wp_dashboard_setup', 'theme_instructions_dashboard_widgets' );

function theme_instructions_widget_function() {
  echo "
  <style>ul > ul {padding-left: 1em}ul > li{padding:3px 0}ul > ul > li{padding:0}</style>
  <h2>1</h2>
  <ul>
    <li>
      Hover over <b>Parallax Scroll</b> i menuen og klik <b>Add New</b>
    </li>
    <li>
      Indtast titel og tekst
    </li>
    <li>
      Klik på <b>Vælg udvalgt billede</b> til højre og vælg/upload et billede
    </li>
    <li>
      Vælg <u>kun</u> følgende <b>Parallax Scroll Options</b>
    </li>
    <ul>
      <li>
        Parallax Height: 0
      </li>
      <li>
        Parallax Image Size: 0
      </li>
      <li>
        Horizontal Position: Centre
      </li>
      <li>
        Vertical Position: Middle
      </li>
    </ul>
  </ul>
  <h2>2</h2>
  <ul>
    <li>
      Klik <b>Udgiv</b>
    </li>
    <li>
      Klik <b>Parallax Scroll</b> i menuen til venstre
    </li>
    <li>
      Kopier <b>Shortcode</b> - på det nyoprettede element fx <code>[parallax-scroll id=</b>665</b>]</code>
    </li>
  </ul>
  <h2>3</h2>
  <ul>
    <li>
      Rediger forsiden
    </li>
    <li>
      Rediger den første widget, indsæt den kopierede shortcode og <b>Opdater</b> siden
    </li>
  </ul>
  <hr>
  <h2 style=\"color:#666\">NB</h2>
  <ul style=\"color:#666\">
    <li>
      Du kan også indsætte shortcode fx <code>[parallax-scroll id=</b>665</b>]</code> på almindelige sider.
    </li>
    <li>
      Du kan redigere i eksisterende parallax elementer under <b>Parallax Scroll</b>
    </li>
  </ul>
  </span>
  ";
}

?>
