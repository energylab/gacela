<?php
/**
 * @author noah
 * @date 4/23/11
 * @brief
 *
*/

namespace App\Mapper;

use Gacela\Mapper\Mapper as M;

class Wizard extends M
{
	protected $_dependents = array('address');

	protected function _load(\stdClass $data)
	{
		if(!empty($data->role) && $data->role == 'student' && get_class($this) != 'App\Mapper\Student') {

			// Because students load from their mapper that allows them to inherit
			// from the wizards resource
			return \Gacela::instance()->loadMapper('student')->load($data);
		}

		if(!empty($data->role)) {
			$model = ucfirst($data->role);
		} else {
			$model = 'Wizard';
		}

		$model = '\\App\\Model\\'.$model;

		return new $model($this->_gacela(), $this, $data);
	}
}
