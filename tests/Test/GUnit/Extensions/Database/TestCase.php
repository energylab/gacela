<?php

namespace Test\GUnit\Extensions\Database;

abstract class TestCase extends \PHPUnit_Extensions_Database_TestCase
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

	public static function tearDownAfterClass()
	{
		\Gacela\Gacela::reset();
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

	public function getSetUpOperation()
	{
		$cascadeTruncates = TRUE; //if you want cascading truncates, false otherwise
		//if unsure choose false

		return new \PHPUnit_Extensions_Database_Operation_Composite(array(
			new Operation\MySQLTruncate($cascadeTruncates),
			\PHPUnit_Extensions_Database_Operation_Factory::INSERT()
		));
	}

	protected function getDataSet()
	{
		return $this->createArrayDataSet(
			array(
				'peeps' => array(),
				'objects' => array(),
				'meta' => array(),
				'tests' => array()
			)
		);
	}

	/**
	 * Creates a new Array DataSet with the given $array.
	 *
	 * @param string $xmlFile
	 * @return DataSet\ArrayDataSet
	 */
	protected function createArrayDataSet(array $array)
	{
		return new DataSet\ArrayDataSet($array);
	}
}
