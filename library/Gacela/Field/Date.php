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
		return preg_match();
	}

	public function validate($meta, $value)
	{
		$pattern = '/^([\+-]?\d{4}(?!\d{2}\b))((-?)((0[1-9]|1[0-2])(\3([12]\d|0[1-9]|3[01]))?|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d|[12]\d{2}|3([0-5]\d|6[1-6])))([T\s]((([01]\d|2[0-3])((:?)[0-5]\d)?|24\:?00)([\.,]\d+(?!:))?)?(\17[0-5]\d([\.,]\d+)?)?([zZ]|([\+-])([01]\d|2[0-3]):?([0-5]\d)?)?)?)?$/';
		
		$return = true;

		$value = (string) $value;

		if(empty($value) && !$meta->null) {
			$return = static::NULL_CODE;
		} elseif(!empty($value) && preg_match($pattern, $value) < 1) {
			$return = static::TYPE_CODE;
		}

		return $return;
	}

	public function transform($meta, $value, $in = true)
	{
		if($in && is_numeric($value)) {
			$value = date('c', $value);
		} elseif($in && stripos($value, 'current') !== false || (stripos($meta->default, 'current') !== false && empty($value))) {
			$value = date('c', time());
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
