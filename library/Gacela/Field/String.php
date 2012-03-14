<?php
/**
 * @author noah
 * @date 3/19/11
 * @brief
 *
*/

namespace Gacela\Field;

class String extends Field {

	public static function validate($meta, $value)
	{
		if(empty($value)) {
			if(!$meta->null) {
				return self::NULL_CODE;
			}

			return $meta->null;
		}

		if(strlen($value) <= $meta->length || is_null($meta->length)) {
			return true;
		} else {
			return self::LENGTH_CODE;
		}
	}

	public static function transform($meta, $value, $in = true)
	{
		return $value;
	}
}
