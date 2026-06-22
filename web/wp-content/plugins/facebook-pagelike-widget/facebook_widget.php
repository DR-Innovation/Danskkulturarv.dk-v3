<?php

/**
 * Plugin Name: Buttonizer - Social Media Share Buttons, Social Icons, & Social Feeds
 * Plugin URI: https://buttonizer.io
 * Description: Floating Social Media Icons, Sticky Share Buttons, Facebook Feeds, & Popup builder. Also, create Call, Email, SMS, & Contact buttons to increase conversions. Supports WhatsApp, Messenger, Live Chat, and 40+ other actions.
 * Version: 7.0.2
 * Author: Buttonizer
 * Author URI: https://buttonizer.io
 * License: GPLv2
 *
 * SOFTWARE LICENSE INFORMATION
 *
 * Copyright (c) 2017 Buttonizer, all rights reserved.
 *
 * This file is part of Buttonizer
 *
 * For detailed information regarding to the licensing of
 * this software, please review the license.txt or visit:
 * https://buttonizer.io/license/
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =========================================================================
// Define Buttonizer constants
// =========================================================================
define('BZ_SOCIAL_FEEDS_VERSION', '7.0.2');
define('BZ_SOCIAL_FEEDS_PLUGIN_FILE', __FILE__);

// =========================================================================
// Social Feeds constants (kept for backward compatibility)
// =========================================================================
if ( ! defined( 'FB_WIDGET_PLUGIN_URL' ) ) {
    define( 'FB_WIDGET_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'FB_WIDGET_PLUGIN_BASE_URL' ) ) {
    define( 'FB_WIDGET_PLUGIN_BASE_URL', plugin_dir_path( __FILE__ ) );
}

// =========================================================================
// Load Buttonizer core
// =========================================================================

// Autoloader
require_once __DIR__ . "/app/autoloader.php";

// Get environment vars
require_once __DIR__ . "/EnvVars.php";

// Initialize shared core configuration
\BZSocialFeeds\Core\PluginConfig::init([
    'name'        => BZ_SOCIAL_FEEDS_NAME,
    'dir'         => BZ_SOCIAL_FEEDS_DIR,
    'app_dir'     => BZ_SOCIAL_FEEDS_APP_DIR,
    'slug'        => BZ_SOCIAL_FEEDS_SLUG,
    'version'     => BZ_SOCIAL_FEEDS_VERSION,
    'plugin_file' => BZ_SOCIAL_FEEDS_PLUGIN_FILE,
    'base_name'   => BZ_SOCIAL_FEEDS_BASE_NAME,
    'api_uri'     => BZ_SOCIAL_FEEDS_API_URI,
    'api_timeout' => defined("BZ_SOCIAL_FEEDS_API_TIMEOUT") ? BZ_SOCIAL_FEEDS_API_TIMEOUT : 20,
    'page_slug'   => 'bz_social_feeds',
    'text_domain' => 'social-feeds-for-wordpress',
    'review_url'  => 'https://wordpress.org/support/plugin/facebook-pagelike-widget/reviews/#new-post?utm_source=wp-plugin-request-review-btn',
    'product_name' => 'Social Sharing & Icons by Buttonizer',
    'notice_id'   => 'buttonizer-sf-admin-notice',
    'notice_js_function' => 'buttonizerSfAdminNotice',
    'referral_slug' => 'wp-social-feeds-plugin-menu',
    'siblings'    => [
        'buttonizer' => [
            'token_option'    => 'buttonizer_site_connection',
            'settings_option' => 'buttonizer_settings',
            'account_option'  => 'buttonizer_account',
        ],
        'bz_contact_button' => [
            'token_option'    => 'bz_contact_button_site_connection',
            'settings_option' => 'bz_contact_button_settings',
            'account_option'  => 'bz_contact_button_account',
        ],
    ],
]);

// Initialize Buttonizer
require_once __DIR__ . "/init.php";

// =========================================================================
// Social Feeds functionality
// =========================================================================

// Load Social Feeds widget and shortcode
require_once __DIR__ . '/app/SocialFeeds/Widget.php';
require_once __DIR__ . '/app/SocialFeeds/Shortcode.php';

// Enqueue Social Feeds frontend styles
add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style( 'fb-widget-frontend-style', FB_WIDGET_PLUGIN_URL . 'assets/css/style.css', array(), BZ_SOCIAL_FEEDS_VERSION );
});

// Load text domain
add_action( 'plugins_loaded', function() {
    load_plugin_textdomain( 'social-feeds-for-wordpress', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
});

// =========================================================================
// Social Feeds - Gutenberg Block
// =========================================================================

/**
 * Register the Facebook Page Like Block.
 */
function bzsf_register_facebook_pagelike_block() {
    $block_path = BZ_SOCIAL_FEEDS_DIR . '/app/SocialFeeds/Block/';
    $block_url  = plugin_dir_url( __FILE__ ) . 'app/SocialFeeds/Block/';

    wp_register_script(
        'bzsf-block-editor-script',
        $block_url . 'block.js',
        array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render' ),
        BZ_SOCIAL_FEEDS_VERSION,
        false
    );

    $saved_feeds = get_option( 'fb_widget_saved_shortcodes', array() );
    wp_localize_script( 'bzsf-block-editor-script', 'fbWidgetData', array(
        'savedFeeds'  => $saved_feeds,
        'settingsUrl' => admin_url( 'admin.php?page=bz-social-feeds-listing&action=new' )
    ) );

    register_block_type( $block_path );
}

add_action( 'init', 'bzsf_register_facebook_pagelike_block' );

add_action( 'enqueue_block_assets', function() {
     if ( is_admin() ) {
        // 1. Fetch saved shortcodes to see if a specific language is preferred
        $saved_feeds = get_option( 'fb_widget_saved_shortcodes', array() );

        // Default fallback to WordPress site locale (e.g., 'en_US')
        $final_lang = get_locale();

        // 2. If we have saved feeds, extract the language from the most recent one
        if ( ! empty( $saved_feeds ) ) {
            $last_feed = end( $saved_feeds );
            $shortcode_str = $last_feed['shortcode'];

            // Use regex to pull the lang="xx_XX" value from the shortcode string
            if ( preg_match( '/lang="([^"]+)"/', $shortcode_str, $matches ) ) {
                $final_lang = $matches[1];
            }
        }
        // 3. Enqueue the SDK with the dynamic language
        wp_enqueue_script(
            'fb-sdk-admin',
            'https://connect.facebook.net/' . esc_attr( $final_lang ) . '/sdk.js#xfbml=1&version=v25.0',
            array(),
            null,
            true
        );
    }
});

// =========================================================================
// Uninstall hook
// =========================================================================
register_uninstall_hook(__FILE__, 'bzsf_plugin_uninstall_event');

function bzsf_plugin_uninstall_event()
{
    // Skip if there is no API token being used
    if (\BZSocialFeeds\Core\Utils\ApiRequest::getApiToken() === false) {
        return;
    }

    try {
        // Invalidate access token for security reasons on uninstall
        (new \BZSocialFeeds\Core\Api\Connection\Disconnect)->disconnect(false);
    } catch (\Error $err) {
        // Errored out, nevermind then
    }
}
