<?php
/** 
 * @author noah
 * @date Oct 4, 2010
 * @brief
 * 
*/

namespace Gacela\DataSource\Adapter;

class Database extends Adapter {

	protected $_db;

	protected $_dbtype;

	protected $_resources = array();
	
	public function __construct(array $config)
	{
		$this->_dbtype = $config['dbtype'];
		
		$dsn = $this->_dbtype.':dbname='.$config['database'].';host='.$config['host'];
		
		$this->_db = new \PDO($dsn, $config['user'], $config['password']);
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
				'dbtype' => $this->_dbtype,
				'db' => $this->_db
			));
		}
	}

	public function quote()
	{

	}
}
