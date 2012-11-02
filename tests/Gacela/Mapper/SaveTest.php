<?php

class SaveTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @covers Gacela\Mapper\Mapper::addAssociation
	 * @todo   Implement testAddAssociation().
	 */
	public function testAddAssociation()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Gacela\Mapper\Mapper::removeAssociation
	 * @todo   Implement testRemoveAssociation().
	 */
	public function testRemoveAssociation()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Gacela\Mapper\Mapper::save
	 */
	public function testSaveNoDependentsNoInherits()
	{

	}

	public function testInsertWithDependent()
	{
		$mapper = \Gacela::instance()->loadMapper('peep');

		$new = (object) array(
			'code' => '123456',
			'fname' => 'First',
			'lname' => 'Last',
			'email' => 'test@test.com',
			'phone' => '3216549878',
			'street' => 'Street Address'
		);

		$changed = array('fname', 'lname', 'email', 'code', 'phone', 'street');

		$orig = array(
			'fname' => null,
			'lname' => null,
			'email' => null,
			'code' => null,
			'phone' => null,
			'street' => null
		);

		$rs = $mapper->save($changed, $new, $orig);

		$this->assertNotSame(false, $rs);

		$this->assertNotEmpty($rs->code);

		$this->assertObjectHasAttribute('email', $rs);

		$this->assertAttributeSame('test@test.com', 'email', $rs);

		return $rs;
	}

	/**
	 * @depends testInsertWithDependent
	 */
	public function testDeleteWithDependent()
	{

	}

	public function testSaveEmptyDependent()
	{

	}

	public function testSaveInherits()
	{

	}

	public function testSaveEmptyInherits()
	{

	}
}
