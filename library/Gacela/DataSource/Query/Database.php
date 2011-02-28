<?php
/** 
 * @author noah
 * @date 2/24/11
 * @brief
 * 
*/

namespace Gacela\DataSource\Query;

class Database {

	protected $_config;

	protected $_select = array();

	protected $_from = array();

	protected $_where = array();

	protected $_join = array();

	protected $_groupBy = array();

	protected $_orderBy = array();

	protected $_having = array();

	public function __construct(array $config)
	{
		$this->_config = (object) $config;
	}

	public function from($tableName, array $columns = array(), $schema = null)
	{
		if(is_array($tableName)) {
			$name = $tableName[0];
		} else {
			$name = $tableName;
		}

		$this->_from[$name] = array($tableName, $columns, $schema);
	}

	public function where($stmt, $value)
	{
		$this->_where[] = array($stmt, $value);
	}

	public function join()
	{
		
	}

	public function groupBy($column)
	{

	}

	public function orderBy($column, $direction)
	{

	}

	public function having()
	{

	}

	public function expr()
	{

	}

	public function assemble()
	{
		if(empty($this->_from)) {
			$this->_from[$this->_config->name] = array($this->_config->name, '*', $this->_config->dbname);
		}
	}
}
