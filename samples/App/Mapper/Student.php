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
}
