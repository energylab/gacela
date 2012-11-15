<?php
/**
 * @author Noah Goodrich
 * @date May 7, 2011
 * @package Gacela
 *
 *
*/

class Gacela
{
	protected static $_instance;

	/**
	 * @var \Memcache
	 */
	protected $_cache = null;

	protected $_cached = array();

	protected $_config = null;

	protected $_fields = array();

	protected $_namespaces = array();

	protected $_sources = array();

    protected function __construct()
    {
        spl_autoload_register(array($this, 'autoload'));

        $this->registerNamespace('Gacela', __DIR__.'/Gacela');
    }

	/**
	 * @param  $key
	 * @param null $object
	 * @return object|bool
	 */
	protected function _cache($key, $object = null)
	{
		if(!is_object($this->_cache)) {
			if($object === null || $object === false) {
				if(isset($this->_cached[$key])) {
					return $this->_cached[$key];
				}

				return false;
			} else {
				$this->_cached[$key] = $object;

				return true;
			}
		} else {
			if($object === null || $object === false) {
				return $this->_cache->get($key);
			} else {
				return $this->_cache->set($key, $object);
			}
		}
	}

	/**
	 * @param  $file
	 * @return bool
	 */
    protected function _findFile($file)
    {
        return file_exists($file) && is_readable($file);
    }

	public static function createDataSource(array $config)
	{
		if(in_array($config['type'], array('mysql', 'mssql', 'postgres', 'oracle'))) {
			$type = 'database';
		} else {
			$type = $config['type'];
		}

		$class = static::instance()->autoload("DataSource\\".ucfirst($type));

		$adapter = static::instance()->autoload("DataSource\\Adapter\\".ucfirst($config['type']));

		return new $class(static::instance(), new $adapter(static::instance(), (object) $config), $config);
	}

	/**
	 * @static
	 * @param \Gacela\DataSource\Query\Query $query
	 * @param bool $return
	 * @return array|string
	 */
	public static function debug($query, $return = false)
	{
		if($query instanceof \Gacela\DataSource\Query\Query)
		{
			list($sql, $args) = $query->assemble();
		}
		else
		{
			if(isset($query['lastDataSourceQuery']))
			{
				$sql = $query['lastDataSourceQuery']['query'];
				$args = $query['lastDataSourceQuery']['args'];
			}
			elseif(isset($query['query']))
			{
				$sql = $query['query'];
				$args = $query['args'];
			}
			elseif(is_numeric(key($query)))
			{
				$sql = $query[0];
				$args = $query[1];
			}
		}

		if(isset($args))
		{
			foreach($args as $key => $val)
			{
				$args[$key] = static::instance()->getDataSource('db')->quote($val);
			}

			$query = strtr($sql, $args);
		}
		else
		{
			$query = $sql;
		}

		if($return) {
			return $query;
		} else {
			print('<pre>'.print_r($query, true).'</pre>');
		}
	}

	/**
	 * @static
	 * @return Gacela
	 */
	public static function instance()
	{
		if(is_null(static::$_instance)) {
			static::$_instance = new Gacela();
		}

		return static::$_instance;
	}

	public static function reset()
	{
		self::$_instance = null;
	}

	/**
	 * @param  string $class
	 * @return bool|string
	 */
    public function autoload($class)
    {
		// Contains the path to the class
        $parts = explode("\\", $class);

		// The class name has to be parsed differently from the namespace path
		$name = array_pop($parts);

		$self = self::instance();

        if(count($parts) && isset($self->_namespaces[$parts[0]])) {
        	if(class_exists($class, false)) {
				return $class;
			} else {
				$path = $parts;
				unset($path[0]);

				// According to PSR-0 - The underscore should be part of the directory structure for class names
				$file = $self->_namespaces[$parts[0]].join(DIRECTORY_SEPARATOR, $path).DIRECTORY_SEPARATOR.str_replace('_', DIRECTORY_SEPARATOR, $name).'.php';

				if($self->_findFile($file)) {
					require $file;
					return $class;
				}
			}
        } else {
            $namespaces = array_reverse($self->_namespaces);

            foreach ($namespaces as $ns => $path) {

				// According to PSR-0 - The underscore should be part of the directory structure for class names
                $file = $path.join(DIRECTORY_SEPARATOR, $parts).DIRECTORY_SEPARATOR.str_replace('_', DIRECTORY_SEPARATOR, $name).'.php';

				$_class = $ns.'\\'.$class;

				if(class_exists($_class, false)) {
					return $_class;
				} elseif($self->_findFile($file)) {
					require $file;
					return $_class;
                }
            }
        }

		return false;
    }

