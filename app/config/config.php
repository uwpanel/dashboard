<?php

/**
 * KumbiaPHP Web Framework
 * Application configuration parameters
 */
return [
    'application' => [
        /**
         * name: is the name of the application
         */
        'name' => 'UWPanel',
        /**
         * database: database to use
         */
        'database' => 'development',
        /**
         * dbdate: default date format of the application
         */
        'dbdate' => 'YYYY-MM-DD',
        /**
         * debug: shows errors on screen (On / off)
         */
        'debug' => 'On',
        /**
         * log_exceptions: Show exceptions on screen (On / off)
         */
        'log_exceptions' => 'On',
        /**
         * cache_template: uncomment to enable template cache
         */
        //'cache_template' => 'On',
        /**
         * cache_driver: cache driver (file, sqlite, memsqlite)
         */
        'cache_driver' => 'file',
        /**
         * metadata_lifetime: cache metadata lifetime
         */
        'metadata_lifetime' => '+1 year',
        /**
         * namespace_auth: default namespace for Auth
         */
        'namespace_auth' => 'default',
        /**
         * routes: uncomment to activate routes on routes.php
         */
        'routes' => '1',
    ],
];
