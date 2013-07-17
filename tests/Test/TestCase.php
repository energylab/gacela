<?php

namespace Test;

abstract class TestCase extends \Gacela\GUnit\Extensions\Database\TestCase
{
	private $conn;

	public static function setUpBeforeClass()
	{
		$gacela = \Gacela\Gacela::instance();

		$source = $gacela::createDataSource(
			array(
				'type' => 'mysql',
				'name' => 'db',
				'user' => 'gacela',
				'password' => 'gacela',
				'schema' => 'gacela',
				'host' => 'localhost'
			)
		);

		$test = $gacela::createDataSource(
			array(
				'type' => 'mysql',
				'name' => 'test',
				'user' => 'gacela',
				'password' => 'gacela',
				'schema' => 'test',
				'host' => 'localhost'
			)
		);

		$sf = $gacela::createDataSource(
			array(
				'type' => 'salesforce',
				'name' => 'sf',
				'soapclient_path' => '/var/www/sf/soapclient/',
				'wsdl_path' => '/var/www/sf.wsdl',
				'username' => 'me@noahgoodrich.com',
				'password' => 'S4l3sforce!wmYPflqJLiWH7e2dxWl0W2ES4',
				'objects' => array('Account')
			)
		);

		$gacela->registerDataSource($source)
				->registerDataSource($test)
				->registerDataSource($sf);

		$gacela->registerNamespace('App', '/var/www/gacela/samples/')
			->registerNamespace('Test', '/var/www/gacela/tests/Test/');

	}

	public function getConnection()
	{
		$test = \Gacela\Gacela::instance()->getDataSource('test');

		$test->loadResource('peeps');

		$r = new \ReflectionClass($test);

		$p = $r->getProperty('_adapter');

		$p->setAccessible(true);

		$p = $p->getValue($test);

		$pr = new \ReflectionClass($p);

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
		return $this->createArrayDataSet(
			array(
				'peeps' => array(),
				'objects' => array(),
				'meta' => array(),
				'tests' => array(),
				'logs' => array()
			)
		);
	}

}
