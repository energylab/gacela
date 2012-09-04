<?php
/**
 * @author Noah Goodrich
 * @date 3/7/11
 *
 *
 */

namespace Gacela\Collection;

abstract class Collection implements \SeekableIterator, \Countable {

	/**
	 * @var \Gacela\Mapper\Mapper
	 */
	protected $_mapper;

	/**
	 * @var array|\PDOStatement
	 */
	protected $_data;

	/**
	 * @var int
	 */
	protected $_count = 0;

	/**
	 * @var int
	 */
	protected $_pointer = 0;

	/**
	 * @param \Gacela\Mapper\Mapper $mapper
	 * @param $data
	 */
	public function __construct(\Gacela\Mapper\Mapper $mapper, $data)
	{
		$this->_mapper = $mapper;

		$this->_data = $data;
	}

	/**
	 * @abstract
	 * @param array $value
	 * @return mixed
	 */
	abstract public function search(array $value);
}
