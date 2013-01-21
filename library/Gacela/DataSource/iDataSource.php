<?php
/**
 * @author Noah Goodrich
 * @date May 7, 2011
 *
 *
*/

namespace Gacela\DataSource;

interface iDataSource {

	/**
	 * @abstract
	 * @param array $config
	 */
	public function __construct(\Gacela $gacela, \Gacela\DataSource\Adapter\iAdapter $adapter, array $config);

	/**
	 * @param $query
	 * @param Resource $resource
	 * @param array $inherits
	 * @param array $dependents
	 * @return int
	 */
	public function count($query, \Gacela\DataSource\Resource $resource, array $inherits, array $dependents);

	/**
	 * @abstract
	 * @param  $name
	 * @param \Gacela\Criteria $where
	 * @return bool
	 */
	public function delete($name, \Gacela\DataSource\Query\Query $where);

	public function find(array $primary, \Gacela\DataSource\Resource $resource, array $inherits = array(), array $dependents = array());

	/**
	 *
	 */
	public function findAll(\Gacela\DataSource\Query\Query $query, \Gacela\DataSource\Resource $resource, array $inherits, array $dependents);

	public function findAllByAssociation(\Gacela\DataSource\Resource $resource, array $relation, array $data, array $inherits, array $dependents);

	/**
	 * @abstract
	 * @return string
	 */
	public function getName();

	/**
	 * @abstract
	 * @param \Gacela\Criteria $criteria
	 * @return Query\Query
	 */
	public function getQuery(\Gacela\Criteria $criteria = null);

	/**
	 * @abstract
	 * @throws \Exception
	 * @param  string $name Resource Name to use
	 * @param  array $data Can be a multi-dimensional array to insert many records or a single array to insert one record
	 * @param string - 'begin', 'commit'
	 * @return int|bool Last insert id (if supported by the DataSource and Resource) otherwise a boolean true
	 */
	public function insert($name, $data, $binds = array());

	/**
	 * @abstract
	 * @param  string $name
	 * @return \Gacela\DataSource\Resource
	 */
	public function loadResource($name, $force = false);

	/**
	 * @abstract
	 * @throws \Exception
	 * @param Resource
	 * @param  array | string | Query\Query $query A valid representation of a query for the DataSource
	 * @param array
	 * @return \PDOStatement | array
	 */
	public function query(\Gacela\DataSource\Resource $resource, $query, $args = array());

	/**
	 * @abstract
	 * @param  string $name
	 * @param  array $data
	 * @param \Gacela\Criteria $where
	 * @param string - 'begin', 'commit'
	 * @return bool
	 */
	public function update($name, $data, $where = array());
}
