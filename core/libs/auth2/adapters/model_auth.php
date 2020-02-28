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
 * @subpackage Adapters
 * 
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Authentication class by BD
 *
 * @category   Kumbia
 * @package    Auth
 * @subpackage Adapters
 */
class ModelAuth extends Auth2
{

    /**
     * Model to use for the authentication process
     *
     * @var String
     */
    protected $_model = 'users';
    /**
     * Session namespace where the model fields will be loaded
     *
     * @var string
     */
    protected $_sessionNamespace = 'default';
    /**
     * Fields that load from the model
     *
     * @var array
     */
    protected $_fields = array('id');
    /**
     *
     *
     * @var string
     */
    protected $_algos;
    /**
     *
     *
     * @var string
     */
    protected $_key;
    /**
     * Assign the model to use
     *
     * @param string $model model name
     */
    public function setModel($model)
    {
        $this->_model = $model;
    }

    /**
     * Assign the session namespace where the model fields will be loaded
     *
     * @param string $namespace session namespace
     */
    public function setSessionNamespace($namespace)
    {
        $this->_sessionNamespace = $namespace;
    }

    /**
     * Indicates which model fields will be loaded in session
     *
     * @param array $fields fields to load
     */
    public function setFields($fields)
    {
        $this->_fields = $fields;
    }

    /**
     * Check
     *
     * @param $username
     * @param $password
     * @return bool
     */
    protected function _check($username, $password)
    {
        // ALL $ _SERVER ['HTTP_HOST'] can be a variable in case you want to offer authentication from any indicated host
        if (strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) === FALSE) {
            self::log('INTENTO HACK IP ' . $_SERVER['HTTP_REFERER']);
            $this->setError('Hack Try!');
            return FALSE;
        }

        // EVERYTHING: check security
        $password = hash($this->_algos, $password);
        //$username = addslashes($username);
        $username = filter_var($username, FILTER_SANITIZE_MAGIC_QUOTES);

        $Model = new $this->_model;
        if ($user = $Model->find_first("$this->_login = '$username' AND $this->_pass = '$password'")) {
            // Load the attributes indicated in session
            foreach ($this->_fields as $field) {
                Session::set($field, $user->$field, $this->_sessionNamespace);
            }

            Session::set($this->_key, TRUE);
            return TRUE;
        }

        $this->setError('Error Login!');
        Session::set($this->_key, FALSE);
        return FALSE;
    }
}
