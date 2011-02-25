<?php
/** 
 * @author noah
 * @date Oct 4, 2010
 * @brief
 * 
*/

namespace Gacela\Mapper;

use Gacela as G;

abstract class Mapper implements iMapper {

	protected $_sources = array('db');

	protected $_primary_key;

	protected function _load()
	{
		
	}

	public function __construct()
	{
		foreach($this->_sources as $i => $source) {
			$this->_sources[$source] = Gacela::instance()->getDataSource($source);

			unset($this->_sources[$i]);
		}

	}

	public function find($id)
	{
		return $this->_load($id);
	}

	public function find_all(G\Criteria $criteria)
	{
		
	}
}
