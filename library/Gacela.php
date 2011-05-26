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

	protected $_memcache = null;

	protected $_memcacheEnabled = false;

	protected $_namespaces = array();

	protected $_sources = array();

	protected $_cache = array();

    protected function __construct()
    {
        spl_autoload_register(array($this, 'autoload'));

        $this->registerNamespace('Gacela', __DIR__);
    }

	/**
	 * @param  $file
	 * @return bool
	 */
    protected function _findFile($file)
    {
        return file_exists($file) && is_readable($file);
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
				$file = $self->_namespaces[$parts[0]].str_replace("\\", "/", $class).'.php';
				if($self->_findFile($file)) {
					require $file;
					return $class;
				}
			}
        } else {
            $namespaces = array_reverse($self->_namespaces);
            foreach ($namespaces as $ns => $path) {
                $file = $path.$ns.str_replace("\\", "/", $class).'.php';

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
		if(!$this->_memcacheEnabled) {
			if(is_null($object)) {
				if(isset($this->_cache[$key])) {
					return $this->_cache[$key];
				}

				return false;
			} else {
				$this->_cache[$key] = $object;

				return true;
			}
		} else {
			if(is_null($object)) {
				return $this->_memcache->get($key);
			} else {
				if($replace) {
					return $this->_memcache->replace($key, $object);
				} else {
					return $this->_memcache->set($key, $object);
				}
			}
		}
	}

	/**
	 * @param  Memcache|array $servers
	 * @return Gacela
	 */
	public function enableMemcache($servers)
	{
		if($servers instanceof Memcache) {
			$this->_memcache = $servers;
		} elseif(is_array($servers)) {
			$this->_memcache = new Memcache;

			foreach($servers as $server) {
				$this->_memcache->addServer($server[0], $server[1]);
			}
		}

		$this->_memcacheEnabled = true;

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
	 * @throws Exception
	 * @param  string $name Relative name of the Mapper to load. For example, if the absolute name of the mapper was \App\Mapper\User, you would pass 'user' in as the argument
	 * @return Gacela\Mapper\Mapper
	 */
	public function loadMapper($name)
	{
		$name = ucfirst($name);

		$cached = $this->cache('mapper_'.$name);

		if ($cached === false) {
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

	public function memcacheEnabled()
	{
		return $this->_memcacheEnabled;
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
