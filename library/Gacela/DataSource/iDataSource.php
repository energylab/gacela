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

	public function insert();

	public function update();

	public function delete();

	public function getQuery();

	public function getResource($name);
}
