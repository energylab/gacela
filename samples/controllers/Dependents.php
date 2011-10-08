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

	public function form($id)
	{
		$model = \Gacela::instance()->loadMapper('wizard')->find($id);
	}

	public function delete()
	{
		$wizard->delete();
	}
}
