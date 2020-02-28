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
 * @see Util
 */
require CORE_PATH . 'kumbia/util.php';
/**
 * @see KumbiaException
 */
require CORE_PATH . 'kumbia/kumbia_exception.php';
/**
 * @see Config
 */
require CORE_PATH . 'kumbia/config.php';
/**
 * @see Load
 */
require CORE_PATH . 'kumbia/load.php';

/**
 * modified by nelsonrojas
 * the problem: using console controller create produces an online error 85.
 *              does not recognize FileUtil
 * solution: include the library with the following line
 */
require CORE_PATH . 'libs/file_util/file_util.php';

/**
 * KumbiaPHP console handler
 *
 * Console for the creation of models.
 * Console for creating drivers.
 * Console for handling cache.
 *
 * @category   Kumbia
 * @package    Core
 */
class Console
{

    /**
     * Generate the argument list for the console, the first argument
     * returned corresponds to the array of named terminal parameters
     *
     * @param array $argv terminal arguments
     * @return array
     * */
    private static function _getConsoleArgs($argv)
    {
        $args = array(array());

        foreach ($argv as $p) {
            if (is_string($p) && preg_match("/--([a-z_0-9]+)[=](.+)/", $p, $regs)) {
                // load in the array of named parameters
                $args[0][$regs[1]] = $regs[2];
            } else {
                // load it as a simple argument
                $args[] = $p;
            }
        }

        return $args;
    }

    /**
     * Create an instance of the indicated console
     *
     * @param string $console_name console name
     * return object
     * @throw KumbiaException
     * */
    public static function load($console_name)
    {
        // console class name
        $Console = Util::camelcase($console_name) . 'Console';

        if (!class_exists($Console)) {
            // try loading the console file
            $file = APP_PATH . "extensions/console/{$console_name}_console.php";

            if (!is_file($file)) {
                $file = CORE_PATH . "console/{$console_name}_console.php";

                if (!is_file($file)) {
                    throw new KumbiaException('Console "' . $file . '" Was not found');
                }
            }

            // includes the console
            include_once $file;
        }

        // create the object instance
        $console = new $Console();

        // initialize the console
        if (method_exists($console, 'initialize')) {
            $console->initialize();
        }

        return $console;
    }

    /**
     * Dispatch and load the console to run from terminal arguments
     *
     * @param array $argv arguments received from the terminal
     * @throw KumbiaException
     * */
    public static function dispatch($argv)
    {
        // I delete the file name from the argument array
        array_shift($argv);

        // get the console name
        $console_name = array_shift($argv);
        if (!$console_name) {
            throw new KumbiaException('You have not indicated the console to run');
        }

        // get the command name to execute
        $command = array_shift($argv);
        if (!$command) {
            $command = 'main';
        }

        // Get the arguments for the console, the first argument
        // is the array of parameters named for terminal
        $args = self::_getConsoleArgs($argv);

        // check the application path
        if (isset($args[0]['path'])) {
            $dir = realpath($args[0]['path']);
            if (!$dir) {
                throw new KumbiaException("The route \"{$args[0]['path']}\" is invalid");
            }
            // remove the path parameter from the array
            unset($args[0]['path']);
        } else {
            // get the current working directory
            $dir = getcwd();
        }

        // define the path of the application
        define('APP_PATH', rtrim($dir, '/') . '/');

        // read the configuration
        $config = Config::read('config');

        // constant that indicates if the application is in production
        define('PRODUCTION', $config['application']['production']);

        // create the console
        $console = self::load($console_name);

        // verify that the command exists in the console
        if (!method_exists($console, $command)) {
            throw new KumbiaException("The command \"$command \" does not exist for the console \"$console_name \"");
        }

        // if you try to run
        if ($command == 'initialize') {
            throw new KumbiaException("The initialize command is a reserved command");
        }

        // check the parameters for the console action
        $reflectionMethod = new ReflectionMethod($console, $command);
        if (count($args) < $reflectionMethod->getNumberOfRequiredParameters()) {
            throw new KumbiaException("Number of wrong parameters to execute the command \"$command\" in the console \"$console_name\"");
        }

        // execute the command
        call_user_func_array(array($console, $command), $args);
    }

    /**
     * Read an input from the console
     *
     * @param string $message message to show
     * @param array $values array of valid values for input
     * @return string Valor read from the console
     **/
    public static function input($message, $values = null)
    {
        // open the entrance
        $stdin = fopen('php://stdin', 'r');

        do {
            // print the message
            echo $message;

            // read the line from the terminal
            $data = str_replace(PHP_EOL, '', fgets($stdin));
        } while ($values && !in_array($data, $values));

        // close the resource
        fclose($stdin);

        return $data;
    }
}
