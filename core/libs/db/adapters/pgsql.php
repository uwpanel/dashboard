<?php

/**
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
 * PostgreSQL Database Support.
 *
 * @category   Kumbia
 * @package    Db
 * @subpackage Adapters
 */
class DbPgSQL extends DbBase implements DbBaseInterface
{
    /**
     * Resource of the Connection to PostgreSQL.
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
     * Last SQL statement sent to PostgreSQL.
     *
     * @var string
     */
    protected $last_query;
    /**
     * Last error generated by PostgreSQL.
     *
     * @var string
     */
    public $last_error;

    /**
     * Associative Array Result.
     */
    const DB_ASSOC = PGSQL_ASSOC;

    /**
     * Result of Associative and Numeric Array.
     */
    const DB_BOTH = PGSQL_BOTH;

    /**
     * Numeric Array Result.
     */
    const DB_NUM = PGSQL_NUM;

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
     * Makes a connection to the PostgreSQL database.
     *
     * @param array $config
     *
     * @return bool
     */
    public function connect(array $config)
    {
        if (!extension_loaded('pgsql')) {
            throw new KumbiaException('You must load the PHP extension called php_pgsql');
        }

        if (!isset($config['port']) || !$config['port']) {
            $config['port'] = 5432;
        }

        if ($this->id_connection = pg_connect("host={$config['host']} user={$config['username']} password={$config['password']} dbname={$config['name']} port={$config['port']}", PGSQL_CONNECT_FORCE_NEW)) {
            return true;
        }
        throw new KumbiaException($this->error('Unable to connect to the database'));
    }

    /**
     * Performs SQL operations on the database.
     *
     * @param string $sqlQuery
     *
     * @return resource|false
     */
    public function query($sqlQuery)
    {
        $this->debug($sqlQuery);
        if ($this->logger) {
            Logger::debug($sqlQuery);
        }

        $this->last_query = $sqlQuery;
        if ($resultQuery = pg_query($this->id_connection, $sqlQuery)) {
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
            return pg_close($this->id_connection);
        }

        return false;
    }

    /**
     * Returns the content of a select row by row.
     *
     * @param resource $resultQuery
     * @param int      $opt
     *
     * @return array
     */
    public function fetch_array($resultQuery = null, $opt = PGSQL_BOTH)
    {
        if (!$resultQuery) {
            $resultQuery = $this->last_result_query;
            if (!$resultQuery) {
                return false;
            }
        }

        return pg_fetch_array($resultQuery, null, $opt);
    }

