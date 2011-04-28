<?php
/** 
 * @author noah
 * @date 2/24/11
 * @brief
 * 
*/

namespace Gacela\DataSource\Query;

/**
 * @class Query_Database
 */
class Database {

	protected static $_operators = array(
		'equals' => '=',
		'notEquals' => '!=',
		'lessThan' => '<',
		'greaterThan' => '>',
		'notIn' => 'NOT IN',
		'in' => "IN",
		'like' => 'LIKE',
		'notLike' => 'NOT LIKE',
		'null' => 'IS NULL',
		'notNull' => 'IS NOT NULL'
	);

	protected $_binds = array();
	
	protected $_config;

	protected $_delete = null;

	protected $_from = array();

	protected $_groupBy = array();

	protected $_having = array();

	protected $_insert = array();

	protected $_join = array();

	protected $_orderBy = array();

	protected $_select = array();

	protected $_update = array();
	
	protected $_where = array();

	private function _alias($schema)
	{
		if(is_array($schema)) {
			return key($schema);
		}

		return $schema;
	}

	private function _buildFromCriteria($criteria)
	{
		foreach($criteria as $stmt) {
			$field = $stmt[1];

			if(isset($stmt[2])) {
				$value = $stmt[2];
				$toBind = ":{$stmt[1]}";
			}


			if(in_array($stmt[0], array('equals', 'notEquals', 'lessThan', 'greaterThan'))) {
				$this->where("{$field} ".self::$_operators[$stmt[0]]." {$toBind}", array("{$toBind}" => $value));
			} elseif(in_array($stmt[0], array('in', 'notIn'))) {

			} elseif($stmt[0] == 'notNull') {
				$this->where("{$field} ".self::$_operators[$stmt[0]]);
			}
		}
	}

	private function _from()
	{
		$_from = array();
		foreach($this->_from as $from) {
			if(is_array($from[0])) {
				$_from[] = $this->_quoteIdentifier(current($from[0]))." AS ".$this->_quoteIdentifier(key($from[0]));
			} else {
				$_from[] = $this->_quoteIdentifier($from[0]);
			}
		}

		return join(', ', $_from)."\n";
	}

	private function _insert()
	{
		if(!isset($this->_insert[0])) {
			return '';
		}
		
		$name = $this->_insert[0];
		$data = $this->_insert[1];
		
		if(!isset($data[0]) || !is_array($data[0])) {
			$data = array($data);
		}

		$keys = current($data);

		if(is_object($keys)) {
			$keys = (array) $keys;
		}

		$keys = array_keys($keys);

		$sql = "INSERT INTO `{$name}` (".join(',',$keys).") VALUES\n";

		// Dynamically sets up the params to be bound
		foreach($data as $index => $row) {
			$tuple = $keys;

			array_walk($tuple, function(&$key, $k, $index) {  $key = ':'.$key.$index; }, $index);

			$sql .= "(".join(",", $tuple)."),";
		}

		// Removes the trailing comma created above.
		$sql = substr($sql, 0, strlen($sql) - 1);

		$stmt = $this->_config->db->prepare($sql);

		// Binding the params per row
		foreach($data as $index => $row) {
			foreach($row as $key => $field) {
				$stmt->bindValue($key.$index, $field);
			}
		}

		return $stmt;
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
				$join[0] = $this->_quoteIdentifier(current($join[0]))." AS ".$this->_quoteIdentifier(key($join[0]));
			} else {
				$join[0] = $this->_quoteIdentifier($join[0]);
			}

