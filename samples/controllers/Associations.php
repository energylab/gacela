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
	}
}
