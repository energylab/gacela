<?php
/**
 * @author Noah Goodrich
 * @date Oct 18, 2012
 *
*/

namespace Gacela\DataSource;

class Database extends DataSource
{
	/**
	 * @var \Gacela\DataSource\Adapter\Pdo
	 */
	protected $_adapter;

	protected function _buildFinder(\Gacela\DataSource\Query\Query $query, \Gacela\DataSource\Resource $resource, array $inherits, array $dependents)
	{
		$include_columns = false;

		if(count($query->from) == 0) {
			$query->from($resource->getName());

			$include_columns = true;
		}

		foreach($inherits as $relation) {
			$this->_buildJoin($relation, $query, 'inner', $include_columns);
		}

		foreach($dependents as $relation) {
			$this->_buildJoin($relation, $query, 'left', $include_columns);
		}

		return $query;
	}

	protected function _buildJoin(array $relation, &$query, $type, $include_columns)
	{
		$on = array();
		$cols = array();

		foreach($relation['meta']->keys as $key => $ref) {
			$on[$relation['meta']->keyTable.'.'.$key] = $relation['meta']->refTable.'.'.$ref;
		}

		if($include_columns) {
			$cols = array_diff(array_keys($relation['resource']->getFields()), $relation['resource']->getPrimaryKey());
		}

		$query->join($relation['meta']->refTable, $on, $cols, $type);
	}

	public function beginTransaction()
	{
		return $this->_adapter->beginTransaction();
	}

	public function commitTransaction()
	{
		return $this->_adapter->commit();
	}

	public function inTransaction()
	{
		return $this->_adapter->inTransaction();
	}

	/**
	 * @param $query
	 * @param Resource $resource
	 * @param array $inherits
	 * @param array $dependents
	 * @return int
	 */
	public function count($query, \Gacela\DataSource\Resource $resource, array $inherits, array $dependents)
	{
		if($query instanceof \Gacela\Criteria || is_null($query)) {
			$query = $this->getQuery($query);

			$query->from($resource->getName(), array('count' => 'COUNT(*)'));
		} elseif($query instanceof \Gacela\DataSource\Query\Sql) {
			$sub = $query;

			$query = $this->getQuery()
				->from(array('s' => $sub), array('count' => 'COUNT(*)'));
		}

		$rs = $this->findAll($query, $resource, $inherits, $dependents)->fetch();

		if($rs) {
			$rs = $rs->count;
		}

		return (int) $rs;
	}

	/**
	 * @see Gacela\DataSource\iDataSource::delete()
	 * @throws \Exception
	 * @param  $name
	 * @param \Gacela\DataSource\Query\Sql $where
	 * @return bool
	 */
	public function delete($name, \Gacela\DataSource\Query\Query $where)
	{
		list($query, $args) = $where->delete($name)->assemble();

		$query = $this->_adapter->prepare($query);

		if($query->execute($args)) {
			if($query->rowCount() == 0) {
				return false;
			}

			return true;
		} else {
			throw new \Exception('Update failed with errors: <pre>'.print_r($query->errorInfo(), true).print_r($query, true).'</pre>');
		}
	}

	/**
	 * @param array $primary
	 * @param Resource $resource
	 * @param array $inherits
	 * @param array $dependents
	 * @return
	 */
	public function find(array $primary, \Gacela\DataSource\Resource $resource, array $inherits = array(), array $dependents = array())
	{
		$crit = new \Gacela\Criteria;

		foreach($primary as $key => $val) {
			$crit->equals($resource->getName().'.'.$key, $val);
		}

		return $this->query(
					$resource,
					$this->_buildFinder($this->getQuery($crit), $resource, $inherits, $dependents)
				)
				->fetchObject();
	}

	/**
	 * @param \Gacela\Criteria|null $criteria
	 * @param Resource $resource
	 * @param array $inherits
	 * @param array $dependents
	 * @return \PDOStatement
	 */
	public function findAll(\Gacela\DataSource\Query\Query $query, \Gacela\DataSource\Resource $resource, array $inherits, array $dependents)
	{
		$query = $this->_buildFinder($query, $resource, $inherits, $dependents);

		return $this->query($resource,$query);
	}

