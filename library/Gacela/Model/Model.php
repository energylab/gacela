<?php
/**
 * @author Noah Goodrich
 * @date May 12, 2011
 *
 *
*/

namespace Gacela\Model;

abstract class Model implements iModel
{
	protected static $meta = array();
	
	protected $_changed = array();

	protected $_data = null;

	protected $_errors = array();

	protected $_mapper;

	protected $_originalData = array();

	/**
	 * @return array $_errors
	 */
	protected function _getErrors()
	{
		return $this->_errors;
	}

	protected function _mapper()
	{
		return \Gacela\Gacela::instance()->loadMapper($this->_mapper);
	}

	protected function _set($key, $val)
	{
		$field = isset(static::$meta[$this->_mapper]['fields'][$key]) ? static::$meta[$this->_mapper]['fields'][$key] : null;

		if($field) {
			$val = \Gacela\Gacela::instance()->getField($field->type)->transform($field, $val, false);

			if(!isset($this->_data[$key])) {
				$this->_data[$key] = $field->default;
			}
			
			if($val !== $this->_data[$key]) {
				if(isset($this->_data[$key])) {
					$this->_originalData[$key] = $this->_data[$key];
				}

				$this->_changed[] = $key;

				$this->_data[$key] = $val;
			}
		} else {
			$this->_data[$key] = $val;
		}
	}

	/**
	 * @param array|stdClass $data
	 */
	public function __construct($mapper, $data = array())
	{
		$this->_mapper = $mapper;

		if(!isset(static::$meta[$this->_mapper])) {
			static::$meta[$this->_mapper] = $this->_mapper()->getMetaData();
		}

		$this->_data = (array) $data;

		$this->init();
	}

	/**
	 * @throws \Exception
	 * @param  string $key
	 * @return mixed
	 */
	public function __get($key)
	{
		$method = '_get'.ucfirst($key);
		if (method_exists($this, $method)) {
			return $this->$method();
		} elseif(isset(static::$meta[$this->_mapper]['fields'][$key])) {
			$meta = static::$meta[$this->_mapper]['fields'][$key];

			if(isset($this->_data[$key])) {
				$value = $this->_data[$key];
			} else {
				$value = $meta->default;
			}
			
			return \Gacela\Gacela::instance()->getField($meta->type)->transform($meta, $value, false);
		} elseif(array_key_exists($key, $this->_data)) {
			return $this->_data[$key];
		} elseif (isset(static::$meta[$this->_mapper]['relations'][$key])) {
			return $this->_mapper()->findRelation($key, $this->toArray());
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
		} elseif(isset(static::$meta[$this->_mapper['relations'][$key]])) {
			$relation = $this->$key;

			if($relation instanceof \Gacela\Collection\Collection) {
				return count($relation) > 0;
			} else {
				foreach(static::$meta[$this->_mapper]['relations'] as $key => $ref) {
					if(!isset($relation->$ref)) {
						return false;
					}

					return true;
				}
			}
		}

		return isset($this->_data[$key]);
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
		return $this->_mapper()->addAssociation($association, $this->toArray(), $delete);
	}

	/**
	 *
	 * @return bool
	 */
	public function delete()
	{
		return $this->_mapper()->delete($this->toArray());
	}

	/**
	 *  Called at the end of __construct.
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
		if(
			((is_array($association) || $association instanceof \Gacela\Collection\Collection) && $association->count())
			|| $association instanceof \Gacela\Model\iModel
		) {
			return $this->_mapper()->removeAssociation($association, $this->toArray());
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

		if(empty($this->_changed)) {
			return true;
		}

		if(!$this->validate()) {
			return false;
		}

		$data = $this->_mapper()->save($this->_changed, $this->toArray(), $this->_originalData);

		if($data === false) {
			return false;
		}
		
		$this->_data = array_merge($this->_data, $data);
		
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
	 * @return array
	 */
	public function toArray()
	{
		foreach(static::$meta[$this->_mapper]['fields'] as $field => $meta) {
			if(!isset($this->_data[$field])) {
				$this->_data[$field] = $meta->default;
			}	
		}

		$data = [];
		foreach((array) $this->_data as $key => $val) {
			if(isset(static::$meta[$this->_mapper]['fields'][$key])) {
				$data[$key] = \Gacela\Gacela::instance()
					->getField($meta->type)
					->transform(static::$meta[$this->_mapper]['fields'][$key], $val, false);
			} else {
				$data[$key] = $val;
			}
		}

		return $data;
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

		foreach(static::$meta[$this->_mapper]['fields'] as $key => $meta) {
			$rs = \Gacela\Gacela::instance()->getField($meta->type)->validate($meta, $this->$key);

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
