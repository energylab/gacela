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
			return date('c', $value);
		} elseif($in && !ctype_digit($value)) {
			return $value;
		} elseif(!$in && !ctype_digit($value)) {
			if(stripos($value, 'current') !== false || (stripos($this->default, 'current') !== false && empty($value))) {
				return time();
			}

			$rs = strtotime($value);

			if($rs === false) {
				return null;
			}

			return $rs;
		} elseif(!$in && ctype_digit($value)) {
			return $value;
		}
	}
}
