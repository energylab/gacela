<?php

abstract class DbTestCase extends PHPUnit_Extensions_Database_TestCase
{
	private $conn;

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

	public function getConnection()
	{
		$test = \Gacela::instance()->getDataSource('test');

		$test->loadResource('peeps');

		$r = new ReflectionClass($test);

		$p = $r->getProperty('_adapter');

		$p->setAccessible(true);

		$p = $p->getValue($test);

		$pr = new ReflectionClass($p);

		$c = $pr->getProperty('_conn');

		$c->setAccessible(true);

		$pdo = $c->getValue($p);

		if(is_null($this->conn)) {
			$this->conn = $this->createDefaultDBConnection($pdo);
		}

		return $this->conn;
	}

	protected function getDataSet()
	{
		return new ArrayDataSet(
			array(
				'peeps' => array(),
			//	'contacts' => array(),
				'tests' => array()
			)
		);
	}
}
