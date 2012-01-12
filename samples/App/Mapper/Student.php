<?php
/**
 * @author noah
 * @date 4/23/11
 * @brief
 *
*/

namespace App\Mapper;

use Gacela\Mapper\Mapper as M;

class Student extends M {

	protected $_dependents = array('address');

	public function findByHouse(\Gacela\Criteria $criteria = null)
	{
		$query = $this->_source()->getQuery($criteria);

		$query->from(array('w' => 'wizards'))
				->join(array('s' => 'students'), 'w.wizardId = s.wizardId', array('*'))
				->in('s.houseId', array(1,2,3));

		exit(debug($this->_source()->query($this->_resource, $query)));
	}
}
