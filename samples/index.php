<?php
/** 
 * @author Noah Goodrich
 * @date 9/7/11
 * @brief
 * 
 */

require '_init.php';
 
$args = explode('//', $_GET['script']);

if(empty($args[0])) {
	$args[0] = 'index';
}

if(empty($args[1])) {
	$args[1] = 'index';
}

$controller = ucfirst(array_shift($args));

require 'controllers//Controller.php';
require 'controllers//'.$controller.'.php';

$controller = new $controller;

$method = array_shift($args);

list($content, $title) = call_user_func_array(array($controller, $method), $args);

require 'views/template.php';