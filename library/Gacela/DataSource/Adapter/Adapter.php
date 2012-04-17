<?php
/**
 * @author Noah Goodrich
 * @date 10/8/11
 * @brief
 *
*/

namespace Gacela\DataSource\Adapter;

abstract class Adapter implements iAdapter {

	protected $_config;

	protected $_conn;

	protected function _singleton()
	{
		return \Gacela::instance();
	}

	public function __call($method, $args)
	{
		$method = new \ReflectionMethod($this->_conn, $method);

		return $method->invokeArgs($this->_conn, $args);
	}

	public function __construct($config)
	{
		$this->_config = (object) $config;
	}

}
