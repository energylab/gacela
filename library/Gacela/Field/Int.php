<?php
/** 
 * @author noah
 * @date 3/19/11
 * @brief
 * 
*/

namespace Gacela\Field;

class Int extends Field {

	public function validate($value)
	{
		if(is_null($value)) {
			if($this->sequenced) {
				return true;
			} else {
				return $this->null;
			}
		}

		if(ctype_digit($value) && strlen($value) <= $this->length) {
			return true;
		} else {
			return false;
		}
	}

	public function transform($value, $in = true)
	{
		return $value;
	}
}
