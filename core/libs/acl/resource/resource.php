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
 * @package    Acl
 * @subpackage AclResource
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Class for the creation of ACL Resources
 *
 * @category   Kumbia
 * @package    Acl
 * @subpackage AclResource
 */
class AclResource
{

    /**
     * Resource Name
     *
     * @var $name
     */
    public $name;

    /**
     * Role class builder
     *
     * @param string $name
     */
    public function __construct($name)
    {
        if ($name == '*') {
            throw new KumbiaException('Invalid Name "*" for Resource name in Acl_Resoruce::__constuct');
        }
        $this->name = $name;
    }

    /**
     * Prevents the name of the Role in the Object from being changed
     *
     * @param string $name
     * @param string $value
     */
    public function __set($name, $value)
    {
        if ($name != 'name') {
            $this->$name = $value;
        }
    }
}
