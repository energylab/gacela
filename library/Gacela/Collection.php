<?php
/** 
 * @author noah
 * @date 3/7/11
 * @brief
 * 
*/

namespace \Gacela;

class Collection 
{
	protected $_mapper;

	protected $_data;

	public function __construct(array $data)
	{
		
	}

	public function find($id)
	{
		return $this->search($id);
	}

	public function search($value, $key = null)
	{
		if(is_null($key)) {

		}
	}
}
