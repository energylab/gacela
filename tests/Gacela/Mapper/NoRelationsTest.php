<?php

class NoRelationsTest extends \Test\GUnit\Extensions\Database\TestCase
{
	/**
	 * @var Test\Mapper\Test
	 */
	protected $object;

	protected function getDataSet()
	{
		return $this->createArrayDataSet(
			array(
				'tests' => array(
					array('testName' => 'Test1', 'flagged' => 1, 'completed' => null),
					array('testName' => 'Test2', 'completed' => date('c'), 'flagged' => 0),
					array('id' => 4, 'testName' => 'AnotherOne', 'flagged' => 0, 'completed' => null),
					array('testName' => 'Test4', 'flagged' => 0, 'completed' => null)
				)
			)
		);
	}

	public function setUp()
	{
		parent::setUp();

		$this->object = Gacela::instance()->loadMapper('Test');
	}

	public function providerLoad()
	{
		return array(
			array('course', (object) array('courseId' => 1, 'wizardId' => 4, 'subject' => 'Care of Magical Teachers')),
			array('house', (object) array('houseId' => 1, 'houseName' => 'Gryffindor')),
		);

	}

	/**
	 * @covers Gacela\Mapper\Mapper::count
	 */
	public function testCountNoQuery()
	{
		$this->assertSame(4, $this->object->count());
	}

	/**
	 * @covers Gacela\Mapper\Mapper::count
	 */
	public function testCountCriteria()
	{
		$crit = new Gacela\Criteria();

		$crit->equals('flagged', 1);

		$this->assertSame(1, $this->object->count($crit));
	}

	/**
	 * @covers Gacela\Mapper\Mapper::count
	 */
	public function testCountQueryObject()
	{
		$query = new Gacela\DataSource\Query\Sql;

		$query->from('tests')
			->where('completed IS NOT NULL');

		$this->assertSame(1, $this->object->count($query));
	}

	/**
	 * @covers Gacela\Mapper\Mapper::find
	 */
	public function testFind()
	{
		$model = $this->object->find(1);

		$this->assertInstanceOf('\Test\Model\Test', $model);
		$this->assertSame('Test1', $model->testName);
		$this->assertTrue($model->flagged);
	}

	/**
	 * @covers Gacela\Mapper\Mapper::findAll
	 */
	public function testFindAll()
	{
		$col = $this->object->findAll();

		$this->assertInstanceOf('\Gacela\Collection\Arr', $col);
		$this->assertSame(4, $col->count());
	}

	/**
	 * @covers Gacela\Mapper\Mapper::findAll
	 */
	public function testFindAllWithCriteria()
	{
		$criteria = new Gacela\Criteria();

		$criteria->isNotNull('completed');

		$col = $this->object->findAll($criteria);

		$this->assertSame(1, $col->count());
	}

	/**
	 * @covers Gacela\Mapper\Mapper::load
	 * @dataProvider providerLoad
	 */
	public function testLoad($mapper, $data)
	{
		$this->assertAttributeEquals($data, '_data', \Gacela::instance()->loadMapper($mapper)->load($data));
	}

	/**
	 * @covers Gacela\Mapper\Mapper::save
	 */
	public function testInsertThroughSave()
	{
		$mapper = \Gacela::instance()->loadMapper('Test\Mapper\Test');

		$date = date('c');

		$new = (object) array(
			'testName' => 'I am a test',
			'started' => $date,
			'flagged' => 0
		);

		$changed = array('testName');

		$original = array(
			'testName' => null
		);

		$rs = $mapper->save($changed, $new, $original);

		$this->assertAttributeEquals(5, 'id', $rs);
	}

	/**
	 * @covers Gacela\Mapper\Mapper::getFields
	 */
	public function testGetFields()
	{
		$resource = Gacela::instance()->getDataSource('test')->loadResource('tests');

		$this->assertEquals($resource->getFields(), $this->object->getFields());
	}

	/**
	 * @covers Gacela\Mapper\Mapper::getPrimaryKey
	 */
	public function testGetSinglePrimaryKey()
	{
		$this->assertEquals(array('id'), $this->object->getPrimaryKey());
	}

	/**
	 * @covers Gacela\Mapper\Mapper::getPrimaryKey
	 */
	public function testGetCompoundPrimaryKey()
	{
		$this->markTestIncomplete();
	}

}
