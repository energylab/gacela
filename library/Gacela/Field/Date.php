<?php
/** 
 * @author noah
 * @date 3/26/11
 * @brief
 * 
*/

namespace Gacela\Field;

class Date extends Field {

	public function validate($value)
	{
		unset($this->errorCode);
		
		if(empty($value) && $this->null) {
			return true;
		}

		if(empty($value) && !$this->null) {
			$this->errorCode = self::NULL_CODE;
			return false;
		}

		return true;
	}

	public function transform($value, $in = true)
	{
		if($in && ctype_digit($value)) {
			return date($value, 'c');
		} elseif($in && ctype_alnum($value)) {
			return $value;
		} elseif(!$in && ctype_alnum($value)) {
			return strtotime($value);
		} elseif(!$in && ctype_digit($value)) {
			return $value;
		}
	}
}
