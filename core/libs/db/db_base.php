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
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Main class of KumbiaPHP database adapters.
 *
 * It contains useful methods and general variables.
 *
 * $debug : Indicates whether all the sql operations that are displayed are shown on the screen
 * realizen with the driver
 * $logger : Indicates if all transactions that are going to be logged into a file
 * is performed in driver. $ logger = true creates a file with the current date
 * in logs / y $logger = "name", create a log with the indicated name.
 *
 * @category   Kumbia
 */
class DbBase
{
    /**
     * Indicates whether it is in debug mode or not.
     *
     * @var bool
     */
    public $debug = false;
    /**
     * Indicates whether to log in or not (also allows setting the name of the log).
     *
     * @var mixed
     */
    public $logger = false;
    /**
     * Last SQL statement sent to the Adapter.
     *
     * @var string
     */
    protected $last_query;

    /**
     * Make a select in a shorter way, ready to use in a foreach.
     *
     * @param string $table
     * @param string $where
     * @param string $fields
     * @param string $orderBy
     *
     * @return array
     */
    public function find($table, $where = '1=1', $fields = '*', $orderBy = '1')
    {
        ActiveRecord::sql_item_sanitize($table);
        ActiveRecord::sql_sanitize($fields);
        ActiveRecord::sql_sanitize($orderBy);
        $q = $this->query("SELECT $fields FROM $table WHERE $where ORDER BY $orderBy");
        $results = array();
        while ($row = $this->fetch_array($q)) {
            $results[] = $row;
        }

        return $results;
    }

    /**
     * Make an SQL query and return an array with the results array in form
     * indexed by numbers and associatively.
     *
     * @param string $sql
     *
     * @return array
     */
    public function in_query($sql)
    {
        $q = $this->query($sql);
        $results = array();
        if ($q) {
            while ($row = $this->fetch_array($q)) {
                $results[] = $row;
            }
        }

        return $results;
    }

    /**
     * Make an SQL query and return an array with the results array in form
     * indexed by numbers and associatively (Alias for in_query).
     *
     * @param string $sql
     *
     * @return array
     */
    public function fetch_all($sql)
    {
        return $this->in_query($sql);
    }

    /**
     * Make an SQL query and return an array with the results array in form
     * associatively indexed.
     *
     * @param string $sql
     *
     * @return array
     */
    public function in_query_assoc($sql)
    {
        $q = $this->query($sql);
        $results = [];
        while ($row = $this->fetch_array($q, db::DB_ASSOC)) {
            $results[] = $row;
        }

        return $results;
    }

    /**
     * Make an SQL query and return an array with the results array in form
     * numeric
     *
     * @param string $sql
     *
     * @return array
     */
    public function in_query_num($sql)
    {
        $q = $this->query($sql);
        $results = [];
        while ($row = $this->fetch_array($q, db::DB_NUM)) {
            $results[] = $row;
        }

        return $results;
    }

    /**
     * Returns an array of the result of a select from a single record.
     *
     * @param string $sql
     *
     * @return array
     */
    public function fetch_one($sql)
    {
        return $this->fetch_array($this->query($sql));
    }

    /**
     * Make an insert.
     *
     * @param string $table
     * @param array  $values
     * @param array  $fields
     *
     * @return bool
     */
    public function insert($table, array $values, $fields = null)
    {
        if (!count($values)) {
            throw new KumbiaException("Impossible to insert into $table no data");
        }
        $insert_sql = "INSERT INTO $table VALUES (" . join(',', $values) . ')';

        if (is_array($fields)) {
            $insert_sql = "INSERT INTO $table (" . join(',', $fields) . ') VALUES (' . join(',', $values) . ')';
        }

        return $this->query($insert_sql);
    }

    /**
     * Update records in a table.
     *
     * @param string $table
     * @param array  $fields
     * @param array  $values
     * @param string $where_condition
     *
     * @return bool
     */
    public function update($table, array $fields, array $values, $where_condition = null)
    {
        $update_sql = "UPDATE $table SET ";
        if (count($fields) != count($values)) {
            throw new KumbiaException('The numbers of values to update is not the same as the fields');
        }
        $i = 0;
        $update_values = array();
        foreach ($fields as $field) {
            $update_values[] = $field . ' = ' . $values[$i];
            ++$i;
        }
        $update_sql .= join(',', $update_values);
        if ($where_condition != null) {
            $update_sql .= " WHERE $where_condition";
        }

        return $this->query($update_sql);
    }

    /**
     * Delete records from a table!
     *
     * @param string $table
     * @param string $where_condition
     */
    public function delete($table, $where_condition)
    {
        if (trim($where_condition)) {
            return $this->query("DELETE FROM $table WHERE $where_condition");
        }

        return $this->query("DELETE FROM $table");
    }

    /**
     * Start a transaction if possible.
     */
    public function begin()
    {
        return $this->query('BEGIN');
    }

    /**
     * Cancel a transaction if possible.
     */
    public function rollback()
    {
        return $this->query('ROLLBACK');
    }

    /**
     * Commit to a transaction if possible.
     */
    public function commit()
    {
        return $this->query('COMMIT');
    }

    /**
     * Add quotes or simple according to the RBDM support.
     *
     * @return string
     */
    public static function add_quotes($value)
    {
        return "'" . addslashes($value) . "'";
    }

    /**
     * Log operations on the database if they are enabled.
     *
     * @param string $msg
     * @param string $type
     */
    protected function log($msg, $type)
    {
        if ($this->logger) {
            Logger::log($this->logger, $msg, $type);
        }
    }

    /**
     * Displays Debug Messages on Screen if enabled.
     *
     * @param string $sql
     */
    protected function debug($sql)
    {
        if ($this->debug) {
            Flash::info($sql);
        }
    }
}
