<?php
/** 
 * @author noah
 * @date Oct 4, 2010
 * @brief
 * 
*/

namespace Gacela\DataSource\Adapter;

class Database extends \Gacela\DataSource\Adapter_Abstract {

	protected $_db;

	public function __construct(array $config)
	{
		$dsn = $config['type'].':dbname='.$config['database'].';host='.$config['host'];

		$this->_db = new PDO($dsn, $config['username'], $config['password']);
	}
}
