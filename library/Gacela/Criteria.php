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
	}

	/**
	 * @return Criteria
	 */
	public function equals($field, $value)
	{
		$this->_addCriterion('equals', func_get_args());

		return $this;
	}

	public function notEquals($field, $value)
	{
		$this->_addCriterion('notEquals', func_get_args());

		return $this;
	}

	public function getIterator()
	{
		return new \ArrayObject($this->_criteria);
	}

	public function greaterThan($field, $value)
	{
		$this->_addCriterion('greaterThan', func_get_args());
	}

	public function lessThan($field, $value)
	{
		$this->_addCriterion('lessThan', func_get_args());

		return $this;
	}

	public function in($field, array $value)
	{
		$this->_addCriterion('in', func_get_args());

		return $this;
	}

	public function notIn($field, array $value)
	{
		$this->_addCriterion('notIn', func_get_args());

		return $this;
	}

	public function like($field, $value)
	{
		$this->_addCriterion('like', func_get_args());

		return $this;
	}

	public function notLike($field, $value)
	{
		$this->_addCriterion('notLike', func_get_args());
		
		return $this;
	}

	public function isNull($field)
	{
		$this->_addCriterion('null', func_get_args());
		return $this;
	}

	public function isNotNull($field)
	{

		$this->_addCriterion('notNull', func_get_args());
		return $this;
	}
}
