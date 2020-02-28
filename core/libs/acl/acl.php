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
 * @see AclRole
 */
include __DIR__ . '/role/role.php';

/**
 * @see AclResource
 */
include __DIR__ . '/resource/resource.php';

/**
 * ACL Lists (Access Control List)
 *
 * The Access Control List (ACLs)
 * it is a concept of computer security used to encourage separation
 * of privileges It is a way to determine the appropriate access permissions
 * to a certain object, depending on certain aspects of the process
 * Who makes the order.
 *
 * Each ACL list contains a list of Roles, resources and actions of
 * access;
 *
 * $roles = List of Acl_Role Objects from List Roles
 * $resources = List of Acl_Resource Objects to be controlled
 * $access = It contains the access list
 * $role_inherits = Contains the list of roles that are inherited by others
 * $resource_names = Resources names
 * $roles_names = Resources names
 *
 * @category   Kumbia
 * @package    Acl
 * @deprecated 1.0 use ACL2
 */
class Acl
{

    /**
     * Role names in the ACL list
     *
     * @var array
     */
    private $roles_names = array();
    /**
     * Roles objects in ACL list
     *
     * @var array
     */
    private $roles = array();
    /**
     * Resources objects in the ACL list
     *
     * @var array
     */
    private $resources = array();
    /**
     * Access List Permissions
     *
     * @var array
     */
    public $access = array();
    /**
     * Role Inheritance
     *
     * @var array
     */
    private $role_inherits = array();
    /**
     * Array of Resource Names
     *
     * @var array
     */
    private $resources_names = array('*');
    /**
     * ACL Permission List
     *
     * @var array
     */
    private $access_list = array('*' => array('*'));

    /**
     * Add a Role to the ACL List
     *
     * $roleObject = Object of the AclRole class to add to the list
     * $access_inherits = Name of the Role from which inherits permissions or group array
     * of profiles from which it inherits permissions
     *
     * Ex:
     * <code>$acl->add_role(new Acl_Role('administrator'), 'consultant');</code>
     *
     * @param AclRole $roleObject
     * @return false|null
     */
    public function add_role(AclRole $roleObject, $access_inherits = '')
    {
        if (in_array($roleObject->name, $this->roles_names)) {
            return false;
        }
        $this->roles[]                             = $roleObject;
        $this->roles_names[]                       = $roleObject->name;
        $this->access[$roleObject->name]['*']['*'] = 'A';
        if ($access_inherits) {
            $this->add_inherit($roleObject->name, $access_inherits);
        }
    }

    /**
     * Makes one role inherit accesses from another role
     *
     * @param string $role
     * @param string $role_to_inherit
     */
    public function add_inherit($role, $role_to_inherit)
    {
        if (!in_array($role, $this->roles_names)) {
            return false;
        }
        if ($role_to_inherit != '') {
            if (is_array($role_to_inherit)) {
                foreach ($role_to_inherit as $rol_in) {
                    if ($rol_in == $role) {
                        return false;
                    }
                    if (!in_array($rol_in, $this->roles_names)) {
                        throw new KumbiaException("The role'{$rol_in}' does not exist in the list");
                    }
                    $this->role_inherits[$role][] = $rol_in;
                }
                $this->rebuild_access_list();
            } else {
                if ($role_to_inherit == $role) {
                    return false;
                }
                if (!in_array($role_to_inherit, $this->roles_names)) {
                    throw new KumbiaException("The role'{$role_to_inherit}' does not exist in the list");
                }
                $this->role_inherits[$role][] = $role_to_inherit;
                $this->rebuild_access_list();
            }
        } else {
            throw new KumbiaException("You must specify a role to inherit in Acl::add_inherit");
        }
    }

    /**
     *
     * Check if a role exists in the list or not
     *
     * @param string $role_name
     * @return boolean
     */
    public function is_role($role_name)
    {
        return in_array($role_name, $this->roles_names);
    }

