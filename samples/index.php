<?php
/** 
 * @author Noah Goodrich
 * @date 9/7/11
 * @brief
 * 
 */

require '_init.php';

if(isset($_GET['script'])) {
	$args = explode('/', $_GET['script']);
} else {
	$args = array();
}


if(empty($args[0])) {
	$args[0] = 'index';
}

if(empty($args[1])) {
	$args[1] = 'index';
}

$controller = ucfirst(array_shift($args));
$method = array_shift($args);

require 'controllers/Controller.php';
require 'controllers/'.$controller.'.php';

$controller = new $controller;

call_user_func_array(array($controller, $method), $args);

list($content, $title) = $controller->render();

require 'views/template.php';