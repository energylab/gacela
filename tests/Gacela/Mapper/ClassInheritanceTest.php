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
				'users' => array(array('id' => 1, 'email' => 'jan@test.com')),
				'customers' => array(array('id' => 1, 'first' => 'January', 'last' => 'Jones', 'phone' => '5556321234'))
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
			'id' => 1,
			'email' => 'jan@test.com',
			'first' => 'January',
			'last' => 'Pfifer',
			'phone' => '5556321234'
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
