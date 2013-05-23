<?php

namespace Gacela\Mapper;

interface MapperInterface
{
	public function init();

	public function count($criteria = null);

	public function getFields();

	public function getRelations();

	public function find($id);

	public function findAll(\Gacela\Criteria $criteria = null);

	public function save(array $changed, array $new, array $old);

	public function delete(array $data);
}