    /**
     * Returns the number of rows of a select.
     */
    public function num_rows($resultQuery = null)
    {
        if (!$resultQuery) {
            $resultQuery = $this->last_result_query;
            if (!$resultQuery) {
                return false;
            }
        }
        if (($numberRows = pg_num_rows($resultQuery)) !== false) {
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
    public function field_name($number, $resultQuery = null)
    {
        if (!$resultQuery) {
            $resultQuery = $this->last_result_query;
            if (!$resultQuery) {
                return false;
            }
        }
        if (($fieldName = pg_field_name($resultQuery, $number)) !== false) {
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
    public function data_seek($number, $resultQuery = null)
    {
        if (!$resultQuery) {
            $resultQuery = $this->last_result_query;
            if (!$resultQuery) {
                return false;
            }
        }
        if (($success = pg_result_seek($resultQuery, $number)) !== false) {
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
    public function affected_rows($resultQuery = null)
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
     * Returns the error of PostgreSQL.
     *
     * @return string
     */
    public function error($err = '')
    {
        if (!$this->id_connection) {
            $this->last_error = pg_last_error() ? pg_last_error() . $err : "[Unknown Error in PostgreSQL \"$err\"]";
            if ($this->logger) {
                Logger::error($this->last_error);
            }

            return $this->last_error;
        }
        $this->last_error = pg_last_error() ? pg_last_error() . $err : "[Unknown Error in PostgreSQL: $err]";
        $this->last_error .= $err;
        if ($this->logger) {
            Logger::error($this->last_error);
        }

        return pg_last_error($this->id_connection) . $err;
    }

    /**
     * Returns the no error of PostgreSQL.
     *
     * @return int ??
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
        $last_id = $this->fetch_one("SELECT CURRVAL('{$table}_{$primary_key}_seq')");

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
        if ($schema == '') {
            $num = $this->fetch_one("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'public' AND TABLE_NAME ='$table'");
        } else {
            $schema = addslashes(strtolower($schema));
            $num = $this->fetch_one("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '$schema' AND TABLE_NAME ='$table'");
        }

        return $num[0];
    }

    /**
     * Returns a valid LIMIT for a SELECT of the RBDM.
     *
     * @param string $sql sql query
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
     * EVERYTHING:
     * - The index parameter needs to work. This should list multipes and unique composite indices
     * - Add the type of table to use (PostgreSQL)
     * - Support for autonomous fields
     * - Foreign key holder
     *
     * @param string $table
     * @param array  $definition
     *
     * @return resource
     */
    public function create_table($table, $definition, $index = array())
    {
        $create_sql = "CREATE TABLE $table (";
        if (!is_array($definition)) {
            throw new KumbiaException("Invalid definition to create the table '$table'");
        }
        $create_lines = array();
        $index = array();
        $unique_index = array();
        $primary = array();
        //$not_null = "";
        //$size = "";
        foreach ($definition as $field => $field_def) {
            if (isset($field_def['not_null'])) {
                $not_null = $field_def['not_null'] ? 'NOT NULL' : '';
            } else {
                $not_null = '';
            }
            if (isset($field_def['size'])) {
                $size = $field_def['size'] ? '(' . $field_def['size'] . ')' : '';
            } else {
                $size = '';
            }
            if (isset($field_def['index']) && $field_def['index']) {
                $index[] = "INDEX($field)";
            }
            if (isset($field_def['unique_index']) && $field_def['unique_index']) {
                $index[] = "UNIQUE($field)";
            }
            if (isset($field_def['primary']) && $field_def['primary']) {
                $primary[] = "$field";
            }
            if (isset($field_def['auto']) && $field_def['auto']) {
                $field_def['type'] = 'SERIAL';
            }
            $extra = isset($field_def['extra']) ? $field_def['extra'] : '';
            $create_lines[] = "$field " . $field_def['type'] . $size . ' ' . $not_null . ' ' . $extra;
        }
        $create_sql .= join(',', $create_lines);
        $last_lines = array();
        if (count($primary)) {
            $last_lines[] = 'PRIMARY KEY(' . join(',', $primary) . ')';
        }
        if (count($index)) {
            $last_lines[] = join(',', $index);
        }
        if (count($unique_index)) {
            $last_lines[] = join(',', $unique_index);
        }
        if (count($last_lines)) {
            $create_sql .= ',' . join(',', $last_lines) . ')';
        }

        return $this->query($create_sql);
    }

    /**
     * List the tables in the database.
     *
     * @return array
     */
    public function list_tables()
    {
        return $this->fetch_all('SELECT c.relname AS table FROM pg_class c, pg_user u '
            . "WHERE c.relowner = u.usesysid AND c.relkind = 'r' "
            . 'AND NOT EXISTS (SELECT 1 FROM pg_views WHERE viewname = c.relname) '
            . "AND c.relname !~ '^(pg_|sql_)' UNION "
            . 'SELECT c.relname AS table_name FROM pg_class c '
            . "WHERE c.relkind = 'r' "
            . 'AND NOT EXISTS (SELECT 1 FROM pg_views WHERE viewname = c.relname) '
            . 'AND NOT EXISTS (SELECT 1 FROM pg_user WHERE usesysid = c.relowner) '
            . "AND c.relname !~ '^pg_'");
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
        $describe = $this->fetch_all("SELECT a.attname AS Field, t.typname AS Type,
                CASE WHEN attnotnull=false THEN 'YES' ELSE 'NO' END AS Null,
                CASE WHEN (select cc.contype FROM pg_catalog.pg_constraint cc WHERE
                cc.conrelid = c.oid AND cc.conkey[1] = a.attnum limit 1)='p' THEN 'PRI' ELSE ''
                END AS Key, CASE WHEN atthasdef=true THEN TRUE ELSE NULL END AS Default
                FROM pg_catalog.pg_class c, pg_catalog.pg_attribute a,
                pg_catalog.pg_type t WHERE c.relname = '$table' AND c.oid = a.attrelid
                AND a.attnum > 0 AND t.oid = a.atttypid order by a.attnum");
        $final_describe = array();
        foreach ($describe as $field) {
            $final_describe[] = array(
                'Field' => $field['field'],
                'Type' => $field['type'],
                'Null' => $field['null'],
                'Key' => $field['key'],
                'Default' => $field['default'],
            );
        }

        return $final_describe;
    }

    /**
     * Returns the content of a select row by row.
     *
     * @param resource $query_result
     * @param string   $class        object class
     *
     * @return object
     */
    public function fetch_object($query_result = null, $class = 'stdClass')
    {
        if (!$query_result) {
            $query_result = $this->last_result_query;
        }

        return pg_fetch_object($query_result, null, $class);
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
