<?php
/**
 * @author noah
 * @date 3/26/11
 *
 *
*/

namespace Gacela\Field;

class Date extends Field
{
	const TYPE_CODE = 'invalid_date';

	public static function validate($meta, $value)
	{
		if(empty($value) && !$meta->null) {
			return self::NULL_CODE;
		} elseif(!is_null($value) && ((string) (int) $value !== $value || $value <= PHP_INT_MAX || $value >= ~PHP_INT_MAX)) {
			return self::TYPE_CODE;
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
