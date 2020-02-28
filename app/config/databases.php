<?php

/**
 * KumbiaPHP Web Framework
 * Database connection parameters
 */
return [
    'development' => [
        /**
         * host: ip or host name of the database
         */
        'host'     => 'localhost',
        /**
         * username: user with permissions in the database
         */
        'username' => 'root', //it is not recommended to use the root user
        /**
         * password: database user key
         */
        'password' => '',
        /**
         * test: database name
         */
        'name'     => 'test',
        /**
         * type: type of database engine (mysql, pgsql, oracle or sqlite)
         */
        'type'     => 'mysql',
        /**
         * charset: Connection character set, for example 'utf8'
         */
        'charset'  => 'utf8',
        /**
     * dsn: Database connection string
     */
        //'dsn' => '',
        /**
     * pdo: activate PDO connections (On / Off); uncomment to use
     */
        //'pdo' => 'On',
    ],

    'production' => [
        /**
         * host: ip or host name of the database
         */
        'host'     => 'localhost',
        /**
         * username: user with permissions in the database
         */
        'username' => 'root', //it is not recommended to use the root user
        /**
         * password: database user key
         */
        'password' => '',
        /**
         * test: database name
         */
        'name'     => 'test',
        /**
         * type: type of database engine (mysql, pgsql or sqlite)
         */
        'type'     => 'mysql',
        /**
         * charset: Connection character set, for example 'utf8'
         */
        'charset'  => 'utf8',
        /**
     * dsn: database connection string
     */
        //'dsn' => '',
        /**
     * pdo: activate PDO connections (OnOff); uncomment to use
     */
        //'pdo' => 'On',
    ],
];

/**
 * SQLite example
 */
/*'development' => [
    'type' => 'sqlite',
    'dsn' => 'temp/data.sq3',
    'pdo' => 'On',
] */
