<?php
/**
 * @author noah
 * @date 3/19/11
 *
 *
*/

namespace Gacela\Field;

class Guid extends Field
{
	const TYPE_CODE = 'invalid_guid';

	public function validate($meta, $value)
	{
		if(!is_string($value) && !empty($value)) {
			return static::TYPE_CODE;
		} elseif(!empty($value) && strlen($value) != $meta->length) {
			return static::LENGTH_CODE;
		}

		return true;
	}

	public function transform($meta, $value, $in = true)
	{
		return $value;
	}
}
