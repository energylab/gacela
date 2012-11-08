<?php

/**
 * Description of Pdo
 *
 * @author noah
 * @date $(date)
 */
namespace Gacela\DataSource\Adapter;

abstract class Pdo extends Adapter
{

	public static $_separator = "_";

	public function loadConnection()
	{
		$this->_config->dsn = $this->_config->type.':dbname='.$this->_config->schema.';host='.$this->_config->host;

		$this->_conn = new \PDO(
						$this->_config->dsn,
						$this->_config->user,
						$this->_config->password,
						property_exists($this->_config, 'options') ? $this->_config->options : null
					);

		$this->_conn->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ);

	}

	/**
	 * @return bool
	 */
	public function inTransaction()
	{
		return $this->__call('inTransaction', array());
	}

	/**
	 * @param null $name
	 * @return string
	 */
	public function lastInsertId($name = null)
	{
		return $this->__call('lastInsertId', array($name));
	}

	/**
	 * @param $statement
	 * @param array $options
	 * @return \PDOStatement
	 */
	public function prepare($statement, array $options = array())
	{
		return $this->__call('prepare', array($statement, $options));
	}

}
