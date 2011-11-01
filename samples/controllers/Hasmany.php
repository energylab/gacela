<?php
/** 
 * @author noah
 * @date 5/2/11
 * @brief
 * 
 */

class Hasmany extends Controller {

	public function index()
	{
		$this->teachers = Gacela::instance()->loadMapper('teacher')->findAllWithCourse();
		
		$this->template = 'hasmany_index';
		$this->title = 'Has Many Relationships - Teachers with Courses';
	}

	public function teacher($id = null)
	{
		$this->teacher = Gacela::instance()->loadMapper('teacher')->find($id);
		
		$this->template = 'hasmany_teacher';
		$this->title = 'Has Many Relationships - Teacher Courses';
	}
}