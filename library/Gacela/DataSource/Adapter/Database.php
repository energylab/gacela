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

	public function __construct(array $config)
	{
		$dsn = $config['dbtype'].':dbname='.$config['database'].';host='.$config['host'];
		
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

	public function quote()
	{

	}
}
