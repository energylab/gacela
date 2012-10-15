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
		$value = trim($value);

		$this->fname = substr($value, 0, strpos($value, ' '));
		$this->lname = substr($value, strpos($value, ' '));
	}
}
