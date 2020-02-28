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
 * @package    Auth
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * @see AuthInterface
 */
require_once __DIR__ . '/auth_interface.php';

// Avoid problems updating beta2
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/**
 * This class allows users to authenticate.
 *
 * @category   Kumbia
 * @package    Auth 
 */
class Auth
{
    /**
     * Names to create the sessions.
     *
     */
    const IDENTITY = 'KUMBIA_AUTH_IDENTITY';
    const VALID = 'KUMBIA_AUTH_VALID';
    /**
     * Adapter name used to authenticate.
     *
     * @var string
     */
    private $adapter;
    /**
     * Current adapter object.
     *
     * @var mixed
     */
    private $adapter_object;
    /**
     * Indicates if a user must log in only once in the system from
     * anywhere.
     *
     * @var bool
     */
    private $active_session = false;
    /**
     * Time in which the session will expire in case it does not end with destroy_active_session.
     *
     * @var int
     */
    private $expire_time = 3600;
    /**
     * Extra arguments sent to the Adapter.
     *
     * @var array
     */
    private $extra_args = array();
    /**
     * Use the same session for applications with the same namespace in config.
     * from config.application.namespace_auth
     * 
     * @var string  
     */
    private static $app_namespace;
    /**
     * Indicates whether the last call to authenticate was successful or not (persistent in session).
     *
     * @var bool|null
     */
    private static $is_valid = null;

    /**
     * Last identity obtained by Authenticate (persistent in session).
     *
     * @var array
     */
    private static $active_identity = array();

    /**
     * Authenticator builder.
     */
    public function __construct()
    {
        $adapter = 'model'; //default
        $extra_args = Util::getParams(func_get_args());
        if (isset($extra_args[0])) {
            $adapter = $extra_args[0];
            unset($extra_args[0]);
        }
        $this->set_adapter($adapter, $this, $extra_args);
        self::$app_namespace = Config::get('config.application.namespace_auth');
    }

    /**
     * Modify the adapter to use.
     * 
     * @param string $adapter Type of adapter to use ('digest', 'model', 'kerberos5', 'radius')
     * @param Auth $auth Instance of the Auth class
     * @param array $extra_args Additional arguments
     * @throws kumbiaException
     */
    public function set_adapter($adapter, $auth = '', $extra_args = array())
    {
        if (!in_array($adapter, array('digest', 'model', 'kerberos5', 'radius'))) {
            throw new kumbiaException("Authentication adapter '$adapter' not supported");
        }
        $this->adapter = Util::camelcase($adapter);
        require_once __DIR__ . "/adapters/{$adapter}_auth.php";
        $adapter_class = $this->adapter . 'Auth';
        $this->extra_args = $extra_args;
        $this->adapter_object = new $adapter_class($auth, $extra_args);
    }

    /**
     * Get the name of the current adapter.
     *
     * @return string
     */
    public function get_adapter_name()
    {
        return $this->adapter;
    }

    /**
     * Perform the authentication process.
     *
     * @return array|bool
     */
    public function authenticate()
    {
        $result = $this->adapter_object->authenticate();
        /*
         * If it is an active session, it handles a persistent file for control
         */
        if ($result && $this->active_session) {
            $this->active_session();
        }
        $_SESSION[self::IDENTITY][self::$app_namespace] = $this->adapter_object->get_identity();
        self::$active_identity = $this->adapter_object->get_identity();
        $_SESSION[self::VALID][self::$app_namespace] = $result;
        self::$is_valid = $result;

        return $result;
    }
    /**
     * If it is an active session, it handles a persistent file for control.
     * 
     * ALL use sqlite
     */
    private function active_session()
    {
        $user_hash = md5(serialize($this->extra_args));
        $filename = APP_PATH . 'temp/cache/' . base64_encode('auth');
        if (file_exists($filename)) {
            $fp = fopen($filename, 'r');
            while (!feof($fp)) {
                $line = fgets($fp);
                $user = explode(':', $line);
                if ($user_hash === $user[0]) {
                    if ($user[1] + $user[2] > time()) {
                        self::$active_identity = array();
                        self::$is_valid = false;

                        return false;
                    }

                    fclose($fp);
                    $this->destroy_active_session();
                    file_put_contents($filename, $user_hash . ':' . time() . ':' . $this->expire_time . "\n");
                }
            }

            fclose($fp);
            $fp = fopen($filename, 'a');
            fputs($fp, $user_hash . ':' . time() . ':' . $this->expire_time . "\n");
            fclose($fp);

            return;
        }

        file_put_contents($filename, $user_hash . ':' . time() . ':' . $this->expire_time . "\n");
    }
    /**
     * Perform the authentication process using HTTP.
     *
     * @return array
     */
    public function authenticate_with_http()
    {
        if (!$_SERVER['PHP_AUTH_USER']) {
            header('WWW-Authenticate: Basic realm="basic"');
            http_response_code(401);

            return false;
        }
        $options = array('username' => $_SERVER['PHP_AUTH_USER'], 'password' => $_SERVER['PHP_AUTH_PW']);
        $this->adapter_object->set_params($options);

        return $this->authenticate();
    }

