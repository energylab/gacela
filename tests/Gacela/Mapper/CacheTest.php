<?php
/**
 * @user: noah
 * @date 11/10/12
 */
class CacheTest extends \Test\GUnit\Extensions\Database\TestCase
{

	/**
	 * @var Test\Mapper\Customer
	 */
	protected $object;

	/**
	 * @var \Memcache
	 */
	protected $memcache;

	public function setUp()
	{
		parent::setUp();

		$this->object = Gacela::instance()->loadMapper('Customer');

		$this->memcache = new Memcache;

		$this->memcache->addServer('127.0.0.1', 11211);

		$this->memcache->flush();

		Gacela::instance()->enableCache($this->memcache);
	}

	public function tearDown()
	{
		parent::tearDown();

		//$this->memcache->flush();
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
					array('id' => 4, 'first' => 'January', 'last' => 'Year', 'phone' => '9876543210'),
					array('id' => 6, 'first' => 'July', 'last' => 'IsHot', 'phone' => '6549873215'),
					array('id' => 8, 'first' => 'August', 'last' => 'IsHotter', 'phone' => '1236549870'),
					array('id' => 10, 'first' => 'March', 'last' => 'MyBirthday', 'phone' => '3271983000'),
					array('id' => 12, 'first' => 'December', 'last' => 'Christmas', 'phone' => '1225190098')
				)
			)
		);
	}

	public function testFindWithoutPrimedCache()
	{
		$key = 'test_test_mapper_customer_0_'.hash('whirlpool', serialize(null));

		$this->assertFalse($this->memcache->get($key));

		$m = $this->object->find(2);

		$this->assertSame('june@test.com', $m->email);
		$this->assertSame('Tester', $m->last);

		$cached = $this->memcache->get($key);

		$cached = current($cached);

		$m2 = new \Test\Model\Customer(\Gacela::instance(), $this->object, $cached);

		$this->assertEquals($m, $m2);
	}

	public function testFindUsingCache()
	{
		$this->object->find(4);

		$this->object->find(12);

		$this->object->find(4);

		$debug = $this->object->debug(true);

		$id = current($debug['lastDataSourceQuery']['args']);

		$this->assertSame(12, $id);
	}

	public function testFindAllWithoutCriteria()
	{
		$key = 'test_test_mapper_customer_0_'.hash('whirlpool', serialize(null));

		$this->assertFalse($this->memcache->get($key));

		$rs = $this->object->findAll();

		$this->assertSame(6, $rs->count());

		$this->assertAttributeEquals($this->memcache->get($key), '_data', $rs);
	}

	public function testIncrementCacheWithSave()
	{
		$key = 'test_test_mapper_customer_version';

		$this->assertFalse($this->memcache->get($key));

		$this->object->findAll();

		$this->assertSame(0, $this->memcache->get($key));

		$new = array(
			'email' => 'independence@test.com',
			'first' => 'Independence',
			'last' => 'American',
			'phone' => 1234567890
		);

		$changed = array_keys($new);

		$old = array();

		foreach($new as $k => $v) {
			$old[$k] = null;
		}

		$rs = $this->object->save($changed, (object) $new, $old);

		$this->assertNotSame(false, $rs);

		$this->assertSame(1, $this->memcache->get($key));
	}

	public function testIncrementCacheWithDelete()
	{
		$key = 'test_test_mapper_customer_version';

		$this->assertFalse($this->memcache->get($key));

		$this->object->findAll();

		$this->assertSame(0, $this->memcache->get($key));

		$this->object->delete((object) array('id' => 2));

		$this->assertSame(1, $this->memcache->get($key));
	}

	public function testFindRelationOfCachedObject()
	{
		$courses = Gacela::instance()->loadMapper('Course')->findAll();

		$c2 = Gacela::instance()->loadMapper('Course')->findAll();

		foreach($c2 as $course) {
			$this->assertInstanceOf('App\Model\Teacher', $course->teacher);
		}


	}
}
