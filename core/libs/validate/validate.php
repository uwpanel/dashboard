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
 * @package    Validate
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Validate is a Class that performs Logic validations
 *
 * @category   KumbiaPHP
 * @package    validate
 */
require __DIR__ . '/validations.php';
class Validate
{
    /**
     * Object to validate
     * @var Object
     */
    protected $obj = null;

    /**
     * Error Messages Stored
     * @var array
     */
    protected $messages = array();

    /**
     * Rules to follow for validation
     * @var array
     */
    protected $rules = array();
    /**
     * Builder
     * @param Object $obj Objeto a validar
     */

    /**
     * Stores if the variable to be validated is an object before converting it
     * @var boolean
     */
    protected $is_obj = false;

    /**
     * The $rules parameter must contain this form
     *  array(
     *   'user' => //this is the name of the field
     *      array(
     *          'alpha' =>  //filter name
     *          null, //past parameters (in array or null if not required)
     *          'lenght' => array('min'=>4, 'max'=>10)
     *      )
     * )
     * @param mixed $obj Object or Array to validate
     * @param array $rules Aray of rules to validate
     */
    public function __construct($obj, array $rules)
    {
        $this->is_obj = is_object($obj);
        $this->obj = (object) $obj;
        $this->rules = $rules;
    }

    /**
     * Run the validations
     * @return bool Devuelve true if everything is valid
     */
    public function exec()
    {
        /*Tour of all fields*/
        foreach ($this->rules as $field => $fRule) {
            $value = self::getValue($this->obj, $field);
            /*Individual rule for each field*/
            foreach ($fRule as $ruleName => $param) {
                $ruleName = self::getRuleName($ruleName, $param);
                $param =  self::getParams($param);
                /*Ignore the rule is starts with "#"*/
                if ($ruleName[0] == '#') continue;
                /*It is a model validation*/
                if ($ruleName[0] == '@') {
                    $this->modelRule($ruleName, $param, $field);
                } elseif (!Validations::$ruleName($value, $param, $this->obj)) {
                    $this->addError($param, $field, $ruleName);
                }
            }
        }
        /*If there are no errors returns true*/
        return empty($this->messages);
    }

    /**
     * Run a model validation
     * @param string $rule rule name
     * @param array $param
     * @param string $field Field Name
     * @return bool
     */
    protected function modelRule($rule, $param, $field)
    {
        if (!$this->is_obj) {
            trigger_error('Cannot execute a model validation in an array', E_USER_WARNING);
            return false;
        }
        $ruleName = ltrim($rule, '@');
        $obj = $this->obj;
        if (!method_exists($obj, $ruleName)) {
            trigger_error('The method for validation does not exist', E_USER_WARNING);
            return false;
        }
        if (!$obj->$ruleName($field, $param)) {
            $this->addError($param, $field, $ruleName);
        }
        return true;
    }

    /**
     * Add a new error
     * @param Array $param parameters
     * @param string $field Field Name
     * @param string $rule Rule Name
     */
    protected function addError(array $param, $field, $rule)
    {
        $this->messages[$field][] = isset($param['error']) ?
            $param['error'] : Validations::getMessage($rule);
    }

    /**
     * Returns the name of the rule
     * @param string $ruleName
     * @param mixed $param
     * @return string
     */
    protected static function getRuleName($ruleName, $param)
    {
        /*Avoid having to place a null when parameters are not passed*/
        return is_integer($ruleName) && is_string($param) ? $param : $ruleName;
    }

    /**
     * Returns the parameters for the rule
     * @param mixed $param
     * @return array
     */
    protected static function getParams($param)
    {
        return is_array($param) ? $param : array();
    }

    /**
     * Returns the value of a field
     * @param object $obj
     * @param string $field
     * @return mixed
     */
    protected static function getValue($obj, $field)
    {
        return !empty($obj->$field) ? $obj->$field : null; //I get the value of the field
    }

    /**
     * Returns error messages
     *
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Instance version for flush error
     */
    public function flash()
    {
        self::errorToFlash($this->getMessages());
    }

    public static function fail($obj, array $rules)
    {
        $val = new self($obj, $rules);
        return $val->exec() ? false : $val->getMessages();
    }

    /**
     * Send error messages via flash
     * @param Array $error
     */
    public static function errorToFlash(array $error)
    {
        foreach ($error as $value)
            Flash::error($value);
    }
}
