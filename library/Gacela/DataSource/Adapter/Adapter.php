<?php
/**
 * @author Noah Goodrich
 * @date 10/8/11
 *
 *
*/

namespace Gacela\DataSource\Adapter;

abstract class Adapter implements iAdapter
{

	protected $_config = null;

	protected $_columns = null;

	protected $_conn = null;

	protected $_relationships = null;

	protected $_singleton = null;

	protected static $_meta = array(
		'type' => null,
		'length' => null,
		'scale' => null,
		'unsigned' => false,
		'sequenced' => false,
		'primary' => false,
		'default' => false,
		'values' => array(),
		'null' => true,
		'min' => null,
		'max' => null
	);

	protected function _loadConfig($name, $skip = false)
	{
		if($skip) {
			return null;
		}

		// Pull from the config file if enabled
		$config = $this->_singleton->loadConfig($name);

		if(is_array($config)) {
			$_meta = array_merge(
				array(
					'name' => $name,
					'primary' => array(),
					'relations' => array(),
					'columns' => array()
				),
				$config
			);

			foreach($_meta['columns'] as $key => $array) {
				$_meta['columns'][$key] = (object) array_merge(self::$_meta, $array);
			}

			foreach($_meta['relations'] as $k => $relation) {
				$_meta['relations'][$k] = (object) $relation;
			}

			return $_meta;
		}

		return null;
	}

	public function __call($method, $args)
	{
		if(!$this->_conn) {
			$this->loadConnection();
		}

		$method = new \ReflectionMethod($this->_conn, $method);

		return $method->invokeArgs($this->_conn, $args);
	}

	public function __construct(\Gacela $gacela, $config)
	{
		$this->_singleton = $gacela;

		$this->_config = (object) $config;
	}

}
