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
 * Filter a string doing alpha numeric
 *
 * @category   Kumbia
 * @package    Filter
 * @subpackage BaseFilter
 */
class AlnumFilter implements FilterInterface
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
        /**
         * Check if PCRE is compiled to support UNICODE
         * in this way also filters tildes and other Latin characters
         */
        if (preg_match('/\pL/u', 'a')) {
            $patron = '/[^\p{L}\p{N}]/';
        } else {
            $patron = '/[^a-zA-Z0-9\s]/';
        }
        return preg_replace($patron, '', (string) $s);
    }
}
