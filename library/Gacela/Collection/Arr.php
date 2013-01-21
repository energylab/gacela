<?php
/**
 * @author Noah Goodrich
 * @date 3/7/11
 *
 *
 */

namespace Gacela\Collection;

class Arr extends Collection
{
	public function __construct(\Gacela\Mapper\Mapper $mapper, array $data)
	{
		parent::__construct($mapper, $data);

		$this->_count = count($data);
	}

	/**
	 * Returns the number of elements in the collection.
	 *
	 * Implements Countable::count()
	 *
	 * @return int
	 */
	public function count()
	{
		return (int) $this->_count;
	}

	/**
	 * @return \Gacela\Model\Model
	 * @throws \Exception
	 */
	public function current()
	{
		if(!isset($this->_data[$this->_pointer])) {
			return $this->_mapper->load(new \stdClass);
		}

		$data = $this->_data[$this->_pointer];

		if($data instanceof \Gacela\Model\iModel) {
			return $data;
		} elseif(array_keys((array) $data) == $this->_mapper->getPrimaryKey()) {
			return $this->_mapper->find($data);
		} else {
			return $this->_mapper->load($data);
		}
	}

	/**
	 * @return int
	 */
	public function key()
	{
		return $this->_pointer;
	}

	/**
	 * @return \Gacela\Model\Model
	 */
	public function next()
	{
		++$this->_pointer;
	}

	public function rewind()
	{
		$this->_pointer = 0;
	}

	/**
	 * Take the Iterator to position $position
	 * Required by interface SeekableIterator.
	 *
	 * @param int $position the position to seek to
	 * @return \Gacela\Collection\Collection
	 * @throws \Exception
	 */
	public function seek($position)
	{
		$position = (int) $position;
		if ($position < 0 || $position > $this->_count) {
			throw new \OutOfBoundsException("Illegal index $position");
		}

		$this->_pointer = $position;

		return $this;
	}

	public function slice($offset, $length = null)
	{
		$data = array_slice($this->_data, $offset, $length);

		return \Gacela::instance()->makeCollection($this->_mapper, $data);
	}

	public function valid()
	{
		return $this->_pointer < $this->_count;
	}
}
