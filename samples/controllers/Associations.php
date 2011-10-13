<?php
/** 
 * @author noah
 * @date 4/20/11
 * @brief
 * 
 */

class Associations extends Controller {

	public function index()
	{
		$this->students = Gacela::instance()->loadMapper('student')->findAll();

		$this->template = 'associations';
		$this->title = 'Association Mapping';
	}

	public function student($id)
	{
		$this->student = Gacela::instance()->loadMapper('student')->find($id);

		$this->template = 'student_associations';
		$this->title = 'Classes: '.$this->student->fullName;

		if(count($_POST)) {
			$course = Gacela::instance()->loadMapper('course')->find($_POST['courseId']);

			$this->student->add($course);
		}

		$criteria = new Gacela\Criteria;

		$courses = $this->student->courses->asArray('courseId');

		$criteria->notIn('courseId', $courses);

		$this->courses = Gacela::instance()->loadMapper('course')->findAll($criteria);
	}

	public function remove($student, $course)
	{
		$this->student = Gacela::instance()->loadMapper('student')->find($student);

		$course = Gacela::instance()->loadMapper('course')->find($course);

		$this->student->remove($course);

		$this->_redirect('/associations/student/'.$student);
	}
}
