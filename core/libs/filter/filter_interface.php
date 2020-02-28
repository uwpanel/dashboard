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
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Filter interface
 *
 * @category  Kumbia
 * @package   Filter
 */
interface FilterInterface
{

    /**
     * Method to run the filter
     *
     * @param string $s cadena a filtrar
     * @param array $options opciones para el filtro
     */
    public static function execute($s, $options);
}
