<?php
/** 
 * @author noah
 * @date 3/19/11
 * @brief
 * 
*/

namespace Gacela\Field;

class Bool extends Field {

	public function validate($value)
	{
		$value = $this->transform($value, false);

		if(is_null($value)) {
			return $this->null;
		}

		if(!is_bool($value)) {
			return false;
		} else {
			return true;
		}
	}

	public function transform($value, $in = true)
	{
		if($in) {
			$value === true ? $value = 1 : $value = 0;
			return $value;
		} else {
			if(is_bool($value)) {
				return $value;
			} else {
				$value == 0 ? $value = false : $value = true;
				return $value;
			}
		}
	}
}
