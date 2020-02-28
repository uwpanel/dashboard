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
 * Informix Database Support.
 *
 * @category   Kumbia
 * @package    Db
 * @subpackage Adapters
 */
class DbInformix extends DbBase implements DbBaseInterface
{
    /**
     * Resource of the Informix Connection.
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
     * Last SQL statement sent to Informix.
     *
     * @var string
     */
    protected $last_query;
    /**
     * Last error generated by Informix.
     *
     * @var string
     */
    public $last_error;
    /**
     * Indicates whether query returns records or not;
     *
     * @var bool
     */
    private $return_rows = true;
    /**
     * It emulates a limit at the level of Adapter for Informix.
     *
     * @var int
     */
    private $limit = -1;
    /**
     * Current limit number for fetch_array.
     *
     * @var int
     */
    private $actual_limit = 0;

    /**
     * Associative Array Result.
     */
    const DB_ASSOC = 1;

    /**
     * Result of Associative and Numeric Array.
     */
    const DB_BOTH = 2;

    /**
     * Numeric Array Result.
     */
    const DB_NUM = 3;

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
     * Makes a connection to the Informix database.
     *
     * @param array $config
     *
     * @return bool
     */
    public function connect(array $config)
    {
        if (!extension_loaded('informix')) {
            throw new KumbiaException('You must load the PHP extension called php_ifx');
        }

        if ($this->id_connection = ifx_connect("{$config['name']}@{$config['host']}", $config['username'], $config['password'])) {
            return true;
        }
        throw new KumbiaException($this->error());
    }

    /**
     * Performs SQL operations on the database.
     *
     * @param string $sql_query
     *
     * @return resource or false
     */
    public function query($sql_query)
    {
        $this->debug($sql_query);
        if ($this->logger) {
            Logger::debug($sql_query);
        }

        $this->last_query = $sql_query;

        // The results that return rows use SCROLL type cursors
        if ($this->return_rows) {
            $result_query = ifx_query($sql_query, $this->id_connection, IFX_HOLD);
        } else {
            $result_query = ifx_query($sql_query, $this->id_connection);
        }
        $this->set_return_rows(true);
        if ($result_query === false) {
            throw new KumbiaException($this->error("when executing <em>\"$sql_query\"</em>"));
        }
        $this->last_result_query = $result_query;

        return $result_query;
    }

    /**
     * Close the Database Engine Connection.
     */
    public function close()
    {
        if ($this->id_connection) {
            return ifx_close($this->id_connection);
        }

        return false;
    }

    /**
     * Returns the content of a select row by row.
     *
     * @param resource $result_query
     * @param int      $opt
     *
     * @return array
     */
    public function fetch_array($result_query = null, $opt = 2)
    {
        if (!$result_query) {
            $result_query = $this->last_result_query;
            if (!$result_query) {
                return false;
            }
        }
        $fetch = ifx_fetch_row($result_query, $opt);

        // Informix does not support limit so you have to emulate it
        if ($this->limit != -1) {
            if ($this->actual_limit >= $this->limit) {
                $this->limit = -1;
                $this->actual_limit = 0;

                return false;
            } else {
                ++$this->actual_limit;
                if ($this->actual_limit == $this->limit) {
                    $this->limit = -1;
                    $this->actual_limit = 0;
                }
            }
        }

        // Informix does not support numeric fetch, only associative
        if (!is_array($fetch) || ($opt == self::DB_ASSOC)) {
            return $fetch;
        }
        if ($opt == self::DB_BOTH) {
            $result = array();
            $i = 0;
            foreach ($fetch as $key => $value) {
                $result[$key] = $value;
                $result[$i++] = $value;
            }

            return $result;
        }
        if ($opt == self::DB_NUM) {
            return array_values($fetch);
        }
    }

    /**
     * Returns the number of rows of a select.
     *
     * @param resource $result_query
     *
     * @return int
     */
    public function num_rows($result_query = null)
    {
        if (!$result_query) {
            $result_query = $this->last_result_query;
            if (!$result_query) {
                return false;
            }
        }
        if (($number_rows = ifx_num_rows($result_query)) !== false) {
            // Emulate an adapter level limit
            if ($this->limit == -1) {
                return $number_rows;
            }

            return $this->limit < $number_rows ? $this->limit : $number_rows;
        }
        throw new KumbiaException($this->error());
    }

    /**
     * Returns the name of a field in the result of a select.
     *
     * @param int      $number
     * @param resource $result_query
     *
     * @return string
     */
    public function field_name($number, $result_query = null)
    {
        if (!$result_query) {
            $result_query = $this->last_result_query;
            if (!$result_query) {
                return false;
            }
        }
        $fields = ifx_fieldproperties($result_query);
        if (!is_array($fields)) {
            return false;
        }

        $fields = array_keys($fields);

        return $fields[$number];
    }

    /**
     * Moves to the result indicated by 4 in a select
     * There are problems with this method, there are problems with IFX_SCROLL currents.
     *
     * @param int      $number
     * @param resource $result_query
     *
     * @return bool
     */
    public function data_seek($number, $result_query = null)
    {
        if (!$result_query) {
            $result_query = $this->last_result_query;
            if (!$result_query) {
                return false;
            }
        }
        if (($success = ifx_fetch_row($result_query, $number)) !== false) {
            return $success;
        }
        throw new KumbiaException($this->error());
    }

    /**
     * Number of rows affected in an insert, update or delete.
     *
     * @param resource $result_query
     *
     * @return int
     */
    public function affected_rows($result_query = null)
    {
        if (!$result_query) {
            $result_query = $this->last_result_query;
            if (!$result_query) {
                return false;
            }
        }
        if (($numberRows = ifx_affected_rows($result_query)) !== false) {
            return $numberRows;
        }
        throw new KumbiaException($this->error());
    }

