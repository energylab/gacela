<?php
/**
 * @author noah
 * @date 2/24/11
 *
 *
*/

namespace Gacela\DataSource\Query;

class Sql extends Query
{

	protected  $_operators = array(
		'equals' => '=',
		'notEquals' => '!=',
		'lessThan' => '<',
		'lessThanOrEqualTo' => '<=',
		'greaterThan' => '>',
		'greaterThanOrEqualTo' => '>=',
		'notIn' => 'NOT IN',
		'in' => "IN",
		'like' => 'LIKE',
		'notLike' => 'NOT LIKE',
		'null' => 'IS NULL',
		'notNull' => 'IS NOT NULL'
	);

	protected $_delete = null;

	protected $_from = array();

	protected $_groupBy = array();

	protected $_having = array();

	protected $_insert = array();

	protected $_limit = array();

	protected $_join = array();

	protected $_orderBy = array();

	protected $_schema = null;

	protected $_select = array();

	protected $_sql = null;

	protected $_union = array();

	protected $_update = array();

	protected $_where = array();

	protected function _alias($schema)
	{
		if(is_array($schema)) {
			return key($schema);
		}

		return $schema;
	}

	protected function _is_function($value)
	{
		$value = trim($value);

		if(strrpos($value, ')') == strlen($value)-1 AND strpos($value, '(') !== false) {
			return true;
		}

		return false;
	}

	protected function _param($field, $args)
	{
		return ':'.preg_replace("/[-\.:\$\^\*& ]/", '_', $field).'_'.sha1($args);
	}

	protected function _from()
	{
		$_from = array();
		foreach($this->_from as $from) {
			if(is_array($from[0])) {
				if(current($from[0]) instanceof Sql) {
					list($table, $args) = current($from[0])->assemble();

					$table = "(\n".$table.")";

					$this->bind($args);
				} else {
					$table = $this->_quoteIdentifier(current($from[0]));
				}

				$alias = $this->_quoteIdentifier(key($from[0]));

				$_from[] = $table." AS ".$alias;
			} else {
				$_from[] = $this->_quoteIdentifier($from[0]);
			}
		}

		return join(', ', $_from)."\n";
	}

	protected function _group()
	{
		if(!count($this->_groupBy)) {
			return '';
		}

		$sql = 'GROUP BY ';

		$i=0;
		foreach($this->_groupBy as $field) {
			if ($i) {
				$sql .= ',';
			}

			$sql .= $this->_quoteIdentifier($field);

			$i++;
		}

		if(strlen($sql) > 0) {
			$sql .= "\n";
		}

		return $sql;
	}

	protected function _insert()
	{
		if(!isset($this->_insert[0])) {
			return '';
		}

		$name = $this->_insert[0];
		$data = $this->_insert[1];

		if(!isset($data[0]) || !is_array($data[0])) {
			$data = array($data);
		}

		$tmp = current($data);

		if(is_object($tmp)) {
			$tmp = (array) $tmp;
		}

		$keys = array();

		foreach($tmp as $key => $val) {
			$keys[] = $this->_quoteIdentifier($key);
		}

		array_walk($this->_insert[2], function(&$val) { $val = strtoupper($val); });

		$modifiers = !empty($this->_insert[2]) ? join(' ', $this->_insert[2]) : '';

		$sql = "INSERT {$modifiers} INTO `{$name}` (".join(',',$keys).") VALUES\n";

		// Dynamically sets up the params to be bound
		foreach($data as $index => $row) {
			$tuple = array_keys($tmp);

			array_walk($tuple, function(&$key, $k, $index) {  $key = ':'.$key.$index; }, $index);

			$sql .= "(".join(",", $tuple)."),\n";
		}

		// Removes the trailing comma created above.
		$sql = substr($sql, 0, strlen($sql) - 2);

		// Binding the params per row
		foreach($data as $index => $row) {
			foreach($row as $key => $field) {
				$this->_binds[':'.$key.$index] = $field;
			}
		}

		return array($sql, $this->_binds);
	}

