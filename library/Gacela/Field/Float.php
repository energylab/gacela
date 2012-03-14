<?php
/**
 * @author Noah Goodrich
 * @date 6/9/11
 * @brief
 *
*/

namespace Gacela\Field;

class Float extends Field {

	const TYPE_CODE = 'invalid_float';

	public static function validate($meta, $value)
	{
		$this->errorCode = null;

		if(is_null($value)) {
			if(!$this->null) {
				$this->errorCode = self::NULL_CODE;
			}

			return $this->null;
		}

		if(is_float($value) && strlen($value) <= $this->precision) {
			return true;
		} else {
			if(!is_float($value)) {
				$this->errorCode = self::TYPE_CODE;
			} elseif(strlen($value) <= $this->precision) {
				$this->errorCode = self::LENGTH_CODE;
			}

			return false;
		}
	}

	public static function transform($meta, $value, $in = true)
	{
		return (float) $value;
	}
}
