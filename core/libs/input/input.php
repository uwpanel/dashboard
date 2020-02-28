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
 * @package    Input
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/** 
 * Class to handle request data
 *
 * @category   Kumbia
 * @package    Input
 */
class Input
{
    /**
     * Verify or obtain the request method
     *
     * @param string $method Http method
     * @return mixed
     */
    public static function is($method = '')
    {
        if ($method) {
            return $method === $_SERVER['REQUEST_METHOD'];
        }
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Indicates if the request is AJAX
     *
     * @return boolean
     */
    public static function isAjax()
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
    }

    /**
     * Detects if the User Agent is a mobile
     *
     * @return boolean
     */
    public static function isMobile()
    {
        return strpos(mb_strtolower($_SERVER['HTTP_USER_AGENT']), 'mobile') ? TRUE : FALSE;
    }

    /**
     * Gets a value of the $_POST array
     *
     * @param string $var
     * @return mixed
     */
    public static function post($var = '')
    {
        return self::getFilter($_POST, $var);
    }

    /**
     * Get a value from the $_GET array, apply the FILTER_SANITIZE_STRING filter
     * default
     *
     * @param string $var
     * @return mixed
     */
    public static function get($var = '')
    {
        return self::getFilter($_GET, $var);
    }

    /**
     *  Gets a value of the $_REQUEST array
     *
     * @param string $var
     * @return mixed
     */
    public static function request($var = '')
    {
        return self::getFilter($_REQUEST, $var);
    }


    /**
     * Gets a value of the $ _SERVER array
     *
     * @param string $var
     * @return mixed
     */
    public static function server($var = '')
    {
        return self::getFilter($_SERVER, $var);
    }

    /**
     *  Check if the item indicated in $ _POST exists
     *
     * @param string $var item to verify
     * @return boolean
     */
    public static function hasPost($var)
    {
        return (bool) self::post($var);
    }

    /**
     * Check if the item indicated in $ _GET exists
     *
     * @param string $var item to verify
     * @return boolean
     */
    public static function hasGet($var)
    {
        return (bool) self::get($var);
    }

    /**
     * Check if the item indicated in $ _REQUEST exists
     *
     * @param string $var item to verify
     * @return boolean
     */
    public static function hasRequest($var)
    {
        return (bool) self::request($var);
    }

    /**
     * Remove item indicated in $ _POST
     *
     * @param string $var item to verify
     * @return boolean|null
     */
    public static function delete($var = '')
    {
        if ($var) {
            $_POST[$var] = array();
            return;
        }
        $_POST = array();
    }

    /**
     * It allows to obtain the User Agent
     * @return String
     */
    public static function userAgent()
    {
        return $_SERVER['HTTP_USER_AGENT'];
    }

    /**
     * It allows to obtain the client's IP, even when using proxy
     * @return String
     */
    public static function ip()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return $_SERVER['REMOTE_ADDR'];
    }


    /**
     * Get and filter a value from the $_REQUEST array
     * By default, use SANITIZE
     *
     * @param string $var
     * @return mixed
     */
    public static function filter($var)
    {
        //EVERYTHING
    }

    /**
     * Returns the value within an array with key in format uno.dos.tres
     * @param Array array that contains the variable
     * @param string $str key to use
     * @return mixed
     */
    protected static function getFilter(array $var, $str)
    {
        if (empty($str)) {
            return filter_var_array($var);
        }
        $arr = explode('.', $str);
        $value = $var;
        foreach ($arr as $key) {
            if (isset($value[$key])) {
                $value = $value[$key];
            } else {
                $value = NULL;
                break;
            }
        }
        return is_array($value) ? filter_var_array($value) : filter_var($value);
    }
}
