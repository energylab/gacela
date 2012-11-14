<?php

class ClassInheritanceTest extends \Test\GUnit\Extensions\Database\TestCase
{
	/**
	 * @var \Test\Mapper\Customer
	 */
	protected $object;

	public function setUp()
	{
		parent::setUp();

		$this->object = \Gacela::instance()->loadMapper('Customer');

		$this->object->setCacheable(false);
	}

	protected function getDataSet()
	{
		return $this->createArrayDataSet(
			array(
				'users' => array(
					array('id' => 1, 'email' => 'mary@test.com'),
					array('id' => 2, 'email' => 'june@test.com'),
					array('id' => 3, 'email' => 'feb@test.com'),
					array('id' => 4, 'email' => 'jan@test.com'),
					array('id' => 5, 'email' => 'may@test.com'),
					array('id' => 6, 'email' => 'july@test.com'),
					array('id' => 7, 'email' => 'sept@test.com'),
					array('id' => 8, 'email' => 'aug@test.com'),
					array('id' => 9, 'email' => 'april@test.com'),
					array('id' => 10, 'email' => 'march@test.com'),
					array('id' => 11, 'email' => 'nov@test.com'),
					array('id' => 12, 'email' => 'december@test.com')
				),
				'customers' => array(
					array('id' => 2, 'first' => 'June', 'last' => 'Tester', 'phone' => '1234567890'),
					array('id' => 4, 'first' => 'January', 'last' => 'Jones', 'phone' => '9876543210'),
					array('id' => 6, 'first' => 'July', 'last' => 'IsHot', 'phone' => '6549873215'),
					array('id' => 8, 'first' => 'August', 'last' => 'IsHotter', 'phone' => '1236549870'),
					array('id' => 10, 'first' => 'March', 'last' => 'MyBirthday', 'phone' => '3271983000'),
					array('id' => 12, 'first' => 'December', 'last' => 'Christmas', 'phone' => '1225190098')
				)
			)
		);
	}

	public function testInsert()
	{
		$data = array(
			'id' => null,
			'email' => 'venus@test.com',
			'first' => 'Venus',
			'last' => 'De Milo',
			'phone' => '5555555543'
		);

		$changed = array_keys($data);

		$old = array();
		foreach($data as $key => $val) {
			$old[$key] = null;
		}

		$rs = $this->object->save($changed, (object) $data, $old);

		$this->assertNotEmpty($rs->id);

		$data['id'] = $rs->id;

		$this->assertEquals((object) $data, $rs);
	}

	public function testUpdateParent()
	{
		$new = array(
			'id' => 1,
			'email' => 'january@test.com',
			'first' => 'January',
			'last' => 'Jones',
			'phone' => '5556321234'
		);

		$changed = array('email');

		$old = array('email' => 'jan@test.com');

		$rs = $this->object->save($changed, (object) $new, $old);

		$this->assertEquals((object) $new, $rs);
	}

	public function testUpdateChild()
	{
		$new = array(
			'id' => 4,
			'email' => 'jan@test.com',
			'first' => 'January',
			'last' => 'Pfifer',
			'phone' => '9876543210'
		);

		$changed = array('last');

		$old = array('last' => 'Jones');

		$rs = $this->object->save($changed, (object) $new, $old);

		$this->assertEquals((object) $new, $rs);
	}

	public function delete()
	{

	}
}
