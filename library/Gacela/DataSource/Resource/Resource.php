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
	
	protected $_name;

	protected function __construct(array $config)
	{
		$this->_name = $config['name'];
	}

	abstract public function getFields();

	abstract public function getRelations();

}
