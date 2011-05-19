<?php
/** 
 * @author noah
 * @date 4/23/11
 * @brief
 * 
*/

namespace App\Mapper;

use Gacela\Mapper\Mapper as M;

class Wizard extends M {

	protected $_dependents = array('address');
	
	protected function _load(\stdClass $data)
	{
		$primary = $this->_primaryKey($this->_primaryKey, $data);
		
		if(!property_exists($data, 'role') || is_null($data->role) || is_null($primary)) {
			return parent::_load($data);
		} elseif($data->role == 'student' && get_class($this) != 'App\Mapper\Student') {
			// Because students load from their mapper that allows them to inherit
			// from the wizards resource
			return \Gacela::instance()->loadMapper('student')->load($data);
		}
		
		$primary = join('-', array_values($primary));

		if(!isset($this->_models[$primary])) {
			$model = '\\App\\Model\\'.ucfirst($data->role);
			
			$this->_models[$primary] = new $model($data);
		}

		return $this->_models[$primary];
	}

	public function findAllWithAddress($criteria = null)
	{
		if(is_null($criteria)) {
			$criteria = new \Gacela\Criteria;
		}

		$criteria->isNotNull('wizards.addressId');

		return $this->findAll($criteria);
	}
}