    /**
     * Returns the Informix error.
     *
     * @return string
     */
    public function error($err = '')
    {
        if (!$this->id_connection) {
            $this->last_error = ifx_errormsg() ?: "[Unknown Error in Informix: $err]";
            if ($this->logger) {
                Logger::error($this->last_error);
            }

            return $this->last_error;
        }
        $this->last_error = ifx_errormsg($this->id_connection) ?: "[Unknown Error in Informix: $err]";
        $this->last_error .= $err;
        if ($this->logger) {
            Logger::error($this->last_error);
        }

        return $this->last_error;
    }

    /**
     * Returns the no Informix error.
     *
     * @return int
     */
    public function no_error()
    {
        return ifx_error();
    }

    /**
     * Returns the last autonumeric id generated in the database.
     *
     * @return int
     */
    public function last_insert_id($table = '', $primary_key = '')
    {
        $sqlca = ifx_getsqlca($this->last_result_query);

        return $sqlca['sqlerrd1'];
    }

    /**
     * Check if a table exists or not.
     *
     * @param string $table
     *
     * @return int
     */
    public function table_exists($table, $schema = '')
    {
        // Informix does not support schemas
        $table = addslashes("$table");
        $num = $this->fetch_one("SELECT COUNT(*) FROM systables WHERE tabname = '$table'");

        return (int) $num[0];
    }

    /**
     * Returns a valid LIMIT for a SELECT of the RBDM.
     *
     * @param string $sql
     *
     * @return string
     */
    public function limit($sql)
    {
        /*
              * It is not supported by Informix
              */
        return "$sql \n";
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
                $this->set_return_rows(false);

                return $this->query("DROP TABLE $table");
            }

            return true;
        }
        $this->set_return_rows(false);

        return $this->query("DROP TABLE $table");
    }

    /**
     * Create a table using RDBM native SQL.
     *
     * EVERYTHING:
     * - The index parameter needs to work. This should list multipes and unique composite indices
     * - Add the type of table to use (Informix)
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
            throw new KumbiaException("Invalid definition to create the table'$table'");
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
            if (isset($field_def['index'])) {
                if ($field_def['index']) {
                    $index[] = "INDEX($field)";
                }
            }
            if (isset($field_def['unique_index'])) {
                if ($field_def['unique_index']) {
                    $index[] = "UNIQUE($field)";
                }
            }
            if (isset($field_def['primary'])) {
                if ($field_def['primary']) {
                    $primary[] = "$field";
                }
            }
            if (isset($field_def['auto'])) {
                if ($field_def['auto']) {
                    $field_def['type'] = 'SERIAL';
                }
            }
            if (isset($field_def['extra'])) {
                $extra = $field_def['extra'];
            } else {
                $extra = '';
            }
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
        $this->set_return_rows(false);

        return $this->query($create_sql);
    }

    /**
     * List the tables in the database.
     *
     * @return array
     */
    public function list_tables()
    {
        return $this->fetch_all("SELECT tabname FROM systables WHERE tabtype = 'T' AND version <> 65537");
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
        // Informix does not support schemas
        // EVERYTHING: There is no identifiable method to obtain primary keys
        // no nulls and actual field sizes
        // Primary Key, Null
        $describe = $this->fetch_all("SELECT c.colname AS Field, c.coltype AS Type,
                'YES' AS NULL FROM systables t, syscolumns c WHERE
                c.tabid = t.tabid AND t.tabname = '$table' ORDER BY c.colno");
        $final_describe = array();
        foreach ($describe as $field) {
            //Serial
            if ($field['field'] == 'id') {
                $field['key'] = 'PRI';
                $field['null'] = 'NO';
            } else {
                $field['key'] = '';
            }
            if (substr($field['field'], -3) == '_id') {
                $field['null'] = 'NO';
            }
            if ($field['type'] == 262) {
                $field['type'] = 'serial';
            }
            if ($field['type'] == 13) {
                $field['type'] = 'varchar';
            }
            if ($field['type'] == 7) {
                $field['type'] = 'date';
            }
            $final_describe[] = array(
                'Field' => $field['field'],
                'Type' => $field['type'],
                'Null' => $field['null'],
                'Key' => $field['key'],
            );
        }

        return $final_describe;
    }

    /**
     * Make an insertion (Overwritten to indicate that it does not return records).
     *
     * @param string $table
     * @param array  $values
     * @param array  $fields
     *
     * @return bool
     */
    public function insert($table, array $values, array $fields = null)
    {
        $this->set_return_rows(false);

        return parent::insert($table, $values, $fields);
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
        $this->set_return_rows(false);

        return parent::update($table, $fields, $values, $where_condition);
    }

    /**
     * Delete records from a table!
     *
     * @param string $table
     * @param string $where_condition
     */
    public function delete($table, $where_condition)
    {
        $this->set_return_rows(false);

        return parent::delete($table, $where_condition);
    }

    /**
     * Indicates internally if the result obtained is returns records or not.
     *
     * @param bool $value
     */
    public function set_return_rows($value = true)
    {
        $this->return_rows = $value;
    }

    /**
     * Start a transaction if possible.
     */
    public function begin()
    {
        $this->set_return_rows(false);

        return $this->query('BEGIN WORK');
    }

    /**
     * Cancel a transaction if possible.
     */
    public function rollback()
    {
        $this->set_return_rows(false);

        return $this->query('ROLLBACK');
    }

    /**
     * Commit to a transaction if possible.
     */
    public function commit()
    {
        $this->set_return_rows(false);

        return $this->query('COMMIT');
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
