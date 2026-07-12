<?php
/** Enable W3 Total Cache */
define('WP_CACHE', true); // Added by W3 Total Cache


// ** MySQL settings - You can get this info from your web host ** //

/** The name of the database for WordPress */
define('DB_NAME', 'name');


/** MySQL database username */
define('DB_USER', 'root');


/** MySQL database password */
define('DB_PASSWORD', 'password');


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

define('AUTH_KEY',         'KmVpc0.cdmV2@,In0<5dgJW|#4:vgGtb_~Y%HNc>b&xyArjZ`s.p9QZ8u~B$SZN=');

define('SECURE_AUTH_KEY',  'mT$.HNy/@]^k66v_Q2^G/vh|s<dLSD^lg0FIm2>[uk19G{~GoksiFaKHRFkCZH7p');

define('LOGGED_IN_KEY',    'Z:QUr6eKlzaqIdkp|#LDh#r*D>XGy%s7|CNi==u`,EJiCK`|naQjl(<!&(7L30tb');

define('NONCE_KEY',        '=4`+G/?n9 FH:M$ay:UQj%)z?WnH_W4sr4hp@s8_yfzcqq[CP7+o 9dEsa+BZ<+,');

define('AUTH_SALT',        'I>#UV823<ilXoklRuQuF=oaL/V*aj~)blH!XN=R,5Ha#ftCWqZQe/vc(tVP!@dV(');

define('SECURE_AUTH_SALT', 'DN>6 8v2yoV=0>MdaLuA`&=~(ercfm{MA%8D;V^d53UY7:7O?ozjy$u;8HuEpFX{');

define('LOGGED_IN_SALT',   'I1ZGSca^5zX2H<Fy9_yJaoq:}f7:lx(<k29o|5grBp!P8!=ai6[|xai?0CAIFPg%');

define('NONCE_SALT',       'JPm+(eH Pu.R|ljbP2:7q;/=54#~FkF:w4UH;@38W!Us(B5/{$=n{ <k)&e4y^(4');



/**#@-*/



/**

 * WordPress Database Table prefix.

 *

 * You can have multiple installations in one database if you give each

 * a unique prefix. Only numbers, letters, and underscores please!

 */

$table_prefix  = 'sm_';



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

ini_set('log_errors','On');

ini_set('display_errors','On');

ini_set('error_reporting', E_ALL );

define('WP_DEBUG', false);

define('WP_DEBUG_LOG', true);

define('WP_DEBUG_DISPLAY', true);


define( 'AUTOSAVE_INTERVAL', 300 );
define( 'WP_POST_REVISIONS', 5 );
define( 'EMPTY_TRASH_DAYS', 7 );
define( 'WP_CRON_LOCK_TIMEOUT', 120 );
/* That's all, stop editing! Happy blogging. */



/** Absolute path to the WordPress directory. */

if ( !defined('ABSPATH') )

	define('ABSPATH', dirname(__FILE__) . '/');



/** Sets up WordPress vars and included files. */

require_once(ABSPATH . 'wp-settings.php');

