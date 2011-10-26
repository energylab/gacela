<?php
/** 
 * @author noah
 * @date 4/23/11
 * @brief
 * 
*/

namespace App\Mapper;

use Gacela\Mapper\Mapper as M;

class House extends M {

	public function findNameAsc(\Gacela\Criteria $criteria = null)
	{
		$coll = $this->_singleton()->autoload('\\Collection');
		
		$query = $this->_source()->getQuery($criteria)
					->orderBy('houseName');
		
		return new 	$coll(
						$this,
						$this->_source()->findAll($query, $this->_resource, $this->_inherits, $this->_dependents)
					);
	}
}
