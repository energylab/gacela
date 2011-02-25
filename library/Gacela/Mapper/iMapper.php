<?php
/**
 * Created by PhpStorm.
 * User: noah
 * Date: Oct 4, 2010
 * Time: 7:40:57 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Gacela\Mapper;

use Gacela as G;

interface iMapper {

	public function find($id);

	public function find_all(G\Criteria $criteria);
}
