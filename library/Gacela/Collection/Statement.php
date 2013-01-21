<?php

namespace Gacela\Collection;

/**
 * User: noah
 * Date: 8/28/12
 * Time: 7:32 AM
 */
class Statement extends Collection
{
	protected $_current = null;

	/**
	 * @param \Gacela\Mapper\Mapper $mapper
	 * @param \PDOStatement $data
	 */
	public function __construct(\Gacela\Mapper\Mapper $mapper, \PDOStatement $data)
	{
		parent::__construct($mapper, $data);

		$this->_data->setFetchMode(\PDO::FETCH_OBJ);

		$this->_count = $data->rowCount();

		if(!$this->_count) {
			$this->_current = false;
		}
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
		if(is_null($this->_current)) {
			$this->_current = $this->_data->fetch();
		}

		if($this->_current == false) {
			return $this->_mapper->find(null);
		}

		if(array_keys((array) $this->_current) == $this->_mapper->getPrimaryKey()) {
			return $this->_mapper->find($this->_current);
		} else {
			return $this->_mapper->load($this->_current);
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
		if(is_null($this->_current)) {
			$this->_current = $this->_data->fetch();
		}

		$this->_pointer++;

		$this->_current = $this->_data->fetch();
	}

	/**
	 * @return Statement|void
	 */
	public function rewind()
	{
		if($this->_current === false) {
			$this->_pointer = 0;

			$this->_data->execute();

			$this->_current = $this->_data->fetch();
		}
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
		} elseif($position < $this->_pointer) {
			$this->rewind();
		}

		while($this->_pointer < $position) {
			$this->next();
		}

		return $this;
	}

	public function slice($offset, $length = null)
	{
		$p = 0;
		$end = $offset+$length;
		$data = array();

		foreach($this as $row) {
			if($p >= $offset && (is_null($length) || $p <= $end)) {
				$data[] = $row;
			}
		}

		return \Gacela::instance()->makeCollection($this->_mapper, $data);
	}

	public function valid()
	{
		return $this->_current !== false;
	}
}
