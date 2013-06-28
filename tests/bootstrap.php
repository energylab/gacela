<?php

set_include_path(get_include_path().PATH_SEPARATOR.dirname(__DIR__).'/');

require_once 'library/Gacela/Gacela.php';

$gacela = \Gacela\Gacela::instance();

function pwp($data)
{
	$b = debug_backtrace();
	$file = $b[0]['file'];
	echo '<pre>';
	echo 'line:' . $b[0]['line'] . ' ' . $file . "\n";
	print_r($data);
	echo '</pre>';
}	

require_once 'Test/TestCase.php';
