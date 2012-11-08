<?php
/**
 * @author Noah Goodrich
 * @date 3/7/11
 *
 *
 */

namespace Gacela\Collection;

abstract class Collection implements \SeekableIterator, \Countable
{

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

	public function asArray()
	{
		if(func_num_args() < 1)
		{
			throw new \Exception('Invalid number of args passed to \Gacela\Collection::asArray().');
		}

		if(func_num_args() == 1) {
			$args = func_get_arg(0);
		} else {
			$args = func_get_args();
		}

		$array = array();
		foreach($this as $row) {
			if(!is_array($args)) {
				$array[] = $row->$args;
			} else {
				$data = array();

				foreach($args as $field) {
					$data[$field] = $row->$field;
				}

				$array[] = $data;
			}

		}

		return $array;
	}

	/**
	* @param array $value
	* @return Collection
	 */
	public function search(array $value)
	{
		$data = array();

		$prop = new \ReflectionProperty($this->current(), '_data');
		$prop->setAccessible(true);

		foreach($this as $row) {
			$rs = true;

			foreach($value as $key => $val) {
				if($row->$key != $val) {
					$rs = false;
					break;
				}
			}

			if($rs === true) {
				$data[] = $prop->getValue($row);
			}
		}

		return \Gacela::instance()->makeCollection($this->_mapper, $data);
	}
}