	public function cacheMetaData($key, $value = null)
	{
		return $this->_cache($key, $value);
	}

	public function cacheData($key, $value = null)
	{
		if(!is_null($value) && is_null(($this->_cache))) {
			return false;
		}

		return $this->_cache($key, $value);
	}

	/**
	 * @throws Exception
	 * @param null $path
	 * @return string|null
	 */
	public function configPath($path = null)
	{
		if(!is_null($path)) {
			if(!is_readable($path) || !is_dir($path)) {
				throw new Exception('Config path ('.$path.') must be a readable directory!');
			}

			if(substr($path, -1, 1) != '/') {
				$path .= '/';
			}

			$this->_config = $path;
		}

		return $this->_config;
	}

	/**
	 * @param  Memcache|array $servers
	 * @return Gacela
	 */
	public function enableCache($servers)
	{
		if($servers instanceof Memcache) {
			$this->_cache = $servers;
		} elseif(is_array($servers)) {
			$this->_cache = new Memcache;

			foreach($servers as $server) {
				$this->_cache->addServer($server[0], $server[1]);
			}
		}

		return $this;
	}

	/**
	 * @throws Exception
	 * @param  $name
	 * @return Gacela\DataSource\DataSource
	 */
	public function getDataSource($name)
	{
		if(!isset($this->_sources[$name])) {
			throw new Gacela\Exception("Invalid Data Source {$name} Referenced");
		}

		return $this->_sources[$name];
	}

	/**
	 * @param $meta
	 * @return Gacela\Field\Field
	 */
	public function getField($type)
	{
		if(!isset($this->_fields[$type])) {
			$class = $this->autoload("Field\\".ucfirst($type));

			$this->_fields[$type] = new $class;
		}

		return $this->_fields[$type];
	}

	/**
	 * @param $key
	 * @return Gacela
	 */
	public function incrementDataCache($key)
	{
		if(is_object($this->_cache)) {
			if(!$this->_cache->increment($key)) {

			}
		}

		return $this;
	}

	public function loadConfig($name)
	{
		if(is_null($this->_config))
		{
			return null;
		}

		$path = $this->_config.$name.'.php';

		if(!file_exists($path)) {
			return null;
		}

		$array = include $path;

		if(empty($array)) {
			throw new \Exception('Config array is empty for resource ('.$name.')! ');
		}

		return $array;
	}

	/**
	 * @throws Exception
	 * @param  string $name Relative name of the Mapper to load. For example, if the absolute name of the mapper was \App\Mapper\User, you would pass 'user' in as the argument
	 * @return Gacela\Mapper\Mapper
	 */
	public function loadMapper($name)
	{
		$name = ucfirst($name);

		if(stripos($name, 'Mapper') === false) {
			$name = "Mapper\\" . $name;
		}

		if(($class = $this->autoload($name)) === false) {
			throw new \Gacela\Exception("Could not find Mapper ($name)!");
		}

		$cached = $this->cacheMetaData($class);

		if (!$cached) {
			$cached = new $class();

			$this->cacheMetaData(str_replace('\\', '_', $class), $cached);
		}

		return $cached;
	}

	/**
	 * Collection factory method
	 *
	 * @param \Gacela\Mapper\Mapper $mapper
	 * @param array $data
	 * @return \Gacela\Collection\Collection
	 * @throws Exception
	 */
	public function makeCollection($mapper, $data)
	{
		if($data instanceof \PDOStatement) {
			$col = Gacela::instance()->autoload("Collection\\Statement");
		} elseif (is_array($data)) {
			$col = Gacela::instance()->autoload("Collection\\Arr");
		} else {
			throw new Gacela\Exception('Collection type is not defined!');
		}

		return new $col($mapper, $data);
	}

	/**
	 * @param  string $name Name by which the DataSource can later be referenced in Mappers and when directly accessing the registered DataSource.
	 * @param  string $type Type of DataSource (database, *service, *xml ) *coming soon
	 * @param  array $config Configuration arguments required by the DataSource
	 * @return Gacela
	 */
	public function registerDataSource(Gacela\DataSource\iDataSource $source)
	{
		$this->_sources[$source->getName()] = $source;

		return $this;
	}

	/**
	 * @param  string $ns
	 * @param  string $path
	 * @return Gacela
	 */
	public function registerNamespace($ns, $path)
	{
		if(substr($path, -1, 1) != '/') {
			$path .= '/';
		}

		$this->_namespaces[$ns] = $path;

		return $this;
	}
}
