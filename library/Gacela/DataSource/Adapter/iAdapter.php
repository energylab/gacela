<?php
/**
 * @author Noah Goodrich
 * @date 5/22/11
 */

namespace Gacela\DataSource\Adapter;

interface iAdapter
{
	public function loadConnection();

	public function load($name, $force = false);
}
