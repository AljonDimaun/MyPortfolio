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
define( 'DB_NAME', 'myportfolio_db' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'lMR df472sA(+6i$^GEEQnpEIPH0=-`fK-8#aj-Hg&;{Iz]J`K;2F=Fjz&^Y ,ft' );
define( 'SECURE_AUTH_KEY',  '<G|ML9myuErOLLfkutz$_2jQVpIrD)lC9Lc~bfFKV0/F,9|_KFwX`0Y7I;z/yJ0j' );
define( 'LOGGED_IN_KEY',    'LA R5[U]pHA8;.jf65i&F#]^.TK7?duF3*p@91PB3G4^kSi[3#lFNz/]=+w&Ay@$' );
define( 'NONCE_KEY',        '<bp^dOy[RYc;_GrV&EskLtI=KLt|V{y.}!C`vF!&K;Nn2+XAL>XgOkCS01){i68c' );
define( 'AUTH_SALT',        'p][KpvU[N~=Ara+W]9eX_C_Y}}W 4J%+6M}McE*@gb30bHp9JkoE:pcMc+TiYO 3' );
define( 'SECURE_AUTH_SALT', 'vhe$5{KXrb_eYh-$xVRTKcy.QJ[[4yxnG62tFB#$ k O#erWPS(W[raMI;WBG5Gt' );
define( 'LOGGED_IN_SALT',   '7vBre+2G76>,Pp^.TO;5zM[*qaEryA8|!!ds6g7<zKsr]|iy2]$NX2))>7*OCoQ7' );
define( 'NONCE_SALT',       'Rd_9 2O}IFng&J2{0j{)&j5[bCw~t?5EXRFr:D~a6eF OE997A^kzFA02!C=hI7<' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

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

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
