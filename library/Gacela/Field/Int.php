<?php
/** 
 * @author noah
 * @date 3/19/11
 * @brief
 * 
*/

namespace Gacela\Field;

/**
 * Error Codes - 'null', 'not_int'
 */
class Int extends Field {

	const TYPE_CODE = 'invalid_int';
	
	public function validate($value)
	{
		unset($this->errorCode);

		if(empty($value)) {
			if($this->sequenced) {
				return true;
			} else {
				if(!$this->null) {
					$this->errorCode = self::NULL_CODE;
				}
				
				return $this->null;
			}
		}

		if(ctype_digit($value) && strlen($value) <= $this->length) {
			return true;
		} else {
			if(!ctype_digit($value)) {
				$this->errorCode = self::TYPE_CODE;
			} elseif(strlen($value) <= $this->length) {
				$this->errorCode = self::LENGTH_CODE;
			}

			return false;
		}
	}

	public function transform($value, $in = true)
	{
		return $value;
	}
}
