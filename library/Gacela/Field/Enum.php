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

	public static function validate($meta, $value)
	{
		if(is_null($value)) {
			if(!$meta->null) {
				return self::NULL_CODE;
			}

			return $meta->null;
		}

		if(in_array($value, $meta->values)) {
			return true;
		} else {
			return  self::VALUE_CODE;
		}
	}

	public static function transform($meta, $value, $in = true)
	{
		return $value;
	}
}
