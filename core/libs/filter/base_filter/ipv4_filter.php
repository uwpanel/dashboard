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
 * Filter a string to be IPv4
 *
 * @category   Kumbia
 * @package    Filter
 * @subpackage BaseFilter
 */
class Ipv4Filter implements FilterInterface
{

    /**
     * Run the filter
     *
     * @param string $value
     * @param array $options
     * @return string
     */
    public static function execute($value, $options)
    {
        $patron = '/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/';
        if (preg_match($patron, $value, $regs)) {
            return $regs[0];
        } else {
            return '';
        }
    }
}
