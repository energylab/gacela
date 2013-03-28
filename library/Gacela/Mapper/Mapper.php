<?php
/**
 * @author Noah Goodrich
 * @date April 13, 2010
 *
*/

namespace Gacela\Mapper;

abstract class Mapper implements iMapper
{

	protected static $_deletedField = 'isDeleted';

	/**
	 * Contains the names of resources that are associations to Mapper::$_resource
	 * <a href="http://martinfowler.com/eaaCatalog/associationTableMapping.html" target="_blank">Association Table Mapping</a>
	 */
	protected $_associations = array();

	protected $_cache = false;

	/**
	 *  Contains the names of resources that are dependent on Mapper::$_resource <br/>
	 * <a href="http://martinfowler.com/eaaCatalog/dependentMapping.html" target="_blank">Dependent Mapping</a>
	 */
	protected $_dependents = array();

	protected $_fields = false;

	/**
	 *  Contains the meta information necessary to load hasMany, belongsTo related data
	 * Also used by Mapper::$_associations to load related data and by Mapper::$_inherits to determine whether
	 * Concrete Table Inheritance is applicable.
	 */
	protected $_foreignKeys = array();

	/**
	 *  Contains the names of resources that Mapper::$_resource inherits from based on Mapper::$_foreignKeys and shared
	 * primary keys
	 * <a href="http://martinfowler.com/eaaCatalog/concreteTableInheritance.html">Concrete Table Inheritance</a>
	 */
	protected $_inherits = array();

	/**
	 *  Registry of Model objects already loaded from the DataSource.
	 */
	protected $_models = array();

	/**
	 *  Model class name to create in _load()
	 */
	protected $_modelName = null;

	/**
	 *  Contains the primary key fields for the mapper.
	 * By default the primary key loads from Resource::getPrimaryKey()
	 *
	 */
	protected $_primaryKey = array();

	protected $_relations;

	/**
	 *  The main Gacela\DataSource\Resource object represented by the Mapper
	 */
	protected $_resource = null;

	protected $_resourceName = null;

	/**
	 *  Instance of DataSource to use for the Mapper.
	 */
	protected $_source = 'db';

	/**
	 * @param null $data
	 * @return mixed
	 */
	protected function _cache($params, $data = null)
	{
		$instance = $this->_gacela();

		$sourceName = $this->_source()->getName();
		$className = strtolower(str_replace('\\', '_', get_class($this)));

		$key = $sourceName.'_'.$className.'_version';

		$version = $instance->cacheData($key);

		if (is_null($version) || $version === false) {
			$version = 0;
			$instance->cacheData($key, $version);
		}

		$key = $sourceName.'_'.$className.'_'.$version . '_' .hash('whirlpool', serialize($params));

		$cached = $instance->cacheData($key);

		if (is_null($data)) {
			return $cached;
		}

		$instance->cacheData($key, $data);

		return $data;
	}

	/**
	 * @param \PDOStatement | array $data
	 * @return \Gacela\Collection\Collection
	 */
	protected function _collection($data)
	{
		return $this->_gacela()->makeCollection($this, $data);
	}

	/**
	 * @param \Gacela\DataSource\Resource $resource
	 * @param \stdClass $data
	 * @return bool
	 */
	protected function _deleteRecord(\Gacela\DataSource\Resource $resource, \stdClass $data)
	{
		$primary = $this->_primaryKey($resource->getPrimaryKey(), $data);

		if(is_null($primary)) {
			return true;
		}

		$where = $this->_gacela()->autoload('Criteria');

		$where = new $where;

		foreach($primary as $key => $value) {
			$where->equals($key, $value);
		}

		return $this->_source()->delete($resource->getName(), $this->_source()->getQuery($where));
	}

	/**
	 * @param string $name
	 * @param \stdClass $data
	 * @return \Gacela\Collection\Collection
	 */
	protected function _findAssociation($name, \stdClass $data)
	{
		$data = $this->_primaryKey($this->_primaryKey, $data);

		if(!is_array($data)) {
			$refs = array_values($this->_associations[$name]['meta']->keys);

			foreach($refs as $key) {
				$data[$key] = null;
			}
		} else {
			foreach($this->_associations[$name]['meta']->keys as $key => $ref) {
				if($key != $ref) {
					$data[$ref] = $data[$key];
					unset($data[$key]);
				}
			}
		}

		return $this->_gacela()
					->loadMapper($this->_singularize($name))
					->findAllByAssociation($this->_resource->getName(), $data);

	}