    /**
     *
     * Check if a resource exists in the list or not
     *
     * @param string $resource_name
     * @return boolean
     */
    public function is_resource($resource_name)
    {
        return in_array($resource_name, $this->resources_names);
    }

    /**
     * Add a resource to the ACL List
     *
     * Resource_name can be the name of a specific object, for example
     * query, search, insert, validate etc or a list of them
     *
     * Ex:
     * <code>
     * // Add a resource to the list:
     * $acl->add_resource(new AclResource('customers'), 'query');
     *
     * // Add Various resources to the list:
     * $acl->add_resource(new AclResource('customers'), 'query', 'search', 'insert');
     * </code>
     *
     * @param AclResource $resource
     * @return boolean|null
     */
    public function add_resource(AclResource $resource)
    {
        if (!in_array($resource->name, $this->resources)) {
            $this->resources[]                  = $resource;
            $this->access_list[$resource->name] = array();
            $this->resources_names[]            = $resource->name;
        }
        if (func_num_args() > 1) {
            $access_list = func_get_args();
            unset($access_list[0]);
            $this->add_resource_access($resource->name, $access_list);
        }
    }

    /**
     * Add access to a Resource
     *
     * @param string $resource
     * @param $access_list
     */
    public function add_resource_access($resource, $access_list)
    {
        if (is_array($access_list)) {
            foreach ($access_list as $access_name) {
                if (!in_array($access_name, $this->access_list[$resource])) {
                    $this->access_list[$resource][] = $access_name;
                }
            }
        } else {
            if (!in_array($access_list, $this->access_list[$resource])) {
                $this->access_list[$resource][] = $access_list;
            }
        }
    }

    /**
     * Remove a resorce access
     *
     * @param string $resource
     * @param mixed $access_list
     */
    public function drop_resource_access($resource, $access_list)
    {
        if (is_array($access_list)) {
            foreach ($access_list as $access_name) {
                if (in_array($access_name, $this->access_list[$resource])) {
                    foreach ($this->access_list[$resource] as $i => $access) {
                        if ($access == $access_name) {
                            unset($this->access_list[$resource][$i]);
                        }
                    }
                }
            }
        } else {
            if (in_array($access_list, $this->access_list[$resource])) {
                foreach ($this->access_list[$resource] as $i => $access) {
                    if ($access == $access_list) {
                        unset($this->access_list[$resource][$i]);
                    }
                }
            }
        }
        $this->rebuild_access_list();
    }

    /**
     * Add a resource list access to a role
     *
     * Use '*' as a wild card
     *
     * Ex:
     * <code>
     * //Access for guests to consult clients
     * $acl->allow('guests', 'customers', 'query');
     *
     * //Access for guests to consult and insert in clients
     * $acl->allow('guests', 'customers', array('query', 'insert'));
     *
     * //Access for anyone to view on products
     * $acl->allow('*', 'products', 'visualize');
     *
     * //Access for anyone to view in any resource
     * $acl->allow('*', '*', 'visualize');
     * </code>
     *
     * @param string $role
     * @param string $resource
     * @param mixed $access
     */
    public function allow($role, $resource, $access)
    {
        if (!in_array($role, $this->roles_names)) {
            throw new KumbiaException("There is no role'$role' on the list");
        }
        if (!in_array($resource, $this->resources_names)) {
            throw new KumbiaException("The resource does not exist '$resource' on the list");
        }
        if (is_array($access)) {
            foreach ($access as $acc) {
                if (!in_array($acc, $this->access_list[$resource])) {
                    throw new KumbiaException("There is no access'$acc' in the resource '$resource' of the list");
                }
            }
            foreach ($access as $acc) {
                $this->access[$role][$resource][$acc] = 'A';
            }
        } else {
            if (!in_array($access, $this->access_list[$resource])) {
                throw new KumbiaException("There is no access '$access' in the resource '$resource' of the list");
            }
            $this->access[$role][$resource][$access] = 'A';
            $this->rebuild_access_list();
        }
    }

