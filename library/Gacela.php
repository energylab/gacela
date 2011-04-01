<?php
/** 
 * @author noah
 * @date 2/24/11
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

    protected function _findFile($file)
    {
        return file_exists($file) && is_readable($file);
    }

    public function autoload($class)
    {
        $parts = explode("\\", $class);
		$self = self::instance();
		
        if(isset($self->_namespaces[$parts[0]])) {
            $file = $self->_namespaces[$parts[0]].str_replace("\\", "/", $class).'.php';
            if($self->_findFile($file)) {
                require $file;
                return $class;
            }
        } else {
            $namespaces = array_reverse($self->_namespaces);
            foreach ($namespaces as $ns => $path) {
                $file = $path.$ns.str_replace("\\", "/", $class).'.php';
                
                if($self->_findFile($file)) {
                    require $file;
                    return $ns . $class;
                }
            }
        }
        
        return false;
    }

	public static function instance()
	{
		if(is_null(self::$_instance)) {
			self::$_instance = new Gacela();
		}

		return self::$_instance;
	}

	public function registerDataSource($name, $type, $config)
	{
		$config['name'] = $name;
		$config['type'] = $type;
		
		$class = self::instance()->autoload("\\DataSource\\".ucfirst($type));
		
		$this->_sources[$name] = new $class($config);
		
		return $this;
	}

	public function registerNamespace($ns, $path)
	{
		if(substr($path, -1, 1) != '/') {
			$path .= '/';
		}
		
		$this->_namespaces[$ns] = $path;

		return $this;
	}

	public function getDataSource($name)
	{
		if(!isset($this->_sources[$name])) {
			throw new Exception("Invalid Data Source {$name} Referenced");
		}
		
		return $this->_sources[$name];
	}

	public function setCachePath($path)
	{
		
	}

	public function loadMapper($name) {
		$name = ucfirst($name);

		if (!isset($this->_mappers[$name])) {
			$class = self::instance()->autoload("\\Mapper\\" . $name);

			if (!$class) {
				throw new \Exception("Failed to find mapper ({$name})!");
			}

			$this->_mappers[$name] = new $class();
		}

		return $this->_mappers[$name];
	}
}
