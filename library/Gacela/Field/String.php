<?php
/** 
 * @author noah
 * @date 3/19/11
 * @brief
 * 
*/

namespace Gacela\Field;

class String extends Field {

	public function validate($value)
	{
		if(is_null($value)) return $this->null;

		if(strlen($value) <= $this->length) {
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
