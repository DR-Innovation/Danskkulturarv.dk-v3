<?php
/*
Plugin Name: WP DKA Custom Dashboard
Description: Mere clean dashboard + theme instruktioner
Version: 1.0
Author: Andreas Larsen
Author URI: http://socialsquare.dk
License: MIT
*/


function remove_dashboard_meta() {
  remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
  remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
  remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
  remove_meta_box( 'dashboard_activity', 'dashboard', 'normal');
  remove_action('welcome_panel', 'wp_welcome_panel');
}
add_action( 'admin_init', 'remove_dashboard_meta' );


function theme_instructions_dashboard_widgets() {

  wp_add_dashboard_widget(
                 'theme_instructions',         // Widget slug.
                 'Vejledning: Tilføj nyt parallax element til forside',         // Title.
                 'theme_instructions_widget_function' // Display function.
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
