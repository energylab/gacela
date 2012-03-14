<?php
/**
 * @author noah
 * @date 3/19/11
 * @brief
 *
*/

namespace Gacela\Field;

/**
 * Error Codes - 'null', 'not_int'
 */
class Int extends Field {

	const TYPE_CODE = 'invalid_int';

	public static function validate($meta, $value)
	{
		if(is_null($value)) {
			if($meta->sequenced) {
				return true;
			} else {
				if(!$meta->null) {
					return self::NULL_CODE;
				}

				return $meta->null;
			}
		}

		if(is_int($value) && strlen($value) <= $meta->length) {
			return true;
		} else {
			if(!is_int($value)) {
				return self::TYPE_CODE;
			}

			return false;
		}
	}

	public static function transform($meta, $value, $in = true)
	{
		if(ctype_digit($value))
		{
			return (int) $value;
		}
		elseif(is_int($value))
		{
			return $value;
		}
	}
}
