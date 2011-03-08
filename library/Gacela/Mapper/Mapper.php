<?php
/** 
 * @author noah
 * @date Oct 4, 2010
 * @brief
 * 
*/

namespace Gacela\Mapper;

abstract class Mapper implements iMapper {

	protected $_models = array();

	protected $_expressions = array('resource' => "{className}s");
	
	protected $_source = 'db';

	protected $_resources = array();

	protected $_relations = array();

	protected $_primaryKey = array();

	protected function _load(object $data)
	{
		
	}

	protected function _loadResources()
	{
		if(empty($resources)) {
			$classes = explode('\\', get_class($this));

			$resource = str_replace("{className}", end($classes), $this->_expressions['resource']);
			$resource[0] = strtolower($resource[0]);

			$resources = array($resource);
		}

		$this->_source = \Gacela::instance()->getDataSource($this->_source);

		foreach($resources as $resource) {
			$this->_resources[$resource] = $this->_source->getResource($resource);

			$this->_primaryKey = array_merge($this->_resources[$resource]->getPrimaryKey(), $this->_primaryKey);
		}

		return $this;
	}

	public function __construct()
	{
		$this->init();
	}

	public function find($id)
	{
		
	}

	public function findAll(Gacela\Criteria $criteria = null)
	{
		$query = $this->_source->getQuery();
		
		foreach($this->_resources as $resource) {
			$query->from($resource->getName());
		}

		$records = $this->_source->query($query);

		return new \Gacela\Collection($this, $records);
	}

	public function getPrimaryKey()
	{
		return $this->_primaryKey;
	}

	public function init()
	{
		$this->_loadResources();
	}

	public function load(object $array)
	{
		
	}


}

