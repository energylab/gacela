<?php
/**
 * @author Noah Goodrich
 * @date 5/22/11
 * @brief
 *
*/

namespace Gacela\DataSource\Adapter;

class Mysql extends Pdo {

	public static $_separator = "_";

	protected $_relationships = null;

	public function __construct($config)
	{
		parent::__construct($config);

		$this->_relationships = $this->_singleton()->cache($this->_config->schema.'_relationships');

		if(!$this->_relationships) {
			$sql = "
				SELECT
					TABLE_NAME AS keyTable,
					GROUP_CONCAT(COLUMN_NAME) AS keyColumns,
					REFERENCED_TABLE_NAME AS refTable,
					GROUP_CONCAT(REFERENCED_COLUMN_NAME) AS refColumns,
					CONSTRAINT_NAME AS constraintName
				FROM INFORMATION_SCHEMA.key_column_usage
				WHERE TABLE_SCHEMA = DATABASE()
				AND REFERENCED_TABLE_NAME IS NOT NULL
				GROUP BY constraintName
				";

			$this->_relationships = $this->query($sql)->fetchAll(\PDO::FETCH_OBJ);

			$this->_singleton()->cache($this->_config->schema.'_relationships');
		}
	}

	public function load($name)
	{
		$_meta = array(
			'name' => $name,
			'columns' => array(),
			'relations' => array(),
			'primary' => array()
		);

		$config = $this->_loadConfig($name);

		if(!is_null($config)) {
			return $config;
		}

		// Setup Column meta information
		$stmt = $this->prepare("DESCRIBE ".$name);

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

			$meta = array_merge(
						self::$_meta,
						array(
							'sequenced' => (bool) (stripos($column->Extra, 'auto_increment') !== false),
							'primary' => (bool) (strtoupper($column->Key) == 'PRI'),
							'default' => $column->Default,
							'null' => (bool) ($column->Null == 'YES')
						)
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
					$meta['precision'] = $matches[1];
					$meta['scale'] = $matches[2];
				} else {
					$meta['precision'] = 53;
					$meta['scale'] = 15;
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

			$_meta['columns'][$column->Field] = (object) $meta;

			if($_meta['columns'][$column->Field]->primary === true) {
				$_meta['primary'][] = $column->Field;
			}
		}

		unset($stmt);

		// Setup Relationships
		foreach($this->_relationships as $rel)
		{
			$key = explode(self::$_separator, $rel->constraintName);

			if(!isset($key[1])) {
				throw new \Exception('Improper relationship definition: '.print_r($rel, true));
			}

			if($rel->keyTable == $name) {
				$row = clone $rel;

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

			} elseif($rel->refTable == $name) {
				$row = clone $rel;

				$rt = $row->refTable;
				$kt = $row->keyTable;

				$row->refTable = $kt;
				$row->keyTable = $rt;

				$key = $key[2];

				$row->type = 'hasMany';

				$keys = explode(',', $row->keyColumns);
				$refs = explode(',', $row->refColumns);

				$row->keys = array();

				foreach($refs as $k => $v) {
					$row->keys[$v] = $keys[$k];
				}

				unset($row->keyColumns);
				unset($row->refColumns);

				$_meta['relations'][$key] = $row;

			}
		}

		return $_meta;
	}
}
