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

	public function form($id = null)
	{
		$this->model = Gacela::instance()->loadMapper('teacher')->find($id);

		$this->template = 'single-inheritance_form';
		$this->title = 'Single Table Inheritance Mapping - Teachers';
		$this->message = '';


		if(count($_POST)) {
			$this->model->fullName = $_POST['fullName'];
			$this->model->role = 'teacher';

			if(!$this->model->save()) {
				foreach($model->errors as $key => $val) {
					$this->message .= 'Field: '.$key.' ErrorCode: '.$val.'<br/>';
				}
			} else {
				$this->message = 'Model saved<br/>';
			}
		}

	}

	public function delete($id)
	{
		$model = Gacela::instance()->loadMapper('teacher')->find($id);
		$model->delete();

		$this->_redirect('/singleInheritance');
	}
}