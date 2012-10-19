<?php

namespace Test\Mapper;

use Gacela\Mapper as M;

class User extends M\Mapper
{
	protected function _init()
	{
		return $this;
	}

	protected function _source()
	{
		return $this->getMock('\Gacela\DataSource\Database');
	}

	public function getFields()
	{
		return array(
			'id' => (object) array(
				'type' => 'int',
				'length' => '10',
				'unsigned' => true,
				'sequenced' => true,
				'primary' => true,
				'default' => false,
				'null' => false,
				'min' => '0',
				'max' => '500'
			),
			'is_admin' => (object) array(
				'type' => 'bool',
				'default' => '0',
				'null' => false,
			),
			'role' => (object) array(
				'type' => 'enum',
				'default' => 'user',
				'values' => array('user', 'customer', 'employee'),
				'null' => false,
				'max' => null
			),
			'affiliate' => (object) array(
				'type' => 'string',
				'length' => '255',
				'default' => 'gacela',
				'null' => true
			)
		);
	}

	public function getRelations()
	{
		return array();
	}
}
