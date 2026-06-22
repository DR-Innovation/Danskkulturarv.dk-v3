<?php
/*
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

namespace BZSocialFeeds\Admin;

use BZSocialFeeds\Core\Admin\BaseAdmin;
use BZSocialFeeds\Core\Utils\PermissionCheck;

# No script kiddies
defined('ABSPATH') or die('No script kiddies please!');

class Admin extends BaseAdmin
{
    /**
     * Admin constructor.
     */
    public function __construct()
    {
        // Register shared admin hooks (assets, modules, notices)
        parent::__construct();

        // Add to admin menu
        add_action('admin_menu', [$this, 'pluginAdminMenu']);

        // Redirect Social Feeds pages to Buttonizer signup if not connected
        add_action('admin_init', [$this, 'maybeRedirectToSignup']);

        // Plugin information, add links
        add_filter("plugin_action_links_" . BZ_SOCIAL_FEEDS_BASE_NAME, function ($actions) {
            $links = [
                '<a href="' . admin_url('admin.php?page=bz_social_feeds#/support') . '">' . __('Support', 'social-feeds-for-wordpress') . '</a>',
                '<a href="' . admin_url('admin.php?page=bz_social_feeds#/editor') . '">' . __('Edit buttons', 'social-feeds-for-wordpress') . '</a>',
                '<a href="' . admin_url('admin.php?page=bz_social_feeds#/settings') . '">' . __('Settings', 'social-feeds-for-wordpress') . '</a>',
            ];

            return array_merge($actions, $links);
        });
    }

    /**
     * Create Admin menu
     */
    public function pluginAdminMenu()
    {

        if (!PermissionCheck::hasPermission()) return;

        // Main menu: Social Sharing & Icons (loads Buttonizer dashboard)
        add_menu_page(
            'Social Sharing & Icons',
            'Social Sharing & Icons',
            'read',
            'bz_social_feeds',
            [$this, 'page'],
            plugins_url('/assets/images/wp-icon.png', BZ_SOCIAL_FEEDS_PLUGIN_FILE),
            81
        );

        // Sub-menu: Buttonizer (same as main page)
        add_submenu_page(
            'bz_social_feeds',
            'Buttonizer',
            __('Buttonizer', 'social-feeds-for-wordpress'),
            'read',
            'bz_social_feeds'
        );

        // Sub-menu: Edit buttons
        add_submenu_page(
            'bz_social_feeds',
            'Edit buttons',
            __('Edit buttons', 'social-feeds-for-wordpress'),
            'read',
            'admin.php?page=bz_social_feeds#/editor'
        );

        // Sub-menu: Facebook feeds (handles listing, add new, edit)
        add_submenu_page(
            'bz_social_feeds',
            __('Facebook feeds', 'social-feeds-for-wordpress'),
            __('Facebook feeds', 'social-feeds-for-wordpress'),
            'manage_options',
            'bz-social-feeds-listing',
            [$this, 'socialFeedsPageContent']
        );

        // Add support link
        add_submenu_page(
            'bz_social_feeds',
            __('I need support', 'social-feeds-for-wordpress'),
            __('I need support', 'social-feeds-for-wordpress'),
            'read',
            'admin.php?page=bz_social_feeds#/support'
        );

        // Add community link
        add_submenu_page(
            'bz_social_feeds',
            __('Community', 'social-feeds-for-wordpress'),
            __('Community', 'social-feeds-for-wordpress'),
            'read',
            'https://r.buttonizer.io/support/community?referral=wp-social-feeds-plugin-menu'
        );

        // Add knowledge base link
        add_submenu_page(
            'bz_social_feeds',
            __('Knowledge base', 'social-feeds-for-wordpress'),
            __('Knowledge base', 'social-feeds-for-wordpress'),
            'read',
            'https://r.buttonizer.io/support/knowledgebase?referral=wp-social-feeds-plugin-menu'
        );
    }

    /**
     * Redirect Social Sharing & Icons pages to Buttonizer signup if not connected.
     * Runs on admin_init, before any output is sent.
     */
    public function maybeRedirectToSignup()
    {
        if (!isset($_GET['page'])) return;

        if ($_GET['page'] === 'bz-social-feeds-listing' && !$this->isButtonizerConnected()) {
            wp_redirect(admin_url('admin.php?page=bz_social_feeds'));
            exit;
        }
    }

    /**
     * Social Sharing & Icons page content - routes to listing or settings based on action
     */
    public function socialFeedsPageContent()
    {
        $action = isset($_GET['action']) ? $_GET['action'] : '';

        if ($action === 'new' || $action === 'edit') {
            include_once BZ_SOCIAL_FEEDS_DIR . '/app/SocialFeeds/Admin/SettingsPage.php';
        } else {
            include_once BZ_SOCIAL_FEEDS_DIR . '/app/SocialFeeds/Admin/ListingPage.php';
        }
    }
}

