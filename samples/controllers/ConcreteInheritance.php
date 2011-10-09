<?php
/** 
 * @author Noah Goodrich
 * @date 4/23/11
 * @brief
 * 
 */

class ConcreteInheritance extends Controller {

	public function index()
	{
		$this->students = Gacela::instance()->loadMapper('student')->findAll();
		exit(debug(\Gacela::instance()->loadMapper('student')));
		$this->template = 'concrete-inheritance_index';
		$this->title = 'Concrete Inheritance';
	}

	public function form($id)
	{
		$this->houses = Gacela::instance()->loadMapper('house')->findAll();
		$this->student = Gacela::instance()->loadMapper('student')->find($id);

		if(count($_POST)) {
			if(!isset($student)) {
				$student = new \App\Model\Student;
			}

			$student->fullName = $_POST['fullName'];
			$student->role = 'student';
			$student->houseId = $_POST['houseId'];

			if(!$student->save()) {
				exit(debug($student->errors));
			} else {
				$message = 'Student saved<br/>';
			}
		}
	}

	public function delete($id)
	{
		$student = Gacela::instance()->loadMapper('student')->find($id);
		$student->delete();
	}
}

