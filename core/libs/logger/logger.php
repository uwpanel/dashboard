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
 * @package    Logger
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Allows logging of text files in the Logs folder
 *
 * $fileLogger = It is the File Handle to write the logs
 * $transaction = Indicate whether or not there is a transaction
 * $queue = array with list of pending logs
 *
 * Ex:
 * <code>
 * //Start a log in logs/logDDMMYY.txt
 *
 *
 * Logger::debug('Log this as a debug');
 *
 * //This is saved to the log immediately.
 * Logger::error('Log this as an error');
 *
 * //Start a transaction
 * Logger::begin();
 *
 * //This is pending until commit is called to save
 * //or rollback to cancel
 * Logger::warning('This is a log in the row.');
 * Logger::notice('This is another log in the row.');
 *
 * //The log is saved
 * Logger::commit();
 *
 * //Close the Log
 * Logger::close();
 * </code>
 *
 * @category   Kumbia
 * @package    Logger
 */
abstract class Logger
{

    /**
     * Resource to the Log Archive
     *
     * @var resource
     */
    private static $fileLogger;
    /**
     * @var
     */
    private static $log_name = null;
    /**
     * Indicate whether there is a transaction or not
     *
     * @var boolean
     */
    private static $transaction = false;
    /**
     * Array with log messages queued in a transaction
     *
     * @var array
     */
    private static $queue = array();
    /**
     * Path where logs will be saved
     *
     * @var string
     */
    private static $log_path = '';

    /**
     * Initialize the Logger
     *
     * @param string $name
     */
    public static function initialize($name = '')
    {
        self::$log_path = APP_PATH . 'temp/logs/'; //EVERYTHING can change the path
        if ($name === '') {
            $name = 'log' . date('Y-m-d') . '.txt';
        }
        self::$fileLogger = fopen(self::$log_path . $name, 'a');
        if (!self::$fileLogger) {
            throw new KumbiaException("Cannot open the called log: " . $name);
        }
    }

    /**
     * Specify the PATH where the logs are stored
     *
     * @param string $path
     */
    public static function set_path($path)
    {
        self::$log_path = $path;
    }

    /**
     * Get the current path
     *
     * @return string
     */
    public static function get_path()
    {
        return self::$log_path;
    }

    /**
     * Store a message in the log
     *
     * @param string $type
     * @param string $msg
     * @param string $name_log
     */
    public static function log($type = 'DEBUG', $msg, $name_log)
    {
        if (is_array($msg)) {
            $msg = print_r($msg, true);
        }
        //EVERYTHING can add other log formats
        $date = date(DATE_RFC1036);
        $msg = "[$date][$type] " . $msg;
        if (self::$transaction) {
            self::$queue[] = $msg;
            return;
        }
        self::write($msg, $name_log);
    }

    /**
     * Write in the log
     *
     * @param string $msg
     */
    protected static function write($msg, $name_log)
    {
        self::initialize($name_log); //EVERYTHING to leave it open when it is a commit
        fputs(self::$fileLogger, $msg . PHP_EOL);
        self::close();
    }

    /**
     * Start a transaction
     *
     */
    public static function begin()
    {
        self::$transaction = true;
    }

    /**
     * Undo a transaction
     *
     */
    public static function rollback()
    {
        self::$transaction = false;
        self::$queue = array();
    }

    /**
     * Commit to a transaction
     *
     * @param string $name_log
     */
    public static function commit($name_log = '')
    {
        foreach (self::$queue as $msg) {
            self::write($msg, $name_log);
        }
        self::$queue = array();
        self::$transaction = false;
    }

    /**
     * Close the Logger
     *
     */
    public static function close()
    {
        if (!self::$fileLogger) {
            throw new KumbiaException("Unable to close the log because it is invalid");
        }
        return fclose(self::$fileLogger);
    }

    /**
     * Generate a log of type WARNING
     *
     * @return
     * @param string $msg
     * @param string $name_log
     */
    public static function warning($msg, $name_log = '')
    {
        self::log('WARNING', $msg, $name_log);
    }

    /**
     * Generate an ERROR type log
     *
     * @return
     * @param string $msg
     * @param string $name_log
     */
    public static function error($msg, $name_log = '')
    {
        self::log('ERROR', $msg, $name_log);
    }

    /**
     * Generates a log of type DEBUG
     *
     * @return
     * @param string $msg
     * @param string $name_log
     */
    public static function debug($msg, $name_log = '')
    {
        self::log('DEBUG', $msg, $name_log);
    }

    /**
     * Generate an ALERT type log
     *
     * @return
     * @param string $msg
     * @param string $name_log
     */
    public static function alert($msg, $name_log = '')
    {
        self::log('ALERT', $msg, $name_log);
    }

    /**
     * Generate a log of CRITICAL type
     *
     * @return
     * @param string $msg
     * @param string $name_log
     */
    public static function critical($msg, $name_log = '')
    {
        self::log('CRITICAL', $msg, $name_log);
    }

    /**
     * Generate a log of type NOTICE
     *
     * @return
     * @param string $msg
     * @param string $name_log
     */
    public static function notice($msg, $name_log = '')
    {
        self::log('NOTICE', $msg, $name_log);
    }

    /**
     * Generate a log of type INFO
     *
     * @return
     * @param string $msg
     * @param string $name_log
     */
    public static function info($msg, $name_log = '')
    {
        self::log('INFO', $msg, $name_log);
    }

    /**
     * Generate an EMERGENCE type log
     *
     * @return
     * @param string $msg
     * @param string $name_log
     */
    public static function emergence($msg, $name_log = '')
    {
        self::log('EMERGENCE', $msg, $name_log);
    }

    /**
     * Generate a Custom log
     *
     * @param string $type
     * @param string $msg
     * @param string $name_log
     */
    public static function custom($type = 'CUSTOM', $msg, $name_log = '')
    {
        self::log($type, $msg, $name_log);
    }
}
