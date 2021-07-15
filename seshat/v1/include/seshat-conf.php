<?php
/** Main setup and definitions script for Seshat framework
 * 
 * Parameters with "(required)" in the comment can be changed but must not be removed.
 * 
 * @file v1/include/seshat.php  
 * @author Per Löwgren
 * @date Modified: 2016-01-04
 * @date Created: 2015-04-09
 */

ini_set('display_errors',1);
error_reporting(E_ALL);
date_default_timezone_set('Europe/Stockholm');

define('VERSION',             'v1.1.0');                           //!< Version of application (required)
define('APP_NAME',            'AppName');                          //!< Name of application, basic title of pages (required)
define('APP_THEME',           'main');                             //!< Default theme of web-site (required)
define('APP_CONTACT',         'contact@email.com');                //!< Contact email (required)

define('AUTH_NONE',           0);
define('AUTH_USER',           5);
define('AUTH_ADMIN',          20);

define('ADMIN_USER',          'admin');
define('ADMIN_PASSWORD',      'admin');
define('ADMIN_EMAIL',         APP_CONTACT);

define('DIR',                 dirname(__DIR__).'/');               //!< Base directory of application
define('DIR_DATA',            DIR.'.data/');                       //!< Generated data directory (read/write/execute; private) (required)
define('DIR_AJAX',            DIR.'ajax/');                        //!< Ajax scripts (read/write/execute; public) (required)
define('DIR_INCLUDE',         DIR.'include/');                     //!< Included PHP scripts (read/execute; private) (required)
define('DIR_PLUGIN',          DIR.'plugin/');                      //!< Plugins (read/execute; public) (required)
define('DIR_RESOURCE',        DIR.'resource/');                    //!< Resource files such as images and documents (read/write; public) (required)
define('DIR_TEMPLATE',        DIR.'template/');                    //!< Templates directory (read; private) (required)
define('DIR_TEST',            DIR.'test/');                        //!< Test scripts (read/write/execute; public)
define('DIR_DOC',             DIR.'doc/');                         //!< Documentation files (read; public) (required)
define('DIR_EMAIL',           DIR.'doc/email/');                   //!< Email files (read; public) (required)
define('DIR_WIKI',            DIR.'doc/wiki/');                    //!< Wiki pages (read; public) (required)

define('HTTP_HOST',           'http://'.$_SERVER['HTTP_HOST']);    //!< HTTP-host (required)
define('HTTPS_HOST',          'https://'.$_SERVER['HTTP_HOST']);   //!< HTTPS-host (required)

define('DB_SQLITE3',          DIR_DATA.'data.db');                 //!< Path to SQLite3 database file, used for sessions, users & wiki etc. (required)

define('SESSION_LIFETIME',    2592000); /* 30 days */              //!< Sessions lifetime (required)

/* These are not used:
define('DB_HOST',             'localhost');                        //!< Database host (MySQL etc.)
define('DB_USER',             '');                                 //!< Database user (MySQL etc.)
define('DB_PASSWORD',         '');                                 //!< Database password (MySQL etc.)
define('DB_NAME',             '');                                 //!< Database name (MySQL etc.)
*/

/* App live:
define('FB_APP_ID',           '<fb-app-id>');                      //!< 
define('FB_APP_SECRET',       '<fb-app-secret>');                  //!< 
*/

// App test:
define('FB_APP_ID',           '<fb-app-id>');                      //!< 
define('FB_APP_SECRET',       '<fb-app-secret>');                  //!< 


/** Main autoload function for classes */
function Seshat_autoloader($class) {
	if(strpos($class,'\\')!==false) $class = str_replace('\\','/',$class);
	if(file_exists($f=DIR_INCLUDE.$class.'.php')) require_once $f;
	else {
		echo '<pre>Seshat_autoloader('.$class.")\n";
		var_dump(debug_backtrace());
		echo '</pre>';
	}
}
spl_autoload_register('Seshat_autoloader');

