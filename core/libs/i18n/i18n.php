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
 * @package    I18n
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */
/**
 * Linking to the application textdomain
 */
bindtextdomain('default', APP_PATH . 'locale/');
textdomain('default');

/**
 * Implementation for internationalization
 *
 * @category   Kumbia
 * @package    I18n
 */
class I18n
{

    /**
     * Make a translation. When additional arguments are passed it is replaced with sprintf
     *
     * @example
     *   $saludo = I18n::get('Hi %s', 'Emilio')
     *
     * @param string
     * @return string
     * */
    public static function get($sentence)
    {
        /**
         * I get the translation
         * */
        $sentence = gettext($sentence);

        /**
         * If multiple parameters are passed
         * */
        if (func_num_args() > 1) {
            $args = func_get_args();
            /**
             * It is replaced with vsprintf
             * */
            unset($args[0]);
            $sentence = vsprintf($sentence, $args);
        }

        return $sentence;
    }

    /**
     * Gets a plural translation, when additional arguments are passed it is replaced with sprintf
     *
     * @param string $sentence1 singular message
     * @param string $sentence2 plural message
     * @param int $n count
     * @return string
     * */
    public static function nget($sentence1, $sentence2, $n)
    {
        /**
         * I get the translation
         * */
        $sentence = ngettext($sentence1, $sentence2, $n);

        /**
         * If multiple parameters are passed
         * */
        if (func_num_args() > 3) {
            $sentence = $sentence = self::sprintf($sentence, func_get_args(), 3);
        }

        return $sentence;
    }

    /**
     * Get a translation by category, when additional arguments are passed it is replaced with sprintf
     *
     * @param string $sentence
     * @param int $category message category (LC_CTYPE, LC_NUMERIC, LC_TIME, LC_COLLATE, LC_MONETARY, LC_MESSAGES, LC_ALL)
     * @return string
     * */
    public static function cget($sentence, $category)
    {
        /**
         * I get the translation
         * */
        $sentence = dcgettext(textdomain(null), $sentence, $category);

        /**
         * If multiple parameters are passed
         * */
        if (func_num_args() > 2) {
            $sentence = $sentence = self::sprintf($sentence, func_get_args(), 2);
        }

        return $sentence;
    }

    /**
     * Gets a plural translation by category, when additional arguments are passed it is replaced with sprintf
     *
     * @param string $sentence1 singular message
     * @param string $sentence2 plural message
     * @param int $n count
     * @param int $category message category (LC_CTYPE, LC_NUMERIC, LC_TIME, LC_COLLATE, LC_MONETARY, LC_MESSAGES, LC_ALL)
     * @return string
     * */
    public static function cnget($sentence1, $sentence2, $n, $category)
    {
        /**
         * I get the translation based on the domain
         * */
        $sentence = dcngettext(textdomain(null), $sentence1, $sentence2, $n, $category);

        /**
         * If multiple parameters are passed
         * */
        if (func_num_args() > 4) {
            $sentence = self::sprintf($sentence, func_get_args(), 4);
        }

        return $sentence;
    }


    private static function sprintf($sentence, $args, $offset)
    {
        return vsprintf($sentence, array_slice($args, $offset));
    }
}
