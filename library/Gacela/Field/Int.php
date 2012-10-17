<?php
/**
 * @author noah
 * @date 3/19/11
 *
 *
*/

namespace Gacela\Field;

/**
 * Error Codes - 'null', 'not_int'
 */
class Int extends Field
{
	const TYPE_CODE = 'invalid_int';
	const BOUNDS_CODE = 'out_of_bounds';

	/**
	 * @static
	 * @param $meta
	 * @param $value
	 * @return bool|string
	 */
	public function validate($meta, $value)
	{
		if(is_null($value)) {
			if($meta->sequenced) {
				return true;
			} else {
				if(!$meta->null) {
					return static::NULL_CODE;
				}

				return $meta->null;
			}
		}

		if(!is_int($value)) {
			return static::TYPE_CODE;
		} elseif(strlen(abs($value)) > $meta->length) {
			return static::LENGTH_CODE;
		} elseif($value < $meta->min || $value > $meta->max) {
			return static::BOUNDS_CODE;
		}

		return true;
	}

	/**
	 * @static
	 * @param $meta
	 * @param $value
	 * @param bool $in
	 * @return int|mixed|null
	 */
	public function transform($meta, $value, $in = true)
	{
		if(!is_int($value) && is_numeric($value)) {
			$value = (int) $value;
		} elseif($value === '' || $value === false) {
			$value = null;
		}

		return $value;
	}
}
