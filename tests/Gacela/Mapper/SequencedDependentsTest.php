<?php

class SequencedDependentsTest extends \Test\Testcase {

	
	public function testInitDependentWithSequencedPrimaryKey()
	{
		$mapper = \Gacela\Gacela::instance()->loadMapper('Object');
		
		$fields = $mapper->getFields();

		$this->assertTrue($fields['metaId']->sequenced);
	}

	public function testDependentWithSequencedKeyValidates()
	{
		$object = new \Test\Model\Object('Object');

		$object->setData(
			array(
				'name' => 'Test',
				'metadata' => 'Some very long string of text'
			)
		);

		$this->assertTrue($object->validate(), print_r($object->errors, true));
	}

	public function testDependentWithSequencedKeyUpdatesPrimaryObjectForeignKey()
	{
		$object = new \Test\Model\Object('Object');

		$object->setData(
			array(
				'name' => 'Test2',
				'metadata' => 'I am some metadata'
			)
		);

		$this->assertTrue($object->save());

		$this->assertSame(1, $object->metaId);
		$this->assertSame('I am some metadata', $object->metadata);
	}	
}
