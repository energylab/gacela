<?php
/** 
 * @author noah
 * @date 4/20/11
 * @brief
 * 
 */


class Dependents extends Controller {

	public function index()
	{
		$this->wizards = \Gacela::instance()->loadMapper('wizard')->findAll();

		$this->template = 'dependents_index';
		$this->title = 'Dependent Mapping';
	}

	public function form($id = null)
	{
		$this->model = \Gacela::instance()->loadMapper('wizard')->find($id);

		$this->template = 'dependents_form';
		$this->title = 'Dependent Mapping - Set Wizard Location';
		$this->message = '';

		if(count($_POST)) {
			$this->model->locationName = $_POST['locationName'];
			
			if(!$this->model->save()) {
				$this->message = debug($this->model->errors);
			} else {
				$this->message = 'Location Saved';
			}
		}
	}
}
