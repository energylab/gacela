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

	protected $_primaryKey;

	protected function _load()
	{
		$primary = func_get_args();
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
		}

		return $this;
	}

	public function __construct()
	{
		$this->init();
	}

	public function init()
	{
		$this->_loadResources();
	}

	public function find($id)
	{
		return $this->_load($id);
	}

	public function findAll(Gacela\Criteria $criteria = null)
	{
		$query = $this->_source->getQuery();
		
		foreach($this->_resources as $resource) {
			$query->from($resource->getName());
		}

		$records = $this->_source->query($query);

		exit(\Util::debug($records));
	}
}
