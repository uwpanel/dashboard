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
 * @package    Core
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Class For the management of Benchmark and Profiling
 *
 * It allows to obtain a script or request execution time
 * in order to find possible bottlenecks and
 * optimize application performance ...
 *
 * @category   Kumbia
 * @package    Core
 */
final class Benchmark
{

    /**
     * Store the data of a specific Benchmark, this to avoid collision
     *
     * @var name
     */
    private static $_benchmark;
    private static $_avgload = 0;

    /**
     * Start the clock (profiling)
     *
     * @return array $_benchmark
     */
    public static function start_clock($name)
    {
        if (!isset(self::$_benchmark[$name])) {
            self::$_benchmark[$name] = array('start_time' => microtime(), 'final_time' => 0, 'memory_start' => memory_get_usage(), 'memory_stop' => 0, 'time_execution' => 0);
        }
    }

    /**
     * Stops the clock for the purpose of calculating the
     * script execution time
     *
     * @return array $_benchmark
     */
    private static function _stop_clock($name)
    {
        if (isset(self::$_benchmark[$name])) {
            $load = 0;
            if (PHP_OS == 'Linux') {
                $load = sys_getloadavg();
            }
            self::$_avgload = $load[0];
            self::$_benchmark[$name]['memory_stop'] = memory_get_usage();
            self::$_benchmark[$name]['final_time'] = microtime();
            list($sm, $ss) = explode(' ', self::$_benchmark[$name]['start_time']);
            list($em, $es) = explode(' ', self::$_benchmark[$name]['final_time']);
            self::$_benchmark[$name]['time_execution'] = number_format(($em + $es) - ($sm + $ss), 4);
            return self::$_benchmark[$name]['time_execution'];
        }
    }

    /**
     * It allows to obtain the memory used by a script
     *
     * @return string memory_usage
     */
    public static function memory_usage($name)
    {
        if (self::$_benchmark[$name]) {
            self::$_benchmark[$name]['memory_usage'] = number_format((self::$_benchmark[$name]['memory_stop'] - self::$_benchmark[$name]['memory_start']) / 1048576, 2);
            return self::$_benchmark[$name]['memory_usage'];
        }
        throw new KumbiaException("There is no Benchmark for the name:'$name', specified");
    }

    /**
     * Returns the execution time of the scripts (profiling)
     *
     * @return string time_execution
     */
    public static function time_execution($name)
    {
        if (isset(self::$_benchmark[$name])) {
            return self::_stop_clock($name);
        }
        throw new KumbiaException("There is no Benchmark for the name: $name,  specified");
    }

    /**
     *
     * @deprecated
     */
    public static function test($func, $loops)
    {
        self::start_clock($func);
        ob_start();
        for ($i = 1; $i <= $loops; $i++) {
            $func;
        }
        ob_end_flush();
        $time = self::time_execution($func);
        echo '** Funcion: ', $func;
        echo $loops, ' veces';
        echo ' Tiempo: ', $time;
    }
}
