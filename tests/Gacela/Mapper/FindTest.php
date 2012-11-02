<?php

class FindTest extends \PHPUnit_Framework_TestCase
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

}
