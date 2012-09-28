<?php
/**
 * @author Noah Goodrich
 * @date 5/22/11
 *
 *
*/

namespace Gacela\DataSource\Adapter;

class Mysql extends Pdo {

	protected function _loadConn()
	{
		if(!$this->_conn) {
			parent::_loadConn();

			$this->_columns = $this->_singleton()->cache($this->_config->schema.'_columns');

			if(!$this->_columns) {
				$sql = "SELECT *
						FROM information_schema.COLUMNS
						WHERE TABLE_SCHEMA = DATABASE()";

				$this->_columns = $this->query($sql)->fetchAll(\PDO::FETCH_OBJ);

				$this->_singleton()->cache($this->_config->schema.'_columns');
			}
		}

		// Moved out of __construct to allow for lazy loading of config data
		$this->_relationships = $this->_singleton()->cache($this->_config->schema.'_relationships');

		if(!$this->_relationships) {
			$sql = "
				SELECT
					TABLE_NAME AS keyTable,
					GROUP_CONCAT(COLUMN_NAME) AS keyColumns,
					REFERENCED_TABLE_NAME AS refTable,
					GROUP_CONCAT(REFERENCED_COLUMN_NAME) AS refColumns,
					CONSTRAINT_NAME AS constraintName
				FROM information_schema.key_column_usage
				WHERE TABLE_SCHEMA = DATABASE()
				AND REFERENCED_TABLE_NAME IS NOT NULL
				GROUP BY constraintName
				";

			$this->_relationships = $this->query($sql)->fetchAll(\PDO::FETCH_OBJ);

			$this->_singleton()->cache($this->_config->schema.'_relationships');
		}
	}

	public function load($name, $force = false)
	{
		$config = $this->_loadConfig($name, $force);

		if(!is_null($config)) {
			return $config;
		}

		$this->_loadConn();

		$_meta = array(
			'name' => null,
			'columns' => array(),
			'relations' => array(),
			'primary' => array()
		);

		// Setup Column meta information
		foreach($this->_columns as $column) {

			if(strtolower($column->TABLE_NAME) == $name) {
				if(is_null($_meta['name'])) {
					$_meta['name'] = $column->TABLE_NAME;
				}

				$meta = array_merge(
					self::$_meta,
					array(
						'sequenced' => (bool) (stripos($column->EXTRA, 'auto_increment') !== false),
						'primary' => (bool) (strtoupper($column->COLUMN_KEY) == 'PRI'),
						'default' => $column->COLUMN_DEFAULT,
						'null' => (bool) ($column->IS_NULLABLE == 'YES')
					)
				);

				if (preg_match('/unsigned/', $column->COLUMN_TYPE)) {
					$meta['unsigned'] = true;
				}

				if (preg_match('/^((?:var)?char)\((\d+)\)/', $column->COLUMN_TYPE, $matches)) {
					$meta['type'] = 'string';
					$meta['length'] = $matches[2];
				} elseif (preg_match('/^float|decimal|double(?:\((\d+),(\d+)\))?$/', $column->COLUMN_TYPE, $matches)) {
					$meta['type'] = 'float';

					if(isset($matches[1])) {
						$meta['precision'] = $matches[1];
						$meta['scale'] = $matches[2];
					} else {
						$meta['precision'] = 53;
						$meta['scale'] = 15;
					}

				} elseif($column->COLUMN_TYPE == 'tinyint(1)') {
					$meta['type'] = 'bool';
				} elseif (stripos($column->DATA_TYPE, 'int')) {
					$meta['type'] = 'int';

					//$size = substr($column->DATA_TYPE)
				} elseif(preg_match('/^(([a-zA-Z]*)text)/', $column->COLUMN_TYPE, $matches)) {
					// Use $matches[2] to determine size of the field for validation.
					$meta['type'] = 'string';
				} elseif(preg_match('/(enum)\((\'.*?\')\)/', $column->COLUMN_TYPE, $matches)) {
					$meta['type'] = 'enum';
					$meta['values'] = explode(',', str_replace("'", "", $matches[2]));
				} elseif(preg_match('/(date*|timestamp|time)/', $column->COLUMN_TYPE, $matches)) {
					$meta['type'] = 'date';
				}

				$_meta['columns'][$column->COLUMN_NAME] = (object) $meta;

				if($_meta['columns'][$column->COLUMN_NAME]->primary === true) {
					$_meta['primary'][] = $column->COLUMN_NAME;
				}
			}
		}

		if(is_null($_meta['name'])) {
			throw new \Exception("Resource ($name) not found!");
		}

		// Setup Relationships
		foreach($this->_relationships as $rel) {
			$key = explode(static::$_separator, $rel->constraintName);

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
