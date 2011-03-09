<?php
/** 
 * @author noah
 * @date 3/7/11
 * @brief
 * 
*/

namespace Gacela;

class Collection implements \SeekableIterator, \Countable, \ArrayAccess
{
	protected $_mapper;

	protected $_data;

	protected $_count = 0;

	protected $_pointer = 0;

	public function __construct(\Gacela\Mapper\Mapper $mapper, array $data)
	{
		$this->_mapper = $mapper;
		
		$this->_data = $data;

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
        return $this->_count;
    }

	public function current()
	{
		$data = $this->_data[$this->_pointer];

		if(is_object($data)) {
			return $this->_mapper->load($data);
		} elseif(is_integer($data)) {
			return $this->_mapper->find($data);
		}
	}

	public function find($id)
	{
		return $this->search($id);
	}

	public function key()
	{
		return $this->_pointer;
	}

	public function next()
	{
		++$this->_pointer;
	}

    /**
     * Check if an offset exists
     * Required by the ArrayAccess implementation
     *
     * @param string $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->_data[(int) $offset]);
    }

    /**
     * Get the row for the given offset
     * Required by the ArrayAccess implementation
     *
     * @param string $offset
     * @return $_modelClass
     */
    public function offsetGet($offset)
    {
        $this->_pointer = (int) $offset;

        return $this->current();
    }

    /**
     * Does nothing
     * Required by the ArrayAccess implementation
     *
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
    }

    /**
     * Does nothing
     * Required by the ArrayAccess implementation
     *
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
    }

	public function rewind()
	{
        $this->_pointer = 0;
        return $this;
	}

	public function search($value, $key = null)
	{
		if(is_null($key)) {

		}
	}

    /**
     * Take the Iterator to position $position
     * Required by interface SeekableIterator.
     *
     * @param int $position the position to seek to
     * @return LP_Model_Collection_Abstract
     * @throws Zend_Exception
     */
    public function seek($position)
    {
        $position = (int) $position;
        if ($position < 0 || $position > $this->_count) {
            throw new Exception("Illegal index $position");
        }

        $this->_pointer = $position;
        return $this;
    }

	public function valid()
	{
		return $this->_pointer < $this->_count;
	}
}
