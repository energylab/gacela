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
	const TYPE_CODE = 'invalid_string';

	public function validate($meta, $value)
	{
		if((is_null($value) || $value === '' || $value === false) && !$meta->null) {
			return static::NULL_CODE;
		} elseif(!is_string($value) && !(is_null($value) || $value === false)) {
			return static::TYPE_CODE;
		} elseif(strlen($value) > $meta->length) {
			return static::LENGTH_CODE;
		}

		return true;
	}

	public function transform($meta, $value, $in = true)
	{
		return $value;
	}
}
