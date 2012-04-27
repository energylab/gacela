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

		foreach($this->_binds as $key => $arg) {
			$this->_binds[$key] = $this->quote($arg);
		}

		$sql = strtr($sql, $this->_binds);

		$this->_sql = $sql;

		return array($this->_sql, $this->_binds);
	}

	public function quote($param)
	{
		// This sucks but its the best of I've got right now.
		return "'".addslashes($param)."'";
	}

}
