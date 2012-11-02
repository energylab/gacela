<?php
/**
 * @author noah
 * @date 4/23/11
 * @brief
 *
*/

namespace App\Mapper;

use Gacela\Mapper\Mapper as M;

class House extends M
{

	public function findNameAsc(\Gacela\Criteria $criteria = null)
	{
		$query = $this->_source()->getQuery($criteria)
					->orderBy('houseName');

		return $this->_collection(
						$this->_source()->findAll($query, $this->_resource, $this->_inherits, $this->_dependents)
					);
	}

	public function findWithAliases()
	{
		$query = $this->_source()->getQuery();

		$query->from('houses', array('id' => 'houseId', '*'));

		exit(debug($query->assemble()));
	}
}
