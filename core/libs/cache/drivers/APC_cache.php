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
 * @package    Cache
 * @subpackage Drivers
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * File Caching
 *
 * @category   Kumbia
 * @package    Cache
 * @subpackage Drivers
 */
class APCCache extends Cache
{

    /**
     * Load a cached item, the apc_fetch can receive an array (our cache doesn't)
     *
     * @param string $id
     * @param string $group
     * @return string
     */
    public function get($id, $group = 'default')
    {
        $this->_id = $id;
        $this->_group = $group;

        $data = apc_fetch("$id.$group");
        if ($data !== FALSE) {
            return $data;
        }
    }

    /**
     * Save an item in the cache with name $id and value $value
     *
     * @param string $id
     * @param string $group
     * @param string $value
     * @param int $lifetime Unix timestamp life time
     * @return boolean
     */
    public function save($id, $group, $value, $lifetime)
    {
        if (!$id) {
            $id = $this->_id;
            $group = $this->_group;
        }

        if ($lifetime) {
            $lifetime = strtotime($lifetime) - time();
        } else {
            $lifetime = '0';
        }

        return apc_store("$id.$group", $value, $lifetime);
    }

    /**
     * Clear the cache, with APC, ALL not only the group is cleaned
     *
     * @param string $group Not used with APC
     * @return boolean
     */
    public function clean($group = false)
    {
        return apc_clear_cache('user');
    }

    /**
     * Remove an item from the cache
     *
     * @param string $id
     * @param string $group
     * @return boolean
     */
    public function remove($id, $group = 'default')
    {
        return apc_delete("$id.$group");
    }
}
