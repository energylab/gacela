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

	protected $_sources = array();

	protected $_mappers = array();

	protected $_resources = array();

	protected function __construct() {}

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

		$datasource = "\\Gacela\\DataSource\\".ucfirst($type);

		$this->_sources[$name] = new $datasource($config);
		
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
