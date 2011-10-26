<?php
/** 
 * @author Noah Goodrich
 * @date 5/2/11
 * @brief
 * 
 */

class Crud extends Controller {

	public function index()
	{
		$this->houses = \Gacela::instance()->loadMapper('house')->findNameAsc();
		
		$this->template = 'crud_index';
		$this->title = 'CRUD Example';
	}

	public function form($id = null)
	{
		$this->house = Gacela::instance()->loadMapper('house')->find($id);

		if(count($_POST)) {
			$this->house->houseName = $_POST['house'];

			if(!$this->house->save()) {
				$this->error = 'House Name is not valid';
			} else {
				$this->_redirect('/crud');
			}
		}

		$this->template = 'crud_form';
		$this->title = 'Crud Example';
	}

	public function delete($id)
	{
		$model = Gacela::instance()->loadMapper('house')->find($id);
		$model->delete();

		$this->_redirect('/crud');
	}
}


 
