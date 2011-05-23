<?php
/** 
 * @author noah
 * @date 4/20/11
 * @brief
 * 
 */

require_once '../library/Gacela.php';

$gacela = Gacela::instance();
 
$gacela->registerNamespace('App', __DIR__);

$gacela->registerDataSource(
	'db',
	'database',
	array(
		'schema' => 'gacela',
		'host' => 'localhost',
		'password' => 'I8Lissa',
		'user' => 'root',
		'dbtype' => 'mysql'
	)
)
->enableMemcache(
	array(
		array('127.0.0.1', 11211)
	)
);

function debug($value)
{
	return '<pre>'.print_r($value, true).'</pre>';
}