			$_join .= "{$type} JOIN {$join[0]} ON {$join[1]}\n";
		}
		
		return $_join;
	}

	private function _quoteIdentifier($identifier)
	{
		if(is_array($identifier)) exit(debug($identifier));
		
		if(strpos($identifier, '*') !== false) {
			return $identifier;
		} elseif(strpos($identifier, '.') !== false) {
			$identifier = explode('.', $identifier);

			foreach($identifier as $key => $value) {
				$identifier[$key] = $this->_quoteIdentifier($value);
			}
			
			return join('.', $identifier);
		} else {
			return "`$identifier`";
		}
	}
	
	private function _select()
	{
		$select = array();

		foreach($this->_from as $from) {
			foreach($from[1] as $item) {
				$select[] = $this->_quoteIdentifier($this->_alias($from[0]).'.'.$item);
			}
		}
		
		foreach($this->_join as $join) {
			if(count($join[2])) {
				foreach($join[2] as $item) {
					$select[] = $this->_quoteIdentifier($this->_alias($join[0]).'.'.$item);
				}
			}
		}

		return join(', ', $select)."\n";
	}

	private function _update()
	{
		if(!isset($this->_update[0])) {
			return '';
		}

		$name = $this->_update[0];
		$data = $this->_update[1];

		$sql = "UPDATE {$name} SET \n";

		foreach($data as $key => $val) {
			$sql .= $key." = :".$key.",\n";

			$this->_binds[':'.$key] = $val;
		}

		$sql = substr($sql, 0, strlen($sql) - 2);

		return $sql;
	}

	private function _where()
	{
		$_where = '';

		if(!count($this->_where)) {
			return $_where;
		}
		
		foreach($this->_where as $where) {
			$where[0] = $this->_quoteIdentifier($where[0]);

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

		if(isset($config['criteria']) && !is_null($config['criteria'])) {
			$this->_buildFromCriteria($config['criteria']);
			unset($config['criteria']);
		}
	}

	/**
	 * @return \PdoStatement
	 */
	public function assemble()
	{
		// First make sure this isn't an insert statement.
		// If it is just return the insert statement.
		$sql = $this->_insert();
		if(!empty($sql)) {
			return $sql;
		}

		// Now it might be an update statement in which case we'll skip select and from
		$sql = $this->_update();

		// If its not an insert or an update, it might also be a delete
		if(!is_null($this->_delete)) {
			$sql = "DELETE FROM {$this->_delete}\n";
		}

		if(empty($sql)) {
			$select = $this->_select();
			$from = $this->_from();
			$sql = '';

			if(!empty($select)) {
				$sql .= "SELECT {$select}\n";
			}

			if(!empty($from)) {
				$sql .= "FROM {$from}\n";
			}
		}
		
		$where = $this->_where();
		$join = $this->_join();

		if(!empty($join)) {
			$sql .= $join;
		}

		if(!empty($where)) {
			$sql .= $where;
		}
		
		$statement = $this->_config->db->prepare($sql);

		foreach($this->_binds as $key => $val) {
			$statement->bindValue($key, $val);
		}
		
		return $statement;
	}

	/**
	 * @param  $name
	 * @return Database
	 */
	public function delete($name)
	{
		$this->_delete = $name;

		return $this;
	}

	/**
	 * @param  $tableName
	 * @param array $columns
	 * @param null $schema
	 * @return Database
	 */
	public function from($tableName, array $columns = array(), $schema = null)
	{
		if(is_array($tableName)) {
			$name = current($tableName);
		} else {
			$name = $tableName;
		}
		
		if(is_null($schema)) $schema = $this->_config->database;

		if(empty($columns)) $columns = array('*');

		$this->_from[$name] = array($tableName, $columns, $schema);

		return $this;
	}

	public function groupBy($column)
	{
		return $this;
	}

	public function having($stmt, $value, $or = false)
	{
		return $this;
	}
	public function insert($tableName, $data)
	{
		$this->_insert = array($tableName, $data);

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
		if($table instanceof $this) {
			$table = $table->assemble();
		}

		$this->_join[] = array($table, $on, $columns, $type);

		return $this;
	}

	/**
	 *
	 */
	public function orderBy($column, $direction = 'ASC')
	{
		return $this;
	}

	/**
	 * @param Name of the table (resource) you wish to update
	 * @param An associative array of the fields and data to update
	 */
	public function update($tableName, $data)
	{
		$this->_update = array($tableName, $data);

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
		if($stmt instanceof $this) {
			$stmt = $stmt->assemble();
		}
		
		$this->_where[] = array($stmt, $value, $or);

		return $this;
	}
}
