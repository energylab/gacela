<?php
/**
 * @author Noah Goodrich
 * @date 6/9/11
 *
 *
*/

namespace Gacela\Field;

class Float extends Field
{
	const TYPE_CODE = 'invalid_float';

	public function validate($meta, $value)
	{
		if(is_null($value) && !$meta->null) {
			return static::NULL_CODE;
		} elseif(!is_null($value) && !is_double($value)) {
			return static::TYPE_CODE;
		}

		return true;
	}

	public function transform($meta, $value, $in = true)
	{
		if(!is_double($value) && is_numeric($value)) {
			return (double) $value;
		} elseif($value === false || $value === '') {
			return null;
		}

		return $value;
	}
}
