<?php

set_include_path(get_include_path().PATH_SEPARATOR.dirname(__DIR__).'/');

require_once 'library/Gacela.php';


ini_set('soap.wsdl_cache_enabled', '0'); 
ini_set('soap.wsdl_cache_ttl', '0'); 

$gacela = Gacela::instance();

function pwp($data)
{
	$b = debug_backtrace();
	$file = $b[0]['file'];
	echo '<pre>';
	echo 'line:' . $b[0]['line'] . ' ' . $file . "\n";
	print_r($data);
	echo '</pre>';
}	
	
require_once 'Test/GUnit/TestCase.php';
require_once 'Test/GUnit/Extensions/Database/TestCase.php';
