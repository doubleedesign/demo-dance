<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'demodance' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost:3309' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          'aHrcO|z0@v9-;tH-oA`{)_Y+6^G??#*OVzBcckQ6nR?HOm7=P3nzw?P.*g7Q!ans' );
define( 'SECURE_AUTH_KEY',   'v_/n9=uQL}S?A9LGb^C``J9PV-kzk[O,Pc_h3}Vip!g=G]emSPU<85&53|9=.-C<' );
define( 'LOGGED_IN_KEY',     's/_5n`{MV0+y ^33!q(#r(2gjaJc#Ks98a;_~e;)CF2nF1,0xHhmDn^v>dpt(tI#' );
define( 'NONCE_KEY',         '^,Ax/4z[y(`|0>@C#Nh7MxijR];xvvu(%l2Aai-r|-#,!*h@A&fRFiN6/])nHY2U' );
define( 'AUTH_SALT',         'h@c>KH.{9JO7TBAS?0Cgf3rpD ]i!q@wQN&gC;-{cs}@H5PlSV~-#W=#W?z0<taR' );
define( 'SECURE_AUTH_SALT',  'f)1F>Q762eaZMP24]-h:=p^(|BAtiH8yAUcgz4/_]oN$28j8JUO2m7dD138yyO!M' );
define( 'LOGGED_IN_SALT',    'L!vk(T+HPm<iLB{E{:+z7I:LdMh5=~XY~APMA-x*xT5X1FmVgk{M~&e25dLk5O]W' );
define( 'NONCE_SALT',        '@/dd1E|Ee}=8/jHbp!MPN^{$7%1Qgr[6a+Q5twflvt_~rAVV;,*Bbz%dl mXAeKR' );
define( 'WP_CACHE_KEY_SALT', 'p4mAQPy;EfhClxn-,9pelP{0SPU{0ypjAp0p&#2ii/*?4*#{5=2W5!f^^};ee)fZ' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



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
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', true );
	define( 'WP_DEBUG_DISPLAY', false );
	define( 'WP_DEBUG_LOG', true );
}

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
