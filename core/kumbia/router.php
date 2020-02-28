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
 * @package    Router
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Class that acts as a Front-Controller router
 *
 * Request redirection handling
 * Contains information regarding the url of
 * the request (module, controller, action, parameters, etc)
 *
 * @category   Kumbia
 * @package    Router
 */
class Router
{

    /**
     * Static array with router variables
     *
     * @var array
     */
    protected static $vars = array(
        'method'          => '', //Method used GET, POST, ...
        'route'           => '', //Route passed in the GET
        'module'          => '', //Current Module Name
        'controller'      => 'index', //Current Controller Name
        'action'          => 'index', //Name of the current action, default index
        'parameters'      => [], //List additional URL parameters
        'controller_path' => 'index',
        'default_path'    => APP_PATH, //Path where the controllers are located
        'suffix'          => '_controller.php', //suffix for controler
        'dir'             => 'controllers', //dir of controller
    );

    /**
     * This is the name of router class
     * @var String
     */
    protected static $router = 'KumbiaRouter';
    //It is the default router

    /**
     * Indicates whether the execution of a route by the dispatcher is pending
     *
     * @var boolean
     */
    protected static $routed = false;

    /**
     * Basic Router Processing
     * @param string $url
     * 
     * @throws KumbiaException
     * @return void
     */
    public static function init($url)
    {
        // The parameters are looked for security
        if (stripos($url, '/../') !== false) {
            throw new KumbiaException("Possible attempt to hack in URL: '$url'");
        }
        // If there is an attempt to hack EVERYTHING: add the ip and referer in the log
        self::$vars['route'] = $url;
        //Method used
        self::$vars['method'] = $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Run a url
     *
     * @param string $url
     * 
     * @throws KumbiaException
     * @return Controller
     */
    public static function execute($url)
    {
        self::init($url);
        //alias
        $router = self::$router;
        $conf   = Config::get('config.application.routes');
        //If config.ini has routes activated, see if it is routed
        if ($conf) {
            /*The router is activated*/
            /* This if for back compatibility*/
            if ($conf === '1') {
                $url = $router::ifRouted($url);
            } else {
                /*It is another kind of router*/
                $router = self::$router = $conf;
            }
        }

        // It breaks down the url
        self::$vars = $router::rewrite($url) + self::$vars;

        // Dispatch the current route
        return self::dispatch($router::getController(self::$vars));
    }

    /**
     * Ship the current route
     * 
     * @param Controller $cont  Controller to use
     *
     * @throws KumbiaException
     * @return Controller
     */
    private static function dispatch($cont)
    {
        // The initialize and before filters are executed
        if ($cont->k_callback(true) === false) {
            return $cont;
        }

        //Getting the method
        try {
            $reflectionMethod = new ReflectionMethod($cont, $cont->action_name);
        } catch (ReflectionException $e) {
            throw new KumbiaException($cont->action_name, 'no_action'); //EVERYTHING: send to a controller method
        }

        //k_callback and __ constructor reserved method
        if ($cont->action_name === 'k_callback' || $reflectionMethod->isConstructor()) {
            throw new KumbiaException('You are trying to execute a reserved method of KumbiaPHP');
        }

        //it is verified that the parameters it receives
        //the action is the correct amount
        $num_params = count($cont->parameters);
        if ($cont->limit_params && ($num_params < $reflectionMethod->getNumberOfRequiredParameters() ||
            $num_params > $reflectionMethod->getNumberOfParameters())) {
            throw new KumbiaException(null, 'num_params');
        }

        try {
            $reflectionMethod->invokeArgs($cont, $cont->parameters);
        } catch (ReflectionException $e) {
            throw new KumbiaException(null, 'no_action'); // EVERYTHING: better no_public
        }

        //Run the filters after and finalize
        $cont->k_callback();

        //If it is routed internally rerun
        self::isRouted();

        return $cont;
    }

    /**
     * Redirect execution internally
     * 
     * @throws KumbiaException
     * @return void
     */
    protected static function isRouted()
    {
        if (self::$routed) {
            self::$routed = false;
            $router = self::$router;
            //Dispatch the current route
            self::dispatch($router::getController(self::$vars));
        }
    }

    /**
     * Send the value of an attribute or array with all the attributes and their router values
     * Look at the router vars attribute
     * ex.
     * <code> Router :: get () </code>
     *
     * ex.
     * <code> Router :: get ('controller') </code>
     *
     * @param string $var (optional) an attribute: route, module, controller, action, parameters or routed
     * 
     * @return array|string with the attribute value
     */
    public static function get($var = '')
    {
        return ($var) ? self::$vars[$var] : self::$vars;
    }

    /**
     * Redirect the execution internally or externally with its own routes
     *
     * @param array $params $ vars array (module, controller, action, params, ...)
     * @param boolean $intern if the redirection is internal
     * 
     * @return void
     */
    public static function to($params, $intern = false)
    {
        if ($intern) {
            self::$routed = true;
        }
        self::$vars = $params + self::$vars;
    }
}
