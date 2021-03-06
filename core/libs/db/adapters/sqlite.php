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
 * @subpackage Adapters
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * SQLite Database Support.
 *
 * @category   Kumbia
 * @package    Db
 * @subpackage Adapters
 */
class DbSQLite extends DbBase implements DbBaseInterface
{
    /**
     * SQLite Connection Resource.
     *
     * @var resource
     */
    public $id_connection;
    /**
     * Last result of a Query.
     *
     * @var resource
     */
    public $last_result_query;
    /**
     * Last SQL statement sent to SQLite.
     *
     * @var string
     */
    protected $last_query;
    /**
     * Last error generated by SQLite.
     *
     * @var string
     */
    public $last_error;

    /**
     * Associative Array Result.
     */
    const DB_ASSOC = SQLITE_ASSOC;

    /**
     * Result of Associative and Numeric Array.
     */
    const DB_BOTH = SQLITE_BOTH;

    /**
     * Numerical Array Result.
     */
    const DB_NUM = SQLITE_NUM;

    /**
     * Integer Data Type.
     */
    const TYPE_INTEGER = 'INTEGER';

    /**
     * Data Type Date.
     */
    const TYPE_DATE = 'DATE';

    /**
     * Varchar Data Type.
     */
    const TYPE_VARCHAR = 'VARCHAR';

    /**
     * Decimal Data Type.
     */
    const TYPE_DECIMAL = 'DECIMAL';

    /**
     * Datetime Data Type.
     */
    const TYPE_DATETIME = 'DATETIME';

    /**
     * Data Type Char.
     */
    const TYPE_CHAR = 'CHAR';

    /**
     * Class Builder
     *
     * @param array $config
     */
    public function __construct($config)
    {
        $this->connect($config);
    }

    /**
     * Makes a connection to the SQLite database.
     *
     * @param array $config
     *
     * @return bool
     */
    public function connect(array $config)
    {
        if (!extension_loaded('sqlite')) {
            throw new KumbiaException('You must load the PHP extension called sqlite');
        }
        if ($this->id_connection = sqlite_open(APP_PATH . 'config/sql/' . $config['name'])) {
            return true;
        }
        throw new KumbiaException($this->error('Unable to connect to the database'));
    }

    /**
     * Performs SQL operations on the database.
     *
     * @param string $sqlQuery
     *
     * @return resource or false
     */
    public function query($sqlQuery)
    {
        $this->debug($sqlQuery);
        if ($this->logger) {
            Logger::debug($sqlQuery);
        }

        $this->last_query = $sqlQuery;
        if ($resultQuery = sqlite_query($this->id_connection, $sqlQuery)) {
            $this->last_result_query = $resultQuery;

            return $resultQuery;
        }
        throw new KumbiaException($this->error(" when executing <em>'$sqlQuery'</em>"));
    }

    /**
     * Close the Database Engine Connection.
     */
    public function close()
    {
        if ($this->id_connection) {
            sqlite_close($this->id_connection);
        }
    }

    /**
     * Returns the content of a select row by row.
     *
     * @param resource $resultQuery
     * @param int      $opt
     *
     * @return array
     */
    public function fetch_array($resultQuery = '', $opt = SQLITE_BOTH)
    {
        if (!$resultQuery) {
            $resultQuery = $this->last_result_query;
            if (!$resultQuery) {
                return false;
            }
        }

        return sqlite_fetch_array($resultQuery, $opt);
    }

    /**
     * Returns the number of rows of a select.
     */
    public function num_rows($resultQuery = '')
    {
        if (!$resultQuery) {
            $resultQuery = $this->last_result_query;
            if (!$resultQuery) {
                return false;
            }
        }
        if (($numberRows = sqlite_num_rows($resultQuery)) !== false) {
            return $numberRows;
        }
        throw new KumbiaException($this->error());
    }

    /**
     * Returns the name of a field in the result of a select.
     *
     * @param int      $number
     * @param resource $resultQuery
     *
     * @return string
     */
    public function field_name($number, $resultQuery = '')
    {
        if (!$resultQuery) {
            $resultQuery = $this->last_result_query;
            if (!$resultQuery) {
                return false;
            }
        }
        if (($fieldName = sqlite_field_name($resultQuery, $number)) !== false) {
            return $fieldName;
        }
        throw new KumbiaException($this->error());
    }

