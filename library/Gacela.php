<?php
/**
 * @author Noah Goodrich
 * @date May 7, 2011
 * @package Gacela
 * @brief
 *
*/

class Gacela {

	protected static $_instance;

	protected $_cache = null;

	protected $_cached = array();

	protected $_cacheData = false;

	protected $_cacheSchema = false;

	protected $_config = null;

	protected $_namespaces = array();

	protected $_sources = array();

    protected function __construct()
    {
        spl_autoload_register(array($this, 'autoload'));

        $this->registerNamespace('Gacela', __DIR__.'/Gacela');
    }

	/**
	 * @param  $file
	 * @return bool
	 */
    protected function _findFile($file)
    {
        return file_exists($file) && is_readable($file);
    }

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
				$args[$key] = self::instance()->getDataSource('db')->quote($val);
			}

			$query = strtr($sql, $args);
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
		if(is_null(self::$_instance)) {
			self::$_instance = new Gacela();
		}

		return self::$_instance;
	}

	/**
	 * @param  string $class
	 * @return bool|string
	 */
    public function autoload($class)
    {
        $parts = explode("\\", $class);
		$self = self::instance();

        if(isset($self->_namespaces[$parts[0]])) {
        	if(class_exists($class)) {
				return $class;
			} else {
				$path = $parts;
				unset($path[0]);

				$path = join(DIRECTORY_SEPARATOR, $path);

				$file = $self->_namespaces[$parts[0]].$path.'.php';
				if($self->_findFile($file)) {
					require $file;
					return $class;
				}
			}
        } else {
            $namespaces = array_reverse($self->_namespaces);
            foreach ($namespaces as $ns => $path) {
            	if(substr($class, 0, 1) == '\\') {
            		$tmp = substr($class, 1);
            	} else {
					$tmp = $class;
            	}

                $file = $path.str_replace("\\", DIRECTORY_SEPARATOR, $tmp).'.php';

                if($self->_findFile($file)) {
                	$class = $ns.$class;

                	if(class_exists($class)) {
                		return $class;
                	} else {
                		require $file;
                    	return $class;
                	}
                }
            }
        }

        return false;
    }

	/**
	 * @param  $key
	 * @param null $object
	 * @return object|bool
	 */
	public function cache($key, $object = null, $replace = false)
	{
		if(!$this->_cacheData AND ($this->_cacheSchema === false OR (stristr($key, 'resource_') === false AND stristr($key, 'mapper_') === false))) {
			if(is_null($object)) {
				if(isset($this->_cached[$key])) {
					return $this->_cached[$key];
				}

				return false;
			} else {
				$this->_cached[$key] = $object;

				return true;
			}
		} else {
			if(is_null($object)) {
				return $this->_cache->get($key);
			} else {
				if($replace) {
					return $this->_cache->replace($key, $object);
				} else {
					return $this->_cache->set($key, $object);
				}
			}
		}
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
	public function enableCache($servers, $schema = true, $data = true)
	{
		if($servers instanceof Memcache) {
			$this->_cache = $servers;
		} elseif(is_array($servers)) {
			$this->_cache = new Memcache;

			foreach($servers as $server) {
				$this->_cache->addServer($server[0], $server[1]);
			}
		}

		$this->_cacheSchema = $schema;
		$this->_cacheData = $data;

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
			throw new Exception("Invalid Data Source {$name} Referenced");
		}

		return $this->_sources[$name];
	}

	public function incrementCache($key)
	{
		if(!$this->_cacheData) {
			$this->_cached[$key]++;
		} else {
			$this->_cache->increment($key);
		}
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
			throw new Exception('Config array is empty for resource ('.$name.')! ');
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

		$cached = $this->cache('mapper_'.$name);

		if ($cached === false || is_null($cached)) {
			$class = "\\Mapper\\" . $name;
			$class = self::instance()->autoload($class);

			if (!$class) {
				throw new \Exception("Failed to find mapper ({$name})!");
			}

			$cached = new $class;

			$this->cache('mapper_'.$name, $cached);
		}

		return $cached;
	}

	/**
	 * @param  string $name Name by which the DataSource can later be referenced in Mappers and when directly accessing the registered DataSource.
	 * @param  string $type Type of DataSource (database, *service, *xml ) *coming soon
	 * @param  array $config Configuration arguments required by the DataSource
	 * @return Gacela
	 */
	public function registerDataSource($name, $type, $config)
	{
		$config['name'] = $name;
		$config['type'] = $type;

		$class = self::instance()->autoload("\\DataSource\\".ucfirst($type));

		if(!$class) {
			throw new \Exception('Failed to load DataSource ('.$name.')');
		}

		$this->_sources[$name] = new $class($config);

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
