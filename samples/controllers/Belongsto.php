<?php
/**
 * @author noah
 * @date 5/2/11
 * @brief
 *
 */

class Belongsto extends Controller
{

	public function index()
	{
		$this->courses = Gacela::instance()->loadMapper('course')->findAll();

		$this->template = 'belongsto_index';
		$this->title = 'Belongs To Relationship Mapping';
	}

	public function form($id = null)
	{
		$this->course = Gacela::instance()->loadMapper('course')->find($id);

		$this->teachers = Gacela::instance()->loadMapper('teacher')->findAll();

		$this->template = 'belongsto_form';
		$this->title = 'Belongs To Relationships - Add a Course';
		$this->message = '';

		if(count($_POST)) {
			$this->course->subject = $_POST['subject'];
			$this->course->wizardId = $_POST['teacherId'];

			if($this->course->save()) {
				$this->message = 'Course Saved';
			} else {
				$this->message = debug($this->course->errors);
			}
		}
	}

	public function delete($id)
	{
		$course = Gacela::instance()->loadMapper('course')->find($id);
		$course->delete();

		$this->_redirect('/belongsto');
	}
}