<?php
/**
 * @package Widget for Social Page Feeds
 * @version 6.5.1
 */
/*
Plugin Name: Widget for Social Page Feeds
Plugin URI: https://patelmilap.wordpress.com/
Description: This widget adds a Simple Facebook Page Like Widget into your WordPress website sidebar within few minutes.
Author: Milap Patel
Version: 6.5.1
Author URI: https://patelmilap.wordpress.com/
Text Domain: facebook-pagelike-widget
*/

// Prevent direct access for security
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( ! class_exists( 'Facebook_Pagelike_widget' ) ) {

    class Facebook_Pagelike_widget {

        /**
         * Plugin version
         */
        const VERSION = '6.5.1';

        public function __construct() {

            // Define constants for URLs and Paths
            if( ! defined( 'FB_WIDGET_PLUGIN_URL' ) ) {
                define( 'FB_WIDGET_PLUGIN_URL' , plugin_dir_url( __FILE__ ) );
            }

            if( ! defined( 'FB_WIDGET_PLUGIN_BASE_URL' ) ) {
                define( 'FB_WIDGET_PLUGIN_BASE_URL' , plugin_dir_path( __FILE__ ) );
            }
            
            // Initialization
            $this->includes();
            
            // Hooks
            register_activation_hook( __FILE__ , array( $this, 'fb_widget_activate' ) );
            register_deactivation_hook( __FILE__ , array( $this, 'fb_widget_deactivate' ) );

            add_action( 'plugins_loaded', array( $this, 'LoadFbtextDomain' ) );
            add_action( 'wp_enqueue_scripts', array( $this,'fb_widget_enqueue_styles') );
            
            // Commented for now, will use it once settings page will be added
            //add_action( 'activated_plugin', array( $this, 'fb_widget_redirect' ) );
        }
        
        public function fb_widget_activate() {
            // Set a transient to handle the redirect after activation
            set_transient( 'fb_widget_do_activation_redirect', true, 30 );
        }
        
        public function fb_widget_deactivate() {
            // Clean up if necessary
            delete_transient( 'fb_widget_do_activation_redirect' );
        }
        
        /**
         * Redirects to the settings or welcome page upon activation
         */
        public function fb_widget_redirect( $plugin ) {
            if ( $plugin == plugin_basename( __FILE__ ) && get_transient( 'fb_widget_do_activation_redirect' ) ) {
                delete_transient( 'fb_widget_do_activation_redirect' );
                // Replace 'options-general.php' with your actual settings page slug
                wp_safe_redirect( admin_url( 'widgets.php' ) );
                exit;
            }
        }

        public function LoadFbtextDomain() {
            load_plugin_textdomain( 'facebook-pagelike-widget', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
        }

        function fb_widget_enqueue_styles() {
            // Check if the constant FB_WIDGET_PLUGIN_URL is defined (from fb_class.php)
            $plugin_url = defined('FB_WIDGET_PLUGIN_URL') ? FB_WIDGET_PLUGIN_URL : plugin_dir_url(__FILE__);
            
            wp_enqueue_style( 'fb-widget-frontend-style', $plugin_url . 'assets/css/style.css', array(), '1.0.0' );
        }

        public function includes() {
            require_once FB_WIDGET_PLUGIN_BASE_URL . 'fb_class.php';
            require_once FB_WIDGET_PLUGIN_BASE_URL . 'short_code.php';
            
            if ( is_admin() ) {
                include_once FB_WIDGET_PLUGIN_BASE_URL . 'admin/includes/add-review.php';
            }
        }
    }
}

// Initialize the plugin
new Facebook_Pagelike_widget();