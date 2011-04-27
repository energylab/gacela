<?php
/** 
 * @author noah
 * @date Oct 4, 2010
 * @brief
 * 
*/

namespace Gacela\Model;

abstract class Model implements iModel {

	/**
	 * @var 
	 */
	protected $_changed = array();

	/**
	 * @var stdClass
	 */
	protected $_data;

	protected $_errors = array();

	/**
	 * @var array
	 */
	protected $_fields;

	/**
	 * @var bool
	 */
	protected $_isValidated = false;

	/**
	 * @var bool
	 */
	protected $_isValid = false;

	/**
	 * @var \Gacela\Mapper\Mapper
	 */
	protected $_mapper;

	/**
	 * @var array $_originalData
	 */
	protected $_originalData = array();

	protected $_relations = array();

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
		if(is_array($data)) {
			$data = (object) $data;
		}

		$this->_fields = $this->_mapper()->getFields();
		$this->_relations = $this->_mapper()->getRelations();
		
		if(!isset($data->{key($this->_fields)})) {
			$this->_data = new \stdClass;

			foreach($this->_fields as $field => $meta) {
				$this->_data->$field = $meta->default;
			}
		} else {
			$this->_data = $data;
		}

		$this->init();
	}

	public function __get($key)
	{
		$method = '_get' . ucfirst($key);
		if (method_exists($this, $method)) {
			return $this->$method();
		} elseif (array_key_exists($key, $this->_relations)) {
			return $this->_mapper()->findRelation($key, $this->_data);
		} else {
			if(isset($this->_fields[$key])) {
				return $this->_fields[$key]->transform($this->_data->$key, false);
			}

			if(isset($this->_data->$key)) {
				return $this->_data->$key;
			}

			throw new \Exception("Specified key ($key) does not exist!");

		}
	}

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
				if(!is_array($this->_relations[$key])) {
					return isset($relation->{$this->_relations[$key]});
				} else {
					// Need to support multi-field key relations
				}
			}
		}
		
		return isset($this->_data->$key);
	}

	public function __set($key, $val)
	{
		$this->_originalData[$key] = $this->_data->$key;
		$this->_changed[] = $key;
		
		$method = '_set'.ucfirst($key);

		if(method_exists($this, $method)) {
			$this->$method($val);
		} else {
			$this->_data->$key = $val;
		}
	}

	/**
	 * Called at the end of __construct.
	 * Allows developers to add additional stuff to the setup process without
	 * having to directly override the constructor.
	 * 
	 */
	public function init() {}

	/**
	 * @return bool
	 */
	public function save($data = null)
	{
		if(!$this->validate($data)) {
			return false;
		}

		$data = $this->_mapper()->save($this->_changed, $this->_data);

		if($data === false) {
			return false;
		}

		$this->_data = $data;
		unset($data);

		$this->_changed = array();
		$this->_originalData = array();
		
		return true;
	}

	public function validate($data = null)
	{
		if(!is_null($data)) {
			foreach($data as $key => $val) {
				$this->$key = $val;
			}
		}

		foreach((array) $this->_data as $key => $val) {
			$rs = $this->_fields[$key]->validate($val);

			if(!$rs) {
				$this->_errors[$key] = 'Error Will Robinson!';
			}
		}

		if(count($this->_errors)) {
			return false;
		}

		return true;
	}
}
