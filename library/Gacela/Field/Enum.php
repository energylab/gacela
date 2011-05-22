<?php
/** 
 * @author Noah Goodrich
 * @date May 12, 2011
 * @brief
 * 
*/

namespace Gacela\Field;

class Enum extends Field{

	const VALUE_CODE = 'invalid_value';

	public function validate($value)
	{
		unset($this->errorCode);
		
		if(empty($value)) {
			if(!$this->null) {
				$this->errorCode = self::NULL_CODE;
			}
			
			return $this->null;
		}

		if(in_array($value, $this->values)) {
			return true;
		} else {
			$this->errorCode = self::VALUE_CODE;
			return false;
		}
	}

	public function transform($value, $in = true)
	{
		return $value;
	}
}
