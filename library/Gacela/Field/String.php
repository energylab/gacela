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
		unset($this->errorCode);

		if(empty($value)) {
			if(!$this->null) {
				$this->errorCode = self::NULL_CODE;
			}

			return $this->null;
		}

		if(strlen($value) <= $this->length) {
			return true;
		} else {
			$this->errorCode = self::LENGTH_CODE;
			return false;
		}
	}

	public function transform($value, $in = true)
	{
		return $value;
	}
}
