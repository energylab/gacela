<?php
/**
 * @author Noah Goodrich
 * @date 10/8/11
 * @brief
 *
*/

class Index extends Controller {

	public function index()
	{
		$this->template = 'index';
		$this->title = 'GacelaPHP';
	}
}
