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
 * PDO Informix Database Support
 *
 * @category   Kumbia
 * @package    Db
 * @subpackage Adapters
 */
class DbPdoInformix extends DbPDO
{

    /**
     * RBDM Name
     */
    protected $db_rbdm = "informix";

    /**
     * Integer Data Type
     *
     */
    const TYPE_INTEGER = "INTEGER";

    /**
     * Data Type Date
     *
     */
    const TYPE_DATE = "DATE";

    /**
     * Varchar Data Type
     *
     */
    const TYPE_VARCHAR = "VARCHAR";

    /**
     * Decimal Data Type
     *
     */
    const TYPE_DECIMAL = "DECIMAL";

    /**
     * Datetime Data Type
     *
     */
    const TYPE_DATETIME = "DATETIME";

    /**
     * Char Data Type
     *
     */
    const TYPE_CHAR = "CHAR";

    /**
     * Execute driver initialization actions
     *
     */
    public function initialize()
    {
    }

    /**
     * Check if a table exists or not
     *
     * @param string $table
     * @return boolean
     */
    public function table_exists($table, $schema = '')
    {
        /**
         * Informix does not support schemas
         */
        $table = addslashes("$table");
        $num = $this->fetch_one("SELECT COUNT(*) FROM systables WHERE tabname = '$table'");
        return (int) $num[0];
    }

    /**
     * Returns a valid LIMIT for a SELECT of the RBDM
     *
     * @param string $sql
     * @return string
     */
    public function limit($sql)
    {
        $params = Util::getParams(func_get_args());

        $limit = '';
        if (isset($params['offset'])) {
            $limit .= " SKIP $params[offset]";
        }
        if (isset($params['limit'])) {
            $limit .= " FIRST $params[limit]";
        }

        return str_ireplace("SELECT ", "SELECT $limit ", $sql);
    }

    /**
     * Delete a table from the database
     *
     * @param string $table
     * @return boolean
     */
    public function drop_table($table, $if_exists = true)
    {
        if ($if_exists) {
            if ($this->table_exists($table)) {
                return $this->query("DROP TABLE $table");
            } else {
                return true;
            }
        } else {
            //$this->set_return_rows(false);
            return $this->query("DROP TABLE $table");
        }
    }

    /**
     * Create a table using RDBM native SQL
     *
     * EVERYTHING:
     * - The index parameter needs to work. This should list multipes and unique composite indices
     * - Add the type of table to use (Informix)
     * - Support for autonomous fields
     * - Foreign key holder
     *
     * @param string $table
     * @param array $definition
     * @return boolean
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
                $not_null = "";
            }
            if (isset($field_def['size'])) {
                $size = $field_def['size'] ? '(' . $field_def['size'] . ')' : '';
            } else {
                $size = "";
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
                    $field_def['type'] = "SERIAL";
                }
            }
            if (isset($field_def['extra'])) {
                $extra = $field_def['extra'];
            } else {
                $extra = "";
            }
            $create_lines[] = "$field " . $field_def['type'] . $size . ' ' . $not_null . ' ' . $extra;
        }
        $create_sql .= join(',', $create_lines);
        $last_lines = array();
        if (count($primary)) {
            $last_lines[] = 'PRIMARY KEY(' . join(",", $primary) . ')';
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
     * List the tables in the database
     *
     * @return array
     */
    public function list_tables()
    {
        return $this->fetch_all("SELECT tabname FROM systables WHERE tabtype = 'T' AND version <> 65537");
    }

    /**
     * List the fields in a table
     *
     * @param string $table
     * @return array
     */
    public function describe_table($table, $schema = '')
    {
        /**
         * Informix does not support schemas
         * EVERYTHING: There is no identifiable method to obtain primary keys
         * no nulls and actual field sizes
         * Primary Key, Null?
         */
        $describe = $this->fetch_all("SELECT c.colname AS Field, c.coltype AS Type,
                'YES' AS NULL, c.collength as Length
                 FROM systables t, syscolumns c WHERE
                c.tabid = t.tabid AND t.tabname = '$table' ORDER BY c.colno");
        $final_describe = array();
        foreach ($describe as $field) {
            //Serial
            if ($field['field'] == 'id') {
                $field["key"] = 'PRI';
                $field["null"] = 'NO';
            } else {
                $field["key"] = '';
            }
            if (substr($field['field'], -3) == '_id') {
                $field["null"] = 'NO';
            }
            if ($field['type'] == 262) {
                $field['type'] = "integer";
            }
            if ($field['type'] == 13) {
                $field['type'] = "varchar(" . $field['length'] . ")";
            }
            if ($field['type'] == 2) {
                $field['type'] = "int(" . $field['length'] . ")";
            }
            if ($field['type'] == 7) {
                $field['type'] = "date";
            }
            $final_describe[] = array(
                "Field" => $field["field"],
                "Type" => $field["type"],
                "Null" => $field["null"],
                "Key" => $field["key"]
            );
        }
        return $final_describe;
    }
}
