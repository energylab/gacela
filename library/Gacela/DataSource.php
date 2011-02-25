<?php
/** 
 * @author noah
 * @date 10/23/10
 * @brief
 * 
*/

namespace Gacela;

use Gacela\DataSource;

class DataSource {

	/*
	 * @var
	 *
	 * @description Type of data source. Determines which DataSource Adapter to use
	 * 
	 */
	protected $_type;

	protected $_config = array();

	protected $_adapter;

	protected function _adapter()
	{
		if(is_null($this->_adapter)) {
			$adapter = '\Gacela\DataSource\Adapter\\'.ucfirst($this->_type);

			$this->_adapter = new $adapter($this->_config);
		}

		return $this->_adapter;
	}

	public function __construct(array $config)
	{
		$this->_type = $config['type'];

		$this->_config = $config;

		exit(\Util::debug($this->_adapter()));
	}
}
