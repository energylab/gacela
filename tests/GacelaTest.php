<?php

class GacelaTest extends Test\GUnit\TestCase
{
    /**
     * @var Gacela
     */
    protected $object;

	/**
	 * @var \Memcache
	 */
	protected $memcache = null;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
		$gacela = Gacela::instance();

		$source = Gacela::createDataSource(
			array(
				'type' => 'mysql',
				'name' => 'db',
				'user' => 'gacela',
				'password' => 'gacela',
				'schema' => 'gacela',
				'host' => 'localhost'
			)
		);

		$test = Gacela::createDataSource(
			array(
				'type' => 'mysql',
				'name' => 'test',
				'user' => 'gacela',
				'password' => 'gacela',
				'schema' => 'test',
				'host' => 'localhost'
			)
		);

		$gacela->registerDataSource($source);
		$gacela->registerDataSource($test);

		$gacela->registerNamespace('App', __DIR__.'/../samples/');
		$gacela->registerNamespace('Test', __DIR__.'/Test');

		$this->object = $gacela;

		$this->memcache = new Memcache;

		$this->memcache->addServer('127.0.0.1', 11211);

		if(is_object($this->memcache)) {
			$this->memcache->flush();
		}
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
		Gacela::reset();

		if(is_object($this->memcache)) {
			$this->memcache->flush();
		}
    }

	public function providerAutoload()
	{
		return array(
			array('Criteria', 'Gacela\Criteria'),
			array('Inflector', 'Gacela\Inflector'),
			array('Mapper\Peep', 'Test\Mapper\Peep'),
			array('Model\Peep', 'Test\Model\Peep'),
			array('Test\Mapper\Test', 'Test\Mapper\Test'),
			array('Test\Model\Test', 'Test\Model\Test'),
			array('Gacela\DataSource\DataSource', 'Gacela\DataSource\DataSource'),
			array('Field\Bool', 'Gacela\Field\Bool'),
			array('Field\Field', 'Test\Field\Field')
		);
	}

	public function providerCollections()
	{
		return array(
			array($this->getMock('\PDOStatement'), 'Gacela\Collection\Statement'),
			array(array(), 'Gacela\Collection\Arr')
		);
	}

	public function providerGetField()
	{
		return array(
			array('Binary'),
			array('Bool'),
			array('Date'),
			array('Decimal'),
			array('Enum'),
			array('Float'),
			array('Int'),
			array('Set'),
			array('String'),
			array('Time')
		);
	}

	public function providerMapper()
	{
		return array(
			array('Peep', 'Test\Mapper\Peep'),
			array('House', 'App\Mapper\House'),
			array('Mapper\Wizard', 'App\Mapper\Wizard'),
			array('App\Mapper\Teacher', 'App\Mapper\Teacher')
		);
	}

	public function providerSources()
	{
		return array(
			array(array('name' => 'mysql', 'type' => 'mysql'), "Gacela\\DataSource\\Database"),
			//	array('mssql', 'mssql', array(), "Gacela\\DataSource\\Database"),
			array(array('name' => 'salesforce', 'type' => 'salesforce'), "Gacela\\DataSource\\Salesforce")
		);
	}

    /**
     * @covers Gacela::instance
     */
    public function testInstance()
    {
        $this->assertInstanceOf('Gacela', Gacela::instance());
    }

    /**
     * @covers Gacela::autoload
	 * @dataProvider providerAutoload
     */
    public function testAutoload($class, $qualified)
    {
		$this->assertSame($qualified, $this->object->autoload($class));
    }

	/**
	 * @covers Gacela::enableCache
	 */
	public function testEnableCache()
	{
		$this->object->enableCache($this->memcache);

		$this->assertAttributeInstanceOf('\Memcache','_cache', $this->object);
	}

    /**
     * @covers Gacela::cacheMetaData
     */
    public function testCacheMetaDataWithoutMemcache()
    {
		$array = array(
			array('var1' => 1, 'var2' => 2, 'var3' => 3),
			array('var1' => 999, 'var2' => 'something else', 'var3' => 'more')
		);

        $this->object->cacheMetaData('test', $array);

		$this->assertSame($array, $this->object->cacheMetaData('test'));
    }

	/**
	 * @covers Gacela::cacheMetaData
	 */
	public function testCacheMetaDataWithMemcache()
	{
		$array = array(
			array('var1' => 1, 'var2' => 2, 'var3' => 3),
			array('var1' => 999, 'var2' => 'something else', 'var3' => 'more')
		);

		$this->assertFalse($this->memcache->get('test'));

		$this->object->enableCache($this->memcache);

		$this->object->cacheMetaData('test', $array);

		$this->assertSame($array, $this->memcache->get('test'));
	}

	public function testCacheDataWithoutMemcache()
	{
		$this->assertFalse($this->object->cacheData('test', (object) array('some test data')));
	}

	public function testCacheDataWithMemcache()
	{
		$array = array(
			array('var1' => 1, 'var2' => 2, 'var3' => 3),
			array('var1' => 999, 'var2' => 'something else', 'var3' => 'more')
		);

		$this->assertFalse($this->memcache->get('test'));

		$this->object->enableCache($this->memcache);

		$this->object->cacheData('test', $array);

		$this->assertSame($array, $this->memcache->get('test'));
	}

	public function testNonCacheableValueWithoutCache()
	{
		$test = $this->object->getDataSource('test');

		$test->loadResource('tests');

		$this->assertFalse($this->object->cacheData('test', $test));
	}

    /**
     * @covers Gacela::configPath
     * @todo   Implement testConfigPath().
     */
    public function testConfigPath()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

	/**
	 * @covers Gacela::registerDataSource
	 * @covers Gacela::getDataSource
	 * @dataProvider providerSources
	 */
	public function testRegisterDataSource($config, $class)
	{
		$source = Gacela::createDataSource($config);

		$this->object->registerDataSource($source);

		$this->assertInstanceOf($class, $this->object->getDataSource($config['name']));
	}

	/**
	 * @expectedException \Gacela\Exception
	 */
	public function testGetDataSourceThrowsException()
	{
		$this->object->getDataSource('not_here');
	}

	/**
	 * @param $type
	 * @dataProvider providerGetField
	 */
	public function testGetField($type)
	{
		$this->assertInstanceOf("\\Gacela\\Field\\".$type, $this->object->getField($type));
	}

	public function testIncrementCache()
	{
		$this->object->enableCache($this->memcache);

		$this->object->cacheData('test', 1);

		$this->object->incrementDataCache('test');

		$this->assertSame(2, $this->object->cacheData('test'));
	}

    /**
     * @covers Gacela::loadConfig
     * @todo   Implement testLoadConfig().
     */
    public function testLoadConfig()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Gacela::loadMapper
     * @dataProvider providerMapper
     */
    public function testLoadMapperWithoutMemcache($name, $expected)
    {
		$this->assertInstanceOf($expected, $this->object->loadMapper($name));
    }

	/**
	 * @param $name
	 * @param $expected
	 * @dataProvider providerMapper
	 */
	public function testLoadMapperWithMemcache($name, $expected)
	{
		$expected = str_replace('\\', '_', $expected);

		$this->assertFalse($this->memcache->get($expected));

		$this->object->enableCache($this->memcache);

		$mapper = $this->object->loadMapper($name);

		$this->assertEquals($mapper, $this->memcache->get($expected));
	}

    /**
     * @covers Gacela::makeCollection
     * @dataProvider providerCollections
     */
    public function testMakeCollection($data, $expected)
    {
		$mapper = new Test\Mapper\Peep($this->object);

		$this->assertInstanceOf($expected, $this->object->makeCollection($mapper, $data));
    }

	/**
	 * @covers Gacela::makeCollection
	 * @expectedException Gacela\Exception
	 */
	public function testMakeCollectionThrowsException()
	{
		$mapper = new Test\Mapper\Peep($this->object);

		$this->object->makeCollection($mapper, new \ArrayObject());
	}

    /**
     * @covers Gacela::registerNamespace
     */
    public function testRegisterNamespace()
    {
		$expected = array('Gacela' => '/var/www/gacela/library/Gacela/', 'App' => __DIR__.'/../samples/', 'Test' => __DIR__.'/Test/');

		$this->assertAttributeSame($expected, '_namespaces', $this->object);
    }
}
