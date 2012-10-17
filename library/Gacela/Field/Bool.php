<?php
/**
 * @author Noah Goodrich
 * @date May 12, 2011
 *
 *
*/

namespace Gacela\Field;

class Bool extends Field
{
	const TYPE_CODE = 'invalid_bool';

	public function validate($meta, $value)
	{
		if(is_null($value) && !$meta->null) {
			return static::NULL_CODE;
		} elseif(!is_null($value) && !is_bool($value)) {
			return  static::TYPE_CODE;
		}

		return true;
	}

	public function transform($meta, $value, $in = true)
	{
		if($in && is_bool($value)) {
			$value === true ? $value = 1 : $value = 0;
		} elseif($in && ctype_digit($value)) {
			$value = (int) $value;
		} elseif(!$in && !is_bool($value)) {
			$value = (bool) $value;
		}

		return $value;
	}
}
