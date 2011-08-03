<?php
/** 
 * @author Noah Goodrich
 * @date May 12, 2011
 * @brief
 * 
*/

namespace Gacela\Model;

abstract class Model implements iModel {

	protected $_changed = array();

	protected $_data;

	protected $_errors = array();

	protected $_fields;

	protected $_isValidated = false;

	protected $_isValid = false;

	protected $_mapper;

	protected $_originalData = array();

	protected $_relations = array();

	/**
	 * @return array $_errors
	 */
	protected function _getErrors()
	{
		return $this->_errors;
	}

	/**
	 * @return \Gacela\Mapper\Mapper
	 */
	protected function _mapper()
	{
		if(!is_string($this->_mapper) && !empty($this->_mapper)) return $this->_mapper;

		if(is_string($this->_mapper)) {
			$class = $this->_mapper;
		} else {
			$class = explode("\\", get_class($this));
			$class = end($class);
		}
		
		$this->_mapper = \Gacela::instance()->loadMapper($class);

		return $this->_mapper;
	}

	/**
	 * @param array|stdClass $data
	 */
	public function __construct($data = array())
	{
		if(is_object($data)) {
			$data = (array) $data;
		}

		$this->_fields = $this->_mapper()->getFields();
		$this->_relations = $this->_mapper()->getRelations();

		if(!count($data)) {
			$this->_data = new \stdClass;
			
			foreach($this->_fields as $field => $meta) {
				$this->_data->$field = $meta->transform($meta->default, false);
			}
		} else {
			$this->_data = new \stdClass;
			
			foreach($data as $key => $value) {
				$this->_data->$key = $this->_fields[$key]->transform($data[$key], false);
			}
		}
		
		$this->init();
	}

	/**
	 * @throws \Exception
	 * @param  string $key
	 * @return mixed
	 */
	public function __get($key)
	{
		$method = '_get' . ucfirst($key);
		if (method_exists($this, $method)) {
			return $this->$method();
		} elseif (array_key_exists($key, $this->_relations)) {
			return $this->_mapper()->findRelation($key, $this->_data);
		} else {
			if(property_exists($this->_data, $key)) {
				return $this->_data->$key;
			}
		}
		
		throw new \Exception("Specified key ($key) does not exist!");
	}

	/**
	 * @param  string $key
	 * @return bool
	 */
	public function __isset($key)
	{
		$method = '_isset'.ucfirst($key);
		
		if(method_exists($this, $method)) {
			return $this->$method($key);
		} elseif(isset($this->_relations[$key])) {
			$relation = $this->$key;

			if($relation instanceof \Gacela\Collection) {
				return count($relation) > 0;
			} else {
				foreach($this->_relations[$key] as $key => $ref) {
					if(!isset($relation->$ref)) {
						return false;
					}

					return true;
				}
			}
		}
		
		return isset($this->_data->$key);
	}

	/**
	 * @param  $key
	 * @param  $val
	 * @return void
	 */
	public function __set($key, $val)
	{
		$method = '_set'.ucfirst($key);

		if(method_exists($this, $method)) {
			$this->$method($val);
		} else {
			if(isset($this->_data->$key)) {
				$this->_originalData[$key] = $this->_data->$key;
			}
			
			$this->_changed[] = $key;

			$this->_data->$key = $this->_fields[$key]->transform($val, false);
		}
	}

	public function add($association, $delete = false)
	{
		return $this->_mapper()->addAssociation($association, $this->_data, $delete);
	}

	public function delete()
	{
		return $this->_mapper()->delete($this->_data);
	}

	/**
	 * @brief Called at the end of __construct.
	 * Allows developers to add additional stuff to the setup process without
	 * having to directly override the constructor.
	 * 
	 */
	public function init() {}

	public function remove($association)
	{
		return $this->_mapper()->removeAssociation($association, $this->_data);
	}

	/**
	 * @param \stdClass|null $data
	 * @return bool
	 */
	public function save($data = null)
	{
		if(!$this->validate($data)) {
			return false;
		}
		
		$data = $this->_mapper()->save($this->_changed, $this->_data, $this->_originalData);

		if($data === false) {
			return false;
		}

		$this->_data = $data;
		unset($data);

		$this->_changed = array();
		$this->_originalData = array();
		
		return true;
	}

	/**
	 * @param \stdClass|null $data
	 * @return bool
	 */
	public function validate(array $data = null)
	{
		if(!is_null($data)) {
			foreach($data as $key => $val) {
				$this->$key = $val;
			}
		}
		
		foreach((array) $this->_data as $key => $val) {
			if($this->_fields[$key]->validate($val) === false) {
				$this->_errors[$key] = $this->_fields[$key]->errorCode;
			}
		}

		if(count($this->_errors)) {
			return false;
		}

		return true;
	}
}
