<?php
/** 
 * @author Noah Goodrich
 * @date 6/3/11
 * @brief
 * 
 */

class Debug extends Controller {

	public function index()
	{
		$this->template = 'debug';
		$this->title = 'Debugging Mappers';

		$this->house = \Gacela::instance()->loadMapper('house');

		$houses = $this->house->findAll();

		$this->student = \Gacela::instance()->loadMapper('student');
	}
}