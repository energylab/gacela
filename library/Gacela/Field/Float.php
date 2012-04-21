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
		if(is_null($value)) {
			if(!$this->null) {
				return self::NULL_CODE;
			}

			return $meta->null;
		}

		if(is_float($value) && strlen($value) <= $meta->precision) {
			return true;
		} else {
			if(!is_float($value)) {
				return self::TYPE_CODE;
			} elseif(strlen($value) <= $meta->precision) {
				return self::LENGTH_CODE;
			}

			return false;
		}
	}

	public static function transform($meta, $value, $in = true)
	{
		return (float) $value;
	}
}
