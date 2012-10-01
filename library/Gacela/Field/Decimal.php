<?php
/**
 * @author Noah Goodrich
 * @date 6/9/11
 *
 *
*/

namespace Gacela\Field;

class Decimal extends Field
{
	const TYPE_CODE = 'invalid_decimal';

	const SCALE_CODE = 'invalid_scale';

	public static function validate($meta, $value)
	{
		if(is_null($value) && !$meta->null) {
			return static::NULL_CODE;
		} elseif(!is_numeric($value) && !is_null($value)) {
			return static::TYPE_CODE;
		} elseif(strlen($value) > $meta->length) {
			return static::LENGTH_CODE;
		} elseif(($pos = strpos($value, '.')) !== false && strlen(substr($value, $pos+1)) > $meta->scale) {
			return static::SCALE_CODE;
		}

		return true;
	}

	public static function transform($meta, $value, $in = true)
	{
		return (string) $value;
	}
}
