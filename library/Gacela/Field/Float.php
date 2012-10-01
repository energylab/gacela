<?php
/**
 * @author Noah Goodrich
 * @date 6/9/11
 *
 *
*/

namespace Gacela\Field;

class Float extends Field
{
	const TYPE_CODE = 'invalid_float';

	public static function validate($meta, $value)
	{
		if(is_null($value)) {
			if(!$meta->null) {
				return static::NULL_CODE;
			}

			return $meta->null;
		}

		if(is_float($value) && strlen($value) <= $meta->precision) {
			return true;
		} else {
			if(!is_float($value)) {
				return static::TYPE_CODE;
			} elseif(strlen($value) >= $meta->precision) {
				return static::LENGTH_CODE;
			}

			return false;
		}
	}

	public static function transform($meta, $value, $in = true)
	{
		return (float) $value;
	}
}
