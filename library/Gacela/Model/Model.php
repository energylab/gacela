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
	protected $_changedCols = array();

	/**
	 * @var stdClass
	 */
	protected $_data;

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

	/**
	 * @return \Gacela\Mapper\Mapper
	 */
	protected function _mapper()
	{
		if(!empty($this->_mapper)) return $this->_mapper;

		$class = explode("\\", get_class($this));
		$pos = array_search('Model', $class);

		$class[$pos] = 'Mapper';

		$class = join("\\", $class);

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

		$this->_data = $data;
		
		$this->_fields = $this->_mapper()->getFields();
	}

	public function __get($key)
	{
		$method = '_get'.ucfirst($key);
		if(method_exists($this, $method)) {
			return $this->$method();
		}
		
		return $this->_data->$key;
	}

	public function __set($key, $val)
	{
		$this->_originalData[$key] = $this->_data->$key;
		$this->_changedCols[] = $key;
		
		$method = '_set'.ucfirst($key);

		if(method_exists($this, $method)) {
			$this->$method($val);
		} else {
			$this->_data->$key = $val;
		}
	}

	/**
	 * @return bool
	 */
	public function save($data = null)
	{
		if(!is_null($data)) {
			
		}

		if(!$this->validate()) {

		}

		return true;
	}

	public function validate($data = null)
	{
		
	}
}
