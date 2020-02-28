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
 * @package    Security
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Class for storing values during a request.
 *
 * It allows you to store values during application execution. Implements the
 * Registry design pattern
 *
 * @category   Kumbia
 * @package    Security
 *
 */
class Registry
{

    /**
     * Variable where the record is saved
     *
     * @var array
     */
    private static $registry = array();

    /**
     * Set a registry value
     *
     * @param string $index
     * @param string $value
     */
    public static function set($index, $value)
    {
        self::$registry[$index] = $value;
    }

    /**
     * Add a value to the registry to an already established one
     *
     * @param string $index
     * @param string $value
     */
    public static function append($index, $value)
    {
        self::exist($index);
        self::$registry[$index][] = $value;
    }

    /**
     * Add a value to the registry at the start of an already established one
     *
     * @param string $index
     * @param string $value
     */
    public static function prepend($index, $value)
    {
        self::exist($index);
        array_unshift(self::$registry[$index], $value);
    }

    /**
     * Get a registry value
     *
     * @param string $index
     * @return mixed
     */
    public static function get($index)
    {
        if (isset(self::$registry[$index])) {
            return self::$registry[$index];
        }
    }

    /**
     * Create an index if it does not exist
     *
     * @param string $index
     */
    protected function exist($index)
    {
        if (!isset(self::$registry[$index])) {
            self::$registry[$index] = array();
        }
    }
}
