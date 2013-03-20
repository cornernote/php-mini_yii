<?php
/**
 * Base class for mysql tables
 *
 * @author Brett O'Donnell - cornernote@gmail.com
 * @copyright 2013, All Rights Reserved
 *
 * @property mysql $mysql
 * @property string $table
 * @property string $primaryKey
 * @property array $fields
 * @property array $schema
 *
 */
abstract class mysql_table
{

    /**
     * Stores instances of static objects
     *
     * @var mysql_table[]  - class name => model
     */
    private static $_models = array();

    /**
     * Stores table fields
     *
     * @var array
     */
    private $_fields = array();

    /**
     * Stores table primaryKey
     *
     * @var bool|string|array
     */
    private $_primaryKey = false;

    /**
     * Stores table schema
     *
     * @var array  - field name => metadata
     */
    private $_schema = array();

    /**
     * Stores if the row is a new unsaved record
     *
     * @var bool
     */
    private $_isNewRecord = true;

    /**
     * Returns a property value.
     * Do not call this method. This is a PHP magic method that we override to allow using the following syntax to read a property:
     * <pre>
     * $value=$mysql_table->propertyName;
     * </pre>
     * @param string $name the property name
     * @return mixed the property value, event handlers attached to the event, or the named behavior
     * @throws Exception if the property is not defined
     * @see __set
     */
    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter))
            return $this->$getter();

        if (in_array($name, $this->fields))
            return null;

        if (!is_array($this->primaryKey) && $name == $this->primaryKey)
            return null;

        if (is_array($this->primaryKey) && in_array($name, $this->primaryKey))
            return null;

        throw new Exception(strtr('Property "{class}.{property}" is not defined.', array(
            '{class}' => get_class($this),
            '{property}' => $name,
        )));
    }

    /**
     * Sets value of a property.
     * Do not call this method. This is a PHP magic method that we override to allow using the following syntax to set a property
     * <pre>
     * $this->propertyName=$value;
     * </pre>
     * @param string $name the property name
     * @param mixed $value the property value
     * @return mixed
     * @throws Exception if the property is not defined or the property is read only.
     * @see __get
     */
    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter))
            return $this->$setter($value);

        if (in_array($name, $this->fields))
            return $this->$name = $value;

        if (!is_array($this->primaryKey) && $name == $this->primaryKey)
            return $this->$name = $value;

        if (is_array($this->primaryKey) && in_array($name, $this->primaryKey))
            return $this->$name = $value;

        if (method_exists($this, 'get' . $name))
            throw new Exception(strtr('Property "{class}.{property}" is read only.', array(
                '{class}' => get_class($this),
                '{property}' => $name,
            )));
        else
            throw new Exception(strtr('Property "{class}.{property}" is not defined.', array(
                '{class}' => get_class($this),
                '{property}' => $name,
            )));
    }

    /**
     * Returns the static model of the specified table class.
     * The model returned is a static instance of the table class.
     * It is provided for invoking class-level methods (something similar to static class methods.)
     *
     * EVERY derived table class must override this method as follows,
     * <pre>
     * public static function model($className=__CLASS__)
     * {
     *     return parent::model($className);
     * }
     * </pre>
     *
     * @param string $className table class name.
     * @return mysql_table table model instance.
     */
    public static function model($className = __CLASS__)
    {
        if (isset(self::$_models[$className]))
            return self::$_models[$className];
        return self::$_models[$className] = new $className(null);
    }

    /**
     * Get cache relating to this table class
     *
     * @param $key
     * @param $usePk
     * @return mixed
     */
    public function getCache($key, $usePk = true)
    {
        $key = $this->getCacheKey($key, $usePk);
        return cache::get($key);
    }

    /**
     * Get cache relating to this table class
     *
     * @param $key
     * @param $data
     * @param $ttl
     * @param bool $usePk
     * @return mixed
     */
    public function setCache($key, $data, $ttl = null, $usePk = true)
    {
        $key = $this->getCacheKey($key, $usePk);
        return cache::set($key, $data, $ttl);
    }

    /**
     * Clear cache relating to this table class
     *
     * @param bool $usePk
     * @return mixed
     */
    public function clearCache($usePk = true)
    {
        $this->getCacheKeyPrefix($usePk, true);
    }

    /**
     * Get cache relating to this table class
     *
     * @param $key
     * @param $usePk
     * @return mixed
     */
    public function getCacheKey($key, $usePk = true)
    {
        $key = $this->getCacheKeyPrefix($usePk) . '_' . get_class($this) . '_' . $key;
        return $key;
    }

    /**
     * @param bool $usePk
     * @param bool $removeOldKey
     * @return bool|string
     */
    public function getCacheKeyPrefix($usePk = true, $removeOldKey = false)
    {
        $key = 'getCacheKeyPrefix.' . get_class($this);
        if ($usePk) {
            if (is_array($this->primaryKey)) {
                foreach ($this->primaryKey as $field) {
                    $key .= '_' . $this->$field;
                }
            }
            else {
                $key .= '_' . $this->{$this->primaryKey};
            }
        }

        $prefix = false;
        if (!$removeOldKey) {
            $prefix = cache::get($key);
        }
        if (!$prefix) {
            $prefix = uniqid();
            cache::set($key, $prefix);
        }
        return $prefix . '.';
    }

    /**
     * Database object
     *
     * @return mysql
     */
    protected function getMysql()
    {
        return $_ENV['mysql']['default'];
    }

    /**
     * Name of the database table
     *
     * @return string
     */
    public function getTable()
    {
        return get_class($this);
    }

    /**
     * Is this a new record that has not yet been saved
     *
     * @return array
     */
    public function getIsNewRecord()
    {
        return $this->_isNewRecord;
    }

    /**
     * Name of the primary key field
     *
     * @return bool|string|array
     */
    public function getPrimaryKey()
    {
        if ($this->_primaryKey)
            return $this->_primaryKey;

        $fields = array();
        foreach ($this->schema as $field => $metadata) {
            if ($metadata->Key != 'PRI') continue;
            $fields[] = $field;
        }
        if (!$fields)
            return false;
        if (count($fields) == 1)
            return $this->_primaryKey = $fields[0];

        return $this->_primaryKey = $fields;
    }

    /**
     * Fields that will be loaded and saved
     *
     * @return array
     */
    public function getFields()
    {
        if ($this->_fields)
            return $this->_fields;

        $fields = array();
        foreach ($this->schema as $field => $metadata) {
            if ($metadata->Key == 'PRI') continue;
            $fields[] = $field;
        }

        return $this->_fields = $fields;
    }

    /**
     * Table schema
     *
     * @return array
     */
    public function getSchema()
    {
        if ($this->_schema)
            return $this->_schema;

        if ($this->_schema = $this->getCache('schema', false))
            return $this->_schema;

        $this->_schema = array();
        $fields = $this->query("SHOW COLUMNS FROM `" . $this->table . "`");
        foreach ($fields as $field) {
            $this->_schema[$field->Field] = $field;
        }

        return $this->setCache('schema', $this->_schema, null, false);
    }

    /**
     * Find all rows matching the criteria
     *
     * @param $where
     * @param array $params
     * @return array
     */
    public function findAll($where = null, $params = array())
    {
        // build where
        $where = $where ? " WHERE $where" : '';
        foreach ($params as $k => $v) {
            $params[$k] = "'" . $this->mysql->escape($v) . "'";
        }
        $where = strtr($where, $params);

        // build fields
        $fields = array();
        foreach ($this->fields as $field) {
            $fields[] = "`$field`";
        }
        if ($this->primaryKey) {
            if (is_array($this->primaryKey)) {
                foreach ($this->primaryKey as $pk) {
                    $fields[] = "`$pk`";
                }
            }
            else {
                $fields = array_merge($fields, array('`' . $this->primaryKey . '`'));
            }
        }

        // get results
        $results = $this->query("SELECT " . implode(', ', $fields) . " FROM `" . $this->table . "` " . $where);
        foreach ($results as $k => $result) {
            $class = get_class($this);
            $model = new $class;
            $model->_isNewRecord = false;
            foreach ($result as $kk => $vv) {
                $model->$kk = $vv;
            }
            $results[$k] = $model;
        }
        return $results;
    }

    /**
     * Find a single row matching the criteria
     *
     * @param $where
     * @param array $params
     * @return array
     */
    public function find($where = null, $params = array())
    {
        $results = $this->findAll($where . " LIMIT 1", $params);
        return $results ? $results[0] : false;
    }

    /**
     * Find a single row with the selected pk
     *
     * @param $pk
     * @return mysql_table
     */
    public function findByPk($pk)
    {
        if (is_array($this->primaryKey)) {
            $where = array();
            foreach ($this->primaryKey as $field) {
                $where[] = "`" . $field . "`='" . $pk[$field] . "'";
            }
            return $this->find(implode(' AND ', $where));
        }
        return $this->find("`" . $this->primaryKey . "`='" . $pk . "'");
    }

    /**
     * Save this row's attributes to the database
     *
     * @return mixed
     */
    public function save()
    {
        $pk = $this->primaryKey;
        $fields = array();
        foreach ($this->fields as $field) {
            if (isset($this->$field)) {
                $value = $this->mysql->escape($this->$field);
                $fields[] = "`$field`='$value'";
            }
        }
        $query = ($this->isNewRecord ? "INSERT INTO" : "UPDATE") . " `" . $this->table . "` SET " . implode(', ', $fields);
        if (!$this->isNewRecord) {
            if (is_array($pk)) {
                $where = array();
                foreach ($pk as $field) {
                    if ($this->$field) {
                        $where[] = "`" . $field . "`='" . (int)$this->$field . "'";
                    }
                }
                if ($where) {
                    $query .= " WHERE " . implode(' AND ', $where);
                }
            }
            else {
                $query .= " WHERE `" . $this->primaryKey . "`='" . (int)$this->$pk . "'";
            }
        }
        $result = $this->query($query);
        $this->clearCache();
        if ($result) {
            if (!is_array($pk)) {
                if (empty($this->$pk)) {
                    $this->$pk = $this->mysql->getInsertId($result);
                }
            }
        }
        $this->_isNewRecord = false;
        return $result;
    }

    /**
     * Delete this row from the database
     *
     * @return mixed
     */
    public function delete()
    {
        $pk = $this->primaryKey;
        if ($this->isNewRecord) {
            return false;
        }
        if (is_array($pk)) {
            $where = array();
            foreach ($pk as $field) {
                $where[] = "`" . $field . "`='" . (int)$this->$field . "'";
            }
            $query = "DELETE FROM `" . $this->table . "` WHERE " . implode(' AND ', $where);
        }
        else {
            $query = "DELETE FROM `" . $this->table . "` WHERE `" . $this->primaryKey . "`='" . (int)$this->$pk . "'";
        }
        $result = $this->query($query);
        $this->clearCache();
        return $result;
    }

    /**
     * Delete all rows matching the criteria
     *
     * @param $where
     * @return mixed
     */
    public function deleteAll($where)
    {
        $query = "DELETE FROM `" . $this->table . "` WHERE " . $where;
        $result = $this->query($query);
        $this->clearCache();
        return $result;
    }

    /**
     * @param $sql
     * @param $params
     * @return bool|stdClass[]
     */
    public function query($sql, $params = array())
    {
        foreach ($params as $k => $v) {
            $params[$k] = "'" . $this->mysql->escape($v) . "'";
        }
        $sql = strtr($sql, $params);
        return $this->mysql->query($sql);
    }

}
