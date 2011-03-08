<?php
/** 
 * @author noah
 * @date Oct 4, 2010
 * @brief
 * 
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

	public function query($query)
	{
		$stmt = $query->assemble();
		$stmt->execute();

		return $stmt->fetchAll(\PDO::FETCH_OBJ);
	}

	public function insert($name, $data) {}

	public function update($name, $data) {}

	public function delete($name, $id) {}

	public function getQuery()
	{
		return new Query\Database(array_merge((array) $this->_config, array('db' => $this->_db)));
	}

	public function getResource($name)
	{
		if(!isset($this->_resources[$name]))  {
			$this->_resources[$name] = new Resource\Database(array_merge((array) $this->_config, array('name' => $name, 'db' => $this->_db)));
		}

		return $this->_resources[$name];
	}
}
