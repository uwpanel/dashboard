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
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Base class for caching components
 *
 * @category   Kumbia
 * @package    Cache
 */
abstract class Cache
{

    /**
     * Cache driver pool
     *
     * @var array
     * */
    protected static $_drivers = [];
    /**
     * Default driver
     *
     * @var string
     * */
    protected static $_default_driver = 'file';
    /**
     * Id of last item requested
     *
     * @var string
     */
    protected $_id;
    /**
     * Group of last item requested
     *
     * @var string
     */
    protected $_group = 'default';
    /**
     * Time of life
     *
     * @var string
     */
    protected $_lifetime = '';
    /**
     * Start - end data
     *
     * @var array
     */
    protected $_start = [];

    /**
     * Load a cached item
     *
     * @param string $id    identifier
     * @param string $group group
     * @return string
     */
    abstract public function get($id, $group = 'default');

    /**
     * Save an item in the cache with name $id and value $value
     *
     * @param string $value     Content to cache
     * @param string $lifetime  Life time with strtotime format, used for cache
     * @param string|null $id   Identifier
     * @param string $group     Group, the cache is created with $group. $Id
     * @return boolean
     */
    abstract public function save($value, $lifetime = '', $id = null, $group = 'default');

    /**
     * Clear the cache
     *
     * @param string|null $group
     * @return boolean
     */
    abstract public function clean($group = null);

    /**
     * Remove an item from the cache
     *
     * @param string $id
     * @param string $group
     * @return boolean
     */
    abstract public function remove($id, $group = 'default');

    /**
     * Start the cache of the output buffer until end is called
     *
     * @param string $lifetime life time with strtotime format, used for cache
     * @param string $id
     * @param string $group
     * @return boolean
     */
    public function start($lifetime, $id, $group = 'default')
    {
        if ($data = $this->get($id, $group)) {
            echo $data;

            //  It is not necessary to cache
            return false;
        }
        $this->_start = [
            'lifetime' => $lifetime,
            'id'       => $id,
            'group'    => $group
        ];

        // start buffer capture
        ob_start();

        // Start frisking
        return true;
    }

    /**
     * The output buffer ends
     *
     * @param boolean $save indicates if at the end save the cache
     * @return boolean
     */
    public function end($save = true)
    {
        if (!$save) {
            ob_end_flush();
            return false;
        }

        // get the buffer content
        $value = ob_get_contents();

        // release the buffer
        ob_end_flush();

        return $this->save($value, $this->_start['lifetime'], $this->_start['id'], $this->_start['group']);
    }

    /**
     * Get the indicated cache driver
     *
     * @param string $driver (file, sqlite, memsqlite, APC)
     * @return Cache
     * */
    public static function driver($driver = '')
    {
        if (!$driver) {
            $driver = self::$_default_driver;
        }

        if (!isset(self::$_drivers[$driver])) {
            require __DIR__ . "/drivers/{$driver}_cache.php";
            $class = $driver . 'cache';
            self::$_drivers[$driver] = new $class();
        }

        return self::$_drivers[$driver];
    }

    /**
     * Change the default driver
     *
     * @param string $driver default driver name
     * @return void
     */
    public static function setDefault($driver = 'file')
    {
        self::$_default_driver = $driver;
    }
}
