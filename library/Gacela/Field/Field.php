<?php
/**
 * @author Noah Goodrich
 * @date May 12, 2011
 *
 *
*/

namespace Gacela\Field;

abstract class Field
{
	const NULL_CODE = 'null';
	const LENGTH_CODE = 'invalid_length';

	/**
	 * @param  $value
	 * @return bool
	 */
	abstract public function validate($meta, $value);

	/**
	 * @param  $value
	 * @param bool $in
	 * @return mixed
	 */
	abstract public function transform($meta, $value, $in = true);

}
