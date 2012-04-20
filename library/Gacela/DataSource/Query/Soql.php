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
	public function quote($param)
	{
		// This sucks but its the best of I've got right now.
		return addslashes($param);
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
			$sql .= 'LIMIT '.(int) $this->_limit[0].', '.(int) $this->_limit[1]."\n";
		}

		$this->_sql = $sql;
	}
}
