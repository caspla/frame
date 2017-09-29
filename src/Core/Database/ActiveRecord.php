<?php
namespace Core\Database;

/**
 * Simple implementation of the ActiveRecord design pattern.
 *
 * Class ActiveRecord
 * @package Core\Database
 * @author Pascal Frey
 */
abstract class ActiveRecord
{
    /**
     * Database connection object
     *
     * @var \Core\Database\Connection
     */
    protected static $db;

    /**
     * @var string
     */
    protected static $tableName;

    /**
     * @var array
     */
    protected static $tableDef = [];

    /**
     * @var array
     */
    protected $values = [];

    /**
     * @var int
     */
    protected $saveAttempts = 0;

    /**
     * Set/Get the database connection object.
     *
     * @return \Core\Database\Connection
     */
    public static function database($set = null)
    {
        if ($set instanceof Connection) self::$db = $set;
        return self::$db;
    }

    /**
     * Get the name of the database table.
     *
     * @return string
     */
    public static function getTableName()
    {
        return static::$tableName;
    }

    /**
     * Get the field definition of the database table.
     *
     * @return array
     */
    public static function getTableDef()
    {
        return static::$tableDef;
    }

    /**
     * Get an Entity by id.
     *
     * @param $id
     * @return mixed
     */
    public static function findById($id)
    {
        return self::find("id=" . (int)$id);
    }

    /**
     * @param null $where
     * @return int
     */
    public static function count($where = null)
    {
        return static::database()->getCountWhere(static::$tableName, $where);
    }

    /**
     * Find an single Entity.
     *
     * @param null $where
     * @param null $orderBy
     * @return mixed
     */
    public static function find($where = null, $orderBy = null)
    {
        $row = self::findAll($where, $orderBy, 1);
        return current($row);
    }

    /**
     * Find multiple Entities.
     *
     * @param null $where
     * @param null $orderBy
     * @param null $limit
     * @return array|mixed
     */
    public static function findAll($where = null, $orderBy = null, $limit = null)
    {
        $rows = [];
        try
        {
            $rows = static::database()->getRowsWhere(static::$tableName, $where, null, $orderBy, $limit);
        }
        catch (\Exception $e)
        {
            self::handleException($e);
        }

        $objects = [];
        if ($rows)
        {
            foreach ($rows as $values)
            {
                $objects[] = new static($values);
            }
        }

        return $objects;
    }

    /**
     * Create the database table by field definition.
     */
    public static function createTable()
    {
        $tableDef = static::$tableDef;

        if ($tableDef)
        {
            $fields = [];
            $fields['id'] = 'int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT';
            foreach ($tableDef as $field => $fieldType)
            {
                preg_match_all('~([a-z]*)(\([0-9]*\)){0,1}~', $fieldType, $matches);
                $fieldType = $matches[0][0];
                $fieldLength = $matches[0][1];
                $defaultValue = 'NOT NULL';

                $fields[$field] = $fieldType . ($fieldLength ? ' (' . $fieldLength . ')' : '');
                $fields[$field] .= ' ' . $defaultValue;
            }
            static::database()->createTable(static::$tableName, $fields);
        }
    }

    /**
     * Handle typical exceptions that can appear using this class.
     *
     * @param \Exception $e
     */
    public static function handleException(\Exception $e)
    {
        switch ($e->getCode())
        {
            // Unknown table column
            case '42S22':

                // get a list of existing fields in the database table
                $fields = [];
                foreach (static::database()->getFields(static::$tableName) as $row)
                {
                    if (strtolower($row['Field']) == 'id') continue;
                    $fields[$row['Field']] = $row['Type'];
                }
                $newFields = [];
                $prevField = 'id';
                // iterate over the field definition array
                foreach (static::$tableDef as $field => $type)
                {
                    // field does exist in definition, but not in the database table
                    if (!isset($fields[$field]))
                    {
                        // remember this field
                        $newFields[$field] = array(
                            'action' => 'add',
                            'column' => $field,
                            'type' => $type,
                            'after' => $prevField ? $prevField : null
                        );
                    }
                    $prevField = $field;
                }
                // alter the database table
                static::database()->alterTable(static::$tableName, $newFields);
                break;

            // Unknown table
            case '42S02':

                self::createTable();
                break;

            default:
                var_debug($e->getMessage());
                break;
        }
    }

    /**
     * @param array $values
     */
    public function __construct($values = [])
    {
        if (is_array($values) || is_object($values)) $this->setValues($values);
    }

    /**
     * @return bool
     */
    public function hasId()
    {
        return isset($this->values['id']) && $this->values['id'] ? true : false;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->values['id'];
    }

    /**
     * @param $key
     * @param $value
     * @return ActiveRecord
     */
    public function setValue($key, $value)
    {
        if ($this->hasId() == false || ($this->hasId() == true && strtolower($key) != 'id'))
        {
            $this->values[$key] = $value;
        }
        return $this;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getValue($key)
    {
        return $this->values[$key];
    }

    /**
     * @param array $values
     * @return ActiveRecord
     */
    public function setValues($values = [])
    {
        foreach ($values as $key => $value)
        {
            $this->setValue($key, $value);
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @return ActiveRecord
     */
    public function save()
    {
        $values = $this->getValues();
        if ($values)
        {
            // execute beforeSave callback method
            $this->beforeSave();

            try
            {
                // update existing record
                if ($this->hasId())
                {
                    static::database()->updateRowById(static::$tableName, $values, $this->getId());
                }
                // insert new record
                else
                {
                    static::database()->insertRow(static::$tableName, $values);
                    $this->values['id'] = static::database()->lastInsertId();
                }
            }
            // Record could not be saved
            catch (\Exception $e)
            {
                self::handleException($e);
                if (in_array($e->getCode(), array('42S22', '42S02')))
                {
                    if (++$this->saveAttempts == 1) $this->save();
                }
            }

            // execute afterSave callback method
            $this->afterSave();
        }

        return $this;
    }

    /**
     * Delete the Entity
     *
     * @return ActiveRecord
     */
    public function delete()
    {
        // execute beforeDelete callback method
        $this->beforeDelete();

        static::database()->deleteRowById(static::$tableName, $this->getId());

        // execute a callback method
        $this->afterDelete();
        return $this;
    }

    /**
     * @callback
     */
    public function beforeSave()
    {
    }

    /**
     * @callback
     */
    public function afterSave()
    {
    }

    /**
     * @callback
     */
    public function beforeDelete()
    {
    }

    /**
     * @callback
     */
    public function afterDelete()
    {
    }

    /**
     * @param $key
     * @param $value
     */
    public function __set($key, $value)
    {
        $this->setValue($key, $value);
    }

    /**
     * @param $key
     * @return int|mixed
     */
    public function __get($key)
    {
        if ($key == 'id') return $this->getId();
        return $this->getValue($key);
    }
}