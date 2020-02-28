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
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Utilities for general use of the framework.
 *
 * Character string management.
 * Conversion of parameters with name to arrangements.
 *
 * @category   Kumbia
 */
class Util
{
    /**
     * Convert the string with spaces or underscore to camelcase notation.
     *
     * @param string $str   string to convert
     * @param bool   $lower indicates if it is lower camelcase
     *
     * @return string
     * */
    public static function camelcase($str, $lower = false)
    {
        // LowerCamelCase notation
        if ($lower) {
            return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $str))));
        }

        return str_replace(' ', '', ucwords(str_replace('_', ' ', $str)));
    }

    /**
     * Convert the CamelCase string into smallcase notation.
     *
     * @param string $str string to convert
     *
     * @return string
     * */
    public static function smallcase($str)
    {
        return strtolower(preg_replace('/([A-Z])/', '_\\1', lcfirst($str)));
    }

    /**
     * Replace the spaces with underscores in the chain.
     *
     * @param string $str
     *
     * @return string
     * */
    public static function underscore($str)
    {
        return strtr($str, ' ', '_');
    }

    /**
     * Replace the spaces with dash (dashes) in the string.
     *
     * @param string $str
     *
     * @return string
     */
    public static function dash($str)
    {
        return strtr($str, ' ', '-');
    }

    /**
     * Replace underscore or dashed in a string with spaces.
     *
     * @param string $str
     *
     * @return string
     */
    public static function humanize($str)
    {
        return strtr($str, '_-', '  ');
    }

    /**
     * Convert the parameters of a function or parameter method by name to an array.
     *
     * @param array $params
     *
     * @return array
     */
    public static function getParams($params)
    {
        $data = array();
        foreach ($params as $p) {
            if (is_string($p)) {
                $match = explode(': ', $p, 2);
                if (isset($match[1])) {
                    $data[$match[0]] = $match[1];
                } else {
                    $data[] = $p;
                }
            } else {
                $data[] = $p;
            }
        }

        return $data;
    }

    /**
     * Receive a string like: item1, item2, item3 and return one like: "item1", "item2", "item3".
     *
     * @param string $lista string with Items separated by commas (,)
     *
     * @return string string with items enclosed in double quotes and separated by commas (,)
     */
    public static function encomillar($lista)
    {
        $items = explode(',', $lista);

        return '"' . implode('","', $items) . '"';
    }
}
