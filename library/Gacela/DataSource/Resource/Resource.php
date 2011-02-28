<?php
/** 
 * @author noah
 * @date 2/26/11
 * @brief
 * 
*/

namespace Gacela\DataSource\Resource;

abstract class Resource {

	protected $_meta = array();
	
	protected $_config;

	protected function __construct(array $config)
	{
		$this->_config = (object) $config;
	}

	abstract public function getFields();

	abstract public function getPrimaryKey();
	
	abstract public function getRelations();

}
