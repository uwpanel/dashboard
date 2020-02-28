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
 * @see DbPDOInterface
 */
require_once CORE_PATH . 'libs/db/adapters/pdo/interface.php';

/**
 * PHP Data Objects Support.
 *
 * @category   Kumbia
 * @package    Db
 * @subpackage Adapters
 */
abstract class DbPDO extends DbBase implements DbPDOInterface
{
    /**
     * PDO instance.
     *
     * @var PDO
     */
    protected $pdo;
    /**
     * Last Result of a Query.
     *
     * @var PDOStament
     */
    public $pdo_statement;
    /**
     * Last SQL statement sent.
     *
     * @var string
     */
    protected $last_query;
    /**
     * Last error generated.
     *
     * @var string
     */
    protected $last_error;
    /**
     * Number of rows affected.
     */
    protected $affected_rows;
    /**
     * RBDM Driver Name.
     */
    protected $db_rbdm;

    /**
     * Associative Array Result.
     */
    const DB_ASSOC = PDO::FETCH_ASSOC;

    /**
     * Result of Associative and Numerical Array.
     */
    const DB_BOTH = PDO::FETCH_BOTH;

    /**
     * Numerical Array result.
     */
    const DB_NUM = PDO::FETCH_NUM;

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
     * Makes a connection to the database.
     *
     * @param array $config
     *
     * @return bool
     */
    public function connect(array $config)
    {
        if (!extension_loaded('pdo')) {
            throw new KumbiaException('You must load the PHP extension called php_pdo');
        }

        try {
            $this->pdo = new PDO($config['type'] . ':' . $config['dsn'], $config['username'], $config['password']);
            if ($this->db_rbdm != 'odbc') {
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->pdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
                $this->pdo->setAttribute(PDO::ATTR_CURSOR, PDO::CURSOR_FWDONLY);
            }
            //Select charset
            if ($config['type'] === 'mysql' && isset($config['charset'])) {
                $this->pdo->exec('set character set ' . $config['charset']);
            }
            $this->initialize();

            return true;
        } catch (PDOException $e) {
            throw new KumbiaException($this->error($e->getMessage()));
        }
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
        if (!$this->pdo) {
            throw new KumbiaException('There is no connection to the database to perform this query.');
        }
        $this->last_query = $sql_query;
        $this->pdo_statement = null;
        try {
            if ($pdo_statement = $this->pdo->query($sql_query)) {
                return $this->pdo_statement = $pdo_statement;
            }
        } catch (PDOException $e) {
            throw new KumbiaException($this->error($e->getMessage() . " when executing <em>\"$sql_query\"</em>"));
        }
    }

    /**
     * Performs SQL operations on the database and returns the number of rows affected.
     *
     * @param string $sql_query
     *
     * @return int
     */
    public function exec($sql_query)
    {
        $this->debug('>' . $sql_query);
        if ($this->logger) {
            Logger::debug($sql_query);
        }
        if (!$this->pdo) {
            throw new KumbiaException('There is no connection to perform this action');
        }
        $this->last_query = $sql_query;
        $this->pdo_statement = null;
        try {
            $result = $this->pdo->exec($sql_query);
            $this->affected_rows = $result;
            if ($result === false) {
                throw new KumbiaException($this->error(" when executing <em>\"$sql_query\"</em>"));
            }

            return $result;
        } catch (PDOException $e) {
            throw new KumbiaException($this->error(" when executing <em>\"$sql_query\"</em>"));
        }
    }

    /**
     * Close the Database Engine Connection.
     */
    public function close()
    {
        if ($this->pdo) {
            unset($this->pdo);

            return true;
        }

        return false;
    }

    /**
     * Returns the content of a select row by row.
     *
     * @param resource $pdo_statement
     * @param int      $opt
     *
     * @return array
     */
    public function fetch_array($pdo_statement = null, $opt = '')
    {
        if ($opt === '') {
            $opt = self::DB_BOTH;
        }

        if (!$pdo_statement) {
            $pdo_statement = $this->pdo_statement;
            if (!$pdo_statement) {
                return false;
            }
        }
        try {
            $pdo_statement->setFetchMode($opt);

            return $pdo_statement->fetch();
        } catch (PDOException $e) {
            throw new KumbiaException($this->error($e->getMessage()));
        }
    }

    /**
     * Returns the number of rows of a select.
     *
     * @param string $pdo_statement
     *
     * @return int
     */
    public function num_rows($pdo_statement = '')
    {
        return $pdo_statement->columnCount();
        //return $pdo_statement->fetchColumn();
    }