    /**
     * Returns the identity found in case of success.
     *
     * @return array
     */
    public function get_identity()
    {
        return $this->adapter_object->get_identity();
    }

    /**
     * It allows to control that user does not log in more than once in the
     * system from anywhere.
     * 
     * @param bool $value True to enable validation
     * @param int $time Time in which the session will expire
     */
    public function set_active_session($value, $time = 3600)
    {
        $this->active_session = $value;
        $this->expire_time = $time;
    }

    /**
     * It allows to destroy active session of the authenticated user.
     */
    public function destroy_active_session()
    {
        $user_hash = md5(serialize($this->extra_args));
        $filename = APP_PATH . 'temp/cache/' . base64_encode('auth');
        $lines = file($filename);
        $lines_out = array();
        foreach ($lines as $line) {
            if (substr($line, 0, 32) !== $user_hash) {
                $lines_out[] = $line;
            }
        }
        file_put_contents($filename, join("\n", $lines_out));
    }

    /**
     * Returns the instance of the adapter.
     *
     * @return mixed Current adapter object.
     */
    public function get_adapter_instance()
    {
        return $this->adapter_object;
    }

    /**
     * Determine if the application should sleep when authentication fails and how long in seconds.
     *
     * @param bool $value
     * @param int  $time
     * 
     * @deprecated it keeps to not break apps
     */
    public function sleep_on_fail($value, $time = 2)
    {
        throw new KumbiaException("The method sleep_on_fail($value, $time) of the Auth class is discouraged. Delete from your code.");
    }

    /**
     * Returns if it is a valid user.
     *
     * @return bool
     */
    public static function is_valid()
    {
        if (!is_null(self::$is_valid)) {
            return self::$is_valid;
        }
        self::$is_valid = isset($_SESSION[self::VALID][Config::get('config.application.namespace_auth')]) ? $_SESSION[self::VALID][Config::get('config.application.namespace_auth')] : null;

        return self::$is_valid;
    }

    /**
     * Returns the result of the last identity obtained in authenticate
     * since the last instantiated Auth object.
     *
     * @return array
     */
    public static function get_active_identity()
    {
        if (count(self::$active_identity)) {
            return self::$active_identity;
        }

        return self::$active_identity = $_SESSION[self::IDENTITY][Config::get('config.application.namespace_auth')];
    }

    /**
     * Gets a current identity value.
     * 
     * @param string $var Key that identifies the value
     * @return string Key value
     */
    public static function get($var)
    {
        if (isset($_SESSION[self::IDENTITY][Config::get('config.application.namespace_auth')][$var])) {
            return $_SESSION[self::IDENTITY][Config::get('config.application.namespace_auth')][$var];
        }
    }

    /**
     * Cancel the current identity.
     */
    public static function destroy_identity()
    {
        self::$is_valid = null;
        unset($_SESSION['KUMBIA_AUTH_VALID'][Config::get('config.application.namespace_auth')]);
        self::$active_identity = array();
        unset($_SESSION[self::IDENTITY][Config::get('config.application.namespace_auth')]);
    }
}
