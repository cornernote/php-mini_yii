<?php

/**
 * Debug the target with syntax highlighting on by default.
 *
 * @param null $var
 * @param null $name
 */
function debug($var = null, $name = null)
{
    $bt = array();
    $file = '';
    if ($name !== false) {
        $bt = debug_backtrace();
        $file = str_replace(bp(), '', $bt[0]['file']);
        print '<div style="font-family: arial; background: #FFFBD6; margin: 10px 0;  padding: 5px; border:1px solid #666;">';
        if ($name) $name = '<b>' . $name . '</b><br/>';
        print '<span style="font-size:14px;">' . $name . '</span>';
        print '<div style="border:1px solid #ccc; border-width: 1px 0;">';
    }
    print '<pre style="margin:0;padding:10px;">';
    print_r($var);
    print '</pre>';
    if ($name !== false) {
        print '</div>';
        print '<span style="font-family: helvetica; font-size:10px;">' . $file . ' on line ' . $bt[0]['line'] . '</span>';
        print '</div>';
    }
}

/**
 * @return string
 */
function bp()
{
    return $_ENV['bp'];
}

/**
 * @param $page
 * @return string
 */
function url($page)
{
    return 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/' . $page;
}

/**
 * Render a view element
 *
 * @param $view
 * @param array $params
 * @param bool $return
 * @return string|bool
 * @throws Exception
 */
function render($view, $params = array(), $return = false)
{
    extract($params);
    $include = bp() . '/views/' . $view . '.php';
    if (!file_exists($include)) {
        throw new Exception('Element not found: ' . $include);
    }
    if ($return)
        ob_start();
    include($include);
    if ($return)
        return ob_get_clean();
    return true;
}

/**
 * @param $location
 * @param int $statusCode
 */
function redirect($location, $statusCode = 302)
{
    header('Location: ' . $location, true, $statusCode);
    exit;
}