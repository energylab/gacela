<?php
/**
 * @author Noah Goodrich
 * @date May 12, 2011
 *
 *
*/

namespace Gacela\Field;

class Enum extends Field
{
	const VALUE_CODE = 'invalid_value';

	public function validate($meta, $value)
	{
		if(is_null($value) && !$meta->null) {
			return static::NULL_CODE;
		} elseif(!is_null($value) && !in_array($value, $meta->values)) {
			return  static::VALUE_CODE;
		}

		return true;
	}

	public function transform($meta, $value, $in = true)
	{
		return $value;
	}
}
