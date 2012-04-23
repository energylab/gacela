<?php
/**
 * @author noah
 * @date Oct 2, 2010
 * @brief
 *
*/

namespace Gacela;

class Criteria implements \IteratorAggregate {

	protected $_criteria = array();

	protected function _addCriterion($operator, array $args)
	{
		$this->_criteria[] = array_merge(array($operator), $args);

		return $this;
	}

	public function criteria(\Gacela\Criteria $criteria, $or = false)
	{
		return $this->_addCriterion($criteria, array(null, null, $or));
	}

	/**
	 * @return Criteria
	 */
	public function equals($field, $value, $or = false)
	{
		return $this->_addCriterion('equals', func_get_args());
	}

	public function getIterator()
	{
		return new \ArrayObject($this->_criteria);
	}

	public function greaterThan($field, $value, $or = false)
	{
		return $this->_addCriterion('greaterThan', func_get_args());
	}

	public function in($field, array $value, $or = false)
	{
		return $this->_addCriterion('in', func_get_args());
	}

	public function isNull($field, $or = false)
	{
		$array = func_get_args();
		array_splice($array, 1, 0, false);

		return $this->_addCriterion('null', $array);
	}

	public function isNotNull($field, $or = false)
	{
		$array = func_get_args();
		array_splice($array, 2, 0, null);

		return $this->_addCriterion('notNull', $array);
	}

	public function lessThan($field, $value, $or = false)
	{
		return $this->_addCriterion('lessThan', func_get_args());
	}

	public function like($field, $value, $or = false)
	{
		return $this->_addCriterion('like', func_get_args());
	}

	public function limit($start, $count)
	{
		return $this->_addCriterion('limit', func_get_args());
	}

	public function notEquals($field, $value, $or = false)
	{
		return $this->_addCriterion('notEquals', func_get_args());
	}

	public function notIn($field, array $value, $or = false)
	{
		return $this->_addCriterion('notIn', func_get_args());
	}

	public function notLike($field, $value, $or = false)
	{
		return $this->_addCriterion('notLike', func_get_args());
	}

	public function sort($field, $dir = 'asc')
	{
		return $this->_addCriterion('sort', array($field, $dir));
	}
}
