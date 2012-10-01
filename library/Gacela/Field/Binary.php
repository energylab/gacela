<?php
/**
 * @author Noah Goodrich
 * @date May 12, 2011
 *
 *
*/

namespace Gacela\Field;

class Binary extends Field
{

	/**
	 * @static
	 * @param  $value
	 * @return bool
	 */
	public static function validate($meta, $value)
	{
		if(empty($value) && !$meta->null) {
			return static::NULL_CODE;
		}

		return true;
	}

	/**
	 * @static
	 * @param  $value
	 * @param bool $in
	 * @return mixed
	 */
	public static function transform($meta, $value, $in = true)
	{

	}
}
