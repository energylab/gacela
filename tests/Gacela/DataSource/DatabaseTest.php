<?php

class DatabaseTest extends \Test\GUnit\Extensions\Database\TestCase
{

	/**
	 * @var Gacela\DataSource\Database
	 */
	protected $object;

	protected function getDataSet()
	{
		return $this->createArrayDataSet(
			array(
				'tests' => array(
					array('testName' => 'test1'),
					array('testName' => 'test2'),
					array('testName' => 'test3'),
					array('testName' => 'test4')
				)
			)
		);
	}

	public function setUp()
	{
		parent::setUp();

		$this->object = \Gacela::instance()->getDataSource('test');
	}

	public function providerSelectQuery()
	{
		return array(
			array('SELECT * FROM tests WHERE testName = "test1"', null),
			array('SELECT * FROM tests WHERE testName = :test', array(':test' => 'test1'))
		);
	}

	/**
	 * @param $sql
	 * @param $args
	 * @dataProvider providerSelectQuery
	 */
	public function testSelectQuery($sql, $args)
	{
		$rs = $this->object->query($this->object->loadResource('tests'), $sql, $args);

		$this->assertSame(1, $rs->rowCount());

		$this->assertSame('test1', $rs->fetchObject()->testName);
	}
}
