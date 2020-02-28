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
 * Helper base for tag creation
 *
 * @category   KumbiaPHP
 * @package    Helpers
 */
class Tag
{

    /**
     * Style sheets
     *
     * @var array
     * */
    protected static $_css = array();

    /**
     * Convert the arguments of a parameter method by name to a string with the attributes
     *
     * @param string|array $params arguments to convert
     * @return string
     */
    public static function getAttrs($params)
    {
        if (!is_array($params)) {
            return (string) $params;
        }
        $data = '';
        foreach ($params as $k => $v) {
            $data .= "$k=\"$v\" ";
        }
        return trim($data);
    }

    /**
     * Create a tag
     *
     * @param string $tag tag name
     * @param string|null $content internal content
     * @param string|array $attrs attributes for the tag
     * @return string
     * */
    public static function create($tag, $content = null, $attrs = '')
    {
        if (is_array($attrs)) {
            $attrs = self::getAttrs($attrs);
        }

        if (is_null($content)) {
            echo "<$tag $attrs/>";
            return;
        }

        echo "<$tag $attrs>$content</$tag>";
    }

    /**
     * It includes a javascript file
     *
     * @param string $src javascript file
     * @param boolean $cache indicates if browser cache is used
     */
    public static function js($src, $cache = TRUE)
    {
        $src = "javascript/$src.js";
        if (!$cache) {
            $src .= '?nocache=' . uniqid();
        }

        return '<script type="text/javascript" src="' . PUBLIC_PATH . $src . '"></script>';
    }

    /**
     * Includes a css file
     *
     * @param string $src css file
     * @param string $media middle style sheet
     */
    public static function css($src, $media = 'screen')
    {
        self::$_css[] = array('src' => $src, 'media' => $media);
    }

    /**
     * Get the array of style sheets
     *
     * @return array
     */
    public static function getCss()
    {
        return self::$_css;
    }
}
