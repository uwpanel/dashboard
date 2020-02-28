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
 * Base class for authentication management
 *
 * @category   Kumbia
 * @package    Auth 
 */
abstract class Auth2 {

    /**
     * Error message
     *
     * @var String
     */
    protected $_error = '';
    /**
     * BD field where the username is saved
     *
     * @var String
     */
    protected $_login = 'login';
    /**
     * BD field where the passcode is stored
     *
     * @var String
     */
    protected $_pass = 'password';
    /**
     * Key / Pass Encryption Algorithm
     *
     * @var String
     */
    protected $_algos = 'md5';
    /**
     * Session Password
     *
     * @var string
     */
    protected $_key = 'jt2D14KIdRs7LA==';
    /**
     * Verify that you are not logged in from a different browser with the same IP
     *
     * @var boolean
     */
    protected $_checkSession = TRUE;
    /**
     * Default adapter
     *
     * @var string
     */
    protected static $_defaultAdapter = 'model';

    /**
     * Assigns the field name for the username field
     *
     * @param string $field field name you receive by POST
     */
    public function setLogin($field) {
        $this->_login = $field;
    }

    /**
     * Assigns the field name for the key field
     *
     * @param string $field field name you receive by POST
     */
    public function setPass($field) {
        $this->_pass = $field;
    }

    /**
     * Assign the session key
     *
     * @param string $key session key
     */
    public function setKey($key) {
        $this->_key = $key;
    }

    /**
     * Perform the identification process.
     *
     * @param $login string Optional value of the username in the bd
     * @param $pass string Optional value of the user's password in the bd
     * @param $mode string Optional value of the identification method (auth)
     * @return bool
     */
    public function identify($login = '', $pass = '', $mode = '') {
        if ($this->isValid()) {
            return TRUE;
        } else {
            // check
            if (($mode == 'auth') || (isset($_POST['mode']) && $_POST['mode'] === 'auth')) {
                $login = empty($login)?Input::post($this->_login):$login;
                $pass  = empty($pass)?Input::post($this->_pass):$pass;
                return $this->_check($login, $pass);
            }
            //FAIL
            return false;
        }
    }

    /**
     * Perform the authentication process according to each adapter
     *
     * @param $username
     * @param $password
     * @return bool
     */
    abstract protected function _check($username, $password);

    /**
     * logout
     *
     * @param void
     * @return void
     */
    public function logout() {
        Session::set($this->_key, FALSE);
        session_destroy();
    }

    /**
     * Verify that there is a valid identity for the current session
     *
     * @return bool
     */
    public function isValid() {
        if ($this->_checkSession) {
            $this->_checkSession();
        }

        return Session::has($this->_key) && Session::get($this->_key) === TRUE;
    }

    /**
     * Verify that you are not logged in from a different browser with the same IP
     *
     */
    private function _checkSession() {
        Session::set('USERAGENT', $_SERVER['HTTP_USER_AGENT']);
        Session::set('REMOTEADDR', $_SERVER['REMOTE_ADDR']);

        if ($_SERVER['REMOTE_ADDR'] !== Session::get('REMOTEADDR') ||
            $_SERVER['HTTP_USER_AGENT'] !== Session::get('USERAGENT')) {
            session_destroy();
        }
    }

    /**
     * Indicates that you are not logged in from a different browser with the same IP
     *
     * @param bool $check
     */
    public function setCheckSession($check) {
        $this->_checkSession = $check;
    }

    /**
     * Indicates encryption algorithm
     *
     * @param string $algos
     */
    public function setAlgos($algos, $salt = '') {
        $this->_algos = $algos;
    }

    /**
     * Get the error message
     *
     * @return string
     */
    public function getError() {
        return $this->_error;
    }

    /**
     * Indicates the error message
     *
     * @param string $error
     */
    public function setError($error) {
        $this->_error = $error;
    }

    /**
     * Logger of Auth operations
     * @param $msg
     */
    public static function log($msg) {
        $date = date('Y-m-d', strtotime('now'));
        Logger::custom('AUTH', $msg, "auth-$date.log");
    }

    /**
     * Get the adapter for Auth
     *
     * @param string $adapter (model, openid, oauth)
     */
    public static function factory($adapter = '') {
        if (!$adapter) {
            $adapter = self::$_defaultAdapter;
        }

        require_once __DIR__ ."/adapters/{$adapter}_auth.php";
        $class = $adapter.'auth';

        return new $class;
    }

    /**
     * Change the default adapter
     *
     * @param string $adapter default adapter name
     */
    public static function setDefault($adapter) {
        self::$_defaultAdapter = $adapter;
    }

}