    /**
     * Returns the name of a field in the result of a select.
     *
     * @param int      $number
     * @param resource $pdo_statement
     *
     * @return string
     */
    public function field_name($number, $pdo_statement = null)
    {
        if (!$this->pdo) {
            throw new KumbiaException('There is no connection to perform this action');
        }
        if (!$pdo_statement) {
            $pdo_statement = $this->pdo_statement;
            if (!$pdo_statement) {
                return false;
            }
        }
        try {
            $meta = $pdo_statement->getColumnMeta($number);

            return $meta['name'];
        } catch (PDOException $e) {
            throw new KumbiaException($this->error($e->getMessage()));
        }
    }

    /**
     * It moves to the result indicated by 4 in a select (Not supported by PDO).
     *
     * @param int          $number
     * @param PDOStatement $pdo_statement
     *
     * @return bool
     */
    public function data_seek($number, $pdo_statement = null)
    {
        return false;
    }

    /**
     * Number of rows affected in an insert, update or delete.
     *
     * @param resource $pdo_statement
     *
     * @return int
     */
    public function affected_rows($pdo_statement = null)
    {
        if (!$this->pdo) {
            throw new KumbiaException('There is no connection to perform this action');
        }
        if ($pdo_statement) {
            try {
                $row_count = $pdo_statement->rowCount();
                if ($row_count === false) {
                    throw new KumbiaException($this->error(" when executing SQL"));
                }

                return $row_count;
            } catch (PDOException $e) {
                throw new KumbiaException($this->error($e->getMessage()));
            }
        }

        return $this->affected_rows;
    }

    /**
     * Returns the PDO error.
     *
     * @return string
     */
    public function error($err = '')
    {
        $error = '';
        if ($this->pdo) {
            $error = $this->pdo->errorInfo()[2];
        }
        $this->last_error .= $error . ' [' . $err . ']';
        if ($this->logger) {
            Logger::error($this->last_error);
        }

        return $this->last_error;
    }

    /**
     * Returns the PDO error number.
     *
     * @return int
     */
    public function no_error($number = 0)
    {
        if ($this->pdo) {
            $number = $this->pdo->errorInfo()[1];
        }

        return $number;
    }

    /**
     * Returns the last auto-numeric id generated in the database.
     *
     * @return string
     */
    public function last_insert_id($table = '', $primary_key = '')
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Start a transaction if possible.
     */
    public function begin()
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Cancel a transaction if possible.
     */
    public function rollback()
    {
        return $this->pdo->rollBack();
    }

    /**
     * Commit to a transaction if possible.
     */
    public function commit()
    {
        return $this->pdo->commit();
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
     * Make an insert.
     *
     * @param string $table
     * @param array  $values
     * @param array  $fields
     *
     * @return int
     */
    public function insert($table, array $values, $fields = null)
    {
        if (!count($values)) {
            throw new KumbiaException("Impossible to insert into $table no data");
        }
        if (is_array($fields)) {
            $insert_sql = "INSERT INTO $table (" . join(',', $fields) . ') VALUES (' . join(',', $values) . ')';
        } else {
            $insert_sql = "INSERT INTO $table VALUES (" . join(',', $values) . ')';
        }

        return $this->exec($insert_sql);
    }

    /**
     * Update records in a table.
     *
     * @param string $table
     * @param array  $fields
     * @param array  $values
     * @param string $where_condition
     *
     * @return int
     */
    public function update($table, array $fields, array $values, $where_condition = null)
    {
        $update_sql = "UPDATE $table SET ";
        if (count($fields) !== count($values)) {
            throw new KumbiaException('The number of values to update is not the same as the fields');
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

        return $this->exec($update_sql);
    }

    /**
     * Delete records from a table!
     *
     * @param string $table
     * @param string $where_condition
     */
    public function delete($table, $where_condition)
    {
        if ($where_condition) {
            return $this->exec("DELETE FROM $table WHERE $where_condition");
        }

        return $this->exec("DELETE FROM $table");
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
            return $this->query("DROP TABLE IF EXISTS $table");
        }

        return $this->query("DROP TABLE $table");
    }

    /**
     * Returns a LIMIT for a SELECT of the RBDM.
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
     * Returns the last sql statement executed by the Adapter.
     *
     * @return string
     */
    public function last_sql_query()
    {
        return $this->last_query;
    }
}
