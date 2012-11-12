<?php

class DependentsTest extends \Test\GUnit\Extensions\Database\TestCase
{
	/**
	 * @var Gacela\Mapper\Mapper
	 */
	protected $object;

	private $new = array(
		'code' => '123456',
		'fname' => 'First',
		'lname' => 'Last',
		'email' => 'test@test.com',
		'phone' => '3216549878',
		'street' => 'Street Address'
	);

	private $changed = array('fname', 'lname', 'email', 'code', 'phone', 'street');

	private $old = array(
		'fname' => null,
		'lname' => null,
		'email' => null,
		'code' => null,
		'phone' => null,
		'street' => null
	);

	public function setUp()
	{
		parent::setUp();

		$this->object = \Gacela::instance()->loadMapper('peep');
	}

	public function testInitDependents()
	{
		$m = \Gacela::instance()->loadMapper('peep');

		$this->assertObjectHasAttribute('_dependents', $m);

		$klass = new ReflectionClass($m);

		$dep = $klass->getProperty('_dependents');

		$dep->setAccessible(true);

		$dep = $dep->getValue($m);

		$this->assertArrayHasKey('contact', $dep);

		$this->assertArrayHasKey('resource', $dep['contact']);

		$fields = $klass->getProperty('_fields');

		$fields->setAccessible(true);

		$fields = $fields->getValue($m);

		$this->assertArrayHasKey('email', $fields);

		$this->assertAttributeSame(true, 'null', $fields['email']);
		$this->assertAttributeSame(true, 'null', $fields['street']);
		$this->assertAttributeSame(true, 'null', $fields['phone']);
	}

	/**
	 * @depends testInitDependents
	 */
	public function testInsertWithDependent()
	{
		$mapper = \Gacela::instance()->loadMapper('peep');

		$rs = $mapper->save($this->changed, (object) $this->new, $this->old);

		$this->assertNotSame(false, $rs);

		$this->assertNotEmpty($rs->code);

		$this->assertObjectHasAttribute('email', $rs);

		$this->assertAttributeSame('test@test.com', 'email', $rs);
	}

	/**
	 * @depends testInsertWithDependent
	 */
	public function testDeleteWithDependent()
	{
		$this->new = (object) $this->new;

		$mapper = \Gacela::instance()->loadMapper('peep');

		$mapper->save($this->changed, (object) $this->new, $this->old);

		$this->assertNotNull($mapper->find($this->new->code)->code);

		$this->assertTrue($mapper->delete($this->new));

		$record = $mapper->find($this->new->code);

		$this->assertNull($record->code);

		$test = \Gacela::instance()->getDataSource('test');

		$rs = $test->query($test->loadResource('contacts'), 'SELECT * FROM contacts');

		$this->assertSame(0, $rs->rowCount());
	}

	/**
	 * @depends testInsertWithDependent
	 */
	public function testInsertEmptyDependent()
	{
		$this->new = (object) array_merge(
			$this->new,
			array('email' => null, 'street' => null, 'phone' => null)
		);

		$this->changed = array('fname', 'lname', 'code');

		unset($this->old['email']);
		unset($this->old['street']);
		unset($this->old['phone']);

		$rs = $this->object->save($this->changed, $this->new, $this->old);

		$this->assertSame($this->new, $rs);

		$this->assertSame($this->new->code, $this->object->find($this->new->code)->code);

		return array($this->changed, $this->new, $this->old);
	}

	/**
	 * @depends testInsertEmptyDependent
	 */
	public function testDeleteEmptyDependent($args)
	{
		$this->object->save($args[0], $args[1], $args[2]);

		$this->object->delete($args[1]);

		$this->assertNull($this->object->find($args[1]->code)->code);
	}
}