	protected function _getRelationArray($array)
	{
		return array(
					'meta' => (object) $array['meta'],
					'resource' => $this->_source()->loadResource($array['resource'])
				);
	}

	protected function _getQuery(\Gacela\Criteria $criteria = null)
	{
		return $this->_source()->getQuery($criteria);
	}

	protected function _gacela()
	{
		return \Gacela::instance();
	}

	/**
	 * @param $name
	 * @return void
	 */
	protected function _incrementCache()
	{
		$instance = $this->_gacela();

		$sourceName = $this->_source()->getName();
		$className = strtolower(str_replace('\\', '_', get_class($this)));

		$key = $sourceName.'_'.$className.'_version';

		$cached = $instance->cacheData($key);

		if($cached !== false) {
			$instance->incrementDataCache($key);
		}
	}

	/**
	 * @return Mapper
	 */
	protected function _init()
	{
		// Everything loads in order based on what resources are needed first.
		$this->_initResource()
			->_initPrimaryKey()
			->_initForeignKeys($this->_resource->getRelations())
			->_initInherits()
			->_initAssociations()
			->_initDependents()
			->_initModel();

		return $this;
	}

	protected function _initAssociations()
	{
		if(empty($this->_associations)) {
			foreach($this->_foreignKeys as $name => $foreign) {
				$resource = $foreign['resource'];

				if(count($resource->getFields()) == count($resource->getPrimaryKey())) {
					$primary = $resource->getPrimaryKey();

					foreach($resource->getRelations() as $relation) {
						$keys = array_keys($relation->keys);

						foreach($keys as $key) {
							$i = array_search($key, $primary);

							if($i !== false) {
								unset($primary[$i]);
							}
						}
					}

					if(!count($primary)) {
						$this->_associations[$name] = $foreign;

						unset($this->_foreignKeys[$name]);
					}
				}
			}
		} else {
			foreach($this->_associations as $name => $assoc) {
				$this->_associations[$name] = $this->_getRelationArray($assoc);

				if(isset($this->_foreignKeys[$name])) {
					unset($this->_foreignKeys[$name]);
				}
			}
		}

		return $this;
	}

	protected function _initDependents()
	{
		// Run so that dependent relationships can't be accidentally loaded independently
		$_dependents = $this->_dependents;
		$this->_dependents = array();

		foreach($_dependents as $key => $name) {
			if(is_array($name)) {
				$dependent = $name;
				$name = $key;

				$dependent = $this->_getRelationArray($dependent);

			} else {
				$dependent = $this->_foreignKeys[$name];
			}

			if(isset($this->_foreignKeys[$name])) {
				unset($this->_foreignKeys[$name]);
			}


			/**
			 * If the keyColumn of the primary resource is nullable, then all fields in the dependent relationship need to
			 * appear nullable.
			 */
			$nullable = false;
			foreach(array_keys($dependent['meta']->keys) as $key) {
				if($this->_fields[$key]->null) {
					$nullable = true;
					break;
				}
			}

			if($nullable) {
				foreach($dependent['resource']->getFields() as $key => $val) {
					if(in_array($key, $this->_primaryKey)) {
						continue;
					}

					$val->null = true;
				}
			}

			$this->_dependents[$name] = $dependent;
		}

		foreach($this->_dependents as $dependent) {
			$this->_fields = array_merge($dependent['resource']->getFields(), $this->_fields);
		}

		return $this;
	}

