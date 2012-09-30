<?php
/**
 * @author noah
 * @date 3/26/11
 *
 *
*/

namespace Gacela\Field;

class Time extends Field
{
	const FORMAT_CODE = 'invalid_format';
	const TIME_CODE = 'invalid_time';

	public static function validate($meta, $value)
	{
		if(empty($value) && $meta->null) {
			return true;
		}

		if(empty($value) && !$meta->null) {
			return self::NULL_CODE;
		}

		if(!is_string($value) || !stristr($value, ':')) {
			return self::FORMAT_CODE;
		}

		$parts = explode(':', $value);

		if(!count($parts) === 3) {
			return self::FORMAT_CODE;
		}

		foreach($parts as $k => $v) {
			if(is_null($v)) {
				return self::TIME_CODE;
			}

			if($k === 0 && ($v < 0 || $v > 24)) {
				return self::TIME_CODE;
			} elseif(in_array($k, array(1,2)) && ($v < 0 || $v > 60)) {
				return self::TIME_CODE;
			}
		}

		return true;
	}

	public static function transform($meta, $value, $in = true)
	{
		return $value;
	}
}
