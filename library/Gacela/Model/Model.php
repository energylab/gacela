<?php
/**
 * @author Noah Goodrich
 * @date May 12, 2011
 * @brief
 *
*/

namespace Gacela\Model;

abstract class Model implements iModel {

	protected static $_field = null;

	protected $_changed = array();

	protected $_data = null;

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
	 *
	 * @param array $data
	 */
	protected function _initData(array $data)
	{
		if(!$this->_data) {
			$this->_data = new \stdClass;
		}

		$field = static::$_field;

		foreach($this->_fields as $name => $meta) {
			if(isset($data[$name])) {
				$value = $data[$name];
			} else {
				$value = $meta->default;
			}

			$this->_data->$name = $field::transform($meta, $value, false);
		}

		$extras = array_diff(array_keys($data), array_keys($this->_fields));

		foreach($extras as $key) {
			$this->_data->$key = $data[$key];
		}
	}

	/**
	 * @return \Gacela\Mapper\Mapper
	 */
	protected function _mapper()
	{
		if($this->_mapper instanceof \Gacela\Mapper\Mapper) {
			return $this->_mapper;
		}

		if(is_string($this->_mapper)) {
			$class = $this->_mapper;
		} else {
			$class = explode("\\", get_class($this));
			$class = end($class);
		}

		$this->_mapper = \Gacela::instance()->loadMapper($class);

		return $this->_mapper;
	}

	protected function _set($key, $val)
	{
		if(isset($this->_fields[$key])) {
			if(isset($this->_data->$key)) {
				$this->_originalData[$key] = $this->_data->$key;
			}

			$this->_changed[] = $key;

			$field = static::$_field;

			$this->_data->$key = $field::transform($this->_fields[$key], $val, false);
		} else {
			$this->_data->$key = $val;
		}
	}

	protected function _singleton()
	{
		return \Gacela::instance();
	}

	/**
	 * @param array|stdClass $data
	 */
	public function __construct($data = array())
	{
		if(is_null(static::$_field)) {
			static::$_field = $this->_singleton()->autoload("\\Field\\Field");
		}

		if(is_object($data)) {
			$data = (array) $data;
		}

		$this->_fields = $this->_mapper()->getFields();
		$this->_relations = $this->_mapper()->getRelations();

		$this->_initData($data);

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
			$this->_set($key, $val);
		}
	}

	public function add($association, $delete = false)
	{
		return $this->_mapper()->addAssociation($association, $this->_data, $delete);
	}

	/**
	 *
	 * @return bool
	 */
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

	/**
	 *
	 * @param type $association
	 * @return boolean
	 */
	public function remove($association)
	{
		if($association->count())
		{
			return $this->_mapper()->removeAssociation($association, $this->_data);
		}

		return true;
	}

	/**
	 * @param \stdClass|null $data
	 * @return bool
	 */
	public function save($data = null)
	{
		if($data) {
			$this->setData($data);
		}

		if(!$this->validate()) {
			return false;
		}

		$data = $this->_mapper()->save($this->_changed, $this->_data, $this->_originalData);

		if($data === false) {
			return false;
		}

		$this->_initData((array) $data);

		unset($data);
		
		$this->_changed = array();
		$this->_originalData = array();

		return true;
	}

	/**
	 *
	 * @param array $data
	 * @return \Gacela\Model\Model
	 */
	public function setData(array $data)
	{
		foreach($data as $field => $val) {
			$this->$field = $val;
		}

		return $this;
	}

	/**
	 * @param \stdClass|null $data
	 * @return bool
	 */
	public function validate(array $data = null)
	{
		if($data) {
			$this->setData($data);
		}

		$field = static::$_field;
		foreach($this->_fields as $key => $meta) {

			$rs = $field::validate($meta, $this->_data->$key);

			if($rs !== true) {
				$this->_errors[$key] = $rs;
			}
		}

		if(count($this->_errors)) {
			return false;
		}

		return true;
	}
}
