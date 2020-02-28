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
 * @package    Db
 * @subpackage PDO Adapters
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Interface for PDO database adapters.
 *
 * This interface exposes the methods that must be implemented in a driver
 * from Kumbia
 *
 * @category   Kumbia
 */
interface DbPdoInterface
{
    public function initialize();

    /**
     * @return bool
     */
    public function connect(array $config);

    public function query($sql);

    /**
     * @return int
     */
    public function exec($sql);

    public function fetch_array($resultQuery = null, $opt = '');

    /**
     * @return bool
     */
    public function close();

    /**
     * This method is not supported by PDO, use fetch_all and then count().
     *
     * @param resource $result_query
     *
     * @return int
     */
    public function num_rows($result_query = null);

    /**
     * @param resource $resultQuery
     *
     * @return string
     */
    public function field_name($number, $resultQuery = null);

    /**
     * This method is not supported by PDO, use fetch_all and then count().
     *
     * @param resource $result_query
     *
     * @return bool
     */
    public function data_seek($number, $result_query = null);

    /**
     * @return int
     */
    public function affected_rows($result_query = null);

    /**
     * @return string
     */
    public function error($err = '');

    /**
     * @return int
     */
    public function no_error($number = 0);

    public function in_query($sql);

    public function in_query_assoc($sql);

    public function in_query_num($sql);

    public function fetch_one($sql);

    public function fetch_all($sql);

    public function last_insert_id($name = '');

    /**
     * @return int
     */
    public function insert($table, array $values, $pk = '');

    /**
     * @param string $where_condition
     *
     * @return int
     */
    public function update($table, array $fields, array $values, $where_condition = null);

    /**
     * @param string $where_condition
     *
     * @return int
     */
    public function delete($table, $where_condition);

    public function limit($sql);

    public function create_table($table, $definition, $index = array());

    public function drop_table($table, $if_exists = false);

    public function table_exists($table, $schema = '');

    public function describe_table($table, $schema = '');
}