	protected function _join()
	{
		$_join = '';

		if(!count($this->_join)) {
			return $_join;
		}

		foreach($this->_join as $join) {
			$type = strtoupper($join[3]);

			if(is_array($join[0])) {
				if(current($join[0]) instanceof Sql) {
					list($table, $args) = current($join[0])->assemble();

					$table = "(\n".$table.")";

					$this->bind($args);
				} else {
					$table = $this->_quoteIdentifier(current($join[0]));
				}

				$alias = $this->_quoteIdentifier(key($join[0]));

				$join[0] = $table." AS ".$alias;
			} else {
				$join[0] = $this->_quoteIdentifier($join[0]);
			}

			if(is_array($join[1])) {
				$joins = $join[1];
				$on = '';

				foreach($joins as $key => $val) {
					if(!empty($on)) {
						$on .= ' AND ';
					}

					$on .= $this->_quoteIdentifier($key). ' = ' . $this->_quoteIdentifier($val);
				}
			} else {
				$on = $join[1];
			}

			if($type == 'STRAIGHT') {
				$type = 'STRAIGHT_JOIN';
			} else {
				$type = $type.' JOIN';
			}

			$_join .= "{$type} {$join[0]} ON {$on}\n";
		}

		return $_join;
	}

	protected function _order()
	{
		if(!count($this->_orderBy)) {
			return '';
		}

		$sql = 'ORDER BY ';

		foreach($this->_orderBy as $field => $dir) {
			$sql .= $this->_quoteIdentifier($field).' '.$dir.',';
		}

		$sql = substr($sql, 0, strlen($sql)-1);

		if(strlen($sql) > 0) {
			$sql .= "\n";
		}

		return $sql;
	}

