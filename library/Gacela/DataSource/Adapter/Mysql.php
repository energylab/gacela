<?php
/** 
 * @author Noah Goodrich
 * @date 5/22/11
 * @brief
 * 
*/

namespace Gacela\DataSource\Adapter;

class Mysql extends Adapter implements iAdapter {

	public static $_separator = "_";
	
	public function load($conn, $name, $schema)
	{
		$_meta = array('name' => $name);

		// Pull from the config file if enabled
		$config = $this->_singleton()->loadConfig($name);
		
		if(!is_null($config)) {
			$_meta = array_merge($_meta, $config);

			foreach($_meta['columns'] as $key => $array) {
				$field = $this->_field($array['type']);
				
				$_meta['columns'][$key] = new $field($array);
			}

			foreach($_meta['relations'] as $k => $relation) {
				$_meta['relations'][$k] = (object) $relation;
			}

			return $_meta;
		}

		// Set it up from the database
		$_meta['columns'] = array();
		$_meta['relations'] = array();
		$_meta['primary'] = array();

		// Setup Column meta information
		$stmt = $conn->prepare("DESCRIBE ".$name);

		if(!$stmt->execute()) {
			throw new \Exception(
				'Error Code: '.
				$stmt->errorCode().
				'<br/>'.
				print_r($stmt->errorInfo(), true).
				'Param Dump:'.
				print_r($stmt->debugDumpParams(), true)
			);
		}

		$columns = $stmt->fetchAll(\PDO::FETCH_OBJ);

		foreach($columns as $column) {
			preg_match('/(?P<type>\w+)($|\((?P<length>(\d+|(.*)))\))/', $column->Type, $meta);

			$meta = array_merge(
						array(
							'length' => null,
							'unsigned' => false,
							'sequenced' => false,
							'primary' => false,
							'default' => $column->Default,
							'values' => array()
						),
						$meta
					);

			$column->Null == 'YES' ? $meta['null'] = true : $meta['null'] = false;

			if($column->Key == 'PRI') {
				$meta['primary'] = true;
			}

			if(stripos($column->Type, 'unsigned') !== false) {
				$meta['unsigned'] = true;
			}

			if(stripos($column->Extra, 'auto_increment') !== false) {
				$meta['sequenced'] = true;
			}

			if($meta['type'] == 'enum') {
				$meta['values'] = explode(',', str_replace("'", "", $meta['length']));
				$meta['length'] = null;
			}

			switch($meta['type']) {
				case 'varchar':
				case 'char':
				case 'text':
				case 'longtext':
					$meta['type'] = 'string';
					break;
				case 'tinyint':
					if($meta['length'] == 1) {
						$meta['type'] = 'bool';
					} else {
						$meta['type'] = 'int';
					}
					break;
				case 'datetime':
				case 'timestamp':
					$meta['type'] = 'date';
					break;
				case 'decimal':
				case 'float':
				case 'double':
					$meta['type'] = 'float';
					break;
			}

			$field = $this->_field($meta['type']);

			$_meta['columns'][$column->Field] = new $field($meta);

			if($_meta['columns'][$column->Field]->primary === true) {
				$_meta['primary'][] = $column->Field;
			}
		}

		unset($stmt);


		// Setup Relationships

		// First check for stored procedure used to generate belongs_to relationships
		$stmt = $conn->prepare("SHOW PROCEDURE STATUS LIKE :sp");

		$stmt->execute(array(':sp' => 'sp_belongs_to'));

		if($stmt->rowCount()) {
			$sp = $conn->prepare("CALL sp_belongs_to (:schema,:table)");
			$sp->execute(array(':schema' => $schema, ':table' => $name));

			$rs = $sp->fetchAll(\PDO::FETCH_OBJ);
			
			foreach($rs as $row) {
				$key = explode(self::$_separator, $row->constraintName);
				$key = $key[1];

				$row->type = 'belongsTo';

				$keys = explode(',', $row->keyColumns);
				$refs = explode(',', $row->refColumns);

				$row->keys = array();

				foreach($keys as $k => $v) {
					$row->keys[$v] = $refs[$k];
				}

				unset($row->keyColumns);
				unset($row->refColumns);

				$_meta['relations'][$key] = $row;
			}
		}

		$stmt->execute(array(':sp' => 'sp_has_many'));

		if($stmt->rowCount()) {
			$sp = $conn->prepare("CALL sp_has_many (:schema, :table)");
			$sp->execute(array(':schema' => $schema, ':table' => $name));

			$rs = $sp->fetchAll(\PDO::FETCH_OBJ);

			foreach($rs as $row) {
				$key = explode(self::$_separator, $row->constraintName);
				$key = $key[2];

				$row->type = 'hasMany';

				$keys = explode(',', $row->keyColumns);
				$refs = explode(',', $row->refColumns);

				$row->keys = array();

				foreach($keys as $k => $v) {
					$row->keys[$v] = $refs[$k];
				}

				unset($row->keyColumns);
				unset($row->refColumns);
				
				$_meta['relations'][$key] = $row;
			}
		}

		return $_meta;
	}

	private function _field($field)
	{
		return "\\Gacela\\Field\\".ucfirst($field);
	}
}
