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
 * File Caching for Operating Systems * Nix
 *
 * @category   Kumbia
 * @package    Cache
 * @subpackage Drivers
 */
class NixfileCache extends Cache
{
    /**
     * Maximum approximate time stamp for 32-bit processors
     *
     * January 18, 2038
     */
    const MAX_TIMESTAMP = 2147401800;

    /**
     * Gets the file name from an id and group
     *
     * @param string $id
     * @param string $group
     * @return string
     * */
    protected function _getFilename($id, $group)
    {
        return 'cache_' . md5($id) . '.' . md5($group);
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

        $filename = APP_PATH . 'temp/cache/' . $this->_getFilename($id, $group);

        if (is_file($filename) && filemtime($filename) >= time()) {
            return file_get_contents($filename);
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
    public function save($value, $lifetime = '', $id = '', $group = 'default')
    {
        if (!$id) {
            $id = $this->_id;
            $group = $this->_group;
        }

        if ($lifetime) {
            $lifetime = strtotime($lifetime);
        } else {
            $lifetime = self::MAX_TIMESTAMP;
        }

        $filename = APP_PATH . 'temp/cache/' . $this->_getFilename($id, $group);

        // Store the expiration date on the modification date
        return file_put_contents($filename, $value) && touch($filename, $lifetime);
    }

    /**
     * Clear the cache
     *
     * @param string $group
     * @return boolean
     */
    public function clean($group = '')
    {
        $pattern = $group ? APP_PATH . 'temp/cache/' . '*.' . md5($group) : APP_PATH . 'temp/cache/*';
        foreach (glob($pattern) as $filename) {
            if (!unlink($filename)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Remove an item from the cache
     *
     * @param string $id
     * @param string $group
     * @return bool
     */
    public function remove($id, $group = 'default')
    {
        return unlink(APP_PATH . 'temp/cache/' . $this->_getFilename($id, $group));
    }
}
