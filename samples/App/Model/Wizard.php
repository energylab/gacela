<?php
/** 
 * @author noah
 * @date 4/23/11
 * @brief
 * 
*/

namespace App\Model;

use Gacela\Model\Model as M;

class Wizard extends M {

	protected function _getFullName()
	{
		return $this->fname.' '.$this->lname;
	}

	protected function _setFullName($value)
	{
		$this->fname = trim(substr($value, 0, strpos($value, ' ')));
		$this->lname = trim(substr($value, strpos($value, ' ')));
	}
}
