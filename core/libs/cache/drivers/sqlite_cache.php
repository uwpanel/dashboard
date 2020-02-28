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
 * Cache with Sqlite
 *
 * @category   Kumbia
 * @package    Cache
 * @subpackage Drivers
 */
class SqliteCache extends Cache
{

    /**
     * Connection to the Sqlite database
     *
     * @var resource
     * */
    protected $_db = null;

    /**
     * Builder
     *
     * */
    public function __construct()
    {
        /**
         * Open an SqLite connection to the cache database
         *
         */
        $this->_db = sqlite_open(APP_PATH . 'temp/cache.db');
        $result = sqlite_query($this->_db, "SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND tbl_name='cache' ");
        $count = sqlite_fetch_single($result);

        if (!$count) {
            sqlite_exec(' CREATE TABLE cache (id TEXT, "group" TEXT, value TEXT, lifetime TEXT) ', $this->_db);
        }
    }

    /**
     * Load a cached item
     *
     * @param string $id
     * @param string $group
     * @return string
     */
    public function get($id, $group = 'default')
    {
        $this->_id = $id;
        $this->_group = $group;

        $id = addslashes($id);
        $group = addslashes($group);

        $id = addslashes($id);
        $group = addslashes($group);
        $lifetime = time();

        $result = sqlite_query($this->_db, " SELECT value FROM cache WHERE id='$id' AND \"group\"='$group' AND lifetime>'$lifetime' OR lifetime='undefined' ");
        return sqlite_fetch_single($result);
    }

    /**
     * Save an item in the cache with name $ id and value $ value
     *
     * @param string $id
     * @param string $group
     * @param string $value
     * @param int $lifetime Unix timestamp life time
     * @return boolean
     */
    public function save($value, $lifetime = '', $id = '', $group = 'default')
    {
        if (!$id) {
            $id = $this->_id;
            $group = $this->_group;
        }

        if ($lifetime) {
            $lifetime = strtotime($lifetime);
        } else {
            $lifetime = 'undefined';
        }

        $id = addslashes($id);
        $group = addslashes($group);
        $value = addslashes($value);

        $result = sqlite_query($this->_db, " SELECT COUNT(*) FROM cache WHERE id='$id' AND \"group\"='$group' ");
        $count = sqlite_fetch_single($result);


        // The cached item already exists
        if ($count) {
            return sqlite_exec(" UPDATE cache SET value='$value', lifetime='$lifetime' WHERE id='$id' AND \"group\"='$group' ", $this->_db);
        }

        return sqlite_exec(" INSERT INTO cache (id, \"group\", value, lifetime) VALUES ('$id','$group','$value','$lifetime')", $this->_db);
    }

    /**
     * Clear the cache
     *
     * @param string $group
     * @return boolean
     */
    public function clean($group = '')
    {
        if ($group) {
            $group = addslashes($group);
            return sqlite_exec(" DELETE FROM cache WHERE \"group\"='$group' ", $this->_db);
        }
        return sqlite_exec(" DELETE FROM cache ", $this->_db);
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
        $id = addslashes($id);
        $group = addslashes($group);

        return sqlite_exec(" DELETE FROM cache WHERE id='$id' AND \"group\"='$group' ", $this->_db);
    }
}
