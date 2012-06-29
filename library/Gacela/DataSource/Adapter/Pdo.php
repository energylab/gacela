<?php

/**
 * Description of Pdo
 *
 * @author noah
 * @date $(date)
 */
namespace Gacela\DataSource\Adapter;

abstract class Pdo extends Adapter {


	protected function _loadConn()
	{
		$this->_config->dsn = $this->_config->dbtype.':dbname='.$this->_config->schema.';host='.$this->_config->host;

		$this->_conn = new \PDO(
						$this->_config->dsn,
						$this->_config->user,
						$this->_config->password,
						property_exists($this->_config, 'options') ? $this->_config->options : null
					);

	}

}
