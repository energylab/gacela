<?php
/**
 * @author Noah Goodrich
 * @date 4/23/11
 * @brief
 *
 */

class ConcreteInheritance extends Controller
{

	public function index()
	{
		$criteria = new \Gacela\Criteria;

		$criteria->sort('fname')
				->sort('lname', 'desc');

		$this->students = Gacela::instance()->loadMapper('student')->findAll($criteria);

		$this->template = 'concrete-inheritance_index';
		$this->title = 'Concrete Inheritance';
	}

	public function form($id = null)
	{
		$this->houses = Gacela::instance()->loadMapper('house')->findAll();
		$this->student = Gacela::instance()->loadMapper('student')->find($id);
		$this->message = '';

		if(count($_POST)) {
			$this->student->fullName = $_POST['fullName'];
			$this->student->role = 'student';
			$this->student->houseId = $_POST['houseId'];

			if(!$this->student->save()) {
				$this->message = debug($this->student->errors);
			} else {
				$this->message = 'Student saved<br/>';
			}
		}

		$this->template = 'concrete-inheritance_form';
		$this->title = 'Concrete Inheritance';
	}

	public function delete($id)
	{
		$student = Gacela::instance()->loadMapper('student')->find($id);
		$student->delete();

		$this->_redirect('/concreteInheritance');
	}
}

