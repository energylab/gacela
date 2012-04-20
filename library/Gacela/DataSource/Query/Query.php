<?php
/**
 * @author noah
 * @date 3/26/11
 * @brief
 *
*/

namespace Gacela\DataSource\Query;

abstract class Query {

	protected $_binds = array();

	abstract protected function _buildFromCriteria(\Gacela\Criteria $criteria);

	protected function _cast($value)
	{
		return $value;
	}

	public function __construct(\Gacela\Criteria $criteria = null)
	{
		if(!is_null($criteria)) {
			$this->_buildFromCriteria($criteria);
		}
	}
}
