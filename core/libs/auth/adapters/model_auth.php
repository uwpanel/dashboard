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
 * This class allows users to authenticate using a database entity
 *
 * @category   extensions
 * @package    Auth
 */
class ModelAuth implements AuthInterface
{
    /**
     * Attributes of the model to be compared for valid authentication
     */
    private $compare_attributes = array();
    /**
     * Identity will find
     */
    private $identity = array();
    /**
     * Model Class Name
     */
    private $class;

    /**
     * Adapter builder
     *
     * @param $auth
     * @param $extra_args
     */
    public function __construct($auth, $extra_args)
    {
        foreach (array('class') as $param) {
            if (isset($extra_args[$param])) {
                $this->$param = $extra_args[$param];
            } else {
                throw new KumbiaException("You must specify the parameter '$param' in the parameters");
            }
        }
        unset($extra_args[0]);
        unset($extra_args['class']);
        $this->compare_attributes = $extra_args;
    }

    /**
     * Get the identity data obtained by authenticating
     *
     */
    public function get_identity()
    {
        return $this->identity;
    }

    /**
     * Authenticate a user using the adapter
     *
     * @return boolean
     */
    public function authenticate()
    {
        $where_condition = array();
        foreach ($this->compare_attributes as $field => $value) {
            $value = addslashes($value);
            $where_condition[] = "$field = '$value'";
        }
        $result = (new $this->class)->count(join(" AND ", $where_condition));
        if ($result) {
            $model = (new $this->class)->find_first(join(" AND ", $where_condition));
            $identity = array();
            foreach ($model->fields as $field) {
                /**
                 * Try not to include the user's password in the identity
                 */
                if (!in_array($field, array('password', 'key', 'password', 'passwd', 'pass'))) {
                    $identity[$field] = $model->$field;
                }
            }
            $this->identity = $identity;
        }
        return $result;
    }

    /**
     * Assigns parameter values to the authenticator object
     *
     * @param array $extra_args
     */
    public function set_params($extra_args)
    {
        foreach (array('server', 'secret', 'principal', 'password', 'port', 'max_retries') as $param) {
            if (isset($extra_args[$param])) {
                $this->$param = $extra_args[$param];
            }
        }
    }
}
