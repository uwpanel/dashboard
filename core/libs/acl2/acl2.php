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
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Base class for ACL management
 *
 * New Base Class for ACL management (Access Control List) permissions
 *
 * @category   Kumbia
 * @package    Acl
 */
abstract class Acl2
{

    /**
     * Default adapter
     *
     * @var string
     */
    protected static $_defaultAdapter = 'simple';

    /**
     * Check if the user can access the resource
     *
     * @param string $resource resource to which access will be verified
     * @param string $user acl user
     * @return boolean
     */
    public function check($resource, $user)
    {
        // iterar in user roles
        foreach ($this->_getUserRoles($user) as $role) {
            if ($this->_checkRole($role, $resource)) {
                return TRUE;
            }
        }

        // By default access is denied
        return FALSE;
    }

    /**
     * Check if a role can access the resource
     *
     * @param string $role
     * @param string $resource
     * @return boolean
     */
    private function _checkRole($role, $resource)
    {
        // Verify if the role can access the resource
        if (in_array($resource, $this->_getRoleResources($role))) {
            return TRUE;
        }

        // Verify if you have inherited access, verifying parental resources
        foreach ($this->_getRoleParents($role) as $parent) {
            if ($this->_checkRole($parent, $resource)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Get the roles of the user who is validated if he can access the resource
     *
     * @param string $user user to whom access is validated
     * @return array user roles
     */
    abstract protected function _getUserRoles($user);

    /**
     * Get the resources that the role can access
     *
     * @param string $role role name
     * @return array resources that the role can access
     */
    abstract protected function _getRoleResources($role);

    /**
     * Get the parents of the role
     *
     * @param string $role role name
     * @return array role parents
     */
    abstract protected function _getRoleParents($role);

    /**
     * Get the ACL adapter
     *
     * @param string $adapter (simple, model, xml, ini)
     */
    public static function factory($adapter = '')
    {
        if (!$adapter) {
            $adapter = self::$_defaultAdapter;
        }

        require_once __DIR__ . "/adapters/{$adapter}_acl.php";
        $class = $adapter . 'acl';

        return new $class;
    }

    /**
     * Change the default adapter
     *
     * @param string $adapter default adapter name
     */
    public static function setDefault($adapter)
    {
        self::$_defaultAdapter = $adapter;
    }
}
