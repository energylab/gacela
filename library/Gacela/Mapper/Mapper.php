<?php
/** 
 * @author noah
 * @date Oct 4, 2010
 * @brief
 *
 * @namespace Gacela\Mapper
 * @class Mapper
*/

namespace Gacela\Mapper;

abstract class Mapper implements iMapper {

	/**
	 * 
	 * @brief Contains the names of resources that are associations to Mapper::$_resource
	 * <a href="http://martinfowler.com/eaaCatalog/associationTableMapping.html">Association Table Mapping</a>
	 */
	protected $_associations = array();

	/**
	 * @brief Contains the names of resources that are dependent on Mapper::$_resource
	 * <a href="http://martinfowler.com/eaaCatalog/dependentMapping.html">Dependent Mapping</a>
	 */
	protected $_dependents = array();

	protected $_expressions = array('resource' => "{className}s");

	/**
	 * @brief Contains the names of resources that Mapper::$_resource inherits from based on Mapper::$_foreignKeys and shared
	 * primary keys
	 * <a href="http://martinfowler.com/eaaCatalog/concreteTableInheritance.html">Concrete Table Inheritance</a>
	 */
	protected $_inherits = array();

	/**
	 * @brief Registry of models already loaded from the database.
	 */
	protected $_models = array();

	/**
	 * @brief Model class name to create in _load()
	 */
	protected $_modelName = null;

	/**
	 * @var array
	 * @brief Contains the primary key fields for the mapper.
	 * By default the primary key loads from Gacela\DataSource\Resource::getPrimaryKey()
	 * 
	 */
	protected $_primaryKey = array();
	
	protected $_foreignKeys = array();

	/**
	 * @var Gacela\DataSource\Resource
	 */
	protected $_resource = null;

	/**
	 * @var string
	 * @brief Instance of Gacela\DataSource\DataSource to use for the mapper.
	 */
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
	 * @return Model
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
			if(!isset($data->$k) || is_null($data->$k)) {
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

	/**
	 * @param  $id integer|array
	 * @return \Gacela\Model\Model
	 *
	 * \brief Find and load a model based on its identity field.
	 * \return Model
	 */
	public function find($id)
	{
		$criteria = new \Gacela\Criteria();
		
		if(!is_array($id)) {
			$id = array(current($this->_primaryKey) => $id);
		}

		$primary = $this->_primaryKey($id);

		if(!is_null($primary)) {
			foreach($primary as $key) {
				$criteria->equals($key, $id[$key]);
			}

			$data = $this->_source->query(
							$this->_source
								->getQuery($criteria)
								->from($this->_resource->getName())
						);

			$data = current($data);
		}

		if(!isset($data) || !count($data)) {
			$data = new \stdClass();
		}

		return $this->_load($data);
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

	/**
	 * 
	 */
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
