<?php
/**
 * @author noah
 * @date 3/26/11
 *
 *
*/

namespace Gacela\Field;

class Date extends Field
{
	const TYPE_CODE = 'invalid_date';

	protected function _isTimestamp($value)
	{
		return (string) (int) $value === $value && $value <= PHP_INT_MAX && $value >= ~PHP_INT_MAX;
	}

	public function validate($meta, $value)
	{
		$return = true;

		$value = (string) $value;

		if(empty($value) && !$meta->null) {
			$return = static::NULL_CODE;
		} elseif(!empty($value) && !$this->_isTimestamp($value)) {
			$return = static::TYPE_CODE;
		}

		return $return;
	}

	public function transform($meta, $value, $in = true)
	{
		if($in && is_numeric($value)) {
			$value = date('c', $value);
		} elseif(!$in && !is_numeric($value)) {
			if(stripos($value, 'current') !== false || (stripos($meta->default, 'current') !== false && empty($value))) {
				$value = time();
			} else {
				$value = strtotime($value);

				if($value === false) {
					return null;
				}
			}
		}

		return $value;
	}
}
