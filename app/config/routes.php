<?php

/**
 * KumbiaPHP Web Framework
 * Route file (Optional)
 * 
 * Use this file to define static routing between
 * controllers and their actions. A controller can be routed to
 * Another controller using '*' as a wildcard like this:
 * 
 * '/controller1/action1/id_value'  =>  'controller2/action2/id2_value'
 * 
 * Ex:
 * Route any request to posts/add to posts/insert / *
 * '/posts/add/*' => 'posts/insert/*'
 * 
 * Other examples:
 * 
 * '/test/route1/*' => 'test/route2/*',
 * '/test/route2/*' => 'test/route3/*',
 */
return [
    'routes' => [
        /**
         * Show the info related to the framework
         */
        '/' => 'index/index',
        /**
         * Status of config.php / config.ini
         */
        '/status' => 'pages/kumbia/status',
    ],
];
