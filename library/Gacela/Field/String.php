<?php
/**
 * @author noah
 * @date 3/19/11
 *
 *
*/

namespace Gacela\Field;

class String extends Field
{

	public static function validate($meta, $value)
	{
		if(empty($value) && !$meta->null) {
			return static::NULL_CODE;
		} elseif(strlen($value) > $meta->length) {
			return static::LENGTH_CODE;
		}

		return true;
	}

	public static function transform($meta, $value, $in = true)
	{
		return $value;
	}
}
