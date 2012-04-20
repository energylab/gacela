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

	protected static $_meta = array(
		'type' => null,
		'length' => null,
		'precision' => null,
		'scale' => null,
		'unsigned' => false,
		'sequenced' => false,
		'primary' => false,
		'default' => false,
		'values' => array(),
		'null' => true
	);

	protected function _loadConfig($name)
	{
		// Pull from the config file if enabled
		$config = $this->_singleton()->loadConfig($name);

		if(!is_null($config)) {
			$_meta = array_merge(
				array(
					'name' => $name,
					'primary' => array(),
					'relations' => array(),
					'columns' => array()
				),
				$config
			);

			if(!is_integer(key($_meta['columns']))) {
				foreach($_meta['columns'] as $key => $array) {
					$_meta['columns'][$key] = (object) array_merge(self::$_meta, $array);
				}
			}

			foreach($_meta['relations'] as $k => $relation) {
				$_meta['relations'][$k] = (object) $relation;
			}

			return $_meta;
		}

		return null;
	}

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
