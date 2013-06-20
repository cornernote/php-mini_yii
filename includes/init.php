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
$_ENV['mysql']['default'] = new mysql('localhost', 'root', '', 'testdb');
