<?php
/**
 * @author noah
 * @date 3/26/11
 * @brief
 *
*/

namespace Gacela\Field;

class Date extends Field {

	public static function validate($meta, $value)
	{
		if(empty($value) && $meta->null) {
			return true;
		}

		if(empty($value) && !$meta->null) {
			return self::NULL_CODE;
		}

		return true;
	}

	public static function transform($meta, $value, $in = true)
	{
		if($in && ctype_digit($value)) {
			return date('c', $value);
		} elseif($in && !ctype_digit($value)) {
			return $value;
		} elseif(!$in && !ctype_digit($value)) {
			if(stripos($value, 'current') !== false || (stripos($meta->default, 'current') !== false && empty($value))) {
				return time();
			}

			$rs = strtotime($value);

			if($rs === false) {
				return null;
			}

			return $rs;
		} elseif(!$in && ctype_digit($value)) {
			return $value;
		}
	}
}
