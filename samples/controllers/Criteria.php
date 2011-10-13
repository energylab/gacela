<?php
/** 
 * @author Noah Goodrich
 * @date 6/3/11
 * @brief
 * 
 */

class Criteria extends Controller {

	public function index()
	{
		$this->template = 'criteria';
		$this->title = 'Criteria vs. Query in Gacela';
		
		$criteria1 = new \Gacela\Criteria;

		// Limit to only students who have no address specified
		$criteria1->equals('role', 'student')
			->isNull('locationName');

		$criteria2 = new \Gacela\Criteria;

		// Pull back all wizards who are students
		$criteria2->equals('role', 'student');

		$this->noAddresses = \Gacela::instance()->loadMapper('wizard')->findAll($criteria1);
		$this->totalStudents = \Gacela::instance()->loadMapper('wizard')->findAll($criteria2);

		$this->withCourse = \Gacela::instance()->loadMapper('teacher')->findAllWithCourse();
		$this->withoutCourse = \Gacela::instance()->loadMapper('teacher')->findAllWithoutCourse();

		$criteria = new \Gacela\Criteria;

		$criteria->notLike('lName', 'e');

		$this->noE = \Gacela::instance()->loadMapper('teacher')->findAllWithCourse($criteria);
	}
}

