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
 * Helper that uses Ajax
 *
 * @category   KumbiaPHP
 * @package    Helpers
 */
class Ajax
{

    /**
     * Create a link in an Application by updating the layer with ajax
     *
     * @param string $action route to action
     * @param string $text text to display
     * @param string $update layer to update
     * @param string $class additional classes
     * @param string|array $attrs additional attributes
     * @return string
     */
    public static function link($action, $text, $update, $class = '', $attrs = '')
    {
        $attrs = Tag::getAttrs($attrs);
        return '<a href="' . PUBLIC_PATH . "$action\" class=\"js-remote $class\" data-to=\"{$update}\" $attrs>$text</a>";
    }

    /**
     * Create a link to an action by updating the layer with ajax
     *
     * @param string $action route to action
     * @param string $text text to display
     * @param string $update layer to update
     * @param string $class additional classes
     * @param string|array $attrs additional attributes
     * @return string
     */
    public static function linkAction($action, $text, $update, $class = '', $attrs = '')
    {
        $attrs = Tag::getAttrs($attrs);
        return '<a href="' . PUBLIC_PATH . Router::get('controller_path') . "/$action\" class=\"js-remote $class\" data-to=\"{$update}\" $attrs>$text</a>";
    }

    /**
     * Create a link in an Application by updating the layer with ajax with message
     * confirmation
     *
     * @param string $action route to action
     * @param string $text text to display
     * @param string $update layer to update
     * @param string $confirm confirmation message
     * @param string $class additional classes
     * @param string|array $attrs additional attributes
     * @return string
     */
    public static function linkConfirm($action, $text, $update, $confirm, $class = '', $attrs = '')
    {
        $attrs = Tag::getAttrs($attrs);
        return '<a href="' . PUBLIC_PATH . "$action\" class=\"js-remote-confirm $class\" data-to=\"{$update}\" title=\"$confirm\" $attrs>$text</a>";
    }

    /**
     * Create a link to an action by updating the layer with ajax with message
     * confirmation
     *
     * @param string $action route to action
     * @param string $text text to display
     * @param string $update layer to update
     * @param string $confirm confirmation message
     * @param string $class additional classes
     * @param string|array $attrs additional attributes
     * @return string
     */
    public static function linkActionConfirm($action, $text, $update, $confirm, $class = '', $attrs = '')
    {
        $attrs = Tag::getAttrs($attrs);
        return '<a href="' . PUBLIC_PATH . Router::get('controller_path') . "/$action\" class=\"js-remote-confirm $class\" data-to=\"{$update}\" title=\"$confirm\" $attrs>$text</a>";
    }

    /**
     * Drop-down list to update using ajax
     *
     * @param string $field field name
     * @param array $data
     * @param string $update layer to be updated
     * @param string $action action to be executed
     * @param string $class
     * @param string|array $attrs
     */
    public static function select($field, $data, $update, $action, $class = '', $attrs = '')
    {
        $attrs = Tag::getAttrs($attrs);
        // route to action
        $action = PUBLIC_PATH . rtrim($action, '/') . '/';
        // generate the field
        return Form::select($field, $data, "class=\"js-remote $class\" data-update=\"$update\" data-url=\"$action\" $attrs");
    }

    /**
     * Drop-down list to update using ajax that takes the values of an array of objects
     *
     * @param string $field field name
     * @param string $show field to be displayed
     * @param array  $data Array('modelo','metodo','param')
     * @param string $update layer to be updated
     * @param string $action action to be executed
     * @param string $blank blank field
     * @param string $class
     * @param string|array $attrs
     */
    public static function dbSelect($field, $show, $data, $update, $action, $blank = null, $class = '', $attrs = '')
    {
        $attrs = Tag::getAttrs($attrs);
        // route to action
        $action = PUBLIC_PATH . rtrim($action, '/') . '/';

        // generate the field
        return Form::dbSelect($field, $show, $data, $blank, "class=\"js-remote $class\" data-update=\"$update\" data-url=\"$action\" $attrs");
    }

    /**
     * Generate an Ajax form
     *
     * @param string $update layer to be updated
     * @param string $action action to execute
     * @param string $class style class
     * @param string $method shipping method
     * @param string|array $attrs attributes
     * @return string
     */
    public static function form($update, $action = '', $class = '', $method = 'post', $attrs = '')
    {
        $attrs = "class=\"js-remote $class\" data-to=\"$update\" " . Tag::getAttrs($attrs);
        return Form::open($action, $method, $attrs);
    }
}
