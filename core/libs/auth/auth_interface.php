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
 * @package    Auth
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Contains key methods that implement adapters
 *
 * @category   Kumbia
 * @package    Auth
 */
interface AuthInterface
{

    /**
     * Adapter builder
     */
    public function __construct($auth, $extra_args);

    /**
     * Get the identity data obtained by authenticating
     *
     */
    public function get_identity();

    /**
     * Authenticate a user using the adapter
     *
     * @return boolean
     */
    public function authenticate();
}
