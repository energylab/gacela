<?php

namespace Gacela\Collection;

/**
 * User: noah
 * Date: 8/28/12
 * Time: 7:32 AM
 */
class Statement extends Collection
{

	/**
	 * @param \Gacela\Mapper\Mapper $mapper
	 * @param \PdoStatement $data
	 */
	public function __construct(\Gacela\Mapper\Mapper $mapper, $data)
	{
		parent::__construct($mapper, $data);

		$this->_count = $data->rowCount();
	}
}
