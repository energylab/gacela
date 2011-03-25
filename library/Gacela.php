<?php
/** 
 * @author noah
 * @date 2/24/11
 * @brief
 * 
*/


use Gacela as G;

class Gacela {

	protected static $_instance;

	protected $_namespaces = array();

	protected $_sources = array();

	protected $_mappers = array();

	protected $_resources = array();

	protected function __construct()
	{
		spl_autoload_register(array(__CLASS__, 'autoload'));

		$this->registerNamespace('Gacela', dirname(realpath(__FILE__)));
	}

	protected function _findFile($file)
	{
		if(file_exists($file) && is_readable($file)) {
			return true;
		}

		return false;
	}

	public static function autoload($class)
	{
		$parts = explode("\\", $class);
		$self = self::instance();
		$return = false;

		if(isset($self->_namespaces[$parts[0]])) {
			$file = $self->_namespaces[$parts[0]].str_replace("\\", "/", $class).'.php';

			if($self->_findFile($file)) {
				$return = $class;
			}
		} else {

			$namespaces = array_reverse($self->_namespaces);

			foreach ($namespaces as $ns => $path) {
				$file = $path.$ns.str_replace("\\", "/", $class).'.php';

				if($self->_findFile($file)) {
					$return = "\\" . $ns . $class;
					break;
				}
			}
		}

		require $file;
		echo $return.'<br/>';
		return $return;
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
		
		$class = self::autoload("\\Gacela\\DataSource\\".ucfirst($type));
		exit($class);
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

	public function loadMapper($name)
	{
		if(!isset($this->_mappers[$name])) {
			$this->_mappers[$name] = new $name();
		}

		return $this->_mappers[$name];
	}
}