	/**
	 * @param Resource $resource
	 * @param array $relation
	 * @param array $data
	 * @param array $inherits
	 * @param array $dependents
	 * @return \PDOStatement
	 */
	public function findAllByAssociation(\Gacela\DataSource\Resource $resource, array $relation, array $data, array $inherits, array $dependents)
	{
		$query = $this->getQuery();

		$this->_buildJoin($relation, $query, 'inner', true);

		foreach($data as $primary => $value) {
			$query->where(
				$relation['meta']->refTable
				.'.'
				.$primary
				.' = :'
				.$primary,
				array(':'.$primary => $value)
			);
		}

		return $this->query(
					$relation['resource'],
					$this->_buildFinder($query, $resource, $inherits, $dependents)
				);
	}

	/**
	 * @param \Gacela\Criteria
	 * @return Query\Sql
	 */
	public function getQuery(\Gacela\Criteria $criteria = null)
	{
		$class = $this->_gacela->autoload("DataSource\\Query\\Sql");

		return new $class($criteria);
	}

	/**
	 * @param string
	 * @param Query\Sql
	 * @param array
	 */
	public function insert($name, $data, $binds = array())
	{
		if($data instanceof Query\Sql) {
			list($sql, $binds) = $data->assemble();
		} elseif(is_array($data)) {
			list($sql, $binds) = $this->getQuery()->insert($name, $data)->assemble();
		} else {
			$sql = $data;
		}

		$query = $this->_adapter->prepare($sql);

		try {
			if($query->execute($binds)) {
				if($query->rowCount() == 0) {
					return false;
				}

				return $this->_adapter->lastInsertId();
			} else {
				if($this->_adapter->inTransaction()) {
					$this->rollbackTransaction();
				}

				throw new \Gacela\Exception
				(
					'Insert to '.$name." failed with errors:\n".
						print_r($query->errorInfo(), true) .
						"\nWith Sql:\n".
						$sql.
						"\n\nAnd Data:\n".
						print_r($binds, true).
						"\n"
				);
			}
		} catch (\PDOException $e) {
			if($this->_adapter->inTransaction()) {
				$this->rollbackTransaction();
			}

			throw $e;
		}
	}

	/**
	 * @see Gacela\DataSource\iDataSource::query()
	 */
	public function query(\Gacela\DataSource\Resource $resource, $query, $args = array())
	{
		$this->_setLastQuery($query, $args);

		$stmt = $this->_adapter->prepare($this->_lastQuery['query']);

		$stmt->setFetchMode(\PDO::FETCH_OBJ);

		foreach($this->_lastQuery['args'] as $param => $val) {
			$stmt->bindValue($param, $val);
		}

		if($stmt->execute() === true) {
			return $stmt;
		} else {
			$error = $stmt->errorInfo();
			$error = $error[2];
			throw new \Exception("Code ({$stmt->errorCode()}) Error: ".$error."<br/>Query: ".$this->_lastQuery['query']."\nArgs: ".print_r($this->_lastQuery['args'], true));
		}
	}

	public function quote($var, $type = null)
	{
		return $this->_adapter->quote($var, $type);
	}

	public function rollbackTransaction()
	{
		return $this->_adapter->rollBack();
	}

	/**
	 * @see Gacela\DataSource\iDataSource::update()
	 * @throws \Exception
	 * @param  $name
	 * @param  $data
	 * @param \Gacela\Criteria $where
	 * @return bool
	 */
	public function update($name, $data, $where = array())
	{
		if($data instanceof Query\Query) {
			list($query, $binds) = $data->assemble();
		} elseif(is_array($data) && $where instanceof Query\Query) {
			list($query, $binds) = $where->update($name, $data)->assemble();
		} elseif(is_array($where)) {
			$query = $data;
			$binds = $where;
		}

		$query = $this->_adapter->prepare($query);

		try {
			if($query->execute($binds)) {
				if($query->rowCount() == 0) {
					return false;
				}

				//$this->_incrementCache($name);
				return true;
			} else {
				if($this->_adapter->inTransaction()) {
					$this->rollbackTransaction();
				}

				throw new \Gacela\Exception('Update failed with errors: <pre>'.print_r($query->errorInfo(), true).print_r($query, true).'</pre>');
			}
		} catch (\PDOException $e) {
			if($this->_adapter->inTransaction()) {
				$this->rollbackTransaction();
			}

			throw $e;
		}
	}
}
