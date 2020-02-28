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
 * This class allows users to authenticate using
 * Radius Authentication (RFC 2865) and Radius Accounting (RFC 2866).
 *
 * @category Kumbia
 * @package Auth
 * @subpackage Adapters
 * @link http://web.mit.edu/kerberos/www/krb5-1.2/krb5-1.2.8/doc/admin_toc.html.
 */
class RadiusAuth implements AuthInterface
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
     * Resource Radius
     */
    private $resource;
    /**
     * Radius Harbor
     */
    private $port = 1812;
    /**
     * Secret Radius
     *
     * @var string
     */
    private $secret;
    /**
     * Timeout to connect to the server
     *
     * @var integer
     */
    private $timeout = 3;
    /**
     * Maximum number of attempts
     *
     * @var integer
     */
    private $max_retries = 3;

    /**
     * Adapter builder
     *
     * @param $auth
     * @param $extra_args
     */
    public function __construct($auth, $extra_args)
    {

        if (!extension_loaded("radius")) {
            throw new KumbiaException("You must load the php extension called radius");
        }

        foreach (array('server', 'secret') as $param) {
            if (isset($extra_args[$param])) {
                $this->$param = $extra_args[$param];
            } else {
                throw new KumbiaException("You must specify the parameter '$param'");
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
        if (!$this->resource) {
            throw new KumbiaException("The connection to the Radius server is invalid");
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

        $radius = radius_auth_open();
        if (!$radius) {
            throw new KumbiaException("Could not create Radius authenticator");
        }

        if (!radius_add_server(
            $radius,
            $this->server,
            $this->port,
            $this->secret,
            $this->timeout,
            $this->max_retries
        )) {
            throw new KumbiaException(radius_strerror($radius));
        }

        if (!radius_create_request($radius, RADIUS_ACCESS_REQUEST)) {
            throw new KumbiaException(radius_strerror($radius));
        }

        if (!radius_put_string($radius, RADIUS_USER_NAME, $this->username)) {
            throw new KumbiaException(radius_strerror($radius));
        }

        if (!radius_put_string($radius, RADIUS_USER_PASSWORD, $this->password)) {
            throw new KumbiaException(radius_strerror($radius));
        }

        if (!radius_put_int($radius, RADIUS_AUTHENTICATE_ONLY, 1)) {
            throw new KumbiaException(radius_strerror($radius));
        }

        $this->resource = $radius;

        if (radius_send_request($radius) == RADIUS_ACCESS_ACCEPT) {
            return true;
        }
        return false;
    }

    /**
     * Clean the object by closing the connection if it exists
     *
     */
    public function __destruct()
    {
        if ($this->resource) {
            radius_close($this->resource);
        }
    }

    /**
     * Assigns the parameter values to the authenticator object
     *
     * @param array $extra_args
     */
    public function set_params($extra_args)
    {
        foreach (array(
            'server', 'secret', 'username', 'principal',
            'password', 'port', 'max_retries'
        ) as $param) {
            if (isset($extra_args[$param])) {
                $this->$param = $extra_args[$param];
            }
        }
    }
}
