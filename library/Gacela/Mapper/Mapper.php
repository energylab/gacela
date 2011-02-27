<?php
/** 
 * @author noah
 * @date Oct 4, 2010
 * @brief
 * 
*/

namespace Gacela\Mapper;

abstract class Mapper implements iMapper {

	protected $_expressions = array('primaryKey' => "{className}Id", 'resource' => "{className}s");
	
	protected $_sources = array('db' => array());

	protected $_resources = array();

	protected $_primaryKey;

	protected function _load()
	{
		$primary = func_get_args();
	}

	protected function _loadResources()
	{
		foreach($this->_sources as $source => $resources) {
			if(!is_array($resources) OR empty($resources)) {
				$classes = explode('\\', get_class($this));

				$resource = str_replace("{className}", end($classes), $this->_expressions['resource']);
				$resource[0] = strtolower($resource[0]);

				$resources = array($resource);
			}

			$this->_sources[$source] = \Gacela::instance()->getDataSource($source);

			foreach($resources as $resource) {
				$this->_resources[$source][$resource] = $this->_sources[$source]->getResource($resource);
			}
		}

		return $this;
	}

	protected function _loadPrimaryKey()
	{
		if(is_null($this->_primaryKey)) {
			$primary = str_replace("{className}", end(explode('\\', get_class($this))), $this->_expressions['primaryKey']);
			$primary[0] = strtolower($primary[0]);

			$this->_primaryKey = $primary;
		}

		return $this;
	}

	public function __construct()
	{
		$this->init();
	}

	public function init()
	{
		$this->_loadPrimaryKey()
			->_loadResources();
	}

	public function find($id)
	{
		return $this->_load($id);
	}

	public function find_all(Gacela\Criteria $criteria)
	{
		
	}
}
