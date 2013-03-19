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
     * @param $hostname
     * @param $username
     * @param $password
     * @param $database
     * @param bool $new_link
     */
    public function __construct($hostname, $username, $password, $database, $new_link = false)
    {
        if (!$this->connection = mysql_connect($hostname, $username, $password, $new_link)) {
            exit('Error: Could not make a database connection using ' . $username . '@' . $hostname);
        }

        if (!mysql_select_db($database, $this->connection)) {
            exit('Error: Could not connect to database ' . $database);
        }

        mysql_query("SET NAMES 'utf8'", $this->connection);
        mysql_query("SET CHARACTER SET utf8", $this->connection);
        mysql_query("SET CHARACTER_SET_CONNECTION=utf8", $this->connection);
        mysql_query("SET SQL_MODE = ''", $this->connection);
    }

    /**
     * @param $sql
     * @throws Exception
     * @return bool|stdClass
     */
    public function query($sql)
    {
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
        mysql_close($this->connection);
    }
}