	protected function _initForeignKeys($relations)
	{
		$plural = $this->_pluralize($this->_resourceName);
		$single = $this->_singularize($this->_resourceName);

		if(in_array($plural, array_keys($relations))) {
			unset($relations[$plural]);
		} elseif(in_array($single, array_keys($relations))) {
			unset($relations[$single]);
		}

		$this->_foreignKeys = array_merge($relations, $this->_foreignKeys);

		foreach($this->_foreignKeys as $relation => $meta) {
			// This foreign key is already initialized
			if(is_array($meta) && isset($meta['resource'])) {
				continue;
			}

			$meta = (object) $meta;

			$resource = $this->_source()->loadResource($meta->refTable);

			$this->_foreignKeys[$relation] = array('meta' => $meta, 'resource' => $resource);
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

				if($this->_resource->getPrimaryKey() === $stuff['resource']->getPrimaryKey() && array_keys($stuff['meta']->keys) === $stuff['resource']->getPrimaryKey()) {
					$this->_inherits[$name] = $stuff;

					$relations = $stuff['resource']->getRelations();

					unset($relations[$this->_singularize($this->_resourceName)]);
					unset($this->_foreignKeys[$name]);

					$this->_initForeignKeys($relations);
				}
			}
		} else {
			foreach($this->_inherits as $name => $inherit) {
				$this->_inherits[$name] = $this->_getRelationArray($inherit);

				$relations = $this->_inherits[$name]['resource']->getRelations();

				unset($relations[$this->_singularize($this->_resourceName)]);

				$this->_initForeignKeys($relations);

				if(isset($this->_foreignKeys[$name])) {
					unset($this->_foreignKeys[$name]);
				}
			}
		}

		foreach($this->_inherits as $stuff) {
			$this->_fields = array_merge($this->_fields, $stuff['resource']->getFields());
		}

