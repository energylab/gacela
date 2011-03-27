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
		if(is_null($value) && $this->_meta->null) {
			return true;
		}

		if(is_null($value) && !$this->_meta->null) {
			return false;
		}

		return true;
	}

	public function transform($value, $in = true)
	{
		if($in) {
			return date($value, 'c');
		} else {
			return strtotime($value);
		}
	}
}
