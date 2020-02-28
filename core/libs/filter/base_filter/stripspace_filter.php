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
 * @package    Filter
 * @subpackage BaseFilter
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Filter a string to remove spaces
 *
 * @category   Kumbia
 * @package    Filter
 * @subpackage BaseFilter
 */
class StripspaceFilter implements FilterInterface
{

    /**
     * Run the filter
     *
     * @param string $s
     * @param array $options
     * @return string
     */
    public static function execute($s, $options)
    {
        $patron = '/[\s]/';
        return preg_replace($patron, '', (string) $s);
    }
}