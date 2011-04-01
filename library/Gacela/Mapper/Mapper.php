<?php
/** 
 * @author noah
 * @date Oct 4, 2010
 * @brief
 * 
*/

namespace Gacela\Mapper;

abstract class Mapper implements iMapper {

	protected $_dependents = array();

	protected $_expressions = array('resource' => "{className}s");

	protected $_inherits = array();
	
	protected $_models = array();

	protected $_modelName = null;

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

		$this->_resource = $this->_source->loadResource($this->_resource);

		if(empty($this->_primaryKey)) {
			$this->_primaryKey = $this->_resource->getPrimaryKey();
		}

		if(empty($this->_relations)) {
			$relations = $this->_resource->getRelations();

			foreach($relations['belongsTo'] as $relation => $meta) {
				$resource = $this->_source->loadResource($meta->refTable);

				$meta->type = 'belongsTo';
				$this->_relations[$relation] = array('meta' => $meta, 'resource' => $resource);
			}

			foreach($relations['hasMany'] as $relation => $meta) {
				$resource = $this->_source->loadResource($meta->refTable);

				$meta->type = 'hasMany';
				$this->_relations[$relation] = array('meta' => $meta, 'resource' => $resource);
			}
		} else {
			foreach($this->_relations as $relation => $meta) {
				$meta = (object) $meta;

				$this->_relations[$relation]['resource'] = $this->_source->loadResource($meta->refTable);
			}
		}

		if(empty($this->_inherits)) {;
			foreach($this->_relations as $name => $stuff) {
				if($stuff['meta']->type == 'hasMany') {
					continue;
				}
				
				$refPrimary = $stuff['resource']->getPrimaryKey();
				
				if($refPrimary[0] == $stuff['meta']->refColumn && count($this->_primaryKey) == 1 && $this->_primaryKey[0] == $stuff['meta']->keyColumn) {
					$this->_inherits[$stuff['resource']->getName()] = $stuff;

					unset($this->_relations[$name]);
				}
			}
		}
		
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
		$relation = $this->_relations[$name];

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
		return array_keys($this->_relations);
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
			$where = new \Gacela\Criteria();

			foreach($this->_primaryKey as $key) {
				$where->equals($key, $data[$key]);
			}
			
			return $this->_source->update($this->_resource->getName(), $data, $where);
		}

		return $data;
	}
}
