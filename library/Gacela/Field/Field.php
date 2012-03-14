<?php
/**
 * @author Noah Goodrich
 * @date May 12, 2011
 * @brief
 *
*/

namespace Gacela\Field;

class Field {

	const NULL_CODE = 'null';
	const LENGTH_CODE = 'invalid_length';

	/**
	 * @static
	 * @param  $value
	 * @return bool
	 */
	public static function validate($meta, $value)
	{
		$class = self::_class($meta->type);

		return $class::validate($meta, $value);
	}

	/**
	 * @static
	 * @param  $value
	 * @param bool $in
	 * @return mixed
	 */
	public static function transform($meta, $value, $in = true)
	{
		$class = self::_class($meta->type);

		return $class::transform($meta, $value, $in);
	}

	protected static function _singleton()
	{
		return \Gacela::instance();
	}

	protected static function _class($type)
	{
		$class = "\\Field\\".ucfirst($type);

		return self::_singleton()->autoload($class);
	}
}
