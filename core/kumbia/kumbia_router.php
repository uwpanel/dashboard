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
 * @package    KumbiaRouter
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

class KumbiaRouter
{

    /**
     * Take $url and break it down into (module), controller, action and arguments
     *
     * @param string $url
     * @return  array
     */
    public static function rewrite($url)
    {
        $router = array();
        //Default value
        if ($url === '/') {
            return $router;
        }

        //The url is cleaned, in case it is written with the last parameter without value, that is controller/action/
        // Obtain and assign all url parameters
        $urlItems = explode('/', trim($url, '/'));

        // The first parameter of the url is a module?
        if (is_dir(APP_PATH . "controllers/$urlItems[0]")) {
            $router['module'] = $urlItems[0];

            // If there are no more parameters it goes
            if (next($urlItems) === false) {
                $router['controller_path'] = "$urlItems[0]/index";
                return $router;
            }
        }

        // Controller, change - by _
        $router['controller']      = str_replace('-', '_', current($urlItems));
        $router['controller_path'] = isset($router['module']) ? "$urlItems[0]/" . $router['controller'] : $router['controller'];

        // If there are no more parameters it comes out
        if (next($urlItems) === false) {
            return $router;
        }

        // Action
        $router['action'] = current($urlItems);

        // If there are no more parameters it comes out
        if (next($urlItems) === false) {
            return $router;
        }

        // Create the parameters and pass them
        $router['parameters'] = array_slice($urlItems, key($urlItems));
        return $router;
    }

    /**
     * Look in the charting table if there is a route in config / routes.ini
     * for controller, action, current id
     *
     * @param string $url Routing url
     * @return string
     */
    public static function ifRouted($url)
    {
        $routes = Config::get('routes.routes');

        // If there is an exact route it returns
        if (isset($routes[$url])) {
            return $routes[$url];
        }

        // If there is a route with the wildcard * create the new route
        foreach ($routes as $key => $val) {
            if ($key === '/*') {
                return rtrim($val, '*') . $url;
            }

            if (strripos($key, '*', -1)) {
                $key = rtrim($key, '*');
                if (strncmp($url, $key, strlen($key)) == 0) {
                    return str_replace($key, rtrim($val, '*'), $url);
                }
            }
        }
        return $url;
    }

    /**
     * Load and return a controller instance
     */
    public static function getController($param)
    {
        // Extract the variables for easy manipulation
        extract($param, EXTR_OVERWRITE);
        if (!include_once "$default_path{$dir}/$controller_path{$suffix}") {
            throw new KumbiaException('', 'no_controller');
        }
        //Assign the active controller
        $app_controller = Util::camelcase($controller) . 'Controller';
        return new $app_controller($param);
    }
}
