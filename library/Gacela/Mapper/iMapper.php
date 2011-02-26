<?php
/**
 * Created by PhpStorm.
 * User: noah
 * Date: Oct 4, 2010
 * Time: 7:40:57 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Gacela\Mapper;

interface iMapper {

	public function init();
	
	public function find($id);

	public function find_all(Gacela\Criteria $criteria);
}
