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
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'zinecato_wp2');

/** MySQL database username */
define('DB_USER', 'zinecato_wp2');

/** MySQL database password */
define('DB_PASSWORD', 'G.oK^u7sf&Czt~LH*Z[20.[3');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

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
define('AUTH_KEY',         'HImVkwCZYTE3btbrooi8uWzn0l6GIKRnAAe2JOQKiEgbllzdeIel4POm7laUUSny');
define('SECURE_AUTH_KEY',  'Ry2Swqe04VGgtmtE6SDirstofLlaT2zZtU8O1i1penHXHk7LZmSm6zHm1cvk6tSu');
define('LOGGED_IN_KEY',    'C71aunKbiFW6iE3t5E3NdddXonshdFZZQvVQTjkbuMCWqauZEAGf3qf2qHCemddE');
define('NONCE_KEY',        'LN0VeZI06dqC6jz6T8outUouqNsfZIhTIgZ7SKvJOC0vPaGlb2eJerVo7iW1LIRh');
define('AUTH_SALT',        'jPG11J47GvIYnVxqevSsujp5ArAEG45oqXKWZ8L9eJrn4vMn3dFMhFAnxgsqvnaW');
define('SECURE_AUTH_SALT', 'XHZ77ZsFtzDhDNAdhZURi4h2OeN2mf7FKrYKp2GGq4k9wteB7ivlUZ4acO50ez9B');
define('LOGGED_IN_SALT',   'LOyzf1SZnwHqb5sX0LPbX20wVD842W4axQyEc5bBPOkH9JzzAwDYNhmaqyj40dFp');
define('NONCE_SALT',       'MKDGgexy165e6joaZbBD7hxRMdKd5Ss5GsA4tH1XI4WcItKB42kGEnMvKc4ttLT0');

/**
 * Other customizations.
 */
define('FS_METHOD','direct');define('FS_CHMOD_DIR',0755);define('FS_CHMOD_FILE',0644);
define('WP_TEMP_DIR',dirname(__FILE__).'/wp-content/uploads');

/**
 * Turn off automatic updates since these are managed upstream.
 */
define('AUTOMATIC_UPDATER_DISABLED', true);


/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

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
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');