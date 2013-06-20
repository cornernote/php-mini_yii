<?php
/**
 *
 * MySQL class
 *
 * @author Brett O'Donnell - cornernote@gmail.com
 * @copyright 2013, All Rights Reserved
 *
 */
final class mysql
{
    /**
     * @var resource
     */
    private $connection;

    /**
     * @var array
     */
    public static $instances = array();

    /**
     * @var array
     */
    public static $connections = array();

    /**
     * @param string $id
     * @param string|resource $hostname
     * @param string $username
     * @param string $password
     * @param string $database
     * @param bool $new_link
     * @throws Exception
     */
    public function __construct($id, $hostname, $username = null, $password = null, $database = null, $new_link = null)
    {

        // hostname can be a 'resource' of the type 'mysql link', if so assign it to the connections
        if (gettype($hostname) == 'resource' && get_resource_type($hostname) == 'mysql link') {
            $this->connection = $hostname;
            self::$instances[$id] = $this;
            return;
        }

        // auto decide if we should create a new mysql connection
        if ($new_link === null) {
            $new_link = true;
            if (isset(self::$instances[$id])) {
                $new_link = false;
            }
        }

        // connect to database
        if (!$this->connection = mysql_connect($hostname, $username, $password, $new_link)) {
            throw new Exception('Error: Could not make a database connection using ' . $username . '@' . $hostname);
        }

        // select database
        if (!mysql_select_db($database, $this->connection)) {
            throw new Exception('Error: Could not connect to database ' . $database);
        }

        // set utf8 stuff
        mysql_query("SET NAMES 'utf8'", $this->connection);
        mysql_query("SET CHARACTER SET utf8", $this->connection);
        mysql_query("SET CHARACTER_SET_CONNECTION=utf8", $this->connection);
        mysql_query("SET SQL_MODE = ''", $this->connection);

        // add ourselves to the instances list
        self::$instances[$id] = $this;
    }

    /**
     * @param string $id
     * @throws Exception
     * @return mysql
     */
    public static function instance($id = 'default')
    {
        if (isset(self::$instances[$id]))
            return self::$instances[$id];
        throw new Exception('No instance with ID "' . $id . '" has been instantiated.');
    }

    /**
     * @param $sql
     * @throws Exception
     * @return bool|stdClass
     */
    public function query($sql)
    {
        var_dump($this->connection);
        $resource = mysql_query($sql, $this->connection);

        if ($resource) {
            if (is_resource($resource)) {
                $data = array();
                while ($result = mysql_fetch_object($resource)) {
                    $data[] = $result;
                }
                mysql_free_result($resource);
                return $data;
            }
            else {
                return true;
            }
        }
        else {
            throw new Exception('Error: ' . mysql_error($this->connection) . '<br />Error No: ' . mysql_errno($this->connection) . '<br />' . $sql);
        }
    }

    /**
     * @param $value
     * @return string
     */
    public function escape($value)
    {
        return mysql_real_escape_string($value, $this->connection);
    }

    /**
     * @return int
     */
    public function getAffectedRows()
    {
        return mysql_affected_rows($this->connection);
    }

    /**
     * @return int
     */
    public function getInsertId()
    {
        return mysql_insert_id($this->connection);
    }

    /**
     *
     */
    public function __destruct()
    {
        if ($this->connection) {
            mysql_close($this->connection);
        }
    }
}