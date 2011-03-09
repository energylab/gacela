<?php
/** 
 * @author noah
 * @date Oct 4, 2010
 * @brief
 * 
*/

namespace Gacela\Model;

interface iModel {

	public function validate();
	
	public function save();
}
