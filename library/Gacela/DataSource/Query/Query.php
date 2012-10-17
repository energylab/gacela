<?php
/**
 * @author noah
 * @date 3/26/11
 *
 *
*/

namespace Gacela\DataSource\Query;

abstract class Query
{

	protected $_binds = array();

	/**
	 * @abstract
	 * @param \Gacela\Criteria $criteria
	 * @return void
	 */
	abstract protected function _buildFromCriteria(\Gacela\Criteria $criteria);

	protected function _cast($value)
	{
		return $value;
	}

	/**
	 * @abstract
	 * @return array($query, $args)
	 */
	abstract public function assemble();

	/**
	 * @param \Gacela\Criteria $criteria
	 */
	public function __construct(\Gacela\Criteria $criteria = null)
	{
		if(!is_null($criteria)) {
			$this->_buildFromCriteria($criteria);
		}
	}
}
