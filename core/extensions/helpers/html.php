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
 * Helper for HTML tags.
 *
 * @category   KumbiaPHP
 */
class Html
{
    /**
     * Metatags
     *
     * @var array
     */
    protected static $_metatags = array();
    /**
     * Head links.
     *
     * @var array
     */
    protected static $_headLinks = array();

    /**
     * Create a link using the PUBLIC_PATH constant, so that it always works.
     *
     * @example <?= Html::link('user/create','Create User') ?>
     * @example Print a link to the user controller and to the create action, with the text 'Create user'
     * @example <?= Html::link('user/create','Create User', 'class="button"') ?>
     * @example The same above, but it adds the class attribute with button value
     *
     * @param string       $action Route to action
     * @param string       $text   Text to display
     * @param string|array $attrs  Additional Attributes
     *
     * @return string
     */
    public static function link($action, $text, $attrs = '')
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }

        return '<a href="' . PUBLIC_PATH . "$action\" $attrs>$text</a>";
    }

    /**
     * Create a link to an action of the same controller that we are.
     *
     * @example <?= Html::linkAction('create/','Create') ?>
     * @example Print a link to the create action of the same controller in which we are, with the text 'Create'
     * @example <?= Html::linkAction('user/create','Create User', 'class="button"') ?>
     * @example The same as above, but it adds the class attribute with button value
     *
     * @param string       $action Route to action
     * @param string       $text   Text to display
     * @param string|array $attrs  Additional Attributes
     *
     * @return string
     */
    public static function linkAction($action, $text, $attrs = '')
    {
        $action = Router::get('controller_path') . "/$action";

        return self::link($action, $text, $attrs);
    }

    /**
     * It allows to include an image, by default goes the public / img / folder
     *
     * @example <?= Html::img('logo.png','KumbiaPHP logo') ?>
     * @example Print a label img <img src = "/ img / logo.png" alt = "KumbiaPHP logo">
     * @example <?= Html::img('logo.png','KumbiaPHP logo', 'width="100px" height="100px"') ?>
     * @example Print a label img <img src = "/ img / logo.png" alt = "KumbiaPHP logo" width = "100px" height = "100px">
     *
     * @param string       $src   Image path from the public / img / folder
     * @param string       $alt   Alternative text of the image.
     * @param string|array $attrs Additional Attributes
     *
     * @return string
     */
    public static function img($src, $alt = '', $attrs = '')
    {
        return '<img src="' . PUBLIC_PATH . "img/$src\" alt=\"$alt\" " . Tag::getAttrs($attrs) . '/>';
    }

    /**
     * Create a metatag.
     *
     * @param string       $content metatag content
     * @param string|array $attrs   attributes
     */
    public static function meta($content, $attrs = '')
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }

        self::$_metatags[] = array('content' => $content, 'attrs' => $attrs);
    }

    /**
     * It includes metatags.
     *
     * @return string
     */
    public static function includeMetatags()
    {
        $code = '';
        foreach (self::$_metatags as $meta) {
            $code .= "<meta content=\"{$meta['content']}\" {$meta['attrs']}>" . PHP_EOL;
        }
        return $code;
    }

    /**
     * Create a list from an array.
     *
     * @param array        $array Array with the contents of the list
     * @param string       $type  default ul, and if not ol
     * @param string|array $attrs attributes
     *
     * @return string
     */
    public static function lists($array, $type = 'ul', $attrs = '')
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }

        $list = "<$type $attrs>" . PHP_EOL;
        foreach ($array as $item) {
            $list .= "<li>$item</li>" . PHP_EOL;
        }

        return "$list</$type>" . PHP_EOL;
    }

    /**
     * It includes the CSS.
     *
     * @return string
     */
    public static function includeCss()
    {
        $code = '';
        foreach (Tag::getCss() as $css) {
            $code .= '<link href="' . PUBLIC_PATH . "css/{$css['src']}.css\" rel=\"stylesheet\" type=\"text/css\" media=\"{$css['media']}\" />" . PHP_EOL;
        }

        return $code;
    }

    /**
     * Link an external resource.
     *
     * @param string       $href  URL of the resource to link
     * @param string|array $attrs attributes
     */
    public static function headLink($href, $attrs = '')
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }

        self::$_headLinks[] = array('href' => $href, 'attrs' => $attrs);
    }

    /**
     * Link an action.
     *
     * @param string       $action route of action
     * @param string|array $attrs  attributes
     */
    public static function headLinkAction($action, $attrs = '')
    {
        self::headLink(PUBLIC_PATH . $action, $attrs);
    }

    /**
     * Link an application resource.
     *
     * @param string       $resource resource location in public
     * @param string|array $attrs    attributes
     */
    public static function headLinkResource($resource, $attrs = '')
    {
        self::headLink(PUBLIC_PATH . $resource, $attrs);
    }

    /**
     * Includes links for the head.
     *
     * @return string
     */
    public static function includeHeadLinks()
    {
        $code = '';
        foreach (self::$_headLinks as $link) {
            $code .= "<link href=\"{$link['href']}\" {$link['attrs']} />" . PHP_EOL;
        }

        return $code;
    }

    /**
     * Includes images from gravatar.com.
     *
     * @example Simple: <?= Html::gravatar($email); ?>
     * @example Full: echo Html::gravatar( $email, $name, 20, 'http://www.example.com/default.jpg') <br>
     * @example A gravatar that is a link: echo Html::link( Html::gravatar($email), $url)
     *
     * @param string $email   Mail to get your gravatar
     * @param string $alt     Alternative text of the image. Default: gravatar
     * @param int    $size    Gravatar size. A number from 1 to 512. Default: 40
     * @param string $default URL gravatar by default if it does not exist, or a default to gravatar. Default: mm
     *
     * @return string
     */
    public static function gravatar($email, $alt = 'gravatar', $size = 40, $default = 'mm')
    {
        $grav_url = 'https://secure.gravatar.com/avatar/' . md5(strtolower(trim($email))) . '?d=' . urlencode($default) . '&amp;s=' . $size;

        return '<img src="' . $grav_url . '" alt="' . $alt . '" class="avatar" width="' . $size . '" height="' . $size . '" />';
    }
}
