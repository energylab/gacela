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

	public function form()
	{
		
	}

	public function delete()
	{
		
	}
}

if(isset($_GET['id']) || isset($_POST['id'])) {
	$id = isset($_GET['id']) ? $_GET['id'] : $_POST['id'];
	$model = Gacela::instance()->loadMapper('teacher')->find($id);
} else {
	$model = new \App\Model\Teacher;
}

if(isset($_GET['action']) && $_GET['action'] == 'delete') {
	$model->delete();
	$message = 'Model deleted<br/>';
}

if(count($_POST)) {
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

if(!isset($_GET['action']) || $_GET['action'] != 'edit') {
	unset($model);
}