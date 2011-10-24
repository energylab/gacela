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
			
			$meta = array(
						'type' => null,
						'length' => null,
						'precision' => null,
						'scale' => null,
						'unsigned' => false,
						'sequenced' => (bool) (stripos($column->Extra, 'auto_increment') !== false),
						'primary' => (bool) (strtoupper($column->Key) == 'PRI'),
						'default' => $column->Default,
						'values' => array(),
						'null' => (bool) ($column->Null == 'YES')
					);
			
			if (preg_match('/unsigned/', $column->Type)) {
                $meta['unsigned'] = true;
            }
			
			if (preg_match('/^((?:var)?char)\((\d+)\)/', $column->Type, $matches)) {
				$meta['type'] = 'string';
				$meta['length'] = $matches[2];
			} elseif (preg_match('/^float|decimal|double(?:\((\d+),(\d+)\))?$/', $column->Type, $matches)) {				
				$meta['type'] = 'float';
				
				if(isset($matches[1])) {
					$meta['precision'] = $matches[2];
					$meta['scale'] = $matches[3];
				}
				
			} elseif (preg_match('/^(([a-zA-Z]*)int)\((\d+)\)/', $column->Type, $matches)) {
				// Use $matches[2] to determine size of the field for validation.
				if($matches[2] == 'tiny' && $matches[3] == 1) {
					$meta['type'] = 'bool';
				} else {
					$meta['type'] = 'int';
					$meta['length'] = $matches[3];
				}
			} elseif(preg_match('/^(([a-zA-Z]*)text)/', $column->Type, $matches)) {
				// Use $matches[2] to determine size of the field for validation.			
				$meta['type'] = 'string';
			} elseif(preg_match('/(enum)\((\'.*?\')\)/', $column->Type, $matches)) {
				$meta['type'] = 'enum';
				$meta['values'] = explode(',', str_replace("'", "", $matches[2]));
			} elseif(preg_match('/(date*|timestamp)/', $column->Type, $matches)) {
				$meta['type'] = 'date';
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
				
				if(!isset($key[1])) {
					throw new \Exception('Improper belongs_to definition: '.print_r($row, true));
				}
				
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
				
				if(!isset($key[2])) {
					throw new \Exception('Improper has_many definition: '.print_r($row, true));
				}
				
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
