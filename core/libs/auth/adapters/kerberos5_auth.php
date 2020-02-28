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
 * This class allows users to authenticate using Kerberos V servers.
 *
 * @category Kumbia
 * @package Auth
 * @subpackage Adapters
 * @link http://web.mit.edu/kerberos/www/krb5-1.2/krb5-1.2.8/doc/admin_toc.html.
 */
class Kerberos5Auth implements AuthInterface
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
     * Resource Kerberos5
     */
    private $resource;
    /**
     * Realm to authenticate
     *
     * @var string
     */
    private $realm;
    /**
     * The main
     *
     * @var string
     */
    private $principal;

    /**
     * Adapter builder
     *
     * @param $auth
     * @param $extra_args
     */
    public function __construct($auth, $extra_args)
    {

        if (!extension_loaded("kadm5")) {
            throw new KumbiaException("You must load the php extension called kadm5");
        }

        foreach (array('server', 'username', 'principal', 'password') as $param) {
            if (isset($extra_args[$param])) {
                $this->$param = $extra_args[$param];
            } else {
                throw new KumbiaException("You must specify the parameter'$param'");
            }
        }
    }

    /**
     * Get the identity data obtained by authenticating
     *
     */
    public function get_identity()
    {
        if (!$this->resource) {
            throw new KumbiaException("The connection to the kerberos5 server is invalid");
        }
        $identity = array("username" => $this->username, "realm" => $this->username);
        return $identity;
    }

    /**
     * Authenticate a user using the adapter
     *
     * @return boolean
     */
    public function authenticate()
    {
        $this->resource = kadm5_init_with_password($this->server, $this->realm, $this->principal, $this->password);
        if ($this->resource === false) {
            return false;
        }
        return true;
    }

    /**
     * Get the prinicipals of the authenticated user
     *
     */
    public function get_principals()
    {
        if (!$this->resource) {
            throw new KumbiaException("The connection to the kerberos5 server is invalid");
        }
        return kadm5_get_principals($this->resource);
    }

    /**
     * Get the authenticated user's policies
     *
     */
    public function get_policies()
    {
        if (!$this->resource) {
            throw new KumbiaException("The connection to the kerberos5 server is invalid");
        }
        return kadm5_get_policies($this->resource);
    }

    /**
     * Clean the object by closing the connection if it exists
     *
     */
    public function __destruct()
    {
        if ($this->resource) {
            kadm5_destroy($this->resource);
        }
    }

    /**
     * Assigns the parameter values to the authenticator object
     *
     * @param array $extra_args
     */
    public function set_params($extra_args)
    {
        foreach (array('server', 'principal', 'username', 'password') as $param) {
            if (isset($extra_args[$param])) {
                $this->$param = $extra_args[$param];
            }
        }
    }
}
