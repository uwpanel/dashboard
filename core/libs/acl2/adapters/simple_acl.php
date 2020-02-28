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
 * ACL implementation with PHP rules definition
 *
 * @category   Kumbia
 * @package    Acl
 */
class SimpleAcl extends Acl2
{

    /**
     * Definition of Roles with their respective parents and resources that they can access
     *
     * @var array
     *
     * @example SimpleAcl-roles
     *   protected $_roles = array(
     *       'role1' => array(
     *           'resources' => array('resource1', 'resource2')
     *       ),
     *       'role2' => array(
     *           'resources' => array('resource2'),
     *           'parents' => array('role1')
     *       )
     *   );
     */
    protected $_roles = array();
    /**
     * System users with their respective roles
     *
     * @var array
     *
     * @example SimpleAcl-users
     * protected $_users = array(
     *     'user1' => array('role1', 'role2'),
     *     'user2' => array('role3')
     * );
     */
    protected $_users = array();

    /**
     * Sets the resources that the role can access
     *
     * @param string $role role name
     * @param array $resources resources that the role can access
     */
    public function allow($role, $resources)
    {
        $this->_roles[$role]['resources'] = $resources;
    }

    /**
     * Set the role's parents
     *
     * @param string $role role name
     * @param array $parents role parents
     */
    public function parents($role, $parents)
    {
        $this->_roles[$role]['parents'] = $parents;
    }

    /**
     * Add a user to the list with their respective roles
     *
     * @param string $user
     * @param array $roles
     */
    public function user($user, $roles)
    {
        $this->_users[$user] = $roles;
    }

    /**
     * Get the roles of the user who is validated if he can access the resource
     *
     * @param string $user user to whom access is validated
     * @return array user roles
     */
    protected function _getUserRoles($user)
    {
        if (isset($this->_users[$user])) {
            return $this->_users[$user];
        }

        return array();
    }

    /**
     * Get the resources that the role can access
     *
     * @param string $role role name
     * @return array resources that the role can access
     */
    protected function _getRoleResources($role)
    {
        if (isset($this->_roles[$role]['resources'])) {
            return $this->_roles[$role]['resources'];
        }

        return array();
    }

    /**
     * Get the parents of the role
     *
     * @param string $role role name
     * @return array role parents
     */
    protected function _getRoleParents($role)
    {
        if (isset($this->_roles[$role]['parents'])) {
            return $this->_roles[$role]['parents'];
        }

        return array();
    }
}
