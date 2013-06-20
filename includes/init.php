<?php
/**
 * Global Init
 */

// set error reporting
//error_reporting(E_ALL);

// setup environment
$_ENV['bp'] = dirname(dirname(__FILE__)); // base path

// functions
require($_ENV['bp'] . '/includes/globals.php');

// classes
require($_ENV['bp'] . '/includes/classes/cache.php');
require($_ENV['bp'] . '/includes/classes/mysql.php');
require($_ENV['bp'] . '/includes/classes/mysql_table.php');

// models
foreach (glob('includes/models/*.php') as $model) require($model);

// start the session
session_start();

// connect to database
cache::init();
new mysql('default', 'localhost', 'root', '', 'test');

// connect to an existing connection
//$connection2 = mysql_connect('localhost', 'root', '', true);
//mysql_select_db('test2', $connection2);
//debug($connection2, 'connection2');
//new mysql('db2', $connection2);

// connect to another database
//new mysql('db3', 'localhost', 'root', '', 'test3');
