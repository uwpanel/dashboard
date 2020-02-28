<?php

/**
 * PHP BaseApp
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  Copyright (c) 2020 BaseApp (https://baseapp.org)
 * @license    GPL-3.0
 */

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
*/

require_once dirname(__DIR__) . '/vendor/autoload.php';

/**
 * This section prepares the environment. All this can be done from the configuration of the
 * Server / PHP, in case you cannot use it from there. You can uncomment these lines.
 */


/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
*/

//require_once dirname(__DIR__) . '/vendor/autoload.php';


//*Locale*
//setlocale(LC_ALL, 'es_ES');

//*Timezone*
//ini_set('date.timezone', 'America/New_York');

/**
 * @TODO
 * CHECK THIS SECTION
 */
const APP_CHARSET = 'UTF-8';

/*
 *Indicate if the application is in production
  * directly from the index.php
  *
  *  WARNING !!!
  * When changing from production = false, to production = true, it is necessary to delete
  * the contents of the application cache directory to be renewed
  * the metadata (/app/tmp/cache/*)
 */
const PRODUCTION = false;

/*
 * Uncomment to show errors
 */
//error_reporting(E_ALL ^ E_STRICT);ini_set('display_errors', 'On');

/*
 * Define el APP_PATH
 *
 * APP_PATH:
 * - Path to the application directory (by default the path to the app directory)
 * - This path is used to load application files
 * - In production, it is advisable to put it manually using const
 */
define('APP_PATH', dirname(__DIR__) . '/app/');
//const APP_PATH = '/path/to/app/';

/*
 * Define the CORE_PATH
 *
 * CORE_PATH:
 * - Path to the directory that contains the Kumbia kernel (by default the path to the core directory)
  * - In production, it is advisable to put it manually using const
 */
define('CORE_PATH', dirname(__DIR__) . '/core/');
//const CORE_PATH = '/path/to/core/';

/*
 * Define PUBLIC_PATH.
 *
 * PUBLIC_PATH:
 * - Path to generate the URL in the links to actions and controllers
 * - This route is used by Kumbia as a base to generate the Urls to access from
 * client (with the web browser) and is relative to the DOCUMENT_ROOT of the web server
 *
 * IN PRODUCTION THIS CONSTANT SHOULD BE MANUALLY ESTABLISHED
 */
define('PUBLIC_PATH', substr($_SERVER['SCRIPT_NAME'], 0, -9)); // - index.php string[9]

/**
 * In production uncomment the line above and use const
 * '/' in the root of the domain, recommended
 * '/ folder /' in a folder or more
 * 'https://www.midominio.com/'  using domain.
 */
//const PUBLIC_PATH = '/';

/**
 * Get the url using PATH_INFO.
 */
$url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';

/**
 * Load the bootloader
 * By default the bootstrap of the core.
 *
 * @see Bootstrap
 */
require APP_PATH . 'libs/bootstrap.php'; //app bootstrap
// require CORE_PATH . 'kumbia/bootstrap.php'; //core bootstrap
