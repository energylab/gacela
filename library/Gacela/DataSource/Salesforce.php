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
	public function count($query, \Gacela\DataSource\Resource $resource, array $inherits, array $dependents)
	{
		if($query instanceof \Gacela\Criteria || is_null($query)) {
			$query = $this->getQuery($query);
		}

		$query->from($resource->getName(), array('count()'));

		$this->_setLastQuery($query, array());

		$rs = $this->_adapter->query($this->_lastQuery['query']);

		return (int) $rs->size;
	}

	/**
	 * @abstract
	 * @param  $name
	 * @param \Gacela\Criteria $where
	 * @return bool
	 */
	public function delete($name, \Gacela\DataSource\Query\Query $where)
	{
		list($query, $args) = $where->delete($name)->assemble();

		$rs = $this->_adapter->delete($args['Ids']);

		if(is_object($rs)) {
			$rs = array($rs);
		}

		$success = true;
		foreach($rs as $r) {

			if(!$r->success) {
				$success = false;
			}
		}

		return $success;
	}

	/**
	 * Can be used to find a single Salesforce record. (\Gacela\Mapper\Mapper works this way).
	 * Can also be used to retrieve an array of Salesforce records if an array of Id's is passed.
	 *
	 * @param array $primary
	 * @param \Gacela\DataSource\Resource $resource
	 * @param array $inherits
	 * @param array $dependents
	 * @return array()
	 */
	public function find(array $primary, \Gacela\DataSource\Resource $resource, array $inherits = array(), array $dependents = array())
	{
		if(key($primary) === 'Id') {
			$primary = array($primary['Id']);
		}

		$return = $this->_adapter->retrieve(join(',', array_keys($resource->getFields())), $resource->getName(), $primary);

		if(is_array($return)) {
			$return = current($return);
		}

		return $return;
	}

	/**
	 *
	 */
	public function findAll(\Gacela\DataSource\Query\Query $query, \Gacela\DataSource\Resource $resource, array $inherits, array $dependents)
	{
		if(!count($query->from) > 0) {
			$query->from($resource->getName(), array_keys($resource->getFields()));
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
		$class = $this->_gacela->autoload("DataSource\\Query\\Soql");

		return new $class($criteria);
	}

	/**
	 * @abstract
	 * @throws \Exception
	 * @param  string $name Resource Name to use
	 * @param  array $data Can be a multi-dimensional array to insert many records or a single array to insert one record
	 * @param string - 'begin', 'commit'
	 * @return int|bool Last insert id (if supported by the DataSource and Resource) otherwise a boolean true
	 */
	public function insert($name, $data, $binds = array())
	{
		if(is_object($data) || (is_array($data) && !is_array(current($data)))) {
			$data = array($data);
		}

		foreach($data as $k => $v)
		{
			if(is_array($v)) {
				$data[$k] = (object) $v;
			}
		}

		$rs = $this->_adapter->create($data, $name);

		if(is_object($rs)) {
			$rs = array($rs);
		}

		if(count($data) == 1) {
			$rs = current($rs);

			if(property_exists($rs, 'errors')) {
				throw new \Gacela\Exception($rs->errors[0]->message);
			} else {

				return $rs->id;
			}
		} else {
			return $rs;
		}
	}

	/**
	 * @abstract
	 * @throws \Exception
	 * @param  string|Query $query A valid representation of a query for the DataSource
	 * @return array
	 */
	public function query(\Gacela\DataSource\Resource $resource, $query, $args = array())
	{
		$this->_setLastQuery($query, $args);

		try {
			$return = $this->_adapter->query($this->_lastQuery['query']);

			if(isset($return->records)) {
				$return = $return->records;
			} else {
				$return = array();
			}

			return $return;
		} catch(\SoapFault $s) {
			if(strpos($s->getMessage(), 'MALFORMED_QUERY') !== false) {
				throw $s;
			} else {
				return array();
			}
		}
	}

	public function quote($value)
	{
		return addslashes($value);
	}

	public function update($name, $data, $where = null){}
}
