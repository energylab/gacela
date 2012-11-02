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

set_exception_handler(array('Gacela\Exception', 'handler'));

$db = $gacela::createDataSource(
	array(
		'name' => 'db',
		'type' => 'mysql',
		'schema' => 'gacela',
		'host' => 'localhost',
		'password' => 'gacela',
		'user' => 'gacela',
	)
);


$wiki = $gacela::createDataSource(
	array(
		'name' => 'wiki',
		'type' => 'mysql',
		'schema' => 'wiki',
		'host' => 'localhost',
		'user' => 'gacela',
		'passwword' => 'gacela',
	)
);

$employees = $gacela::createDataSource(
	array(
		'name' => 'employees',
		'type' => 'mysql',
		'schema' => 'employees',
		'host' => 'localhost',
		'user' => 'gacela',
		'passwword' => 'gacela',
	)
);

$gacela->registerDataSource($db)
	->registerDataSource($wiki)
	->registerDataSource($employees);

$memcache = new Memcache;

$memcache->addServer('127.0.0.1', 11211);

//$gacela->enableCache($memcache);

// Comment out to avoid using the config files and use the dynamic version instead
//$gacela->configPath(__DIR__.'/config');

function debug($value)
{
	return '<pre>'.print_r($value, true).'</pre>';
}