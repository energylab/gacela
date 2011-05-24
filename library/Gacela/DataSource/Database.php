<?php
/** 
 * @author Noah Goodrich
 * @date May 7, 2011
 * @brief
*/

namespace Gacela\DataSource;

class Database extends DataSource {

	protected $_conn;

	protected $_config = array();

	protected $_driver;

	protected $_resources = array();

	protected function _buildFinder(\Gacela\DataSource\Query\Query $query, \Gacela\DataSource\Resource $resource, array $inherits, array $dependents)
	{
		$query->from($resource->getName());

		foreach($inherits as $relation) {
			$on = 	$relation['meta']->keyTable
					.'.'
					.$relation['meta']->keyColumn
					." = "
					.$relation['meta']->refTable
					.'.'
					.$relation['meta']->refColumn;

			$query->join($relation['meta']->refTable, $on, array('*'));
		}

		foreach($dependents as $relation) {
			$on = 	$relation['meta']->keyTable
					.'.'
					.$relation['meta']->keyColumn
					." = ".
					$relation['meta']->refTable
					.'.'
					.$relation['meta']->refColumn;
						
			$query->join($relation['meta']->refTable, $on, array('*'), 'left');
		}

		return $query;
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

	/**
	 * @see Gacela\DataSource\iDataSource::delete()
	 * @throws \Exception
	 * @param  $name
	 * @param Gacela\Criteria $where
	 * @return bool
	 */
	public function delete($name, \Gacela\Criteria $where)
	{
		if($this->getQuery($where)->delete($name)->assemble()->execute()) {
			return true;
		} else {
			throw new \Exception('Update failed with errors: '.\Util::debug($query->errorInfo()));
		}
	}

	/**
	 * @param array $primary
	 * @param Resource\Resource $resource
	 * @param array $inherits
	 * @param array $dependents
	 * @return 
	 */
	public function find(array $primary, \Gacela\DataSource\Resource $resource, array $inherits, array $dependents)
	{
		$query = $this->getQuery();

		foreach($primary as $key => $val) {
			$query->where($resource->getName().'.'.$key.' = :'.$key, array(':'.$key => $val));
		}

		return $this->query($this->_buildFinder($query, $resource, $inherits, $dependents));
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
		return 	$this->query(
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
		$query = $this->getQuery()
					->join(
						$relation['meta']->refTable,
						$resource->getName()
						.'.'
						.$relation['meta']->keyColumn
						.' = '
						.$relation['meta']->refTable
						.'.'
						.$relation['meta']->refColumn
					);

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

		return $this->query($this->_buildFinder($query, $resource, $inherits, $dependents));
	}
	
	/**
	 * @see \Gacela\DataSource\iDataSource::getQuery()
	 */
	public function getQuery(\Gacela\Criteria $criteria = null)
	{
		return new Query\Database(array_merge((array) $this->_config, array('db' => $this->_conn, 'criteria' => $criteria)));
	}

	/**
	 * @see Gacela\DataSource\iDataSource::insert()
	 */
	public function insert($name, $data)
	{
		if($this->getQuery()->insert($name, $data)->assemble()->execute()) {
			return $this->_conn->lastInsertId();
		} else {
			throw new \Exception('Insert failed with errors: '.\Util::debug($query->errorInfo()));
		}
	}

	/**
	 * @see Gacela\DataSource\iDataSource::query()
	 */
	public function query($query)
	{
		if($query instanceof Query\Database)  {
			$stmt = $query->assemble();
		} elseif(is_string($query)) {
			$stmt = $this->_conn->prepare($query);
		}

		if($stmt->execute() === true) {
			return $stmt->fetchAll(\PDO::FETCH_OBJ);
		} else {
			$error = $stmt->errorInfo();
			$error = $error[2];
			throw new \Exception("Code ({$stmt->errorCode()}) Error: ".$error);
		}
	}

	public function quote($var, $type = null)
	{
		return $this->_conn->quote($var, $type);
	}

	/**
	 * @see Gacela\DataSource\iDataSource::update()
	 * @throws \Exception
	 * @param  $name
	 * @param  $data
	 * @param \Gacela\Criteria $where
	 * @return bool
	 */
	public function update($name, $data, \Gacela\Criteria $where)
	{
		$query = $this->getQuery($where)->update($name, $data)->assemble();
		
		if($query->execute()) {
			return true;
		} else {
			throw new \Exception('Update failed with errors: <pre>'.print_r($query->errorInfo(), true).print_r($query, true).'</pre>');
		}
	}
}
