<?php
/**
 * @user: noah
 * @date 11/10/12
 */
class CacheTest extends \DbTestCase
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

			)
		);
	}

	public function testFindWithoutPrimedCache()
	{

	}

	public function testFindAllWithoutCriteria()
	{

	}
}
