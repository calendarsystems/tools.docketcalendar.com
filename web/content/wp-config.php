<?php
define('WP_MEMORY_LIMIT', '64M');
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

// ** MySQL settings ** //
/** The name of the database for WordPress */
define( 'DB_NAME', '375786_toolsdocket' );

/** MySQL database username */
define( 'DB_USER', '375786_tools' );

/** MySQL database password */
define( 'DB_PASSWORD', 'D0cketT00ls123' );

/** MySQL hostname */
define( 'DB_HOST', 'mariadb-016.wc1.phx1.stabletransit.com' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

//define( 'AUTOMATIC_UPDATER_DISABLED', true );
/*
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define( 'WP_DEBUG_DISPLAY', true );
*/


/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'Z%{p7m.,JK(=@W0t64D%903gxURnvgOc0$ky+PX-)ic%.*}yMN=8nZV0S5:mgzc^');
define('SECURE_AUTH_KEY',  'oeza$Q{|f:{Pr%>u|D]vIWj;&d(#BOyhaQtf(-@G!4v5r!D(e}w-KeQ#!I&]5`p ');
define('LOGGED_IN_KEY',    '8+-&Ci!oT|h+a&1>?|#9n2*-^ x>0|+,Y4@5_&mNpS->9l{n[XT0h$A~Z]kp:MaK');
define('NONCE_KEY',        '{D{D)N+h m5%Qy+XuP&?3HN++{sw%+fdVT_s _x-[a&:{!*2QrVn)u>@%A0w*k}H');
define('AUTH_SALT',        ' iyavf-5Y?&9e&,f*=+e~?Xo)*MI|F`,:37{]ZQ(UW]8} {0g,=~3Le^:-bH;FUZ');
define('SECURE_AUTH_SALT', 'mnghsUxgh5k5kU~*C jJ|VLE@zp5TUUv5V0lxRUhdf+l,6+l^hO:.)Sep3x1E%bf');
define('LOGGED_IN_SALT',   'KXBVU1t7 A<UOL<EEJUd|VV>y^-gnL^*!58Emk,(7yRw:MB|F/)7{I)FNjK3~c. ');
define('NONCE_SALT',       'YqS~PZs// Y~-CTj7Qi6,6|?1(c-D?_UM&}#@g>yEnG}N5o_-Ob4z+gZG2j0htIJ');

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) )
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
