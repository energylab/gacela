<?php
/**
 * @author Noah Goodrich
 * @date May 12, 2011
 *
 *
*/

namespace Test\Field;

abstract class Field extends \Gacela\Field\Field
{

	/**
	 * @param  $value
	 * @return bool
	 */
	public function validate($meta, $value) {}

	/**
	 * @param  $value
	 * @param bool $in
	 * @return mixed
	 */
	public function transform($meta, $value, $in = true) {}

}
