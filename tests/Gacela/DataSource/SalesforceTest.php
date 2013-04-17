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

	public function testDelete()
	{
		$q = new Query\Soql();

		$q->where('Id = 001E000000NmEprIAF');

		$rs = $this->object->delete('Account', $q);


	}

	public function testDeleteNonExistantObject()
	{

	}
}
