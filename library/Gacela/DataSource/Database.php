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

	public function query()
	{
		
	}

	public function insert() {}

	public function update() {}

	public function delete() {}

	public function select() {}

	public function getQuery()
	{
		return new Query\Database();
	}

	public function getResource($name)
	{
		if(!isset($this->_resources[$name]))  {
			$this->_resources[$name] = new Resource\Database(array(
				'name' => $name,
				'config' => $this->_config,
				'db' => $this->_db
			));
		}

		return $this->_resources[$name];
	}

	public function quote()
	{

	}
}
