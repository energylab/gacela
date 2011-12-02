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
		$this->errorCode = null;

		if(is_null($value)) {
			if($this->sequenced) {
				return true;
			} else {
				if(!$this->null) {
					$this->errorCode = self::NULL_CODE;
				}

				return $this->null;
			}
		}

		if(is_int($value) && strlen($value) <= $this->length) {
			return true;
		} else {
			if(!is_int($value)) {
				$this->errorCode = self::TYPE_CODE;
			}

			return false;
		}
	}

	public function transform($value, $in = true)
	{
		if(ctype_digit($value))
		{
			return (int) $value;
		}
		elseif(is_int($value))
		{
			return $value;
		}
	}
}