    /**
     * It moves to the result indicated by 3 in a select.
     *
     * @param int      $number
     * @param resource $resultQuery
     *
     * @return bool
     */
    public function data_seek($number, $resultQuery = '')
    {
        if (!$resultQuery) {
            $resultQuery = $this->last_result_query;
            if (!$resultQuery) {
                return false;
            }
        }
        if (($success = sqlite_rewind($resultQuery, $number)) !== false) {
            return $success;
        }
        throw new KumbiaException($this->error());
    }

    /**
     * Number of rows affected in an insert, update or delete.
     *
     * @param resource $resultQuery
     *
     * @return int
     */
    public function affected_rows($resultQuery = '')
    {
        if (!$resultQuery) {
            $resultQuery = $this->last_result_query;
            if (!$resultQuery) {
                return false;
            }
        }
        if (($numberRows = pg_affected_rows($resultQuery)) !== false) {
            return $numberRows;
        }
        throw new KumbiaException($this->error());
    }

    /**
     * Returns the error of SQLite.
     *
     * @return string
     */
    public function error($err = '')
    {
        if (!$this->id_connection) {
            $this->last_error = sqlite_last_error($this->id_connection) ? sqlite_last_error($this->id_connection) . $err : "[Unknown error in SQLite \"$err\"]";
            if ($this->logger) {
                Logger::error($this->last_error);
            }

            return $this->last_error;
        }
        $this->last_error = 'SQLite error: ' . sqlite_error_string(sqlite_last_error($this->id_connection));
        $this->last_error .= $err;
        if ($this->logger) {
            Logger::error($this->last_error);
        }

        return $this->last_error;
    }

    /**
     * Returns the no SQLite error.
     *
     * @return int
     */
    public function no_error()
    {
        return 0; //Error code?
    }

    /**
     * Returns the last autonumeric id generated in the database.
     *
     * @return int
     */
    public function last_insert_id($table = '', $primary_key = '')
    {
        $last_id = $this->fetch_one("SELECT COUNT(*) FROM $table");

        return $last_id[0];
    }

    /**
     * Check if a table exists or not.
     *
     * @param string $table
     *
     * @return bool
     */
    public function table_exists($table, $schema = '')
    {
        $table = addslashes(strtolower($table));
        if (strpos($table, '.')) {
            list($schema, $table) = explode('.', $table);
        }
        $num = $this->fetch_one("SELECT COUNT(*) FROM sqlite_master WHERE name = '$table'");

        return $num[0];
    }

    /**
     * Returns a valid LIMIT for a SELECT of the RBDM.
     *
     * @param string $sql consulta sql
     *
     * @return string
     */
    public function limit($sql)
    {
        $params = Util::getParams(func_get_args());

        if (isset($params['limit']) && is_numeric($params['limit'])) {
            $sql .= " LIMIT $params[limit]";
        }

        if (isset($params['offset']) && is_numeric($params['offset'])) {
            $sql .= " OFFSET $params[offset]";
        }

        return $sql;
    }

    /**
     * Delete a table from the database.
     *
     * @param string $table
     *
     * @return bool
     */
    public function drop_table($table, $if_exists = true)
    {
        if ($if_exists) {
            if ($this->table_exists($table)) {
                return $this->query("DROP TABLE $table");
            }

            return true;
        }

        return $this->query("DROP TABLE $table");
    }

    /**
     * Create a table using RDBM native SQL.
     *
     * @param string $table
     * @param array  $definition
     *
     * @return bool|null
     */
    public function create_table($table, $definition, $index = array())
    {
    }

    /**
     * List the tables in the database.
     *
     * @return array
     */
    public function list_tables()
    {
        return $this->fetch_all("SELECT name FROM sqlite_master WHERE type='table' " .
            'UNION ALL SELECT name FROM sqlite_temp_master ' .
            "WHERE type='table' ORDER BY name");
    }

    /**
     * List the fields of a table.
     *
     * @param string $table
     *
     * @return array
     */
    public function describe_table($table, $schema = '')
    {
        $fields = array();
        $results = $this->fetch_all("PRAGMA table_info($table)");
        //var_dump($results); die();
        foreach ($results as $field) {
            $fields[] = array(
                'Field' => $field['name'],
                'Type' => $field['type'],
                'Null' => $field['notnull'] == '0' ? 'YES' : 'NO',
                'Key' => $field['pk'] == 1 ? 'PRI' : '',
            );
        }

        return $fields;
    }

    /**
     * Returns the last sql statement executed by the Adapter.
     *
     * @return string
     */
    public function last_sql_query()
    {
        return $this->last_query;
    }
}
