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
                 'Tilføje parallax element til forside',         // Title.
                 'theme_instructions_widget_function' // Display function.
        );
}
add_action( 'wp_dashboard_setup', 'theme_instructions_dashboard_widgets' );

function theme_instructions_widget_function() {
  echo "
  <style>ul > ul {padding-left: 1em}ul > li{padding:3px 0}ul > ul > li{padding:0}</style>
  <ul>
    <li>
      Hover over \"Parallax Scroll\" til venstre og klik \"Add New\"
    </li>
    <li>
      Indtast titel og tekst
    </li>
    <li>
      Klik på \"Vælg udvalgt billede\" til højre og vælg/upload et billede
    </li>
    <li>
      <b>Vigtigt: </b>Vælg kun følgende \"Parallax Scroll Options\"
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
    <li>
      Klik \"Udgiv\"
    </li>
    <li>
      Klik \"Parallax Scroll\" i menuen til venstre
    </li>
    <li>
      Kopier \"Shortcode\" - fx <code>[parallax-scroll id=\"665\"]</code>
    </li>
    <li>
      Rediger forsiden*
    </li>
    <li>
      Rediger den første \"Tekst\" widget, indsæt den kopierede shortcode og \"Opdater\" siden
    </li>
  </ul>
  <p>
  *Gå til danskkulturarv.dk og tryk \"Rediger Side\" i top bar
  </p>
  <hr>
  <span style=\"color:#999\">
  <ul>
    <li>
      Du kan også indsætte <code>[parallax-scroll id=\"665\"]</code> på almindelige sider.
    </li>
    <li>
      Du kan redigere i eksisterende parallax elementer under \"Parallax Scroll\"
    </li>
  </ul>
  </span>
  ";
}

?>
