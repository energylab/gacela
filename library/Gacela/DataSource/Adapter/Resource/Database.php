<?php
/** 
 * @author noah
 * @date 2/26/11
 * @brief
 * 
*/

namespace \Gacela\DataSource\Adapter\Resource;

class Database extends Resource {

	protected $_db;
	
	public function __construct(array $config)
	{
		parent::__construct();

		$this->_db = $config['db'];

		$method = '_'.ucfirst($config['dbtype']);

		$this->$method();
	}

	private function _loadMysql()
	{
		exit(\Util::debug($this));
	}

}
