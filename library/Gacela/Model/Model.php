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

	protected function _field()
	{
		return $this->_singleton()->autoload("\\Field\\Field");
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

	protected function _set($key, $val)
	{
		if(isset($this->_data->$key)) {
			$this->_originalData[$key] = $this->_data->$key;
		}

		$this->_changed[] = $key;

		$field = $this->_field();

		$this->_data->$key = $field::transform($this->_fields[$key], $val, false);
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
		if(is_object($data)) {
			$data = (array) $data;
		}

		$this->_fields = $this->_mapper()->getFields();
		$this->_relations = $this->_mapper()->getRelations();

		$this->_data = new \stdClass;

		$field = $this->_field();

		foreach($this->_fields as $name => $meta) {
			if(isset($data[$name])) {
				$value = $data[$name];
			} else {
				$value = $meta->default;
			}

			$this->_data->$name = $field::transform($meta, $value, false);
		}

		$extras = array_diff_key($data, $this->_fields);

		foreach($extras as $key => $val) {
			$this->_data->$key = $val;
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
			$this->_set($key, $val);
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

		$field = $this->_field();
		foreach((array) $this->_data as $key => $val) {
			if(($rs = $field::validate($this->_fields[$key], $val)) !== true) {
				$this->_errors[$key] = $rs;
			}
		}

		if(count($this->_errors)) {
			return false;
		}

		return true;
	}
}
