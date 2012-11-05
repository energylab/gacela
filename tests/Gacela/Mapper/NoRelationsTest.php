<?php

class FindTest extends \DbTestCase
{
	public function providerFind()
	{
		return array(
			array('course', 1),
			array('house', 2),
			array('wizard', 3)
		);
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
	 * @todo   Implement testCount().
	 */
	public function testCount()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Gacela\Mapper\Mapper::find
	 * @dataProvider providerFind
	 */
	public function testFind($mapper, $primary)
	{
		$model = \Gacela::instance()->loadMapper($mapper)->find($primary);

		$key = $mapper.'Id';

		$this->assertSame($primary, $model->$key);
	}

	/**
	 * @covers Gacela\Mapper\Mapper::findAll
	 * @todo   Implement testFindAll().
	 */
	public function testFindAll()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Gacela\Mapper\Mapper::findAllByAssociation
	 * @todo   Implement testFindAllByAssociation().
	 */
	public function testFindAllByAssociation()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Gacela\Mapper\Mapper::findRelation
	 */
	public function testFindRelation()
	{

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

		$this->assertAttributeEquals(1, 'id', $rs);
	}

}
