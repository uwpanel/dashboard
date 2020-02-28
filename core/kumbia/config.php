<?php

/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @category   Config
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Class for loading .INI files and configuration.
 *
 * Apply the Singleton pattern that uses an array
 * indexed by file name to prevent
 * a configuration .ini is read more than one
 * time in runtime with which we increase the speed.
 *
 * @category   Kumbia
 */
class Config
{
    /**
     * Contain all the config
     * -
     * Content of configuration variables.
     *
     * @var array
     */
    protected static $vars = [];

    /**
     * Get config vars
     * -
     * Get settings.
     *
     * @param string $var variable.section.sector
     *
     * @return mixed
     */
    public static function get($var)
    {
        $namespaces = explode('.', $var);
        if (!isset(self::$vars[$namespaces[0]])) {
            self::load($namespaces[0]);
        }
        switch (count($namespaces)) {
            case 3:
                return isset(self::$vars[$namespaces[0]][$namespaces[1]][$namespaces[2]]) ?
                    self::$vars[$namespaces[0]][$namespaces[1]][$namespaces[2]] : null;
            case 2:
                return isset(self::$vars[$namespaces[0]][$namespaces[1]]) ?
                    self::$vars[$namespaces[0]][$namespaces[1]] : null;
            case 1:
                return isset(self::$vars[$namespaces[0]]) ? self::$vars[$namespaces[0]] : null;

            default:
                trigger_error('Maximum 3 levels in Config::get(variable.section.sector), order: ' . $var);
        }
    }

    /**
     * Get all configs
     * -
     * Get all the settings.
     *
     * @return array
     */
    public static function getAll()
    {
        return self::$vars;
    }

    /**
     * Set variable in config
     * -
     * Assign a configuration attribute.
     *
     * @param string $var   configuration variable
     * @param mixed  $value attribute value
     * 
     * @return void
     */
    public static function set($var, $value)
    {
        $namespaces = explode('.', $var);
        switch (count($namespaces)) {
            case 3:
                self::$vars[$namespaces[0]][$namespaces[1]][$namespaces[2]] = $value;
                break;
            case 2:
                self::$vars[$namespaces[0]][$namespaces[1]] = $value;
                break;
            case 1:
                self::$vars[$namespaces[0]] = $value;
                break;
            default:
                trigger_error('Maximum 3 levels in Config::set(variable.section.sector), order: ' . $var);
        }
    }

    /**
     * Read config file
     * -
     * Read and return a configuration file.
     *
     * @param string $file  .php or .ini file
     * @param bool   $force force reading of .php or .ini
     *
     * @return array
     */
    public static function &read($file, $force = false)
    {
        if (isset(self::$vars[$file]) && !$force) {
            return self::$vars[$file];
        }
        self::load($file);

        return self::$vars[$file];
    }

    /**
     * Load config file
     * -
     * Read a configuration file.
     *
     * @param string $file archive
     * 
     * @return void
     */
    private static function load($file)
    {
        if (file_exists(APP_PATH . "config/$file.php")) {
            self::$vars[$file] = require APP_PATH . "config/$file.php";

            return;
        }
        // but load the .ini
        self::$vars[$file] = parse_ini_file(APP_PATH . "config/$file.ini", true);
    }
}
