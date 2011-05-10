<?php
/** 
 * @author Noah Goodrich
 * @date May 7, 2011
 * @brief
*/

namespace Gacela\DataSource;

class Database extends DataSource {

	protected $_db;

	protected $_config = array();

	protected $_resources = array();

	public function __construct(array $config)
	{
		$this->_config = (object) $config;

		$dsn = $this->_config->dbtype.':dbname='.$this->_config->database.';host='.$this->_config->host;

		$this->_db = new \PDO($dsn, $this->_config->user, $this->_config->password);
	}

	/**
	 * @throws \Exception
	 * @param  $name
	 * @param Gacela\Criteria $where
	 * @return bool
	 */
	public function delete($name, Gacela\Criteria $where)
	{
		if($this->getQuery($where)->delete($name)->assemble()->execute()) {
			return true;
		} else {
			throw new \Exception('Update failed with errors: '.\Util::debug($query->errorInfo()));
		}
	}

	/**
	 * @see \Gacela\DataSource\iDataSource::getQuery()
	 */
	public function getQuery(\Gacela\Criteria $criteria = null)
	{
		return new Query\Database(array_merge((array) $this->_config, array('db' => $this->_db, 'criteria' => $criteria)));
	}

	/**
	 * @see Gacela\DataSource\iDataSource::insert()
	 */
	public function insert($name, $data)
	{
		if($this->getQuery()->insert($name, $data)->assemble()->execute()) {
			return $this->_db->lastInsertId();
		} else {
			throw new \Exception('Insert failed with errors: '.\Util::debug($query->errorInfo()));
		}
	}

	/**
	 * @see \Gacela\DataSource\iDataSource::loadResource()
	 */
	public function loadResource($name)
	{
		if(!isset($this->_resources[$name]))  {
			$this->_resources[$name] = new Resource\Database(array_merge((array) $this->_config, array('name' => $name, 'db' => $this->_db)));
		}

		return $this->_resources[$name];
	}

	/**
	 * @see Gacela\DataSource\iDataSource::query()
	 */
	public function query($query)
	{
		if($query instanceof Query\Database)  {
			$stmt = $query->assemble();
		} elseif(is_string($query)) {
			$stmt = $this->_db->prepare($query);
		}

		if($stmt->execute() === true) {
			return $stmt->fetchAll(\PDO::FETCH_OBJ);
		} else {
			$error = $stmt->errorInfo();
			$error = $error[2];
			throw new \Exception("Code ({$stmt->errorCode()}) Error: ".$error);
		}
	}

	public function update($name, $data, \Gacela\Criteria $where)
	{
		if($this->getQuery($where)->update($name, $data)->assemble()->execute()) {
			return true;
		} else {
			throw new \Exception('Update failed with errors: '.\Util::debug($query->errorInfo()));
		}
	}
}
