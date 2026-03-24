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

define('BZ_SOCIAL_FEEDS_NAME', 'bz_social_feeds');
define('BZ_SOCIAL_FEEDS_DIR', dirname(__FILE__));
define('BZ_SOCIAL_FEEDS_APP_DIR', __DIR__ . "/app");
define('BZ_SOCIAL_FEEDS_SLUG', basename(BZ_SOCIAL_FEEDS_DIR));
define('BZ_SOCIAL_FEEDS_PLUGIN_DIR', __FILE__);
define("BZ_SOCIAL_FEEDS_BASE_NAME", plugin_basename(BZ_SOCIAL_FEEDS_PLUGIN_FILE));

if (!defined("BZ_SOCIAL_FEEDS_API_URI")) {
    define("BZ_SOCIAL_FEEDS_API_URI", "https://api.buttonizer.io");
}

// DEBUG ONLY
if (defined("BZ_SOCIAL_FEEDS_DEBUG") && BZ_SOCIAL_FEEDS_DEBUG) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ERROR);
}

# No script kiddies
defined('ABSPATH') or die('No script kiddies please!');

