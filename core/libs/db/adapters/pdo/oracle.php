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
 * PDO Oracle Database Support.
 *
 * @category   Kumbia
 */
class DbPdoOracle extends DbPDO
{
    /**
     * RBDM name.
     */
    protected $db_rbdm = 'oci';

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
    const TYPE_VARCHAR = 'VARCHAR2';

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
        $this->pdo->setAttribute(PDO::ATTR_AUTOCOMMIT, false);
        $this->exec("alter session set nls_date_format = 'YYYY-MM-DD'");
        $this->begin();
    }

    /**
     * Returns a valid LIMIT for a SELECT of the RBDM.
     *
     * @param int $number
     *
     * @return string
     */
    public function limit($sql, $number)
    {
        if (!is_numeric($number) || $number < 0) {
            return $sql;
        }
        if (preg_match("/ORDER[\t\n\r ]+BY/i", $sql)) {
            if (stripos($sql, 'WHERE')) {
                return preg_replace("/ORDER[\t\n\r ]+BY/i", "AND ROWNUM <= $number ORDER BY", $sql);
            }

            return preg_replace("/ORDER[\t\n\r ]+BY/i", "WHERE ROWNUM <= $number ORDER BY", $sql);
        }
        if (stripos($sql, 'WHERE')) {
            return "$sql AND ROWNUM <= $number";
        }

        return "$sql WHERE ROWNUM <= $number";
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
     * - Add the type of table to use (Oracle)
     * - Support for autonomous fields
     * - Foreign key holder
     *
     * @param string $table
     * @param array  $definition
     * @param array  $index
     *
     * @return bool
     */
    public function create_table($table, $definition, $index = [])
    {
        $create_sql = "CREATE TABLE $table (";
        if (!is_array($definition)) {
            throw new KumbiaException("Invalid definition to create the table '$table'");
        }
        $create_lines = [];
        $index = [];
        $unique_index = [];
        $primary = [];
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
                $this->query("CREATE SEQUENCE {$table}_{$field}_seq START WITH 1");
            }
            $extra = isset($field_def['extra']) ? $field_def['extra'] : '';
            $create_lines[] = "$field " . $field_def['type'] . $size . ' ' . $not_null . ' ' . $extra;
        }
        $create_sql .= join(',', $create_lines);
        $last_lines = [];
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
     * List of Tables.
     *
     * @return bool
     */
    public function list_tables()
    {
        return $this->fetch_all('SELECT table_name FROM all_tables');
    }

    /**
     * Returns the last autonumeric id generated in the database.
     *
     * @return int
     */
    public function last_insert_id($table = '', $primary_key = '')
    {
        /*
         *Oracle does not support rich auto columns &eacute;
         */
        if ($table && $primary_key) {
            $sequence = $table . '_' . $primary_key . '_seq';
            $value = $this->fetch_one("SELECT $sequence.CURRVAL FROM dual");

            return $value[0];
        }

        return false;
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
        return $this->fetch_one("SELECT COUNT(*) FROM ALL_TABLES WHERE TABLE_NAME = '" . strtoupper($table) . "'")[0];
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
        /**
         * Does schemas support?
         */
        $describe = $this->fetch_all("SELECT LOWER(ALL_TAB_COLUMNS.COLUMN_NAME) AS FIELD,
                                        LOWER(ALL_TAB_COLUMNS.DATA_TYPE) AS TYPE,
                                        ALL_TAB_COLUMNS.DATA_LENGTH AS LENGTH, (
                                        SELECT COUNT(*)
                                        FROM ALL_CONS_COLUMNS
                                        WHERE TABLE_NAME = '" . strtoupper($table) . "' AND ALL_CONS_COLUMNS.COLUMN_NAME = ALL_TAB_COLUMNS.COLUMN_NAME AND ALL_CONS_COLUMNS.POSITION IS NOT NULL) AS KEY, ALL_TAB_COLUMNS.NULLABLE AS ISNULL FROM ALL_TAB_COLUMNS
                                        WHERE ALL_TAB_COLUMNS.TABLE_NAME = '" . strtoupper($table) . "'");
        $final_describe = [];
        foreach ($describe as $field) {
            $final_describe[] = array(
                'Field' => $field['field'],
                'Type' => $field['type'],
                'Length' => $field['length'],
                'Null' => $field['isnull'] === 'Y' ? 'YES' : 'NO',
                'Key' => $field['key'] == 1 ? 'PRI' : '',
            );
        }

        return $final_describe;
    }
}
