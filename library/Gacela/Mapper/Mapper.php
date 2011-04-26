<?php
/** 
 * @author Noah Goodrich
 * @date April 13, 2010
 *
 * @namespace Mapper
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

	/**
	 * @brief Contains the names of resources that Mapper::$_resource inherits from based on Mapper::$_foreignKeys and shared
	 * primary keys
	 * <a href="http://martinfowler.com/eaaCatalog/concreteTableInheritance.html">Concrete Table Inheritance</a>
	 */
	protected $_inherits = array();

	/**
	 * @brief Registry of Model objects already loaded from the DataSource.
	 */
	protected $_models = array();

	/**
	 * @brief Model class name to create in _load()
	 */
	protected $_modelName = null;

	/**
	 * @brief Contains the primary key fields for the mapper.
	 * By default the primary key loads from Resource::getPrimaryKey()
	 * 
	 */
	protected $_primaryKey = array();

	/**
	 * @brief Contains the meta information necessary to load hasMany, belongsTo related data
	 * Also used by Mapper::$_associations to load related data and by Mapper::$_inherits to determine whether
	 * Concrete Table Inheritance is applicable.
	 */
	protected $_foreignKeys = array();

	/**
	 * @brief 
	 */
	protected $_resource = null;

	/**
	 * @brief Instance of DataSource to use for the Mapper.
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

		$primary = join('-', array_values($primary));
		
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
			$class = explode('\\', get_class($this));
			$class = end($class);
			$class[0] = strtolower($class[0]);
			
			$this->_resource = \Gacela\Inflector::pluralize($class);
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
			
			$primary[$k] = $data->$k;
		}
		
		if(!count($primary) || count($primary) != count($this->_primaryKey)) {
			$primary = null;
		}
		
		return $primary;
	}

	public function __construct()
	{
		$this->init();
	}

	/**
	 * @brief Returns a single instance of Mapper::$_modelName based on the identity field
	 * @param  $id integer|array
	 * @return Model
	 *
	 */
	public function find($id)
	{
		$criteria = new \Gacela\Criteria();

		if(!is_object($id)) {
			if(is_scalar($id)) {
				$id = array(current($this->_primaryKey) => $id);
			}

			$id = (object) $id;
		}

		$primary = $this->_primaryKey($id);
		
		if(!is_null($primary)) {
			foreach($primary as $key => $value) {
				$criteria->equals($key, $value);
			}

			$query = $this->_source
								->getQuery($criteria)
								->from($this->_resource->getName());

			foreach($this->_inherits as $name => $relation) {
				$on = $this->_resource->getName().'.'.$relation['meta']->keyColumn." = ".$relation['meta']->refTable.'.'.$relation['meta']->refColumn;

				$query->join($name, $on);
			}

			$data = current($this->_source->query($query));
		}

		if(!isset($data) || !count($data)) {
			$data = new \stdClass();
		}

		return $this->_load($data);
	}

	/**
	 * @brief Returns a Collection of Model objects based on the Criteria specified
	 * @param Criteria|null $criteria
	 * @return Collection
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

	/**
	 * @brief Requests a related Model or Collection and returns it to the requesting Model.
	 * Uses Mapper::$_associations, Mapper::$_foreignKeys
	 *
	 * @param  $name - The name of the Model or Collection to return
	 * @param  $data - The data from the Model
	 * @return Model | Collection
	 */
	public function findRelation($name, $data) {
		$relation = $this->_foreignKeys[$name];

		if($relation['meta']->type == 'hasMany') {
			$name = \Gacela\Inflector::singularize($name);
		}

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
	 * @brief Called by the Model to delete the record represented by the identity field
	 * @param stdClass - The data from the Model
	 * @return true on success, false on failure
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
	 * @brief Used by Model to get all of the fields available from mapper.
	 * @return A merged array of all fields from $_resource, $_inherits, $_dependents
	 */
	public function getFields()
	{
		$array = $this->_resource->getFields();

		foreach($this->_inherits as $key => $stuff) {
			$array = array_merge($array, $stuff['resource']->getFields());
		}

		return $array;
	}

	/**
	 * @brief Provides the Model with the names of related Models
	 * @return An array of all the relation names whether as $_associations or $_foreignKeys meaning belongsTo or hasMany
	 */
	public function getRelations()
	{
		$relations = array();
		foreach($this->_foreignKeys as $key => $array) {
			$relations[$key] = $array['meta']->keyColumn;
		}

		return $relations;
	}

	public function init()
	{
		$this->_init();
	}

	/**
	 * @brief Loads a new instance of $_modelName from the $data provided.
	 * @param stdClass $data 
	 * @returns Model
	 */
	public function load(\stdClass $data)
	{
		return $this->_load($data);
	}

	/**
	 * @brief Save is called by Model, the Mapper is responsible for knowing whether to call insert() or update() on the DataSource for $_resource, $_inherits, and $_dependents.
	 * @param array $changed - An array of the changed fields
	 * @param \stdClass $data - The data from the Model
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
