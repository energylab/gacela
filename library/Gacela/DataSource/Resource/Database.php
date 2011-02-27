<?php
/** 
 * @author noah
 * @date 2/26/11
 * @brief
 * 
*/

namespace Gacela\DataSource\Resource;

class Database extends Resource {

	/**
	 * @var PDO
	 */
	protected $_db;
	
	public function __construct(array $config)
	{
		parent::__construct($config);

		$this->_db = $config['db'];

		$method = '_load'.ucfirst($config['config']->dbtype);

		$this->$method();
	}

	private function _loadMysql()
	{
		$stmt = $this->_db->prepare("DESCRIBE ".$this->_name);

		if(!$stmt->execute()) {
			throw new \Exception(
				'Error Code: '.
				$stmt->errorCode().
				'<br/>'.
				\Util::debug($stmt->errorInfo()).
				'Param Dump:'.
				\Util::debug($stmt->debugDumpParams())
			);
		}

		$this->_meta['columns'] = $stmt->fetchAll(\PDO::FETCH_OBJ);

		
	}

	public function getFields()
	{

	}

	public function getRelations()
	{

	}

}
