<?php

class SequencedDependentsTest extends \Test\GUnit\Extensions\Database\Testcase {

	
	public function testInitDependentWithSequencedPrimaryKey()
	{
		$mapper = \Gacela::instance()->loadMapper('Object');
		
		$fields = $mapper->getFields();

		$this->assertTrue($fields['metaId']->sequenced);
	}

	public function testDependentWithSequencedKeyValidates()
	{
		$object = new \Test\Model\Object(
			\Gacela::instance(),
			\Gacela::instance()->loadMapper('Object')
		);

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
		$object = new \Test\Model\Object(
			\Gacela::instance(),
			\Gacela::instance()->loadMapper('Object')
		);

		$object->setData(
			array(
				'name' => 'Test2',
				'metadata' => '1234'
			)
		);

		$this->assertTrue($object->save());

		$this->assertSame(1, $object->metaId);
		$this->assertSame('1234', $object->metadata);
	}	
}
