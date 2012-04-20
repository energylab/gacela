<?php

/**
 * Description of salesforce
 *
 * @author noah
 * @date $(date)
 */

namespace Gacela\DataSource;

class Salesforce extends DataSource
{
	protected function _driver()
	{
		if(empty($this->_driver))
		{
			$adapter = $this->_singleton()->autoload("\\DataSource\\Adapter\\".ucfirst($this->_config->type));

			$this->_driver = new $adapter($this->_config);
		}

		return $this->_driver;
	}

	/**
	 * @abstract
	 * @param  $name
	 * @param \Gacela\Criteria $where
	 * @return bool
	 */
	public function delete($name, \Gacela\DataSource\Query\Query $where) {}

	public function find(array $primary, \Gacela\DataSource\Resource $resource, array $inherits, array $dependents)
	{
		$query = $this->getQuery();

		foreach($primary as $key => $val) {
			$query->where($resource->getName().'.'.$key.' = :'.$key, array(':'.$key => $val));
		}

		return $this->query(
					$resource,
					$query->from($resource->getName())
				);
	}

	/**
	 *
	 */
	public function findAll(\Gacela\DataSource\Query\Query $query, \Gacela\DataSource\Resource $resource, array $inherits, array $dependents)
	{
		if(count($query->from) === 0) {
			$query->from($resource->getName());
		}

		return $this->query($resource,$query);
	}

	public function findAllByAssociation(\Gacela\DataSource\Resource $resource, array $relation, array $data, array $inherits, array $dependents) {}

	/**
	 * @abstract
	 * @param \Gacela\Criteria $criteria
	 * @return \Gacela\DataSource\Query\Query
	 */
	public function getQuery(\Gacela\Criteria $criteria = null)
	{
		return new Query\Soql($criteria);
	}

	/**
	 * @abstract
	 * @throws \Exception
	 * @param  string $name Resource Name to use
	 * @param  array $data Can be a multi-dimensional array to insert many records or a single array to insert one record
	 * @param string - 'begin', 'commit'
	 * @return int|bool Last insert id (if supported by the DataSource and Resource) otherwise a boolean true
	 */
	public function insert($name, $data) {}

	/**
	 * @abstract
	 * @throws \Exception
	 * @param  string|Query $query A valid representation of a query for the DataSource
	 * @return array
	 */
	public function query(\Gacela\DataSource\Resource $resource, $query)
	{
		// Bunch of setup to do here
	}

	/**
	 * @abstract
	 * @param  string $name
	 * @param  array $data
	 * @param \Gacela\Criteria $where
	 * @param string - 'begin', 'commit'
	 * @return bool
	 */
	public function update($name, $data, \Gacela\DataSource\Query\Query $where = null){}
}