    /**
     * Deny a resource list access to a role
     *
     * Use '*' as a wild card
     *
     * Ex:
     * <code>
     * //Denies access for guests to consult clients
     * $acl->deny('guests', 'customers', 'query');
     *
     * //Denies access for guests to consult and insert in clients
     * $acl->deny('guests', 'customers', array('query', 'insert'));
     *
     * //Denies access for anyone to view products
     * $acl->deny('*', 'products', 'visualize');
     *
     * //Deny access for anyone to view on any resource
     * $acl->deny('*', '*', 'visualize');
     * </code>
     *
     * @param string $role
     * @param string $resource
     * @param mixed $access
     */
    public function deny($role, $resource, $access)
    {
        if (!in_array($role, $this->roles_names)) {
            throw new KumbiaException("There is no role '$role' on the list");
        }
        if (!in_array($resource, $this->resources_names)) {
            throw new KumbiaException("The resource does not exist'$resource' on the list");
        }
        if (is_array($access)) {
            foreach ($access as $acc) {
                if (!in_array($acc, $this->access_list[$resource])) {
                    throw new KumbiaException("There is no access'$acc' in the resource '$resource' of the list");
                }
            }
            foreach ($access as $acc) {
                $this->access[$role][$resource][$acc] = 'D';
            }
        } else {
            if (!in_array($access, $this->access_list[$resource])) {
                throw new KumbiaException("There is no access '$access' in the resource '$resource' of the list");
            }
            $this->access[$role][$resource][$access] = 'D';
            $this->rebuild_access_list();
        }
    }

    /**
     * Returns true if a $ role has access to a resource
     *
     * <code>
     * //Andrew has access to insert in the resource products
     * $acl->is_allowed('Andrew', 'products', 'insert');
     *
     * //Guest have access to edit in any resource?
     * $acl->is_allowed('invited', '*', 'Edit');
     *
     * //Guest have access to edit in any resource?
     * $acl->is_allowed('invited', '*', 'Edit');
     * </code>
     *
     * @param string $role
     * @param string $resource
     * @param mixed $access_list
     * @return boolean|null
     */
    public function is_allowed($role, $resource, $access_list)
    {
        if (!in_array($role, $this->roles_names)) {
            throw new KumbiaException("The role'$role' does not exist in the list inacl::is_allowed");
        }
        if (!in_array($resource, $this->resources_names)) {
            throw new KumbiaException("The resource '$resource' does not exist in the list in acl::is_allowed");
        }
        if (is_array($access_list)) {
            foreach ($access_list as $access) {
                if (!in_array($access, $this->access_list[$resource])) {
                    throw new KumbiaException("Does not exist in access '$access' in the resource '$resource' in acl::is_allowed");
                }
            }
        } else {
            if (!in_array($access_list, $this->access_list[$resource])) {
                throw new KumbiaException("Does not exist in access '$access_list' in the resource '$resource' in acl::is_allowed");
            }
        }

        /* foreach($this->access[$role] as ){

        } */
        // FIXME: For now we do this validation, then it will be improved
        if (!isset($this->access[$role][$resource][$access_list])) {
            return false;
        }

        if ($this->access[$role][$resource][$access_list] == "A") {
            return true;
        }
    }

    /**
     * Rebuild the access list from inheritances
     * and access allowed and denied
     *
     * @access private
     */
    private function rebuild_access_list()
    {
        for ($i = 0; $i <= ceil(count($this->roles) * count($this->roles) / 2); $i++) {
            foreach ($this->roles_names as $role) {
                if (isset($this->role_inherits[$role])) {
                    foreach ($this->role_inherits[$role] as $role_inherit) {
                        if (isset($this->access[$role_inherit])) {
                            foreach ($this->access[$role_inherit] as $resource_name => $access) {
                                foreach ($access as $access_name                       => $value) {
                                    if (!in_array($access_name, $this->access_list[$resource_name])) {
                                        unset($this->access[$role_inherit][$resource_name][$access_name]);
                                    } else {
                                        if (!isset($this->access[$role][$resource_name][$access_name])) {
                                            $this->access[$role][$resource_name][$access_name] = $value;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
