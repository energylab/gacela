<?php
/**
 * @author noah
 * @date 3/26/11
 * @brief
 *
*/

namespace Gacela\DataSource\Query;

abstract class Query {

	protected function _cast($value)
	{
		if(ctype_digit($value))
		{
			return (int) $value;
		}

		return $value;
	}

}
