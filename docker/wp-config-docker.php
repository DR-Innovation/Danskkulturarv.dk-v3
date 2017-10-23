<?php

/**
 * Database credentials for Docker setup.
 *
 * Can be overridden in wp-config-local.php on other development
 * environments. In production platform.sh credentials is set via
 * wp-config-platformsh.php.
 */
define('DB_NAME', 'db');
define('DB_USER', 'db');
define('DB_PASSWORD', 'db');
define('DB_HOST', 'db');
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');
define('WP_DEBUG', true);
