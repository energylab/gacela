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
		$return = true;

		$value = (string) $value;

		if(empty($value) && !$meta->null) {
			$return = static::NULL_CODE;
		} elseif(!empty($value) && ((string) (int) $value !== $value || $value > PHP_INT_MAX || $value < ~PHP_INT_MAX)) {
			$return = static::TYPE_CODE;
		}

		return $return;
	}

	public static function transform($meta, $value, $in = true)
	{
		if($in && is_numeric($value)) {
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
