<?php
/** 
 * @author noah
 * @date Oct 4, 2010
 * @brief
 * 
*/

namespace Gacela\Mapper;

abstract class Mapper implements iMapper {

	protected $_expressions = array('resource' => "{className}s");

	protected $_models = array();

	protected $_modelName;

	protected $_primaryKey = array();
	
	protected $_relations = array();

	protected $_resources = array();

	protected $_source = 'db';

	protected function _load(\stdClass $data)
	{
		$primary = array();
		foreach($this->_primaryKey as $k) {
			$primary[] = $data->$k;
		}

		$primary = join("_", $primary);
		
		if(!isset($this->_models[$primary])) {
			$this->_models[$primary] = new $this->_modelName($data);
		}

		return $this->_models[$primary];
	}

	protected function _loadModelName()
	{
		$classes = explode('\\', get_class($this));

		$pos = array_search('Mapper', $classes);

		$classes[$pos] = 'Model';

		$this->_modelName = "\\".join("\\", $classes);

		return $this;
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
		$this->_loadResources()
			->_loadModelName();
	}

	public function load(\stdClass $array)
	{
		return $this->_load($array);
	}


}

