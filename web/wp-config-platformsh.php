<?php

// This is where we get the relationships of our application dynamically
//from Platform.sh

if (isset($_ENV['PLATFORM_RELATIONSHIPS'])) {
  $relationships = json_decode(base64_decode($_ENV['PLATFORM_RELATIONSHIPS']), TRUE);

  // The DB credentials are stored in the first item in the relationship
  // to use. By default it's called "database", but could be named differentl
  // if desired in .platform.app.yaml.
  if (!defined(DB_NAME)) {
    define('DB_NAME', $relationships['database'][0]['path']);
    define('DB_USER', $relationships['database'][0]['username']);
    define('DB_PASSWORD', $relationships['database'][0]['password']);
    define('DB_HOST', $relationships['database'][0]['host']);
    define('DB_CHARSET', 'utf8');
    define('DB_COLLATE', '');
  }
}

$site_host = $_SERVER['HTTP_HOST'];
$site_scheme = !empty($_SERVER['https']) ? 'https' : 'http';

// Check whether a route is defined for this application in the Platform.sh routes.
// Use it as the site hostname if so (it is not ideal to trust HTTP_HOST).
if (isset($_ENV['PLATFORM_ROUTES'])) {
  $routes = json_decode(base64_decode($_ENV['PLATFORM_ROUTES']), TRUE);
  foreach ($routes as $url => $route) {
    if ($route['type'] === 'upstream' && $route['upstream'] === $_ENV['PLATFORM_APPLICATION_NAME']) {
      // Pick the first hostname, or the first HTTPS hostname if one exists.
      $host = parse_url($url, PHP_URL_HOST);
      $scheme = parse_url($url, PHP_URL_SCHEME);
      if ($host !== false && (!isset($site_host) || ($site_scheme === 'http' && $scheme === 'https'))) {
        $site_host = $host;
        $site_scheme = $scheme ?: 'http';
      }
    }
  }
}
if (!defined('WP_HOME')) {
  // Change site URL per environment.
  define('WP_HOME', $site_scheme . '://' . $site_host);
  define('WP_SITEURL', WP_HOME);
}

// Set all of the necessary keys to unique values, based on the Platform.sh
// entropy value.
if (isset($_ENV['PLATFORM_PROJECT_ENTROPY'])) {
  $keys = [
    'AUTH_KEY', 'SECURE_AUTH_KEY',
    'LOGGED_IN_KEY', 'LOGGED_IN_SALT',
    'NONCE_KEY', 'NONCE_SALT',
    'AUTH_SALT', 'SECURE_AUTH_SALT',
  ];

  foreach ($keys as $key) {
    if (!defined($key)) {
      define($key, $_ENV['PLATFORM_PROJECT_ENTROPY'] . $key);
    }
  }
}

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */

// Debug mode should be disabled on Platform.sh. Set this constant to true
// in a wp-config-local.php file to skip this setting on local development.
if (!defined('WP_DEBUG')) {
  define('WP_DEBUG', false);
}
