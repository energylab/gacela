<?php
/** 
 * @author Noah Goodrich
 * @date 10/8/11
 * @brief
 * 
*/

namespace Gacela\DataSource\Adapter;

abstract class Adapter implements iAdapter {

	protected function _singleton()
	{
		return \Gacela::instance();
	}
}
