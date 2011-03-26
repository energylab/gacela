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

	public function update($name, $data, $where);

	public function delete($name, $where);

	public function getQuery();

	public function loadResource($name);
}
