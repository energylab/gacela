<?php

namespace Test\Mapper;

use \Gacela\Mapper\Mapper as M;

class Object extends M {
	
	protected $_source = 'test';

	protected $_dependents = array('meta');
}
