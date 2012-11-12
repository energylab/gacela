<?php
/**
 * @user: noah
 * @date 11/10/12
 */
class CacheTest extends \Test\GUnit\Extensions\Database\TestCase
{

	/**
	 * @var Test\Mapper\?
	 */
	protected $object;

	/**
	 * @var \Memcache
	 */
	protected $memcache;

	public function setUp()
	{
		parent::setUp();

		$this->object = Gacela::instance()->loadMapper('');

		$this->memcache = new Memcache;

		$this->memcache->addServer('127.0.0.1', 11211);
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
					array('id' => 2, 'first' => 'June', 'last' => 'Tester', 'phone' => 1234567890),
					array('id' => 4, 'first' => 'January', 'last' => 'Year', 'phone' => 9876543210),
					array('id' => 6, 'first' => 'July', 'last' => 'IsHot', 'phone' => 6549873215),
					array('id' => 8, 'first' => 'August', 'last' => 'IsHotter', 'phone' => 1236549870),
					array('id' => 10, 'first' => 'March', 'last' => 'MyBirthday', 'phone' => 3271983000),
					array('id' => 12, 'first' => 'December', 'last' => 'Christmas', 'phone' => 1225190098)
				)
			)
		);
	}

	public function testFindWithoutPrimedCache()
	{

	}

	public function testFindAllWithoutCriteria()
	{
		$key = 'test_customer_0_'.hash(MHASH_WHIRLPOOL, serialize(null));

		$this->assertFalse($this->memcache->get($key));
	}
}
