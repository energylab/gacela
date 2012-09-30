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

	public static function validate($meta, $value)
	{
		if(is_null($value)) {
			if(!$meta->null) {
				return self::NULL_CODE;
			}

			return $meta->null;
		}


	}

	public static function transform($meta, $value, $in = true)
	{

	}
}
