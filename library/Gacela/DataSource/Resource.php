<?php
/**
 * @author noah
 * @date 2/26/11
 *
 *
*/

namespace Gacela\DataSource;

class Resource
{

	protected $_meta = array();

	public function __construct(array $meta)
	{
		$this->_meta = $meta;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->_meta['name'];
	}

	/**
	 * @return array
	 */
	public function getFields()
	{
		return $this->_meta['columns'];
	}

	/**
	 * @return array
	 */
	public function getPrimaryKey()
	{
		return $this->_meta['primary'];
	}

	/**
	 * @return array
	 */
	public function getRelations()
	{
		return $this->_meta['relations'];
	}

}
