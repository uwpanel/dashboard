<?php

/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @category   Kumbia
 * @package    Core
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * This script executes the loading of KumbiaPHP
 *
 * @category   Kumbia
 * @package    Core
 */

// Start the output buffer
ob_start();

/**
 * KumbiaPHP version
 *
 * @return string
 */
function kumbia_version()
{
    return '1.0.0';
}

/**
 * Initialize the ExceptionHandler
 * @see KumbiaException
 *
 * @return void
 */
set_exception_handler(function ($e) {
    KumbiaException::handleException($e);
});


// @see Autoload
require CORE_PATH . 'kumbia/autoload.php';

// @see Config
require CORE_PATH . 'kumbia/config.php';

if (PRODUCTION && Config::get('config.application.cache_template')) {
    // @see Cache
    require CORE_PATH . 'libs/cache/cache.php';

    //Assign the default driver using the config.ini
    if ($config = Config::get('config.application.cache_driver')) {
        Cache::setDefault($config);
    }

    // Check if the template is cached
    if ($template = Cache::driver()->get($url, 'kumbia.templates')) {
        //check template cache for url
        echo $template;
        echo '<!-- Time: ', round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 4), ' ms -->';
        return;
    }
}

// @see Router
require CORE_PATH . 'kumbia/router.php';

// @see Controller
require APP_PATH . 'libs/app_controller.php';

// @see KumbiaView
require APP_PATH . 'libs/view.php';

// Execute the request
// Dispatch and render the view
View::render(Router::execute($url));

// End of request exit ()
