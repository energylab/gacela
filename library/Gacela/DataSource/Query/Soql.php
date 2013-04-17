<?php

/**
 * Description of soql
 *
 * @author noah
 * @date $(date)
 */

namespace Gacela\DataSource\Query;

class Soql extends Sql
{
	/**
	 * @throws \Gacela\Exception
	 * @return string
	 */
	protected function _delete()
	{
		if(count($this->_where) > 1) {
			throw new \Gacela\Exception('For salesforce only one statement is allowed when deleting!');
		}

		$where = current($this->_where);

		if(empty($where[1]) AND strpos($where[0], '=') !== false) {
			$arg = explode('=', $where[0]);

			$arg = trim($arg[1], '\'" ');

			$this->_binds['Ids'] = array($arg);
		} elseif(strpos($where[0], '=') !== false) {
			$this->_binds['Ids'] = array(current($where[1]));
		}  elseif(stripos($where[0], 'in') !== false) {
			$this->_binds['Ids'] = array_values($where[1]);
		}
	}

	protected function _quoteIdentifier($identifier)
	{
		return $identifier;
	}

	protected function _select()
	{
		$select = array();

		foreach($this->_from as $from) {
			foreach($from[1] as $alias => $field) {
				if(is_int($alias)) {
					$select[] = $this->_quoteIdentifier($field);
				} else {
					$select[] = $this->_quoteIdentifier($field).' AS '.$this->_quoteIdentifier($alias);
				}
			}
		}

		return join(', ', $select)."\n";
	}

	public function __construct(\Gacela\Criteria $criteria = null)
	{
		$this->_operators['null'] = '=';
		$this->_operators['notNull'] = '!=';

		parent::__construct($criteria);
	}

	public function assemble()
	{
		if($this->_delete) {
			$sql = $this->_delete;

			$this->_delete();
		} else {
			$select = trim($this->_select());
			$from = trim($this->_from());
			$sql = '';

			if(!empty($select)) {
				$sql .= "SELECT {$select}\n";
			}

			if(!empty($from)) {
				$sql .= "FROM {$from}\n";
			}

			$where = $this->_where_or_having($this->_where);

			if(!empty($sql) && !empty($where)) {
				$sql .= 'WHERE ';
			}

			$sql .= $where;

			$sql .= $this->_group();

			$sql .= $this->_where_or_having($this->_having);

			$sql .= $this->_order();

			if(!empty($this->_limit)) {
				$sql .= 'LIMIT '.(int) $this->_limit[1]."\n";
			}
		}

		foreach($this->_binds as $key => $arg) {
			$this->_binds[$key] = $this->quote($arg);
		}

		$sql = strtr($sql, $this->_binds);

		$this->_sql = $sql;

		return array($this->_sql, $this->_binds);
	}

	public function quote($param)
	{
		if(is_array($param))
		{
			return $param;
		}

		// This sucks but its the best of I've got right now.
		return "'".addslashes($param)."'";
	}

}
