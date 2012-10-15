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
	'mysql',
	array(
		'schema' => 'gacela',
		'host' => 'localhost',
		'password' => 'gacela',
		'user' => 'gacela',
	)
);

$gacela->registerDataSource(
	'wiki',
	'mysql',
	array(
		'schema' => 'wiki',
		'host' => 'localhost',
		'user' => 'gacela',
		'passwword' => 'gacela',
	)
);

$gacela->registerDataSource(
	'employees',
	'mysql',
	array(
		'schema' => 'employees',
		'host' => 'localhost',
		'user' => 'gacela',
		'passwword' => 'gacela',
	)
);

$memcache = new Memcache;

$memcache->addServer('127.0.0.1', 11211);

//$gacela->enableCache($memcache);

// Comment out to avoid using the config files and use the dynamic version instead
//$gacela->configPath(__DIR__.'/config');

function debug($value)
{
	return '<pre>'.print_r($value, true).'</pre>';
}