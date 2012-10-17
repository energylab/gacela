<?php
/**
 * @author Noah Goodrich
 * @date May 12, 2011
 *
 *
*/

namespace Gacela\Field;

class Set extends Field
{
	const VALUE_CODE = 'invalid_value';

	public function validate($meta, $value)
	{

		if(is_null($value) && !$meta->null) {
			return static::NULL_CODE;
		} elseif(!is_null($value) && !is_array($value) && !in_array($value, $meta->values)) {
			return static::VALUE_CODE;
		} elseif(is_array($value) && count(array_diff($value, $meta->values)) > 0) {
			return  static::VALUE_CODE;
		}

		return true;
	}

	public function transform($meta, $value, $in = true)
	{
		// For the database, it translates to a comma delimited string for sets
		if($in) {
			if(is_scalar($value)) {
				$value = array($value);
			}

			return join(',', $value);
		}

		return $value;
	}
}
