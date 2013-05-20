<?php

namespace Test\GUnit;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		$gacela = \Gacela\Gacela::instance();

		$source = \Gacela\Gacela::createDataSource(
			array(
				'type' => 'mysql',
				'name' => 'db',
				'user' => 'gacela',
				'password' => 'gacela',
				'schema' => 'gacela',
				'host' => 'localhost'
			)
		);

		$test = \Gacela\Gacela::createDataSource(
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

		$gacela->registerNamespace('App', '/var/www/gacela/samples/')
			->registerNamespace('Test', '/var/www/gacela/tests/Test/');

	}

	public static function tearDownAfterClass()
	{
		\Gacela\Gacela::reset();
	}
}
