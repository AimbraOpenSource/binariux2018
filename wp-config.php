<?php
define('DB_NAME', 'binariux-wp-o1KX16rW');
define('DB_USER', 'joiND5o7L3zt');
define('DB_PASSWORD', 'SS8gyX9KBj1Wgz90');

define('DB_HOST', 'localhost');
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');

define('AUTH_KEY',         'HOYfq4oKlHd8Q4vmOlQWyBsVCG0sueuDbaEtOaUD');
define('SECURE_AUTH_KEY',  'sS4WkqiIHwhlt8xGwJab963LKCdbT2hzOTxOzTCN');
define('LOGGED_IN_KEY',    '6nzhrt40JK8oZC4PPdVmDMqfwvu8xGoPfnj4ZVoy');
define('NONCE_KEY',        'utDbaGMH8OBUJVivWpkh900U6WO0uIzPDoKgZmsu');
define('AUTH_SALT',        'CFo9Wi2ebgMA0OrCgR7GN37mWtWfa6HLpypIqc58');
define('SECURE_AUTH_SALT', '5aMPtRUjr9GyIWVXo4H6qDUJVUSXVsmEtV2fIs9r');
define('LOGGED_IN_SALT',   '6irzhvZL0hseme3b1iX0yabiuzfu89dA59xwwmbf');
define('NONCE_SALT',       '8dbGENfMbbpb9VtIZPgMGjgKcoXNNr6K9ENcSD8Z');

$table_prefix  = 'wp_f003be6e34_';

define('SP_REQUEST_URL', ($_SERVER['HTTPS'] ? 'https://' : 'http://') . $_SERVER['HTTP_HOST']);

define('WP_SITEURL', SP_REQUEST_URL);
define('WP_HOME', SP_REQUEST_URL);

/* Change WP_MEMORY_LIMIT to increase the memory limit for public pages. */
define('WP_MEMORY_LIMIT', '256M');

/* Uncomment and change WP_MAX_MEMORY_LIMIT to increase the memory limit for admin pages. */
//define('WP_MAX_MEMORY_LIMIT', '256M');

/* That's all, stop editing! Happy blogging. */

if ( !defined('ABSPATH') )
        define('ABSPATH', dirname(__FILE__) . '/');

require_once(ABSPATH . 'wp-settings.php');
