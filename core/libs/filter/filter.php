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
 * @package    Filter
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */
/**
 * @see FilterInterface
 * */
require_once __DIR__ . '/filter_interface.php';

/**
 * Implementation of Filters for Kumbia
 *
 * @category   Kumbia
 * @package    Filter
 * @deprecated 1.0 Use PHP Filter
 */
class Filter
{

    /**
     * Apply filter statically
     *
     * @param mixed $s variable to filter
     * @param string $filter filter
     * @param array $options
     * @return mixed
     */
    public static function get($s, $filter, $options = array())
    {
        if (is_string($options)) {
            $filters = func_get_args();
            unset($filters[0]);

            $options = array();
            foreach ($filters as $f) {
                $filter_class = Util::camelcase($f) . 'Filter';
                if (!class_exists($filter_class, false)) {
                    self::_load_filter($f);
                }

                $s = call_user_func(array($filter_class, 'execute'), $s, $options);
            }
        } else {
            $filter_class = Util::camelcase($filter) . 'Filter';
            if (!class_exists($filter_class, false)) {
                self::_load_filter($filter);
            }
            $s = call_user_func(array($filter_class, 'execute'), $s, $options);
        }

        return $s;
    }

    /**
     * Apply filters to an array
     *
     * @param array $array array to filter
     * @param string $filter filter
     * @param array $options
     * @return array
     */
    public static function get_array($array, $filter, $options = array())
    {
        $args = func_get_args();

        foreach ($array as $k => $v) {
            $args[0]   = $v;
            $array[$k] = call_user_func_array(array('self', 'get'), $args);
        }

        return $array;
    }

    /**
     * Apply the filters to an array of data.
     *
     * Very useful when we want to validate that a form only reach us
     * the necessary data for a certain situation, eliminating possible elements
     * unwanted
     *
     * Usage examples:
     *
     * $form = array(
     *          'name' => "Pedro Jose",
     *          'surname' => "  Perez Aguilar  ",
     *          'birth_date' => "2000-05-20",
     *          'input_cool' => "Colleague",
     *          'age' => "25"
     *      );
     *
     * Filter::data($form, array(
     *                      'name',
     *                      'surname',
     *                      'birth_date' => 'date',
     *                      'age' => 'int'
     *                  ), 'trim');
     *
     * Bring back: array(
     *          'name' => "Pedro José",
     *          'surname' => "Perez Aguilar",
     *          'birth_date' => "2000-05-20",
     *          'age' => "25"
     *      );
     *
     * Another example for the same $ form:
     *
     * Filter::data($form, array(
     *                      'name' => 'upper|alpha',
     *                      'surname' => 'lower|htmlentities|addslashes'
     *                      'birth_date' => 'date',
     *                      'age' => 'int'
     *                  ), 'trim');
     *
     * Other examples:
     *
     * Filter::data($form, array('name', 'surname','birth_date','age'),'trim');
     *
     * Filter::data($form, array('name', 'surname','birth_date'));
     *
     * @param array $data data to filter.
     * @param array $fields arrangement where the indexes are the fields to return
     * of the original array, and the value of each index is the filter that is
     * will apply if you don't want to specify any filter for any index,
     * only its name is placed as another value of the arrangement.
     * @param string $filterAll filters that will be applied to all elements.
     * @return array datos filtered. (Also only returns the indices
     * specified in the second parameter).
     */
    public static function data(array $data, array $fields, $filterAll = '')
    {
        $filtered = array(); //filtered data to return.
        foreach ($fields as $index => $filters) {
            if (is_numeric($index) && array_key_exists($filters, $data)) {
                //if the index is numeric, we don't want to use filter for that field
                $filtered[$filters] = $data[$filters];
                continue;
            } elseif (array_key_exists($index, $data)) { //we verify again the existence of the index in $ data
                $filters = explode('|', $filters); //we turn the filter into arrangement
                array_unshift($filters, $data[$index]);
                $filtered[$index] = call_user_func_array(array('self', 'get'), $filters);
                //$filtered[$index] = self::get($data[$index], $filters); //for now without additional options.
            }
        }
        if ($filterAll) {
            $filterAll = explode('|', $filterAll);
            array_unshift($filterAll, $filtered);
            return call_user_func_array(array('self', 'get_array'), $filterAll);
        } else {
            return $filtered;
        }
    }

    /**
     * Apply filters to an object
     *
     * @param mixed $object
     * @param array $options
     * @return object
     */
    public static function get_object($object, $filter, $options = array())
    {
        $args = func_get_args();

        foreach ($object as $k => $v) {
            $args[0]    = $v;
            $object->$k = call_user_func_array(array('self', 'get'), $args);
        }

        return $object;
    }

    /**
     * Load a Filter
     *
     * @param string $filter filter
     * @throw KumbiaException
     */
    protected static function _load_filter($filter)
    {
        $file = APP_PATH . "extensions/filters/{$filter}_filter.php";
        if (!is_file($file)) {
            $file = __DIR__ . "/base_filter/{$filter}_filter.php";
            if (!is_file($file)) {
                throw new KumbiaException("Filter $filter not found");
            }
        }

        include $file;
    }
}
