<?php
/**
 * Created by PhpStorm.
 * User: noah
 * Date: Oct 4, 2010
 * Time: 7:40:57 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Gacela\Mapper;

interface iMapper
{
	public function init();

	public function count($criteria = null);

	public function getFields();

	public function getRelations();

	public function find($id);

	public function findAll(\Gacela\Criteria $criteria = null);

	public function save(array $changed, \stdClass $new, array $old);

	public function delete(\stdClass $data);
}
