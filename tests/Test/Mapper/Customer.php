<?php

namespace Test\Mapper;

use \Gacela\Mapper\Mapper as M;

class Customer extends M
{
	protected $_source = 'test';

	protected $_cache = true;
}
