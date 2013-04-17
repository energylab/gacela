<?php
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

	public function providerInvalidCriteria()
	{
		$array = array(array(new \Gacela\Criteria, 'one'), array(new \Gacela\Criteria, 'equals'));

		$array[0][0]->in('Id', array('123', '234'))
				->like('Id', 'ABC');

		$array[1][0]->like('Id', 'ABC');

		return $array;
	}

	/**
	 * @dataProvider providerInvalidCriteria
	 * */
	public function testDeleteInvalidCriteria(\Gacela\Criteria $crit, $word)
	{
		try {
			$this->object->delete('Account', $crit);
		} catch(\Gacela\Exception $e) {
			$this->assertStringContains($word, $e->getMessage());
		}

		$this->fail('Failed to throw proper exception');
	}

	public function testDeleteWithEquals()
	{
		$c = new \Gacela\Criteria;

		$c->equals('Id', '1234567890ASDFGHJU');

		$rs = $this->object->delete('Account', $c);


	}


	public function testDeleteWithIn()
	{
	}

	public function testDeleteNonExistantObject()
	{
		
	}
}
