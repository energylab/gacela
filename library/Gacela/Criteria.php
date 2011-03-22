<?php
/** 
 * @author noah
 * @date Oct 2, 2010
 * @brief
 * 
*/

namespace Gacela;

class Criteria {

	protected $_criteria = array();

	protected function _addCriterion($operator, array $args)
	{
		$this->_criteria[] = array_merge(array($operator), $args);
	}

	/**
	 * @return Criteria
	 */
	public function equals($field, $value, $isArgument = true)
	{
		$this->_addCriterion('equals', func_get_args());

		return $this;
	}

	public function notEquals($field, $value, $isArgument = true)
	{
		$this->_addCriterion('notEquals', func_get_args());

		return $this;
	}

	public function greaterThan($field, $value, $isArgument = true)
	{
		$this->_addCriterion('greaterThan', func_get_args());
	}

	public function lessThan($field, $value, $isArgument = true)
	{
		$this->_addCriterion('lessThan', func_get_args());

		return $this;
	}

	public function in($field, $value, $isArgument)
	{
		$this->_addCriterion('in', func_get_args());

		return $this;
	}

	public function notIn($field, $value, $isArgument = true)
	{
		$this->_addCriterion('notIn', func_get_args());

		return $this;
	}

	public function like($field, $value, $isArgument = true)
	{
		$this->_addCriterion('like', func_get_args());

		return $this;
	}

	public function notLike($field, $value, $isArgument = true)
	{

		return $this;
	}

	public function isNull($field)
	{

		return $this;
	}
}
