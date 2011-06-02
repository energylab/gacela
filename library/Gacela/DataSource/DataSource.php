<?php
/** 
 * @author Noah Goodrich
 * @date May 7, 2011
 * @brief
 * 
*/

namespace Gacela\DataSource;

abstract class DataSource implements iDataSource {

	protected $_config = array();

	abstract protected function _driver();

	public function beginTransaction()
	{
		return false;
	}

	public function commitTransaction()
	{
		return false;
	}

	/**
	 * @see \Gacela\DataSource\iDataSource::loadResource()
	 */
	public function loadResource($name)
	{
		$cached = \Gacela::instance()->cache('resource_'.$name);

		if($cached === false)  {
			$cached = new Resource($this->_driver()->load($this->_conn, $name, $this->_config->schema));

			\Gacela::instance()->cache('resource_'.$name, $cached);
		}
		
		return $cached;
	}

	public function rollbackTransaction()
	{
		return false;
	}
}

