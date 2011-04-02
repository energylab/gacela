<?php
/** 
 * @author noah
 * @date Oct 4, 2010
 * @brief
 * 
*/

namespace Gacela\Mapper;

abstract class Mapper implements iMapper {

	protected $_associations = array();

	protected $_dependents = array();

	protected $_expressions = array('resource' => "{className}s");

	protected $_inherits = array();
	
	protected $_models = array();

	protected $_modelName = null;

	protected $_primaryKey = array();
	
	protected $_foreignKeys = array();

	/**
	 * @var Gacela\DataSource\Resource
	 */
	protected $_resource = null;

	protected $_source = 'db';

	/**
	 * @return Mapper
	 */
	private function _init()
	{
		// Everything loads in order based on what resources are needed first.
		$this->_initResource()
			->_initPrimaryKey()
			->_initForeignKeys()
			->_initInherits()
			->_initDependents()
			->_initAssociations()
			->_initModelName();
			
		return $this;
	}

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

	protected function _initAssociations()
	{
		return $this;
	}

	protected function _initDependents()
	{
		return $this;
	}

	protected function _initForeignKeys()
	{
		if(empty($this->_foreignKeys)) {
			$relations = $this->_resource->getRelations();

			foreach($relations['belongsTo'] as $relation => $meta) {
				$resource = $this->_source->loadResource($meta->refTable);

				$meta->type = 'belongsTo';
				$this->_foreignKeys[$relation] = array('meta' => $meta, 'resource' => $resource);
			}

			foreach($relations['hasMany'] as $relation => $meta) {
				$resource = $this->_source->loadResource($meta->refTable);

				$meta->type = 'hasMany';
				$this->_foreignKeys[$relation] = array('meta' => $meta, 'resource' => $resource);
			}
		} else {
			foreach($this->_foreignKeys as $relation => $meta) {
				$meta = (object) $meta;

				$this->_foreignKeys[$relation]['resource'] = $this->_source->loadResource($meta->refTable);
			}
		}

		return $this;
	}

	protected function _initInherits()
	{
		if(empty($this->_inherits)) {
			foreach($this->_foreignKeys as $name => $stuff) {
				if($stuff['meta']->type == 'hasMany') {
					continue;
				}

				$refPrimary = $stuff['resource']->getPrimaryKey();

				if($refPrimary[0] == $stuff['meta']->refColumn && count($this->_primaryKey) == 1 && $this->_primaryKey[0] == $stuff['meta']->keyColumn) {
					$this->_inherits[$stuff['resource']->getName()] = $stuff;

					unset($this->_foreignKeys[$name]);
				}
			}
		}

		return $this;
	}

	/**
	 * @return Mapper
	 */
	protected function _initModelName()
	{
		$classes = explode('\\', get_class($this));

		$pos = array_search('Mapper', $classes);

		$classes[$pos] = 'Model';

		$this->_modelName = "\\".join("\\", $classes);

		return $this;
	}

	protected function _initPrimaryKey()
	{
		if(empty($this->_primaryKey)) {
			$this->_primaryKey = $this->_resource->getPrimaryKey();
		}

		return $this;
	}

	/**
	 * @return Mapper
	 */
	protected function _initResource()
	{
		if(is_null($this->_resource)) {
			$classes = explode('\\', get_class($this));

			$resource = str_replace("{className}", end($classes), $this->_expressions['resource']);
			$resource[0] = strtolower($resource[0]);

			$this->_resource = $resource;
		}

		$this->_source = \Gacela::instance()->getDataSource($this->_source);

		$this->_resource = $this->_source->loadResource($this->_resource);

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
		$criteria = \Gacela::instance()->autoload('\\Criteria');
		$criteria = new $criteria();
		
		if(!is_array($id)) {
			$id = array(current($this->_primaryKey) => $id);
		}

		foreach($this->_primaryKey as $key) {
			$criteria->equals($key, $id[$key]);
		}

		$data = $this->_source->query(
						$this->_source
							->getQuery($criteria)
							->from($this->_resource->getName())
					);

		return $this->_load(current($data));
	}

	/**
	 * @param Gacela\Criteria|null $criteria
	 * @return \Gacela\Collection
	 */
	public function findAll(\Gacela\Criteria $criteria = null)
	{
		$query = $this->_source->getQuery($criteria);
		
		$query->from($this->_resource->getName());

		foreach($this->_inherits as $name => $relation) {
			$on = $this->_resource->getName().'.'.$relation['meta']->keyColumn." = ".$relation['meta']->refTable.'.'.$relation['meta']->refColumn;

			$query->join($name, $on);
		}
		
		$records = $this->_source->query($query);

		return new \Gacela\Collection($this, $records);
	}

	public function findRelation($name, $data) {
		$relation = $this->_foreignKeys[$name];

		$criteria = new \Gacela\Criteria();

		$criteria->equals($relation['meta']->refColumn, $data->{$relation['meta']->keyColumn});

		$result = \Gacela::instance()->loadMapper($name)->findAll($criteria);

		if ($relation['meta']->type == 'belongsTo') {
			return $result->current();
		} elseif ($relation['meta']->type == 'hasMany') {
			return $result;
		}
	}

	public function delete(\stdClass $data)
	{
		$where = new \Gacela\Criteria();

		foreach($this->_primaryKey as $key) {
			$where->equals($key, $data[$key]);
		}

		return $this->_source->delete($this->_resource->getName(), $where);
	}

	/**
	 * @return array
	 */
	public function getFields()
	{
		$array = $this->_resource->getFields();

		foreach($this->_inherits as $key => $stuff) {
			$array = array_merge($array, $stuff['resource']->getFields());
		}

		return $array;
	}

	public function getRelations()
	{
		return array_keys($this->_foreignKeys);
	}

	public function init()
	{
		$this->_init();
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
			$where = new \Gacela\Criteria();

			foreach($this->_primaryKey as $key) {
				$where->equals($key, $data[$key]);
			}
			
			return $this->_source->update($this->_resource->getName(), $data, $where);
		}

		return $data;
	}
}
