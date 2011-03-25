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

	protected $_loadWith = array();

	protected $_models = array();

	protected $_modelName;

	protected $_primaryKey = array();
	
	protected $_relations = array();

	/**
	 * @var Gacela\DataSource\Resource
	 */
	protected $_resource = null;

	protected $_source = 'db';

	/**
	 * @param \stdClass $data
	 * @return \Gacela\Model\Model
	 */
	protected function _load(\stdClass $data)
	{
		$primary = $this->_primaryKey($data);

		if(is_null($primary)) {
			return new $this->_modelName($data);
		}

		if(!isset($this->_models[$primary])) {
			$this->_models[$primary] = new $this->_modelName($data);
		}

		return $this->_models[$primary];
	}

	/**
	 * @return Mapper
	 */
	protected function _loadModelName()
	{
		$classes = explode('\\', get_class($this));

		$pos = array_search('Mapper', $classes);

		$classes[$pos] = 'Model';

		$this->_modelName = "\\".join("\\", $classes);

		return $this;
	}

	/**
	 * @return Mapper
	 */
	protected function _loadResource()
	{
		if(is_null($this->_resource)) {
			$classes = explode('\\', get_class($this));

			$resource = str_replace("{className}", end($classes), $this->_expressions['resource']);
			$resource[0] = strtolower($resource[0]);

			$this->_resource = $resource;
		}

		$this->_source = \Gacela::instance()->getDataSource($this->_source);

		$this->_resource = $this->_source->loadResource($resource);

		$this->_primaryKey = $this->_resource->getPrimaryKey();

		return $this;
	}

	/**
	 * @param  $data
	 * @return null|string
	 */
	protected function _primaryKey($data)
	{
		$primary = array();
		foreach($this->_primaryKey as $k) {
			if(is_null($data->$k)) {
				continue;
			}
			
			$primary[] = $data->$k;
		}
		
		if(!count($primary) || count($primary) != count($this->_primaryKey)) {
			$primary = null;
		} else {
			$primary = join("_", $primary);
		}
		
		return $primary;
	}

	public function __construct()
	{
		$this->init();
	}

	public function find($id)
	{
		$query = $this->_source->getQuery();

		$query->from($this->_resource->getName());
	}

	/**
	 * @param Gacela\Criteria|null $criteria
	 * @return \Gacela\Collection
	 */
	public function findAll(Gacela\Criteria $criteria = null)
	{
		$query = $this->_source->getQuery();
		
		$query->from($this->_resource->getName());

		$records = $this->_source->query($query);

		return new \Gacela\Collection($this, $records);
	}

	public function delete(\stdClass $data)
	{
		
	}

	/**
	 * @return array
	 */
	public function getFields()
	{
		return $this->_resource->getFields();
	}

	public function init()
	{
		$this->_loadResource()
			->_loadModelName();
	}

	public function load(\stdClass $data)
	{
		return $this->_load($data);
	}

	/**
	 * @param array $changed
	 * @param \stdClass $data
	 * @return bool
	 */
	public function save(array $changed, \stdClass $data)
	{
		$primary = $this->_primaryKey($data);
		$fields = $this->getFields();

		$toSave = array();
		foreach($changed as $field) {
			$toSave[$field] = $fields[$field]->transform($data->$field);
		}

		if(!isset($this->_models[$primary])) {
			$rs = $this->_source->insert($this->_resource->getName(), $toSave);

			if($rs === false) {
				return false;
			}

			if(count($this->_primaryKey) == 1) {
				if($fields[$this->_primaryKey[0]]->sequenced == true) {
					$data->{$this->_primaryKey[0]} = $rs;
				}
			}
		} else {
			// Add where statement to a Query\Database object and pass as third param.
			
			return $this->_source->update($this->_resource->getName(), $data);
		}

		return $data;
	}
}
