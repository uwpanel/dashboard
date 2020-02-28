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
 * @package    Redirect
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */
/**
 * Class to redirect requests
 *
 * @category   Kumbia
 * @package    Redirect
 */
class Redirect
{   

    /**
     * Redirect execution to another controller in a
     * determined execution time
     *
     * @param string $route route to which the request will be redirected.
     * @param integer $seconds seconds to wait before redirecting
     * @param integer $statusCode http code of the response, default 302
     * 
     * @return void
     */
    public static function to($route = '', $seconds = 0, $statusCode = 302)
    {
        $route || $route = Router::get('controller_path') . '/';

        $route = PUBLIC_PATH . ltrim($route, '/');

        if ($seconds) {
            header("Refresh: $seconds; url=$route");
            return;
        }
        header('Location: '.$route, TRUE, $statusCode);
        $_SESSION['KUMBIA.CONTENT'] = ob_get_clean();
        View::select(null, null);
    }

    /**
     * Redirect the execution to an action of the current controller in a
     * determined execution time
     *
     * @param string $action current controller action to which it is redirected
     * @param integer $seconds seconds to wait before redirecting
     * @param integer $statusCode http code of the response, default 302
     * 
     * @return void
     */
    public static function toAction($action, $seconds = 0, $statusCode = 302)
    {
        self::to(Router::get('controller_path') . "/$action", $seconds, $statusCode);
    }

    /**
     * Internal routing
     * @example
     * Redirect::intern("module: modulo", "controller: nombre", "action: accion", "parameters: 1/2")
     * 
     * @return void
     */
    public static function internal()
    {
        static $cyclic = 0;
        $url = Util::getParams(func_get_args());
        $default = array('controller' => 'index', 'action' => 'index');

        $url['parameters'] = isset($url['parameters']) ? explode('/', $url['parameters']) : array();
        
        if (isset($url['module'])) {
            $vars = $url + $default;
            $vars['controller_path'] = $vars['module'] . '/' . $vars['controller'];
        } elseif (isset($url['controller'])) {
            $vars = $url + $default;
            $vars['controller_path'] = $vars['controller'];
        } else {
            $vars = $url;
        }
        
        if (++$cyclic > 1000)
            throw new KumbiaException('Cyclic routing has been detected. This can cause stability problems.');

        Router::to($vars, TRUE);
    }
    /**
     * Internal routing
     * @deprecated It is kept by legacy temporarily
     * @example
     * Redirect::route_to("module: modulo", "controller: nombre", "action: accion", "parameters: 1/2")
     * 
     * @return void
     */
    public static function route_to() {
        self::internal(func_get_args());
    }
}
