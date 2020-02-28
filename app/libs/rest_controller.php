<?php

/**
 * Controller to handle REST requests
 * 
 * By default each action is called as the method used by the client
 * (GET, POST, PUT, DELETE, OPTIONS, HEADERS, PURGE ...)
 * You can also add more actions by placing the method name in front
 * followed by the name of the put_cancel action, post_reset ...
 *
 * @category Kumbia
 * @package Controller
 * @author kumbiaPHP Team
 */
require_once CORE_PATH . 'kumbia/kumbia_rest.php';
class RestController extends KumbiaRest
{

    /**
     * Request Initialization
     * ****************************************
     * Here must go API authentication
     * ****************************************
     */
    final protected function initialize()
    {
    }

    final protected function finalize()
    {
    }
}
