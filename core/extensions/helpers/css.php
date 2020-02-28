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
 * Helper that uses Css
 *
 * @category   KumbiaPHP
 * @package    Helpers
 */
class Css
{
    /**
     * Css that are required by others
     *
     * @var array
     * */
    protected static $_dependencies  = array();

    /**
     * Css
     *
     * @var array
     * */
    protected static $_css = array();

    /**
     * Css Directory
     *
     * @var array
     * */
    protected static $css_dir = 'css/';

    /**
     * Add a Css file outside the template to be included in the template
     *
     * @param string $file name of the file to add
     * @param array $dependencies  files that are a requirement of the file to add
     */
    public static function add($file, array $dependencies = [])
    {
        self::$_css[$file] = $file;
        foreach ($dependencies  as $file) self::$_dependencies[$file] = $file;
    }

    /**
     * Include all the CSS files in the template added with the add method
     *
     * @return string
     */
    public static function inc()
    {
        $css = self::$_dependencies  + self::$_css;
        $html = '';
        foreach ($css as $file) {
            $html .= '<link href="' . PUBLIC_PATH . self::$css_dir . "$file.css\" rel=\"stylesheet\" type=\"text/css\" />" . PHP_EOL;
        }
        return $html;
    }
}
