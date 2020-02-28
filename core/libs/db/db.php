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
 * @package    Db
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */
/**
 * @see DbBaseInterface
 */
require_once __DIR__ . '/db_base_interface.php';
/**
 * @see DbBase
 */
require_once __DIR__ . '/db_base.php';

/**
 * Class that manages the connection pool.
 *
 * @category   Kumbia
 */
class Db
{
    /**
     * Singleton database connections.
     *
     * @var array
     */
    protected static $_connections = array();

    /**
     * Return the connection, if it does not exist call Db :: connect to create it.
     *
     * @param string $database database to connect to
     *
     * @return DbBase
     */
    public static function factory($database = null)
    {
        //I charge the mode for my application
        if (!$database) {
            $database = Config::get('config.application.database');
        }
        //If it is not a new connection and the singleton connection exists
        if (isset(self::$_connections[$database])) {
            return self::$_connections[$database];
        }

        return self::$_connections[$database] = self::connect($database);
    }

    /**
     * Make a direct connection to the database engine
     * using the Kumbia driver.
     *
     * @param string $database database to connect to
     *
     * @return DbBase
     */
    private static function connect($database)
    {
        $config = Config::read('databases')[$database];

        // load the default values for the connection, if they don't exist
        $config = $config + [
            'port' => 0, 'dsn' => null, 'dbname' => null, 'host' => 'localhost',
            'username' => null, 'password' => null, 'pdo' => false, 'charset' => '',
        ];
        $path = __DIR__;

        //If you use PDO
        if ($config['pdo']) {
            $dbclass = "DbPdo{$config['type']}";
            $db_file = "$path/adapters/pdo/{$config['type']}.php";
        } else {
            if ($config['type'] === 'mysqli') {
                $config['type'] = 'mysql';
            }
            $dbclass = "Db{$config['type']}";
            $db_file = "$path/adapters/{$config['type']}.php";
        }

        //Load the necessary adapter class
        if (!include_once $db_file) {
            throw new KumbiaException("Class does not exist $dbclass, required to start the adapter");
        }

        return new $dbclass($config);
    }
}
