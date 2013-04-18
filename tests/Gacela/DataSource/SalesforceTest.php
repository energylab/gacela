<?php
namespace Gacela\DataSource;
/**
 * @user: noah
 * @date 11/10/12
 */
class SalesforceTest extends \Test\GUnit\Extensions\Database\TestCase
{
	/**
	 * @var \Gacela\DataSource\Salesforce
	 */
	protected $object;

	protected function setUp()
	{
		parent::setUp();

		$this->object = \Gacela::instance()->getDataSource('sf');
	}

	public function providerInsertOne()
	{
		$data = array
		(
			'Name' => "Gacela Unit Test",
			'AccountNumber' => 9876542
		);

		return array
		(
			array($data),
			array((object) $data)
		);
	}

	/**
	 * @param $record
	 * @dataProvider providerInsertOne
	 */
	public function testInsertOne($record)
	{
		$id = $this->object->insert('Account', $record);

		$this->assertSame(18, strlen($id));
	}

	/**
	 * @throws \Gacela\Exception
	 */
	public function testInsertOneFailure()
	{
		try {
			$this->object->insert('Account', array('NumberOfEmployees' => 5, 'AccountNumber' => 1234567));
		} catch (\Gacela\Exception $e) {
			$this->assertEquals('Required fields are missing: [Name]', $e->getMessage());

			return;
		}

		$this->fail('Failed to assert that insert fails!');
	}

	public function testInsertMultiple()
	{
		$data = array
		(
			array
			(
				'Name' => 'Success 1',
				'AccountNumber' => 3216548
			),
			array
			(
				'Name' => 'Failure 1',
			),
			array
			(
				'Name' => 'Success 2',
				'AccountNumber' => 6549875
			),
			array
			(
				'AccountNumber' => 3214569
			)
		);

		$rs = $this->object->insert('Account', $data);

		$this->assertSame(18, strlen($rs[0]->id));
		$this->assertSame('Account Number must be 7 Digits', $rs[1]->errors[0]->message);

		return $rs;
	}

	/**
	 * @depends testInsertMultiple
	 */
	public function testDelete($rs)
	{
		$q = new Query\Soql();

		$ids = array($rs[0]->id, $rs[2]->id);

		$q->in('Id', $ids);

		$this->assertTrue($this->object->delete('Account', $q));
	}
}
