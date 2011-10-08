<?php
/** 
 * @author Noah Goodrich
 * @date 5/11/11
 * @brief
 * 
 */

class SingleInheritance extends Controller {

	public function index()
	{
		$criteria = new \Gacela\Criteria;

		// For this list we only want to see teachers
		$criteria->notEquals('role', 'student');

		$this->wizards = Gacela::instance()->loadMapper('wizard')->findAll($criteria);

		$this->template = 'single-inheritance_index';
		$this->title = 'Single Table Inheritance - List of Students';
	}

	public function form($id)
	{
		$model = Gacela::instance()->loadMapper('teacher')->find($id);

		$model->fullName = $_POST['fullName'];
		$model->role = 'teacher';

		if(!$model->save()) {
			foreach($model->errors as $key => $val) {
				$errors .= 'Field: '.$key.' ErrorCode: '.$val.'<br/>';
			}
		} else {
			$message = 'Model saved<br/>';
		}
	}

	public function delete($id)
	{
		$model->delete();
	}
}