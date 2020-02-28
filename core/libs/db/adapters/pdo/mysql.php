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
require_once CORE_PATH.'libs/db/adapters/pdo.php';

/**
 * PDO MySQL Database Support.
 *
 * @category   Kumbia
 */
class DbPdoMySQL extends DbPDO
{
    /**
     * RBDM name.
     */
    protected $db_rbdm = 'mysql';
    /**
     * MySQL Connection Port.
     *
     * @var int
     */
    protected $db_port = 3306;

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
     * Execute driver initialization actions.
     */
    public function initialize()
    {
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
        if ($schema === '') {
            $num = $this->fetch_one("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '$table'");
        } else {
            $schema = addslashes("$schema");
            $num = $this->fetch_one("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '$table' AND TABLE_SCHEMA = '$schema'");
        }

        return $num[0];
    }

    /**
     * Create a table using RDBM native SQL.
     *
     * EVERYTHING:
     * - The index parameter is missing to work. This should list multiple and unique composite indexes
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
                $size = $field_def['size'] ? '('.$field_def['size'].')' : '';
            } else {
                $size = '';
            }
            if (isset($field_def['index'])) {
                if ($field_def['index']) {
                    $index[] = "INDEX(`$field`)";
                }
            }
            if (isset($field_def['unique_index'])) {
                if ($field_def['unique_index']) {
                    $index[] = "UNIQUE(`$field`)";
                }
            }
            if (isset($field_def['primary'])) {
                if ($field_def['primary']) {
                    $primary[] = "`$field`";
                }
            }
            if (isset($field_def['auto'])) {
                if ($field_def['auto']) {
                    $field_def['extra'] = isset($field_def['extra']) ? $field_def['extra'].' AUTO_INCREMENT' : 'AUTO_INCREMENT';
                }
            }
            if (isset($field_def['extra'])) {
                $extra = $field_def['extra'];
            } else {
                $extra = '';
            }
            $create_lines[] = "`$field` ".$field_def['type'].$size.' '.$not_null.' '.$extra;
        }
        $create_sql .= join(',', $create_lines);
        $last_lines = array();
        if (count($primary)) {
            $last_lines[] = 'PRIMARY KEY('.join(',', $primary).')';
        }
        if (count($index)) {
            $last_lines[] = join(',', $index);
        }
        if (count($unique_index)) {
            $last_lines[] = join(',', $unique_index);
        }
        if (count($last_lines)) {
            $create_sql .= ','.join(',', $last_lines).')';
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
        return $this->fetch_all('SHOW TABLES');
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
        $table = $schema ? "$table.$schema" : $table;
        $describe = $this->fetch_all("DESCRIBE `$table`");

        $final_describe = [];
        foreach ($describe as $field) {
            $final_describe[] = array(
                'Field' => $field['field'],
                'Type' => $field['type'],
                'Null' => $field['null'],
                'Default' => $field['default'],
                'Key' => $field['key'],
            );
        }

        return $final_describe;
    }
}
