<?php
/**
* The base configuration for WordPress
*
* The wp-config.php creation script uses this file during the
* installation. You don't have to use the web site, you can
* copy this file to "wp-config.php" and fill in the values.
*
* This file contains the following configurations:
*
* * MySQL settings
* * Secret keys
* * Database table prefix
* * ABSPATH
*
* @link https://wordpress.org/support/article/editing-wp-config-php/
*
* @package WordPress
*/

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'admin_symamem');

/** MySQL database username */
define('DB_USER', 'admin_symamem');

/** MySQL database password */
define('DB_PASSWORD', 'MemeSolis38');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
* Authentication Unique Keys and Salts.
*
* Change these to different unique phrases!
* You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
* You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
*
* @since 2.6.0
*/
define('AUTH_KEY', 'Y/acI7Pm5XOQfh7b0hWLaH3LDRADlH1ZBgtq3NJWThwX0YKmFKku1fOBlS0RR+pf');
define('SECURE_AUTH_KEY', '9LyueUpVZ33RWeeEESKz21lbvy6MMEvSIckQ5zF5xL199nNiUGH0EbMmgWBvPtxT');
define('LOGGED_IN_KEY', 'q1iI/rWdakGLqex3+X35Vxxnn7ro+WywoxlEv+cvSGWzkPW5N2rxsCmp3HqOn/nA');
define('NONCE_KEY', 'BkDpTpUbxdXS/Ctgt++AgjaHpe3nV+I6SJ80qxh1/6JbJXc/NqzzygSx/wMjK3el');
define('AUTH_SALT', 'gLMrIWksPRXq9DHSVwq6zUF+GIJE6vqILE3IAbCefrRzo+MZcrEy4D0RBbWRlF9o');
define('SECURE_AUTH_SALT', 'JGSKVfZeqsZgGLtwAo+RmF4Ye8hHoQUxZw8EybzhVG316SWTJNbO83rwNHc3yQiP');
define('LOGGED_IN_SALT', 'Pz8hnuHnE4RQ4Wui1O97bqqypjScNegSMzuC5YSrMCF3B2eBeFSj9aCT/j4d2xv6');
define('NONCE_SALT', 'KUeupJATR9I1tTuQ3rN72iH9LBI/aiOFdH8fJbvqKuZSNRIL/KE3PwP0KIbh3FsV');

/**#@-*/

/**
* WordPress Database Table prefix.
*
* You can have multiple installations in one database if you give each
* a unique prefix. Only numbers, letters, and underscores please!
*/
$table_prefix = 'utmN9IQFy_';

/**
* For developers: WordPress debugging mode.
*
* Change this to true to enable the display of notices during development.
* It is strongly recommended that plugin and theme developers use WP_DEBUG
* in their development environments.
*
* For information on other constants that can be used for debugging,
* visit the documentation.
*
* @link https://wordpress.org/support/article/debugging-in-wordpress/
*/
define( 'WP_DEBUG', false );

define( 'CONCATENATE_SCRIPTS', false );




/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
