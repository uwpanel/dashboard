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
 * @package    Console
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */
// library load for cache handling
Load::lib('cache');

/**
 * Console to handle the cache
 *
 * @category   Kumbia
 * @package    Console
 */
class CacheConsole
{

    /**
     * Console command to clear the cache
     *
     * @param array $params named parameters of the console
     * @param string $group group name
     * @throw KumbiaException
     */
    public function clean($params, $group = '')
    {
        // get the cache driver
        $cache = $this->setDriver($params);

        // clear the cache
        if ($cache->clean($group)) {
            if ($group) {
                echo "-> The group has been cleaned $group", PHP_EOL;
            } else {
                echo "-> The cache has been cleaned", PHP_EOL;
            }
        } else {
            throw new KumbiaException('Failed to delete cache content');
        }
    }

    /**
     * Console command to delete a cached item
     *
     * @param array $params named parameters of the console
     * @param string $id item id
     * @param string $group group name
     * @throw KumbiaException
     */
    public function remove($params, $id, $group = 'default')
    {
        // get the cache driver
        $cache = $this->setDriver($params);

        // remove item
        if ($cache->remove($id, $group)) {
            echo '-> Cache item deleted', PHP_EOL;
        } else {
            throw new KumbiaException("Failed to remove item \"$id\" of the group \"$group\"");
        }
    }

    /**
     * Returns a cache instance of the last driver
     *
     * @param array $params named parameters
     */
    private function setDriver($params)
    {
        if (isset($params['driver'])) {
            return Cache::driver($params['driver']);
        }
        return Cache::driver();
    }
}
