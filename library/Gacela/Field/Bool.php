<?php
/**
 * @author Noah Goodrich
 * @date May 12, 2011
 * @brief
 *
*/

namespace Gacela\Field;

class Bool extends Field {

	const TYPE_CODE = 'invalid_bool';

	public static function validate($meta, $value)
	{
		if(is_null($value)) {
			if(!$meta->null) {
				return self::NULL_CODE;
			}

			return $meta->null;
		}

		if(!is_bool($value)) {
			return  self::TYPE_CODE;
		} else {
			return true;
		}
	}

	public static function transform($meta, $value, $in = true)
	{
		if($in && is_bool($value)) {
			$value === true ? $value = 1 : $value = 0;
			return $value;
		} elseif($in && ctype_digit($value)) {
			return $value;
		} elseif(!$in) {
			if(is_bool($value)) {
				return $value;
			} else {
				$value == 0 ? $value = false : $value = true;
				return $value;
			}
		}
	}
}
