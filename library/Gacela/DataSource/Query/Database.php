<?php
/** 
 * @author noah
 * @date 2/24/11
 * @brief
 * 
*/

namespace Gacela\DataSource\Query;

class Database {

	protected $_config;

	protected $_select = array();

	protected $_from = array();

	protected $_where = array();

	protected $_join = array();

	protected $_groupBy = array();

	protected $_orderBy = array();

	protected $_having = array();

	public function __construct(array $config)
	{
		$this->_config = (object) $config;
	}

	public function from($tableName, array $columns = array(), $schema = null)
	{
		if(is_array($tableName)) {
			$name = $tableName[0];
		} else {
			$name = $tableName;
		}
		
		if(is_null($schema)) $schema = $this->_config->database;

		if(empty($columns)) $columns = array('*');

		$this->_from[$name] = array($tableName, $columns, $schema);
	}

	public function where($stmt, $value)
	{
		$this->_where[] = array($stmt, $value);
	}

	public function join()
	{
		
	}

	public function groupBy($column)
	{

	}

	public function orderBy($column, $direction)
	{

	}

	public function having()
	{

	}

	public function expr()
	{

	}

	public function assemble()
	{
		$_from = array();
		$_select = array();

		foreach($this->_from as $from) {
			if(is_array($from[0])) {
				$_from[] = "{$from[0][1]} AS {$from[0][0]}";
			} else {
				$_from[] = "{$from[0]}";
			}

			if(is_array($from[1])) {
				$_select = array_merge($from[1]);
			} else {
				$_select[] = $from[1];
			}
		}

		$_select = join(',', $_select);
		$_from = join(', ', $_from);

		$sql = "SELECT {$_select} FROM {$_from}";
		
		$statement = $this->_config->db->prepare($sql);

		return $statement;
	}
}
