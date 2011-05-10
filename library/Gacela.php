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

	protected $_namespaces = array();

	protected $_sources = array();

	protected $_mappers = array();

	protected $_resources = array();

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
	 * @param  string $path
	 * @return Gacela
	 */
	public function setCachePath($path)
	{
		
	}

	/**
	 * @throws Exception
	 * @param  string $name Relative name of the Mapper to load. For example, if the absolute name of the mapper was \App\Mapper\User, you would pass 'user' in as the argument
	 * @return Gacela\Mapper\Mapper
	 */
	public function loadMapper($name)
	{
		$name = ucfirst($name);

		if (!isset($this->_mappers[$name])) {
			$class = "\\Mapper\\" . $name;
			$class = self::instance()->autoload($class);

			if (!$class) {
				throw new \Exception("Failed to find mapper ({$name})!");
			}

			$this->_mappers[$name] = new $class;
		}

		return $this->_mappers[$name];
	}
}
