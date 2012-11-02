<?php
/**
 * @author noah
 * @date 4/30/11
 *
*/

namespace App\Mapper;

class Teacher extends Wizard
{

	protected $_resourceName = 'wizards';

	public function findAll(\Gacela\Criteria $criteria = null)
	{
		if(is_null($criteria)) {
			$criteria = new \Gacela\Criteria;
		}

		$criteria->equals('role', 'teacher');

		return parent::findAll($criteria);
	}

	public function findAllWithCourse(\Gacela\Criteria $criteria = null)
	{
		$query = $this->_source()->getQuery($criteria)
					->from('wizards')
					->where('role = :role', array(':role' => 'teacher'))
					->where('EXISTS (SELECT * FROM courses WHERE courses.wizardId = wizards.wizardId)');

		return $this->_collection(
						$this->_source()->findAll($query, $this->_resource, $this->_inherits, $this->_dependents)
					);
	}

	public function findAllWithoutCourse(\Gacela\Criteria $criteria = null)
	{
		$existsQuery = $this->_source()->getQuery()
							->from('courses')
							->where('courses.wizardId = wizards.wizardId')
							->assemble();

		$query = $this->_source()->getQuery($criteria)
					->from('wizards')
					->where('role = :role', array(':role' =>  'teacher'))
					->where("NOT EXISTS ({$existsQuery[0]})");

		return $this->_collection(
						$this->_source()->findAll($query, $this->_resource, $this->_inherits, $this->_dependents)
					);
	}
}
