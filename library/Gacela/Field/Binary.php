<?php
/**
 * @author Noah Goodrich
 * @date May 12, 2011
 * Borrowing from this implementation for Doctrine2:
 * https://gist.github.com/525030/38a0dd6a70e58f39e964ec53c746457dd37a5f58
 *
 *
 *
*/

namespace Gacela\Field;

class Binary extends Field
{
	public function validate($meta, $value)
	{
		if(empty($value) && !$meta->null) {
			return self::NULL_CODE;
		} elseif(!empty($value) && strlen($value) > $meta->length) {
			return self::LENGTH_CODE;
		}

		return true;
	}

	public function transform($meta, $value, $in = true)
	{
		return $value;
	}
}
