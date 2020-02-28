<?php

/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @category   KumbiaPHP
 * @package    Helpers
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Class to send messages on demand
 *
 * Sending warning messages, success, information
 * and errors in sight.
 * It also sends messages on the console, if used from the console.
 *
 * @category   Kumbia
 * @package    Flash
 */
class Flash
{

    /**
     * Display a flash message
     *
     * @param string $name  For message type and for CSS class = '$name'.
     * @param string $text  Message to show
     */
    public static function show($name, $text)
    {
        if (isset($_SERVER['SERVER_SOFTWARE'])) {
            echo '<div class="', $name, ' flash">', $text, '</div>', PHP_EOL;
            return;
        }
        // CLI output
        echo $name, ': ', strip_tags($text), PHP_EOL;
    }

    /**
     * Display an error message
     *
     * @param string $text
     */
    public static function error($text)
    {
        return self::show('error', $text);
    }

    /**
     * Display a warning message on the screen
     *
     * @param string $text
     */
    public static function warning($text)
    {
        return self::show('warning', $text);
    }

    /**
     * Display information on screen
     *
     * @param string $text
     */
    public static function info($text)
    {
        return self::show('info', $text);
    }

    /**
     * View correct event information on screen
     *
     * @param string $text
     */
    public static function valid($text)
    {
        return self::show('valid', $text);
    }
}
