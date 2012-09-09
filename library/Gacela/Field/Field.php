<?php
/**
 * @author Noah Goodrich
 * @date May 12, 2011
 *
 *
*/

namespace Gacela\Field;

class Field {

	protected static $_classes = array();

	const NULL_CODE = 'null';
	const LENGTH_CODE = 'invalid_length';

	/**
	 * @static
	 * @param  $value
	 * @return bool
	 */
	public static function validate($meta, $value)
	{
		$class = static::_class($meta->type);

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
		$class = static::_class($meta->type);

		return $class::transform($meta, $value, $in);
	}

	protected static function _singleton()
	{
		return \Gacela::instance();
	}

	protected static function _class($type)
	{
		if(!isset(static::$_classes[$type])) {
			static::$_classes[$type] = static::_singleton()->autoload("\\Field\\".ucfirst($type));
		}

		return static::$_classes[$type];
	}
}
