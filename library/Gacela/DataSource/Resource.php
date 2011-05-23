<?php
/** 
 * @author noah
 * @date 2/26/11
 * @brief
 * 
*/

namespace Gacela\DataSource;

class Resource {

	protected $_meta = array();

	public function __construct(array $meta)
	{
		$this->_meta = $meta;
	}

	public function getName()
	{
		return $this->_meta['name'];
	}

	public function getFields()
	{
		return $this->_meta['columns'];
	}

	public function getPrimaryKey()
	{
		return $this->_meta['primary'];
	}

	public function getRelations()
	{
		return $this->_meta['relations'];
	}

}
