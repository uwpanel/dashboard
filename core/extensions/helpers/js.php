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
 * Helper that uses Javascript
 *
 * @category   KumbiaPHP
 * @package    Helpers
 */
class Js
{
    /**
     * Javascripts that are required by others
     *
     * @var array
     * */
    protected static $_dependencies = array();

    /**
     * Javascript
     *
     * @var array
     * */
    protected static $_js = array();

    /**
     * Javascript Directory
     *
     * @var array
     * */
    protected static $js_dir = 'javascript/';

    /**
     * Create a link in an Application with confirmation message respecting
     * Kumbia conventions
     *
     * @param string $action route to action
     * @param string $text text to display
     * @param string $confirm confirmation message
     * @param string $class additional classes for the link
     * @param string|array $attrs additional attributes
     * @return string
     */
    public static function link($action, $text, $confirm = 'Are you sure?', $class = '', $attrs = '')
    {
        $attrs = Tag::getAttrs($attrs);
        return '<a href="' . PUBLIC_PATH . "$action\" data-msg=\"$confirm\" class=\"js-confirm $class\" $attrs>$text</a>";
    }

    /**
     * Create a link to an action with confirmation message respecting
     * Kumbia conventions
     *
     * @param string $action action
     * @param string $text text to display
     * @param string $confirm confirmation message
     * @param string $class additional classes for the link
     * @param string|array $attrs additional attributes
     * @return string
     */
    public static function linkAction($action, $text, $confirm = 'Are you sure?', $class = '', $attrs = '')
    {
        $attrs = Tag::getAttrs($attrs);
        return '<a href="' . PUBLIC_PATH . Router::get('controller_path') . "/$action\" data-msg=\"$confirm\" class=\"js-confirm $class\" $attrs>$text</a>";
    }

    /**
     * Create a submit button with confirmation message respecting
     * Kumbia conventions
     *
     * @param string $text text to display
     * @param string $confirm confirmation message
     * @param string $class additional classes for the link
     * @param string|array $attrs additional attributes
     * @return string
     */
    public static function submit($text, $confirm = 'Are you sure?', $class = '', $attrs = '')
    {
        $attrs = Tag::getAttrs($attrs);
        return "<input type=\"submit\" value=\"$text\" data-msg=\"$confirm\" class=\"js-confirm $class\" $attrs/>";
    }

    /**
     * Create an image type button
     *
     * @param string $img
     * @param string $class additional classes for the link
     * @param string|array $attrs additional attributes
     * @return string
     */
    public static function submitImage($img, $confirm = 'Are you sure?', $class = '', $attrs = '')
    {
        $attrs = Tag::getAttrs($attrs);
        return "<input type=\"image\" data-msg=\"$confirm\" src=\"" . PUBLIC_PATH . "img/$img\" class=\"js-confirm $class\" $attrs/>";
    }

    /**
     * Add a Javascript file to be included in the template
     *
     * @param string $file name of the file to add
     * @param array $dependencies files that are a requirement of the file to add
     */
    public static function add($file, $dependencies = array())
    {
        self::$_js[$file] = $file;
        foreach ($dependencies as $file) self::$_dependencies[$file] = $file;
    }

    /**
     * Include all Javascript files in the template added with the add method
     *
     * @return string
     */
    public static function inc()
    {
        $js = self::$_dependencies + self::$_js;
        $html = '';
        foreach ($js as $file) {
            $html .= '<script type="text/javascript" src="' . PUBLIC_PATH . self::$js_dir . "$file.js" . '"></script>';
        }
        return $html;
    }
}
