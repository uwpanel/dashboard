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
 * This class allows users to authenticate using Digest Access Authentication.
 *
 * @category Kumbia
 * @package Auth
 * @subpackage Adapters
 * @link http://en.wikipedia.org/wiki/Digest_access_authentication
 */
class DigestAuth implements AuthInterface
{

    /**
     * File Name (if used)
     *
     * @var string
     */
    private $filename;
    /**
     * Authentication server (if used)
     *
     * @var string
     */
    private $server;
    /**
     * Username to connect to the authentication server (if used)
     *
     * @var string
     */
    private $username;
    /**
     * User password to connect to the authentication server (if used)
     *
     * @var string
     */
    private $password;
    /**
     * Found realm
     *
     * @var string
     */
    private $realm;
    /**
     * Resource
     *
     * @var string
     */
    private $resource;

    /**
     * Adapter builder
     *
     * @param $auth
     * @param $extra_args
     */
    public function __construct($auth, $extra_args)
    {
        foreach (array('filename') as $param) {
            if (isset($extra_args[$param])) {
                $this->$param = $extra_args[$param];
            } else {
                throw new KumbiaException("You must specify the parameter'$param'.");
            }
        }
        foreach (array('username', 'password') as $param) {
            if (isset($extra_args[$param])) {
                $this->$param = $extra_args[$param];
            }
        }
    }

    /**
     * Get the identity data obtained by authenticating
     *
     */
    public function get_identity()
    {
        return array("username" => $this->username, "realm" => $this->realm);
    }

    /**
     * Authenticate a user using the adapter
     *
     * @return boolean
     */
    public function authenticate()
    {
        $this->resource = @fopen($this->filename, "r");
        if ($this->resource === false) {
            throw new KumbiaException("File does not exist or cannot be loaded'{$this->filename}'");
        }

        $exists_user = false;
        while (!feof($this->resource)) {
            $line = fgets($this->resource);
            $data = explode(":", $line);

            if ($data[0] === $this->username) {
                if (trim($data[2]) === md5($this->password)) {
                    $this->realm = $data[1];
                    $exists_user = true;
                    break;
                }
            }
        }
        return $exists_user;
    }

    /**
     * Assigns the parameter values to the authenticator object
     *
     * @param array $extra_args
     */
    public function set_params($extra_args)
    {
        foreach (array('filename', 'username', 'password') as $param) {
            if (isset($extra_args[$param])) {
                $this->$param = $extra_args[$param];
            }
        }
    }

    /**
     * Clean the object by closing the connection if it exists
     *
     */
    public function __destruct()
    {
        @fclose($this->resource);
    }
}
