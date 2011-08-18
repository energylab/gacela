<?php
/** 
 * @author Noah Goodrich
 * @date May 7, 2011
 * @brief
*/

namespace Gacela\DataSource;

class Database extends DataSource {

	protected $_conn;

	protected $_driver;

	protected $_lastQuery = array();

	protected function _buildFinder(\Gacela\DataSource\Query\Query $query, \Gacela\DataSource\Resource $resource, array $inherits, array $dependents)
	{
		$query->from($resource->getName());

		foreach($inherits as $relation) {
			$this->_buildJoin($relation, $query);
		}

		foreach($dependents as $relation) {
			$this->_buildJoin($relation, $query, 'left');
		}
		
		return $query;
	}

	protected function _buildJoin(array $relation, &$query, $type = null)
	{
		$on = 	array();

		foreach($relation['meta']->keys as $key => $ref) {
			$on[$relation['meta']->keyTable.'.'.$key] = $relation['meta']->refTable.'.'.$ref;
		}

		$cols = array_diff(array_keys($relation['resource']->getFields()), $relation['resource']->getPrimaryKey());

		$query->join($relation['meta']->refTable, $on, $cols, $type);
	}

	protected function _driver()
	{
		if(empty($this->_driver)) {

			$adapter = "\\Gacela\\DataSource\\Adapter\\".ucfirst($this->_config->dbtype);
			$this->_driver = new $adapter;
		}

		return $this->_driver;
	}

	public function __construct(array $config)
	{
		$this->_config = (object) $config;

		$dsn = $this->_config->dbtype.':dbname='.$this->_config->schema.';host='.$this->_config->host;

		$this->_conn = new \PDO($dsn, $this->_config->user, $this->_config->password);
	}

	public function beginTransaction()
	{
		return $this->_conn->beginTransaction();
	}

	public function commitTransaction()
	{
		return $this->_conn->commit();
	}

	/**
	 * @see Gacela\DataSource\iDataSource::delete()
	 * @throws \Exception
	 * @param  $name
	 * @param Gacela\Criteria $where
	 * @return bool
	 */
	public function delete($name, \Gacela\Criteria $where)
	{
		list($query, $args) = $this->getQuery($where)->delete($name)->assemble();

		$query = $this->_conn->prepare($query);

		if($query->execute($args)) {
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
	public function findAll(\Gacela\Criteria $criteria = null, \Gacela\DataSource\Resource $resource, array $inherits, array $dependents)
	{
		return $this->query(
					$resource,
					$this->_buildFinder(
						$this->getQuery($criteria),
						$resource,
						$inherits,
						$dependents
					)
				);
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

		$this->_buildJoin($relation, $query);
		
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
		return new Query\Database($this->_config->schema, $criteria);
	}

	/**
	 * @see Gacela\DataSource\iDataSource::insert()
	 */
	public function insert($name, array $data, $transaction = null)
	{
		list($query, $binds) = $this->getQuery()->insert($name, $data)->assemble();

		$query = $this->_conn->prepare($query);

		try {
			if($query->execute($binds)) {
				$this->_incrementCache($name);
				
				return $this->_conn->lastInsertId();
			} else {
				if($this->_conn->inTransaction()) {
					$this->rollbackTransaction();
				}

				throw new \Exception('Insert failed with errors: <pre>'.print_r($query->errorInfo(), true).'</pre>');
			}
		} catch (PDOException $e) {
			if($this->_conn->inTransaction()) {
				$this->rollbackTransaction();
			}

			throw $e;
		}

	}

	public function lastQuery()
	{
		return $this->_lastQuery;
	}

	/**
	 * @see Gacela\DataSource\iDataSource::query()
	 */
	public function query(\Gacela\DataSource\Resource $resource, $query, $args = null)
	{
		if($query instanceof Query\Query)  {
			// Using the _lastQuery variable so that we can see the query when debugging
			list($this->_lastQuery['query'], $this->_lastQuery['args']) = $query->assemble();
		} else {
			$this->_lastQuery = array('query' => $query, 'args' => $args);
		}
		
		$key = hash('whirlpool', serialize(array($this->_lastQuery['query'], $this->_lastQuery['args'])));
		
		$cached = $this->_cache($resource->getName(), $key);

		// If the query is cached, return the cached data
		if($cached !== false AND !is_null($cached)) {
			return $cached;
		}
		
		$stmt = $this->_conn->prepare($this->_lastQuery['query']);
		
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
		return $this->_conn->quote($var, $type);
	}

	public function rollbackTransaction()
	{
		return $this->_conn->rollBack();
	}

	/**
	 * @see Gacela\DataSource\iDataSource::update()
	 * @throws \Exception
	 * @param  $name
	 * @param  $data
	 * @param \Gacela\Criteria $where
	 * @return bool
	 */
	public function update($name, $data, \Gacela\Criteria $where, $transaction = null)
	{
		list($query, $args) = $this->getQuery($where)->update($name, $data)->assemble();

		$query = $this->_conn->prepare($query);

		try {
			if($query->execute($args)) {
				$this->_incrementCache($name);
				return true;
			} else {
				if($this->_conn->inTransaction()) {
					$this->rollbackTransaction();
				}

				throw new \Exception('Update failed with errors: <pre>'.print_r($query->errorInfo(), true).print_r($query, true).'</pre>');
			}
		} catch (PDOException $e) {
			if($this->_conn->inTransaction()) {
				$this->rollbackTransaction();
			}

			throw $e;
		}
	}
}
