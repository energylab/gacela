<?php

class InitTest extends \PHPUnit_Framework_TestCase
{
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
}