		return $this;
	}

	/**
	 * @return Mapper
	 */
	protected function _initModel()
	{
		if(is_null($this->_modelName)) {
			$classes = explode('\\', get_class($this));

			$pos = array_search('Mapper', $classes);

			$classes[$pos] = 'Model';

			$this->_modelName = "\\".join("\\", $classes);
		}


		$this->_relations = array();
		foreach($this->_foreignKeys as $key => $array) {
			$this->_relations[$key] = $array['meta']->keys;
		}

		foreach($this->_associations as $key => $array) {
			$this->_relations[$key] = $array['meta']->keys;
		}

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
		if(is_null($this->_resourceName)) {
			$class = explode('\\', get_class($this));
			$class = end($class);
			$class = strtolower($class);

			$this->_resourceName = $this->_pluralize($class);
		}

		$this->_resource = $this->_source()->loadResource($this->_resourceName);
		$this->_resourceName = $this->_resource->getName();

		$this->_fields = $this->_resource->getFields();

		return $this;
	}

	/**
	 * @param \stdClass $data
	 * @return Model
	 */
	protected function _load(\stdClass $data)
	{
		return new $this->_modelName($this->_gacela(), $this, $data);
	}

	protected function _pluralize($string)
	{
		return \Gacela\Inflector::pluralize($string);
	}

	/**
	 * @param  $data
	 * @return null|string
	 */
	protected function _primaryKey($primaryKey, \stdClass $data)
	{
		$primary = array();
		foreach($primaryKey as $k) {
			if(!isset($data->$k) || is_null($data->$k)) {
				continue;
			}

			$primary[$k] = $data->$k;
		}

		if(!count($primary) || count($primary) != count($primaryKey)) {
			$primary = null;
		}

		return $primary;
	}

	protected function _runQuery($query, $args = null, \Gacela\DataSource\Resource $resource = null)
	{
		if(is_null($resource)) {
			$resource = $this->_resource;
		}

		return $this->_source()->query($resource, $query, $args);
	}

	/**
	 * @param \Gacela\DataSource\Resource $resource
	 * @param array $changed
	 * @param \stdClass $new
	 * @param array $old
	 * @return array|bool
	 * @throws \Exception
	 */
	protected function _saveRecord($resource, $changed, $new, $old)
	{
		$fields = $resource->getFields();

		$data = array_intersect_key((array) $new, $fields, array_flip($changed));

		foreach($data as $key => $val) {
			$data[$key] = $this->_gacela()->getField($fields[$key]->type)->transform($fields[$key], $val);
		}

		if(empty($data)) {
			return true;
		}

		$test = array_merge((array) $new, $old);

		$primary = $this->_primaryKey($resource->getPrimaryKey(), (object) $test);
		$fields = $resource->getFields();

		$update = true;
		if(is_null($primary)) {
			$update = false;
		} elseif($fields[key($primary)]->sequenced === false) {
			$update = $this->_source()->find($primary, $resource);
		}

		// Insert the record
		if($update === false) {
			$rs = $this->_source()->insert($resource->getName(), $data);

			if($rs === false) {
				return $rs;
			}

			$fields = $resource->getFields();

			if(count($resource->getPrimaryKey()) == 1 && $fields[current($resource->getPrimaryKey())]->sequenced === true) {
				$new->{current($resource->getPrimaryKey())} = $rs;
				$changed[] = current($resource->getPrimaryKey());
			}
		// Update the existing record
		} else {
			$primary = $this->_primaryKey($resource->getPrimaryKey(), (object) $test);

			if(is_null($primary)) {
				throw new \Exception('Oops! primary key is null');
			}

			$where = $this->_gacela()->autoload('Criteria');

			$where = new $where;

			foreach($primary as $k => $v) {
				$where->equals($k, $v);
			}

			if($this->_source()->update($resource->getName(), $data, $this->_source()->getQuery($where)) === false) {
				return false;
			}
		}

		return array($changed, $new);
	}

	protected function _singularize($string)
	{
		return \Gacela\Inflector::singularize($string);
	}

	/**
	 * @return \Gacela\DataSource\DataSource
	 */
	protected function _source()
	{
		return $this->_gacela()->getDataSource($this->_source);
	}

	public function __construct()
	{
		$this->_init();

		$this->init();
	}

	/**
	 *
	 * @param $association
	 * @param $data
	 * @param bool $delete
	 * @return bool
	 */
	public function addAssociation($association, $data, $delete = false)
	{
		if($association instanceof \Gacela\Collection\Collection) {
			$model = $association->current();
		} else {
			$model = $association;
			$association = array($model);
		}

		$name = explode('\\', get_class($model));
		$name = end($name);
		$name = $this->_pluralize($name);
		$name[0] = strtolower($name[0]);

		if(!isset($this->_associations[$name])) {
			return false;
		}

		$assoc = $this->_associations[$name];

		if($delete) {
			$criteria = $this->_gacela()->autoload('Criteria');
			$criteria = new $criteria;

			foreach($assoc['meta']->keys as $key => $ref) {
				$criteria->equals($ref, $data->$key);
			}

			$this->_source()->delete($assoc['meta']->refTable, $this->_source()->getQuery($criteria));
		}

		$toInsert = array();

		$me = array();

		foreach($assoc['meta']->keys as $key => $ref) {
			$me[$ref] = $data->$key;
		}

		foreach($association as $model) {
			$array = $me;

			foreach($assoc['resource']->getRelations() as $relation) {
				if($relation->type == 'belongsTo') {
					foreach($relation->keys as $key => $ref) {
						if(array_search($key, $assoc['meta']->keys) === false) {
							$array[$key] = $model->$ref;
						}
					}
				}
			}

			$toInsert[] = $array;
		}

		$rs = $this->_source()->insert($assoc['meta']->refTable, $toInsert);

		return $rs;
	}

	public function count($query = null)
	{
		return $this->_source()->count($query, $this->_resource, $this->_inherits, $this->_dependents);
	}

	public function debug($return = true)
	{
		$array = array(
			'associations' => array_keys($this->_associations),
			'dependents' => array_keys($this->_dependents),
			'inherits' => array_keys($this->_inherits),
			'other' => array_keys($this->_foreignKeys),
			'fields' => array_keys($this->getFields()),
			'lastDataSourceQuery' => $this->_source()->lastQuery(),
		);

		if($return) {
			return $array;
		} else {
			echo '<pre>'.print_r($array, true).'</pre>';
			return;
		}
	}

	/**
	 *  Called by the Model to delete the record represented by the identity field
	 * @param stdClass - The data from the Model
	 * @return true on success, false on failure
	 */
	public function delete(\stdClass $data)
	{
		$this->_source()->beginTransaction();

		if(!$this->_deleteRecord($this->_resource, $data)) {
			$this->_source()->rollbackTransaction();
			return false;
		}

		foreach($this->_inherits as $inherits) {
			$tmp = array();
			foreach($inherits['meta']->keys as $key => $ref) {
				$tmp[$ref] = $data->$key;
			}

			if(!$this->_deleteRecord($inherits['resource'], (object) $tmp)) {
				$this->_source()->rollbackTransaction();
				return false;
			}
		}

		unset($tmp);

		foreach($this->_dependents as $dep) {
			$tmp = array();
			foreach($dep['meta']->keys as $key => $ref) {
				$tmp[$ref] = $data->$key;
			}

			if(!$this->_deleteRecord($dep['resource'], (object) $tmp)) {
				$this->_source()->rollbackTransaction();
				return false;
			}
		}

		unset($tmp);

		$this->_source()->commitTransaction();

		if($this->_cache) {
			$this->_incrementCache();
		}

		return true;
	}

	/**
	 *  Returns a single instance of Mapper::$_modelName based on the identity field
	 * @param  $id integer|array
	 * @return Model
	 *
	 */
	public function find($id)
	{
		if(!is_object($id)) {
			if(is_scalar($id)) {
				$id = array(current($this->_primaryKey) => $id);
			}

			$id = (object) $id;
		}

		$primary = $this->_primaryKey($this->_primaryKey, $id);

		if(is_null($primary)) {
			return $this->_load(new \stdClass);
		}

		$data = null;

		if($this->_cache && ($cached = $this->_cache(null))) {
			do {
				$row = current($cached);

				$fail = false;

				do {
					$key = key($primary);
					$val = current($primary);

					if($row->$key != $val) {
						$fail = true;
					}
				} while(!$fail && next($primary) !== false);

				if(!$fail) {
					$data = $row;
				}

				reset($primary);
			} while(!$data && next($cached) !== false);
		}

		if(!$data) {
			$data = $this->_source()->find($primary, $this->_resource, $this->_inherits, $this->_dependents);

			if($data && $this->_cache) {
				if(!$cached) {
					$cached = array($data);
				} else {
					$cached[] = $data;
				}

				$this->_cache(null, $cached);
			}
		}

		if(!$data) {
			$data = new \stdClass();
		}

		return $this->_load($data);
	}

	/**
	 *  Returns a Collection of Model objects based on the Criteria specified
	 * @param \Gacela\Criteria|null $criteria
	 * @return \Gacela\Collection\Collection
	 */
	public function findAll(\Gacela\Criteria $criteria = null)
	{
		if($this->_cache) {
			$cached = $this->_cache(is_null($criteria) ? new \Gacela\Criteria : $criteria);

			if($cached) {
				return $this->_collection($cached);
			}
		}

		$data = $this->_source()
			->findAll(
				$this->_source()->getQuery($criteria),
				$this->_resource,
				$this->_inherits,
				$this->_dependents
			);

		if($data instanceof \PDOStatement) {
			$data = $data->fetchAll();
		}

		if($this->_cache) {
			$this->_cache($criteria, $data);
		}

		return $this->_collection($data);
	}

	/**
	 * @param  $relation
	 * @param array $data
	 * @return \Gacela\Collection\Collection
	 */
	public function findAllByAssociation($relation, array $data)
	{
		$data = $this->_source()->findAllByAssociation(
			$this->_resource,
			$this->_associations[$relation],
			$data,
			$this->_inherits,
			$this->_dependents
		)
		->fetchAll();

		return $this->_collection($data);
	}

	/**
	 *  Requests a related Model or Collection and returns it to the requesting Model.
	 * Uses Mapper::$_associations, Mapper::$_foreignKeys
	 *
	 * @param  $name - The name of the Model or Collection to return
	 * @param  $data - The data from the Model
	 * @return Model | Collection
	 */
	public function findRelation($name, $data)
	{
		if(isset($this->_associations[$name])) {
			return $this->_findAssociation($name, $data);
		}

		$relation = $this->_foreignKeys[$name];

		if($relation['meta']->type == 'hasMany') {
			$name = $this->_singularize($name);
		}

		$criteria = $this->_gacela()->autoload('Criteria');

		$criteria = new $criteria;

		foreach($relation['meta']->keys as $key => $ref) {
			$criteria->equals($relation['meta']->refTable.'.'.$ref, $data->{$key});
		}

		$result = $this->_gacela()->loadMapper($name)->findAll($criteria);

		if ($relation['meta']->type == 'belongsTo') {
			return $result->current();
		} elseif ($relation['meta']->type == 'hasMany') {
			return $result;
		}

		throw new \Gacela\Exception('Invalid Relationship Type!');
	}

	/**
	 *  Used by Model to get all of the fields available from mapper.
	 * @return A merged array of all fields from $_resource, $_inherits, $_dependents
	 */
	public function getFields()
	{
		return $this->_fields;
	}

	public function getPrimaryKey()
	{
		return $this->_primaryKey;
	}

	/**
	 *  Provides the Model with the names of related Models
	 * @return An array of all the relation names whether as $_associations or $_foreignKeys meaning belongsTo or hasMany
	 */
	public function getRelations()
	{
		return $this->_relations;
	}

	public function init() {}

	/**
	 *  Loads a new instance of $_modelName from the $data provided.
	 * @param \stdClass $data
	 * @return Model
	 */
	public function load(\stdClass $data)
	{
		return $this->_load($data);
	}

	/**
	 *  - Removes an association between two different Models
	 * @param $association
	 * @param $data
	 * @return bool
	 */
	public function removeAssociation($association, $data)
	{
		if($association instanceof \Gacela\Collection\Collection) {
			$model = $association->current();
		} else {
			$model = $association;
			$association = array($model);
		}

		$name = explode('\\', get_class($model));
		$name = end($name);
		$name = $this->_pluralize($name);
		$name[0] = strtolower($name[0]);

		if(!isset($this->_associations[$name])) {
			return false;
		}

		$assoc = $this->_associations[$name];


		$criteria = $this->_gacela()->autoload('Criteria');

		/**
		 * @var \Gacela\Criteria
		 */
		$main = new $criteria;

		/**
		 * @var \Gacela\Criteria
		 */
		$me = new $criteria;

		foreach($assoc['meta']->keys as $key => $ref) {
			$me->equals($ref, $data->$key);
		}

		foreach($association as $model) {
			$sub = clone $me;

			foreach($assoc['resource']->getRelations() as $relation) {
				if($relation->type == 'belongsTo') {
					foreach($relation->keys as $key => $ref) {
						if(array_search($key, $assoc['meta']->keys) === false) {
							$sub->equals($key, $model->$ref);
						}
					}
				}
			}

			$main->criteria($sub, true);
		}

		return $this->_source()->delete($assoc['meta']->refTable, $this->_source()->getQuery($main));
	}

	/**
	 *  Save is called by Model, the Mapper is responsible for knowing whether to call insert() or update() on the DataSource for $_resource, $_inherits, and $_dependents.
	 * @param array $changed - An array of the changed fields
	 * @param \stdClass $new - The data from the Model
	 * @param array $old - The original data from the Model
	 * @return bool|\stdClass - FALSE on failure, the modified $data on success.
	 */
	public function save(array $changed, \stdClass $new, array $old)
	{
		$rs = $this->_source()->beginTransaction()."\n";

		foreach($this->_dependents as $dependent) {

			// This all looks like a nasty hack. Here's to hoping to some more brilliant minds to provide input. -- ndg
			$data = array('old' => array(), 'new' => array(), 'changed' => array());

			// Setup for differing key names
			foreach($dependent['meta']->keys as $key => $ref) {
				if(!empty($old[$key])) {
					$data['old'][$ref] = $old[$key];
				}

				if(isset($new->$key) && (!isset($data['old'][$ref]) || $data['old'][$ref] != $new->$key)) {
					$data['changed'][] = $ref;
					$data['new'][$ref] = $new->$key;
				}
			}

			foreach($dependent['resource']->getFields() as $name => $field) {
				if(in_array($name, $changed) && !in_array($name, $data['changed'])) {
					$data['new'][$name] = $new->$name;
					$data['changed'][] = $name;
				}
			}

			$rs = $this->_saveRecord($dependent['resource'], $data['changed'], (object) $data['new'], $data['old']);

			if (is_array($rs)) {
				foreach($dependent['meta']->keys as $key => $ref) {
					$changed[] = $key;
					$new->$key = $rs[1]->$ref;
				}
			}
		}

		foreach($this->_inherits as $parent) {
			$rs = $this->_saveRecord($parent['resource'], $changed, $new, $old);

			if(is_array($rs)) {
				list($changed, $new) = $rs;
			}
		}

		$rs = $this->_saveRecord($this->_resource, $changed, $new, $old);

		if($rs === false) {
			$rs = $this->_source()->rollbackTransaction();

			return false;
		}

		$rs = $this->_source()->commitTransaction();

		if($this->_cache) {
			$this->_incrementCache();
		}

		return (object) $new;
	}

	/**
	 * @param bool
	 * @return Mapper
	 */
	public function setCacheable($bool)
	{
		$this->_cache = (bool) $bool;

		return $this;
	}
}
