<?php
namespace Core\Database;

/**
 * Represents a SQL database connection.
 * Uses PDO internally.
 *
 * Class Connection
 * @package Core
 * @author Pascal Frey
 */
class Connection
{
    /**
     * @var \PDO $pdo
     */
    private $pdo;

    /**
     * @return \PDO
     */
    public function pdo()
    {
        return $this->pdo;
    }

    /**
     * Connect to a SQL database using PDO
     *
     * @param $dsn
     * @param string $user
     * @param string $password
     * @param array $options
     * @return self
     */
    public function connect($dsn, $user = '', $password = '', $options = array())
    {
        $this->pdo = new \PDO($dsn, $user, $password, $options);
        return $this;
    }

    /**
     * Close the database connection
     */
    public function close()
    {
        $this->pdo = null;
    }

    /**
     * Returns an string quoted.
     *
     * Alias for PDO::quote
     *
     * @see PDO::quote
     * @param $str
     * @return string
     */
    public function quote($str)
    {
        return $this->pdo->quote($str);
    }

    /**
     * Executes an SQL statement.
     *
     * The method can be used to do a direct query, or to execute an prepared statement.
     *
     * For the first variant, you should escape your inputs using the quote() method.
     *
     * @example
     * direct query:
     * $db->query("SELECT * FROM users");
     *
     * @example
     * prepared statement:
     * $db->query("SELECT * FROM users WHERE lastname = :lastname", array(':lastname' => 'Mustermann'));
     * or
     * $db->query("SELECT * FROM users WHERE lastname = ? AND firstname = ?", array(0 => 'Mustermann', 1 => 'Max'));
     *
     * @param $sql
     * @param array $values
     * @return \PDOStatement
     */
    public function query($sql, $values = array())
    {
        if ($values)
        {
            $statement = $this->pdo->prepare($sql);
            $statement->execute($values);
            return $statement;
        }
        else
        {
            return $this->pdo->query($sql);
        }
    }

    /**
     * Alias for PDO::prepare
     *
     * @param $sql
     * @return \PDOStatement
     */
    public function prepare($sql)
    {
        return $this->pdo->prepare($sql);
    }

    /**
     * Alias for PDO::exec
     *
     * @param $sql
     * @return int
     */
    public function exec($sql)
    {
        return $this->pdo->exec($sql);
    }

    /**
     * Fetch a single row from the database
     *
     * @param $sql
     * @param array $values
     * @param null $fetchStyle
     * @return mixed
     */
    public function getRow($sql, $values = array(), $fetchStyle = null)
    {
        return $this->query($sql, $values)->fetch($fetchStyle);
    }


    /**
     * Fetch multiple rows from the database
     *
     * @param $sql
     * @param array $values
     * @param null $fetchStyle
     * @return mixed
     */
    public function getRows($sql, $values = array(), $fetchStyle = null)
    {
        return $this->query($sql, $values)->fetchAll($fetchStyle);
    }

    /**
     * Fetch a row by ID
     *
     * @param string $table
     * @param int $id
     * @param null $fetchStyle
     * @return mixed
     */
    public function getRowById($table, $id, $fetchStyle = null)
    {
        return $this->getRowWhere($table, "id=?", array($id), null, $fetchStyle);
    }

    /**
     * Fetch a row identified by WHERE clause
     *
     * @param $table
     * @param $where
     * @param array $values
     * @param null $orderBy
     * @param null $fetchStyle
     * @return mixed
     */
    public function getRowWhere($table, $where, $values = array(), $orderBy = null, $fetchStyle = null)
    {
        return current($this->getRowsWhere($table, $where, $values, $orderBy, 1, $fetchStyle));
    }

    /**
     * Fetch multiple rows identified by WHERE clause
     *
     * @param $table
     * @param null $where
     * @param array $values
     * @param null $orderBy
     * @param null $limit
     * @param null $fetchStyle
     * @return mixed
     */
    public function getRowsWhere($table, $where = null, $values = array(), $orderBy = null, $limit = null, $fetchStyle = null)
    {
        if (!is_array($values) && $values) $values = array($values);
        $sql = "SELECT * FROM `" . $table . "`";
        if ($where) $sql .= " WHERE " . $where;
        if ($orderBy) $sql .= " ORDER BY " . $orderBy;
        if ($limit)
        {
            if (is_array($limit)) $limit = $limit[0] . ',' . $limit[1];
            $sql .= " LIMIT " . $limit;
        }
        return $this->getRows($sql, $values, $fetchStyle);
    }

    /**
     * Fetch count of rows identified by WHERE clause
     *
     * @param $table
     * @param null $where
     * @param array $values
     * @return int
     */
    public function getCountWhere($table, $where = null, $values = array())
    {
        if (!is_array($values) && $values) $values = array($values);
        $sql = "SELECT * FROM `" . $table . "`";
        if ($where) $sql .= " WHERE " . $where;
        return $this->query($sql, $values)->rowCount();
    }

