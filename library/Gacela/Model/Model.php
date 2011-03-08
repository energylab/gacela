<?php
/** 
 * @author noah
 * @date Oct 4, 2010
 * @brief
 * 
*/

namespace \Gacela\Model;

abstract class Model implements iModel {

	protected $_changedCols = array();
	
	protected $_data;

	protected $_fields;

	protected $_originalData = array();

	public function __construct($data = array())
	{
		if(is_array($data)) {
			$data = (object) $data;
		}

		$this->_data = $data;
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
	}
}
