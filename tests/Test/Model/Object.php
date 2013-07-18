<?php

namespace Test\Model;

use \Gacela\Model\Model as M;

class Object extends M {

	protected function _getMetadata()
	{
		if(($meta = @unserialize($this->_data->metadata)) !== false) {
			return $meta;
		}

		return $this->_data['metadata'];	
	}

	protected function _setMetadata($data)
	{
		if(is_array($data)) {
			$data = serialize($data);
		}

		$this->_set('metadata', $data);
	}
}