    /**
     * Insert a single row
     *
     * @param $table
     * @param array $values
     * @return int
     */
    public function insertRow($table, $values = array())
    {
        $sql = "INSERT INTO `" . $table . "` (" . $this->getFieldsString(array_keys($values)) . ")";
        $sql .= " VALUES (" . $this->getValuesString($values) . ")";
        return $this->exec($sql);
    }

    /**
     * Update one or multiple rows
     *
     * @param $table
     * @param array $values
     * @param $where
     * @return bool|int
     */
    public function updateRowsWhere($table, $values = array(), $where)
    {
        if (!is_array($values) || empty($values)) return false;
        $sql = "UPDATE `" . $table . "` SET ";
        $tmp = array();
        foreach ($values as $field => $value) $tmp[] = '`' . $field . '` = ' . $this->quote($value);
        $sql .= $this->getValuesString($tmp, false);
        $sql .= " WHERE " . $where;
        return $this->exec($sql);
    }

    /**
     * Update a single row by ID
     *
     * @param $table
     * @param array $values
     * @param $id
     * @return bool|int
     */
    public function updateRowById($table, $values = array(), $id)
    {
        return $this->updateRowsWhere($table, $values, 'id = ' . intval($id));
    }

    /**
     * @see PDO::lastInsertId
     * @return string
     */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Delete row(s) identified by WHERE clause
     *
     * @param $table
     * @param $where
     * @return int
     */
    public function deleteRowWhere($table, $where)
    {
        return $this->exec('DELETE FROM `' . $table . '` WHERE ' . $where);
    }

    /**
     * Delete row by ID
     *
     * @param $table
     * @param $id
     * @return int
     */
    public function deleteRowById($table, $id)
    {
        return $this->deleteRowWhere($table, 'id = ' . intval($id));
    }

    /**
     * Create an new table
     *
     * @param $table
     * @param array $fields
     * @return bool|int
     */
    public function createTable($table, $fields = array())
    {
        if (!is_array($fields) || empty($fields)) return false;

        $sql = 'CREATE TABLE IF NOT EXISTS `' . $table . '` (';

        $tmp = array();
        foreach ($fields as $name => $type) $tmp[] = '`' . $name . '` ' . $type . '';
        $sql .= $this->getValuesString($tmp, false);
        $sql .= ')';

        return $this->exec($sql);
    }

    /**
     * Alter an existing table
     *
     * @param $table
     * @param array $fields
     * @return bool|int
     */
    public function alterTable($table, $fields = array())
    {
        if (!is_array($fields) || empty($fields)) return false;

        $sql = 'ALTER TABLE `' . $table . '`';

        $tmp = array();
        foreach ($fields as $field => $options)
        {
            $tmp[$field] = ' ' . strtoupper($options['action']);
            $tmp[$field] .= ' `' . $options['column'] . '`' . ' ' . $options['type'] . '';
            if (isset($options['after'])) $tmp[$field] .= ' AFTER `' . $options['after'] . '`';
        }
        $sql .= $this->getValuesString($tmp, false);

        return $this->exec($sql);
    }

    /**
     * Get list of exiting tables (SHOW TABLES)
     *
     * @return array
     */
    public function getTables()
    {
        return array_values($this->getRow('SHOW TABLES'));
    }

    /**
     * Return columns for a given table
     *
     * @param $table
     * @return mixed
     */
    public function getFields($table)
    {
        return $this->getRows('SHOW COLUMNS FROM `' . $table . '`');
    }

    /**
     * Returns a comma seperated set of fields.
     *
     * @param array $fields
     * @param bool $quoteValues
     * @return string
     */
    public function getFieldsString($fields = array(), $quoteValues = true)
    {
        if ($quoteValues) foreach ($fields as $idx => $field) $fields[$idx] = '`' . $field . '`';
        return implode(',', $fields);
    }

    /**
     * Returns a comma seperated set of values.
     *
     * @example $db->query("SELECT * FROM users WHERE id IN (" .  $this->getValuesString(array(5,8,3)) . ")");
     *
     * @param array $values
     * @param bool $quoteValues
     * @return string
     */
    public function getValuesString($values = array(), $quoteValues = true)
    {
        if ($quoteValues) foreach ($values as $idx => $value) $values[$idx] = $this->quote($value);
        return implode(',', $values);
    }

    /**
     * Returns a comma seperated set of placeholders, for each given value.
     *
     * @param array $values
     * @return string
     */
    public function getPlaceholdersString($values = array())
    {
        return implode(',', array_fill(0, count($values), '?'));
    }
}