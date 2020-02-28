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
 * @package    Rest
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 * 
 * @deprecated Deprecated since version 1.0, Use RestController
 */

/**
 * * Class for API management based on RESTClase para el manejo de API basada en REST
 *
 * @category   Kumbia
 * @package    Controller
 * @deprecated Deprecated since version 1.0, Use RestController
 *
 */
class Rest
{

    private static $code = array(
        201 => 'Creado ', /* A new recourse has been created (INSERT) */
        400 => 'Bad Request', /* Herronea request */
        401 => 'Unauthorized', /*  The request requires loggin */
        403 => 'Forbidden',
        405 => 'Method Not Allowed'/* This method is not allowed. */
    );
    /**
     * Array with the data types supported for output
     */
    private static $_outputFormat = array('json', 'text', 'html', 'xml', 'cvs', 'php');
    /**
     *  Type of data supported for input
     */
    private static $_inputFormat = array('json', 'plain', 'x-www-form-urlencoded');
    /**
     * Request method (GET, POST, PUT, DELETE)
     */
    private static $_method = null;
    /**
     * Set the output format
     */
    private static $_oFormat = null;
    /**
     *  Set the input format
     */
    private static $_iFormat = null;

    /**
     * Set the accepted response types
     *
     * @param string $accept Each of the comma separated types','
     */
    static public function accept($accept)
    {
        self::$_outputFormat = is_array($accept) ? $accept : explode(',', $accept);
    }

    /**
     *  Defines the start of a REST service
     *
     * @param Controller $controller controller that will become a REST service
     */
    static public function init(Controller $controller)
    {
        $content = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : 'text/html';
        /**
         * I check the input format
         */
        self::$_iFormat = str_replace(array('text/', 'application/'), '', $content);

        /* I check the request method */
        self::$_method = strtolower($_SERVER['REQUEST_METHOD']);
        $format        = explode(',', $_SERVER['HTTP_ACCEPT']);
        while (self::$_oFormat = array_shift($format)) {
            self::$_oFormat = str_replace(array('text/', 'application/'), '', self::$_oFormat);
            if (in_array(self::$_oFormat, self::$_outputFormat)) {
                break;
            }
        }

        /**
         * If I can't find it, I scramble a mistake
         */
        if (self::$_oFormat == null) {
            return 'error';
        } else {
            View::response(self::$_oFormat);
            View::select('response');
        }

        /**
         * If the controller action is a number we pass it to the parameters
         */
        if (is_numeric($controller->action_name)) {
            $controller->parameters = array($controller->action_name) + Rest::param();
        } else {
            $controller->parameters = Rest::param();
        }

        /**
         * we rewrite the action to be executed, now it will be the method of
         * the request: get, put, post, delete, etc.
         */
        $controller->action_name  = self::$_method;
        $controller->limit_params = FALSE; //There is no verification in the number of parameters.
        $controller->data         = array(); //default variable for views.

    }

    /**
     * Returns the parameters of the request the function of the method
     * of the petition
     * @return Array
     */
    public static function param()
    {
        $input = file_get_contents('php://input');
        if (strncmp(self::$_iFormat, 'json', 4) == 0) {
            return json_decode($input, true);
        } else {
            parse_str($input, $output);
            return $output;
        }
    }
}
