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
class Validations
{
    /**
     * Constants to define patterns
     */

    /*
     * The value must be only letters and numbers
     */
    const IS_ALPHANUM = '/^[\p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Nd}]*$/mu';

    /**
     * Only letters
     */
    const IS_ALPHA    = '/^(?:[^\W\d_]|([ ]))*$/mu';

    /**
     * Store the Regular Expression
     *
     * @var String
     */
    public static $regex = NULL;


    /**
     * Valid to be numerical
     * @param  mixed $check Value to be checked
     * @return bool
     */
    public static function numeric($check){
        return is_numeric($check);
    }

    /**
     * Valid that int
     *
     * @param int $check
     * @return bool
     */
    public static function int($check)
    {
        return filter_var($check, FILTER_VALIDATE_INT);
    }

    /**
     * Validates that a chain is between a range.
     * The spaces are counted
     * Returns true if the $ value string is between min and max
     *
     * @param string $value
     * @param array $param
     * @return bool
     */
    public static function maxlength($value, $param)
    {
        $max= isset($param['max'])?$param['max']:0;
        return !isset($value[$max]);
    }

    /**
     * Valid chain length
     */
    public static function length($value, $param){
        $param = array_merge(array(
            'min' => 0,
            'max' => 9e100,
        ), $param);
        $length = strlen($value);
        return ($length >= $param['min'] && $length <= $param['max']);
    }

    /**
     * Validate that a number is found
     * in a minimum and maximum range
     *
     * @param int $value
     * @param array $param min, max
     */
    public static function range($value, $param)
    {
        $min = isset($param['min']) ? $param['min'] : 0;
        $max = isset($param['max']) ? $param['max'] : 10;
        $int_options = array('options' => array('min_range'=>$min, 'max_range'=>$max));
        return filter_var($value, FILTER_VALIDATE_INT, $int_options);
    }

    /**
     * Validates that a value is in a list
     * Returns true if the string $ value is in the list $ list
     *
     * @param string $value
     * @param array $param
     * @return bool
     */
    public static function select($value, $param)
    {
        $list = isset($param['list']) && is_array($param['list']) ? $param['list'] : array();
        return in_array($value, array_keys($list));
    }

    /**
     * Validate that a string is an email
     * @param string $mail
     * @return bool
     */
    public static function email($mail)
    {
        return filter_var($mail, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Valid URL
     *
     * @param string $url
     * @return bool
     */
    public static function url($url, $param)
    {
        $flag = isset($param['flag'])? $param['flag'] : 0;
        return filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED | $flag);
    }

    /**
     * Valid that it is an IP, by default v4
     * EVERYTHING: Review this method
     * @param String $ip
     * @return bool
     */
    public static function ip($ip, $flags = FILTER_FLAG_IPV4)
    {
        return filter_var($ip, FILTER_VALIDATE_IP, $flags);
    }

    /**
     * Validate that a string is not null
     *
     * @param string $check
     * @return bool
     */
    public static function required($check)
    {
        return (boolean) strlen(trim($check));
    }

    /**
     * Validate that a String is alpha-num (includes accented characters)
     * EVERYTHING: Review this method
     *
     * @param string $string
     * @return bool
     */
    public static function alphanum($string)
    {
        return self::pattern($string, array('regexp' => self::IS_ALPHANUM));
    }



    /**
     * Validate that a String is alpha (includes accented characters and space)
     *
     * @param string $string
     * @return bool
     */
    public static function alpha($string)
    {
        return self::pattern($string, array('regexp' => self::IS_ALPHA));
    }


    /**
     * Validate a date
     * @param string $value date to be validated according to the indicated format
     * @param array $param as in DateTime
     * @return boolean
     */
    public static function date($value, $param)
    {
        $format = isset($param['format'])? $param['format'] : 'Y-m-d';
        $date = DateTime::createFromFormat($format, $value);
        return $date && $date->format($format) == $value;
    }

    /**
     * Validate a string given a Regular Expression
     *
     * @param string $check
     * @param array $param  regex
     * @return bool
     */
    public static function pattern($check, $param)
    {
        $regex = isset($param['regexp'])? $param['regexp'] : '/.*/';
        return empty($check) || FALSE !== filter_var($check, FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => $regex)));
    }

    /**
     * Valid if it is a decimal number
     *
     * @param string $value
     * @param array $param
     * @return boolean
     */
    public static function decimal($value, $param)
    {
        $decimal = isset($param['decimal'])? $param['decimal'] : ',';
        return filter_var($value, FILTER_VALIDATE_FLOAT, array('options' => array('decimal' => $decimal)));
    }

    /**
     * Valid if the values ​​are equal
     *
     * @param string $value
     * @param array $param
     * @param object $obj
     * @return boolean
     */
    public static function equal($value, $param, $obj)
    {
        $equal = isset($param['to'])? $param['to'] : '';
        return ($obj->$equal == $value);
    }

    /**
     * Returns the default message of a validation
     * @param string $key
     * @return string
     */
    public static function getMessage($key){
        $arr  = array(
            'required' => 'This field is required',
            'alphanum' => 'It must be an alphanumeric value',
            'alpha'    => 'Only alphabetic characters',
            'length'   => 'Incorrect length',
            'email'    => 'Invalid Email',
            'pattern'  => 'The value does not have the correct format',
            'date'     => 'Invalid Date'
        );
        return $arr[$key];
    }
}
