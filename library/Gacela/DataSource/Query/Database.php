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

	private function _from()
	{
		$_from = array();
		foreach($this->_from as $from) {
			if(is_array($from[0])) {
				$_from[] = "{$from[0][1]} AS {$from[0][0]}";
			} else {
				$_from[] = "{$from[0]}";
			}
		}

		return join(', ', $_from);
	}

	private function _select()
	{
		$select = array();
		foreach($this->_from as $from) {
			if(is_array($from[1])) {
				$select = array_merge($from[1]);
			} else {
				$select[] = $from[1];
			}
		}

		return join(', ', $select);
	}

	private function _where()
	{
		foreach($this->_where as $where) {
			
		}
	}
	
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
		
		if(is_null($schema)) $schema = $this->_config->database;

		if(empty($columns)) $columns = array('*');

		$this->_from[$name] = array($tableName, $columns, $schema);

		return $this;
	}

	public function where($stmt, $value, $or = false)
	{
		$this->_where[] = array($stmt, $value, $or);

		return $this;
	}

	public function join()
	{
		return $this;
	}

	public function groupBy($column)
	{
		return $this;
	}

	public function orderBy($column, $direction = 'ASC')
	{
		return $this;
	}

	public function having($stmt, $value, $or = false)
	{
		return $this;
	}

	public function expr()
	{
		$expr = null;
		
		return $expr;
	}

	public function assemble()
	{
		$select = $this->_select();
		$from = $this->_from();

		$sql = "SELECT {$select} FROM {$from}";
		
		$statement = $this->_config->db->prepare($sql);

		return $statement;
	}
}
