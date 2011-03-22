<?php
/** 
 * @author noah
 * @date Oct 4, 2010
 * @brief
 * 
*/

namespace Gacela\DataSource;

class Database extends DataSource {

	protected $_db;

	protected $_config = array();

	protected $_resources = array();

	public function __construct(array $config)
	{
		$this->_config = (object) $config;
		
		$dsn = $this->_config->dbtype.':dbname='.$this->_config->database.';host='.$this->_config->host;
		
		$this->_db = new \PDO($dsn, $this->_config->user, $this->_config->password);
	}

	public function query($query)
	{
		if($query instanceof Query\Database)  {
			$stmt = $query->assemble();
		} elseif(is_string($query)) {
			$stmt = $this->_db->prepare($query);
		}

		$stmt->execute();

		return $stmt->fetchAll(\PDO::FETCH_OBJ);
	}

	/**
	 * @throws \Exception
	 * @param  string $name
	 * @param  array $data
	 * @param bool $multiple
	 * @return int
	 */
	public function insert($name, $data, $multiple = false)
	{
		if(!$multiple) {
			$data = array($data);
		}

		$keys = current($data);

		if(is_object($keys)) {
			$keys = (array) $keys;
		}

		$keys = array_keys($keys);

		$sql = "INSERT INTO `{$name}` (".join(',',$keys).") VALUES\n";

		foreach($data as $index => $row) {
			$tuple = $keys;

			array_walk($tuple, function(&$key, $k, $index) {  $key = ':'.$key.$index; }, $index);

			$sql .= "(".join(",", $tuple)."),";
		}

		$sql = substr($sql, 0, strlen($sql) - 1);

		$stmt = $this->_db->prepare($sql);
		
		foreach($data as $index => $row) {
			foreach($row as $key => $field) {
				$stmt->bindValue($key.$index, $field);
			}
		}

		if($stmt->execute()) {
			return $this->_db->lastInsertId();
		} else {
			throw new \Exception('Insert failed with errors: '.\Util::debug($stmt->errorInfo()));
		}
	}

	public function update($name, $data)
	{
		
	}

	public function delete($name, $id)
	{

	}

	public function getQuery(\Gacela\Criteria $criteria = null)
	{
		return new Query\Database(array_merge((array) $this->_config, array('db' => $this->_db, 'criteria' => $criteria)));
	}

	public function loadResource($name)
	{
		if(!isset($this->_resources[$name]))  {
			$this->_resources[$name] = new Resource\Database(array_merge((array) $this->_config, array('name' => $name, 'db' => $this->_db)));
		}

		return $this->_resources[$name];
	}
}
