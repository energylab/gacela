<?php

abstract class TestCase extends \PHPUnit_Framework_TestCase
{

	public static function setUpBeforeClass()
	{
		$gacela = Gacela::instance();

		$source = Gacela::createDataSource(
			array(
				'type' => 'mysql',
				'name' => 'db',
				'user' => 'gacela',
				'password' => 'gacela',
				'schema' => 'gacela',
				'host' => 'localhost'
			)
		);

		$test = Gacela::createDataSource(
			array(
				'type' => 'mysql',
				'name' => 'test',
				'user' => 'gacela',
				'password' => 'gacela',
				'schema' => 'test',
				'host' => 'localhost'
			)
		);

		$gacela->registerDataSource($source)
			->registerDataSource($test);

		$gacela->registerNamespace('App', __DIR__.'/../samples/')
			->registerNamespace('Test', __DIR__);

	}
}
