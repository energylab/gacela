<?php
/** 
 * @author noah
 * @date 4/20/11
 * @brief
 * 
 */

require_once '../library/Gacela.php';

$gacela = Gacela::instance();

$gacela->registerNamespace('App', __DIR__.'/App');

$gacela->registerDataSource(
	'db',
	'database',
	array(
		'schema' => 'gacela',
		'host' => 'localhost',
		'password' => 'gacela',
		'user' => 'gacela',
		'dbtype' => 'mysql'
	)
);

$memcache = new Memcache;

$memcache->addServer('127.0.0.1', 11211);

//$gacela->enableCache($memcache);

// Comment out to avoid using the config files and use the dynamic version instead
$gacela->configPath(__DIR__.'/config');

function debug($value)
{
	return '<pre>'.print_r($value, true).'</pre>';
}