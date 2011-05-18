<?php
/** 
 * @author Noah Goodrich
 * @date May 12, 2011
 * @brief
 * 
*/

namespace Gacela\Field;

class Bool extends Field {

	const TYPE_CODE = 'invalid_bool';
	
	public function validate($value)
	{
		unset($this->errorCode);

		if(is_null($value)) {
			if(!$this->null) {
				$this->errorCode = self::NULL_CODE;
			}

			return $this->null;
		}
		
		if(!is_bool($value)) {
			$this->errorCode = self::TYPE_CODE;
			return false;
		} else {
			return true;
		}
	}

	public function transform($value, $in = true)
	{
		if($in && is_bool($value)) {
			$value === true ? $value = 1 : $value = 0;
			return $value;
		} elseif($in && ctype_digit($value)) {
			return $value;
		} elseif(!$in) {
			if(is_bool($value)) {
				return $value;
			} else {
				$value == 0 ? $value = false : $value = true;
				return $value;
			}
		}
	}
}
