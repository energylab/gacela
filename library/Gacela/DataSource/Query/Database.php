<?php
/** 
 * @author noah
 * @date 2/24/11
 * @brief
 * 
*/

namespace Gacela\DataSource\Query;

class Database {

	protected $_binds = array();
	
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

		return join(', ', $_from)."\n";
	}

	private function _join()
	{
		$_join = '';

		if(!count($this->_join)) {
			return $_join;
		}

		foreach($this->_join as $join) {
			$type = strtoupper($join[3]);

			if(is_array($join[0])) {
				$join[0] = "{$join[0][0]} AS {$join[0][1]}";
			}

			$_join .= "{$type} JOIN {$join[0]} ON {$on}\n";
		}

		return $_join;
	}

	private function _select()
	{
		$select = array();
		foreach($this->_from as $from) {
			if(is_array($from[1])) {
				$select = array_merge($from[1], $select);
			} else {
				$select[] = $from[1];
			}
		}

		foreach($this->_join as $join) {
			
		}

		return join(', ', $select)."\n";
	}

	private function _where()
	{
		$_where = '';

		if(!count($this->_where)) {
			return $_where;
		}

		foreach($this->_where as $where) {
			if(empty($_where)) {
				$_where = "WHERE ({$where[0]})";
			} else {
				// Check for OR statements
				if($where[2]) {
					$_where .= "OR ({$where[0]})";
				} else {
					$_where .= "AND ({$where[0]})";
				}
			}

			if(count($where[1])) {
				foreach($where[1] as $param => $val) {
					$this->_binds[$param] = $val;
				}
			}
		}

		return $_where;
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

	/**
	 * @param  $stmt
	 * @param array $value
	 * @param bool $or
	 * @return Query\Database
	 */
	public function where($stmt, array $value = array(), $or = false)
	{
		$this->_where[] = array($stmt, $value, $or);

		return $this;
	}

	/**
	 * @param  string|array $table
	 * @param  string $on
	 * @param array $columns
	 * @param string $type
	 * @return Query\Database
	 */
	public function join($table, $on, array $columns = array(), $type = 'inner')
	{
		$this->_join[] = array($table, $on, $columns, $type);
		
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

	public function assemble()
	{
		$select = $this->_select();
		$from = $this->_from();
		$where = $this->_where();
		$join = $this->_join();
		
		$sql = "
				SELECT {$select}
				FROM {$from}
				{$join}
				{$where}
			";
		
		$statement = $this->_config->db->prepare($sql);

		foreach($this->_binds as $key => $val) {
			$statement->bindValue($key, $val);
		}

		return $statement;
	}
}
