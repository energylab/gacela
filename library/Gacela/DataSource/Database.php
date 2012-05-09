<?php
/**
 * @author Noah Goodrich
 * @date May 7, 2011
 * @brief
*/

namespace Gacela\DataSource;

class Database extends DataSource {

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

	protected function _driver()
	{
		if(empty($this->_driver)) {
			$adapter = $this->_singleton()->autoload("\\DataSource\\Adapter\\".ucfirst($this->_config->dbtype));

			$this->_driver = new $adapter($this->_config);
		}

		return $this->_driver;
	}

	public function beginTransaction()
	{
		return $this->_driver()->beginTransaction();
	}

	public function commitTransaction()
	{
		return $this->_driver()->commit();
	}

	/**
	 * @see Gacela\DataSource\iDataSource::delete()
	 * @throws \Exception
	 * @param  $name
	 * @param Gacela\Criteria $where
	 * @return bool
	 */
	public function delete($name, \Gacela\DataSource\Query\Query $where)
	{
		list($query, $args) = $where->delete($name)->assemble();

		$query = $this->_driver()->prepare($query);

		if($query->execute($args)) {
			if($query->rowCount() == 0) {
				return false;
			}

			$this->_incrementCache($name);
			return true;
		} else {
			throw new \Exception('Update failed with errors: <pre>'.print_r($query->errorInfo(), true).print_r($query, true).'</pre>');
		}
	}

	/**
	 * @param array $primary
	 * @param Resource\Resource $resource
	 * @param array $inherits
	 * @param array $dependents
	 * @return
	 */
	public function find(array $primary, \Gacela\DataSource\Resource $resource, array $inherits = array(), array $dependents = array())
	{
		$query = $this->getQuery();

		foreach($primary as $key => $val) {
			$query->where($resource->getName().'.'.$key.' = :'.$key, array(':'.$key => $val));
		}

		return $this->query(
					$resource,
					$this->_buildFinder($query, $resource, $inherits, $dependents)
				);
	}

	/**
	 * @param \Gacela\Criteria|null $criteria
	 * @param Resource\Resource $resource
	 * @param array $inherits
	 * @param array $dependents
	 * @return
	 */
	public function findAll(\Gacela\DataSource\Query\Query $query, \Gacela\DataSource\Resource $resource, array $inherits, array $dependents)
	{
		$query = $this->_buildFinder($query, $resource, $inherits, $dependents);

		return $this->query($resource,$query);
	}

	/**
	 * @param Resource\Resource $resource
	 * @param array $relation
	 * @param array $data
	 * @param array $inherits
	 * @param array $dependents
	 * @return
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
	 * @see \Gacela\DataSource\iDataSource::getQuery()
	 */
	public function getQuery(\Gacela\Criteria $criteria = null)
	{
		return new Query\Sql($criteria);
	}

	/**
	 * @see Gacela\DataSource\iDataSource::insert()
	 */
	public function insert($name, $data, $binds = array())
	{
		if($data instanceof \Gacela\DataSource\Query\Query) {
			list($sql, $binds) = $data->assemble();
		} elseif(is_array($data)) {
			list($sql, $binds) = $this->getQuery()->insert($name, $data)->assemble();
		} else {
			$sql = $data;
		}

		$query = $this->_driver()->prepare($sql);

		try {
			if($query->execute($binds)) {
				if($query->rowCount() == 0) {
					return false;
				}

				$this->_incrementCache($name);

				return $this->_driver()->lastInsertId();
			} else {
				if($this->_driver()->inTransaction()) {
					$this->rollbackTransaction();
				}

				throw new \Exception
				(
					'Insert to '.
					$name .
					' failed with errors: <pre>'.
					print_r($query->errorInfo(), true) .
					'</pre> With SQL: <pre>'.
					$sql.
					'</pre> And Data: </pre>'.
					print_r($binds, true).
					'</pre>'
				);
			}
		} catch (PDOException $e) {
			if($this->_driver()->inTransaction()) {
				$this->rollbackTransaction();
			}

			throw $e;
		}

	}

	/**
	 * @see Gacela\DataSource\iDataSource::query()
	 */
	public function query(\Gacela\DataSource\Resource $resource, $query, $args = null)
	{
		$key = $this->_setLastQuery($query, $args);

		$cached = $this->_cache($resource->getName(), $key);

		// If the query is cached, return the cached data
		if($cached !== false AND !is_null($cached)) {
			return $cached;
		}

		$stmt = $this->_driver()->prepare($this->_lastQuery['query']);

		if($stmt->execute($this->_lastQuery['args']) === true) {
			$return = $stmt->fetchAll(\PDO::FETCH_OBJ);
			$this->_cache($resource->getName(), $key, $return);
			return $return;
		} else {
			$error = $stmt->errorInfo();
			$error = $error[2];
			throw new \Exception("Code ({$stmt->errorCode()}) Error: ".$error."<br/>Query: ".$this->_lastQuery['query']."\nArgs: ".print_r($this->_lastQuery['args'], true));
		}
	}

	public function quote($var, $type = null)
	{
		return $this->_driver()->quote($var, $type);
	}

	public function rollbackTransaction()
	{
		return $this->_driver()->rollBack();
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

		$query = $this->_driver()->prepare($query);

		try {
			if($query->execute($binds)) {
				if($query->rowCount() == 0) {
					return false;
				}

				$this->_incrementCache($name);
				return true;
			} else {
				if($this->_driver()->inTransaction()) {
					$this->rollbackTransaction();
				}

				throw new \Exception('Update failed with errors: <pre>'.print_r($query->errorInfo(), true).print_r($query, true).'</pre>');
			}
		} catch (PDOException $e) {
			if($this->_driver()->inTransaction()) {
				$this->rollbackTransaction();
			}

			throw $e;
		}
	}
}