	protected function _quoteIdentifier($identifier)
	{
		if(!is_string($identifier)) {

			$type = is_array($identifier) ? 'array' : get_class($identifier);

			throw new \Exception('Identifier in _quoteIdentifier is a(n) '.$type.'!');
		}

		if(strpos($identifier, '*') !== false) {
			return $identifier;
		} elseif($this->_is_function($identifier)) {
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

	protected function _select()
	{
		$select = array();

		foreach($this->_from as $from) {
			foreach($from[1] as $alias => $field) {
				if(preg_match('#[\.|\(\)]#', $field) === 0) {
					$field = $this->_alias($from[0]).'.'.$field;
				}

				if(is_int($alias)) {
					$select[] = $this->_quoteIdentifier($field);
				} else {
					$select[] = $this->_quoteIdentifier($field).' AS '.$this->_quoteIdentifier($alias);
				}
			}
		}

		foreach($this->_join as $join) {
			if(count($join[2])) {
				foreach($join[2] as $alias => $field) {
					if(preg_match('#[\.|\(\)]#', $field) === 0) {
						$field = $this->_alias($join[0]).'.'.$field;
					}

					if(is_int($alias)) {
						$select[] = $this->_quoteIdentifier($field);
					} else {
						$select[] = $this->_quoteIdentifier($field).' AS '.$this->_quoteIdentifier($alias);
					}
				}
			}
		}

		return join(', ', $select)."\n";
	}

	protected function _union()
	{
		if(empty($this->_union)) {
			return '';
		}

		$sql = '';
		$binds = array();

		foreach($this->_union as $union) {
			if(!empty($sql)) {
				$sql .= "UNION\n";
			}

			if($union instanceof Sql) {
				list($query, $args) = $union->assemble();
			} elseif(is_array($union)) {
				$query = $union[0];
				$args = $union[1];
			} else {
				$query = $union;
				$args = array();
			}

			$sql .= $query;

			$binds = array_merge($args, $binds);
		}

		$this->bind($binds);

		return $sql;
	}

	protected function _update()
	{
		if(!isset($this->_update[0])) {
			return array();
		}

		$name = $this->_update[0];
		$data = $this->_update[1];

		$update = "UPDATE {$name}";

		$set = "SET ";
		foreach($data as $key => $val) {
			$param = $this->_param($key, $val);

			$set .= $this->_quoteIdentifier($key)." = ".$param;

			$this->_binds[$param] = $val;

			$set .= ",\n";
		}

		$set = substr($set, 0, strlen($set) - 2);

		return array($update, $set);
	}

	protected function _where_or_having($array)
	{
		$_where = '';

		if(!count($array)) {
			return $_where;
		}

		foreach($array as $where) {
			if($where[0] instanceof $this) {
				list($where[0], $where[1]) = $where[0]->assemble();
			} elseif($where[1] instanceof $this) {
				list($query, $args) = $where[1]->assemble();

				str_replace(':query', $query, $_where[0]);

				$this->bind($args);
			}

			if(empty($_where)) {
				$_where = "({$where[0]})\n";
			} else {
				// Check for OR statements
				if($where[2]) {
					$_where .= "OR ({$where[0]})\n";
				} else {
					$_where .= "AND ({$where[0]})\n";
				}
			}

			if(count($where[1])) {
				$this->bind($where[1]);
			}
		}

		return $_where;
	}

	protected function _buildFromCriteria(\Gacela\Criteria $criteria)
	{
		foreach($criteria as $stmt) {
			$op = $stmt[0];

			if($op instanceof \Gacela\Criteria) {
				$query = new Sql($op);

				$query = $query->assemble();

				$this->where($query[0], $query[1], $stmt[3]);

				// Move along, nothing more to see here
				continue;
			}

			$field = $stmt[1];
			$or = isset($stmt[3]) ? $stmt[3] : false;

			if(isset($stmt[2]) && $stmt[2] !== false) {
				$args = $stmt[2];
			} else {
				$args = '';
			}

			if($op == 'limit') {
				$this->limit($field, $args);

				// Move on, this one is all done.
				continue;
			} elseif($op == 'sort') {
				$this->orderBy($field, $args);

				// Move along, move along
				continue;
			}



			$bind = array();
			$toBind = '';

			if(isset($args)) {
				if(!in_array($op, array('in', 'notIn'))) {
					$toBind = $this->_param($field, $args);

					if(in_array($op, array('like', 'notLike'))) {
						$args = '%'.$args.'%';
					}

					$bind = array($toBind => $args);
				}
			}

			if(in_array($op, array('in', 'notIn'))) {
				$this->in($field, $stmt[2], $op === 'in' ? false : true, $or);
			} elseif(in_array($op, array('notNull', 'null'))) {
				$this->where("{$field} ".$this->_operators[$stmt[0]], array(), $or);
			} else {
				$this->where($this->_quoteIdentifier($field).' '.$this->_operators[$op]." {$toBind}", $bind, $or);
			}
		}
	}

	public function __get($val)
	{
		$val = '_'.$val;

		return $this->$val;
	}

	/**
	 * @return array - String for the query, array of parameters to be bound
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
		$update = $this->_update();

		if(isset($update[0])) {
			$sql = $update[0]."\n";
		}

		// If its not an insert or an update, it might also be a delete
		if(!is_null($this->_delete)) {
			$sql = "DELETE FROM {$this->_delete}\n";
		}

		// Now there is another type of query - UNION - that needs to be considered
		$sql .= $this->_union();

		if(empty($sql)) {
			$select = trim($this->_select());
			$from = trim($this->_from());
			$sql = '';

			if(!empty($select)) {
				$sql .= "SELECT {$select}\n";
			}

			if(!empty($from)) {
				$sql .= "FROM {$from}\n";
			}
		}

		$sql .= $this->_join();

		if(isset($update[1])) {
			$sql .= $update[1]."\n";
		}

		$where = $this->_where_or_having($this->_where);

		if(!empty($sql) && !empty($where)) {
			$sql .= 'WHERE ';
		}

		$sql .= $where;

		$sql .= $this->_group();

		$having = $this->_where_or_having($this->_having);

		if($having) {
			$sql .= 'HAVING '.$having;
		}


		$sql .= $this->_order();

		if(!empty($this->_limit)) {
			$sql .= 'LIMIT '.(int) $this->_limit[0].', '.(int) $this->_limit[1]."\n";
		}

		$this->_sql = $sql;

		return array($this->_sql, $this->_binds);
	}

	/**
	 * @param array $binds
	 * @return Sql
	 */
	public function bind(array $binds)
	{
		foreach($binds as $key => $val) {
			$this->_binds[$key] = $this->_cast($val);
		}

		return $this;
	}

	/**
	 * @param  $name
	 * @return Sql
	 */
	public function delete($name)
	{
		$this->_delete = $name;

		return $this;
	}

	/**
	 * @param  $tableName
	 * @param array $columns
	 * @return Sql
	 */
	public function from($tableName, array $columns = array())
	{
		if(is_array($tableName)) {
			$name = key($tableName);
		} else {
			$name = $tableName;
		}

		if(empty($columns)) {
			$columns = array('*');
		}

		$this->_from[$name] = array($tableName, $columns);

		return $this;
	}

	/**
	 * @param  string $column
	 * @return Sql
	 */
	public function groupBy($column)
	{
		if(!in_array($column, $this->_groupBy)) {
			$this->_groupBy[] = $column;
		}

		return $this;
	}

	/**
	 * @param $stmt
	 * @param array $value
	 * @param bool $or
	 * @return Sql
	 */
	public function having($stmt, $value = array(), $or = false)
	{
		$this->_having[] = array($stmt, $value, $or);

		return $this;
	}

	/**
	 * @param $field
	 * @param array $values
	 * @param bool $not
	 * @param bool $or
	 * @return Sql
	 * @throws \Exception
	 */
	public function in($field, array $values, $not = false, $or = false)
	{
		if(!count($values)) {
			throw new \Exception('Sql::in() requires an array of values that are not empty!');
		}

		if($not) {
			$stmt = $this->_operators['notIn'];
		} else {
			$stmt = $this->_operators['in'];
		}

		$keys = $values;

		foreach($keys as $key => $val) {
			$keys[$key] = $this->_param($field, $val);
		}

		$stmt = $field.' '.$stmt.' ('.join(',', $keys).')';

		$values = array_combine($keys, array_values($values));

		return $this->where($stmt, $values, $or);
	}

	public function insert($tableName, $data, $modifiers = array(), $on_duplicate_update = array())
	{
		$this->_insert = array($tableName, $data, $modifiers, $on_duplicate_update);

		return $this;
	}

	/**
	 * @param  string|array $table
	 * @param  string $on
	 * @param array $columns
	 * @param string $type
	 * @return Sql
	 */
	public function join($table, $on, array $columns = array(), $type = 'inner')
	{
		if(is_null($type)) {
			$type = 'inner';
		}

		if($table instanceof $this) {
			$table = $table->assemble();
		}

		$this->_join[] = array($table, $on, $columns, $type);

		return $this;
	}

	/**
	 * A convenience wrapper for join
	 * @param  string|array $table
	 * @param  string $on
	 * @param array $columns
	 * @param string $type
	 * @return Query\Sql
	 */
	public function leftJoin($table, $on, array $columns = array())
	{
		return $this->join($table, $on, $columns, 'left');
	}

	/**
	 * A convenience wrapper for join
	 * @param  string|array $table
	 * @param  string $on
	 * @param array $columns
	 * @param string $type
	 * @return Query\Sql
	 */
	public function rightJoin($table, $on, array $columns = array())
	{
		return $this->join($table, $on, $columns, 'right');
	}

	/**
	 * @param $start
	 * @param $count
	 * @return Sql
	 */
	public function limit($start, $count)
	{
		$this->_limit = array($start, $count);

		return $this;
	}

	/**
	 * @param string
	 * @param string
	 * @return Sql
	 */
	public function orderBy($column, $direction = 'ASC')
	{
		$this->_orderBy[$column] = $direction;
		return $this;
	}

	/**
	 * @param array $queries
	 * @return Sql
	 */
	public function union(array $queries)
	{
		$this->_union = $queries;

		return $this;
	}

	/**
	 * @param $tableName Name of the table (resource) you wish to update
	 * @param $data An associative array of the fields and data to update
	 * @return Sql
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
	 * @return Sql
	 */
	public function where($stmt, $value = array(), $or = false)
	{
		$this->_where[] = array($stmt, $value, $or);

		return $this;
	}
}
