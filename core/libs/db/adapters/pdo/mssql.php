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
 * @see DbPdo Father of Drivers Pdo
 */
require_once CORE_PATH . 'libs/db/adapters/pdo.php';

/**
 * PDO Microsoft SQL Server Database Support.
 *
 * @category   Db adapters
 */
class DbPdoMsSQL extends DbPDO
{
    /**
     * RBDM Driver Name.
     */
    protected $db_rbdm = 'odbc';

    /**
     * Integer Data Type.
     */
    const TYPE_INTEGER = 'INTEGER';

    /**
     * Data Type Date.
     */
    const TYPE_DATE = 'SMALLDATETIME';

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
     * Execute driver initialization actions.
     */
    public function initialize()
    {
        /*
         * It allows to insert values in identity columns
         */
        //$this->exec("SET IDENTITY_INSERT ON");
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
        $table = addslashes("$table");
        $num = $this->fetch_one("SELECT COUNT(*) FROM sysobjects WHERE type = 'U' AND name = '$table'");

        return $num[0];
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
        $params = Util::getParams(func_get_args());

        if (!isset($params['offset']) && isset($params['limit'])) {
            return str_ireplace('SELECT ', "SELECT TOP $params[limit] ", $sql);
        }
        $orderby = stristr($sql, 'ORDER BY');
        if ($orderby !== false) {
            $sort = (stripos($orderby, 'desc') !== false) ? 'desc' : 'asc';
            $order = str_ireplace('ORDER BY', '', $orderby);
            $order = trim(preg_replace('/ASC|DESC/i', '', $order));
        }
        $sql = preg_replace('/^SELECT\s/i', 'SELECT TOP ' . $params[offset] . ' ', $sql);
        $sql = 'SELECT * FROM (SELECT TOP ' . $params[limit] . ' * FROM (' . $sql . ') AS itable';
        if ($orderby !== false) {
            $sql .= ' ORDER BY ' . $order . ' ';
            $sql .= (stripos($sort, 'asc') !== false) ? 'DESC' : 'ASC';
        }
        $sql .= ') AS otable';
        if ($orderby !== false) {
            $sql .= ' ORDER BY ' . $order . ' ' . $sort;
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
     * - Add the type of table to use (MySQL)
     * - Support for autonomous fields
     * - Foreign key holder
     *
     * @param string $table
     * @param array  $definition
     *
     * @return bool
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
                    $field_def['extra'] = isset($field_def['extra']) ? $field_def['extra'] . ' IDENTITY' : 'IDENTITY';
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

        return $this->query($create_sql);
    }

    /**
     * List the tables in the database.
     *
     * @return array
     */
    public function list_tables()
    {
        return $this->fetch_all("SELECT name FROM sysobjects WHERE type = 'U' ORDER BY name");
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
        $describe_table = $this->fetch_all("exec sp_columns @table_name = '$table'");
        $final_describe = array();
        foreach ($describe_table as $field) {
            $final_describe[] = array(
                'Field' => $field['COLUMN_NAME'],
                'Type' => $field['LENGTH'] ? $field['TYPE_NAME'] : $field['TYPE_NAME'] . '(' . $field['LENGTH'] . ')',
                'Null' => $field['NULLABLE'] == 1 ? 'YES' : 'NO',
            );
        }
        $describe_keys = $this->fetch_all("exec sp_pkeys @table_name = '$table'");
        foreach ($describe_keys as $field) {
            for ($i = 0; $i <= count($final_describe) - 1; ++$i) {
                if ($final_describe[$i]['Field'] == $field['COLUMN_NAME']) {
                    $final_describe[$i]['Key'] = 'PRI';
                } else {
                    $final_describe[$i]['Key'] = '';
                }
            }
        }

        return $final_describe;
    }

    /**
     * Returns the last autonumeric id generated in the database.
     *
     * @return int
     */
    public function last_insert_id($table = '', $primary_key = '')
    {
        /**
         * Why does SELECT SCOPE_IDENTITY() not work?
         */
        $num = $this->fetch_one("SELECT MAX($primary_key) FROM $table");

        return (int) $num[0];
    }
}
