<?php
/** 
 * @author noah
 * @date 10/23/10
 * @brief
 * 
*/

namespace Gacela;

use Gacela\DataSource\Adapter AS A;

class DataSource implements A\iAdapter {

	/*
	 * @var
	 *
	 * @description Type of data source. Determines which DataSource Adapter to use
	 * 
	 */
	protected $_type;

	protected $_config = array();

	protected $_adapter;

	/**
	 * @return \Gacela\DataSource\Adapter\Adapter
	 */
	protected function _adapter()
	{
		if(is_null($this->_adapter)) {
			$adapter = "\\Gacela\\DataSource\\Adapter\\".ucfirst($this->_type);

			$this->_adapter = new $adapter($this->_config);
		}

		return $this->_adapter;
	}

	public function __construct(array $config)
	{
		$this->_type = $config['type'];

		$this->_config = $config;
	}

	public function query()
	{

	}

	public function insert()
	{

	}

	public function update()
	{

	}

	public function delete()
	{

	}

	public function select()
	{

	}

	/**
	 * @return \Gacela\DataSource\Adapter\Query
	 */
	public function getQuery()
	{
		return $this->_adapter()->getQuery();
	}

	public function getResource($name)
	{
		return $this->_adapter()->getResource($name);
	}
}
