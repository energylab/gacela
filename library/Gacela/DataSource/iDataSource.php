<?php
/** 
 * @author noah
 * @date 2/24/11
 * @brief
 * 
*/

namespace Gacela\DataSource;

interface iDataSource {

	public function query($query);

	public function insert($name, $data);

	public function update($name, $data, Gacela\Criteria $where);

	public function delete($name, Gacela\Criteria $where);

	public function getQuery();

	public function loadResource($name);
}
