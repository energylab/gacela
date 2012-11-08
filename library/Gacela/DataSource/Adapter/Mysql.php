<?php
/**
 * @author Noah Goodrich
 * @date 5/22/11
 *
 *
*/

namespace Gacela\DataSource\Adapter;

class Mysql extends Pdo
{
	public function loadConnection()
	{
		if(!$this->_conn) {
			parent::loadConnection();

			$this->_columns = $this->_singleton->cacheMetaData($this->_config->schema.'_columns');

			if(!$this->_columns) {
				$sql = "SELECT *
						FROM information_schema.COLUMNS
						WHERE TABLE_SCHEMA = DATABASE()";

				$this->_columns = $this->query($sql)->fetchAll(\PDO::FETCH_OBJ);

				$this->_singleton->cacheMetaData($this->_config->schema.'_columns', $this->_columns);
			}
		}

		// Moved out of __construct to allow for lazy loading of config data
		$this->_relationships = $this->_singleton->cacheMetaData($this->_config->schema.'_relationships');

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

			$this->_singleton->cacheMetaData($this->_config->schema.'_relationships', $this->_relationships);
		}
	}

	public function load($name, $force = false)
	{
		$config = $this->_loadConfig($name, $force);

		if(!is_null($config)) {
			return $config;
		}

		$this->loadConnection();

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

				if (stripos($column->DATA_TYPE, 'char') !== false || stripos($column->DATA_TYPE, 'text') !== false) {
					$meta['type'] = 'string';
					$meta['length'] = (int) $column->CHARACTER_MAXIMUM_LENGTH;
				} elseif(stripos($column->DATA_TYPE, 'binary') !== false || stripos($column->DATA_TYPE, 'blob') !== false) {
					$meta['type'] = 'binary';
					$meta['length'] = (int) $column->CHARACTER_MAXIMUM_LENGTH;
				} elseif(preg_match('/(enum)\((\'.*?\')\)/', $column->COLUMN_TYPE, $matches)) {
					$meta['type'] = 'enum';
					$meta['values'] = explode(',', str_replace("'", "", $matches[2]));
				} elseif(preg_match('/(set)\((\'.*?\')\)/', $column->COLUMN_TYPE, $matches)) {
					$meta['type'] = 'set';
					$meta['values'] = explode(',', str_replace("'", "", $matches[2]));
				} elseif($column->DATA_TYPE == 'decimal') {
					$meta = array_merge(
						$meta,
						array(
							'type' => 'decimal',
							'length' => (int) $column->NUMERIC_PRECISION,
							'scale' => (int) $column->NUMERIC_SCALE
						)
					);
				}elseif(in_array($column->DATA_TYPE, array('float', 'double'))) {
					$meta = array_merge(
						$meta,
						array(
							'type' => 'float',
							'length' => (int) $column->NUMERIC_PRECISION
						)
					);

				} elseif($column->COLUMN_TYPE == 'tinyint(1)') {
					$meta['type'] = 'bool';
				} elseif (stripos($column->DATA_TYPE, 'int') !== false) {
					$meta['type'] = 'int';
					$meta['length'] = (int) $column->NUMERIC_PRECISION;

					$size = substr($column->DATA_TYPE, 0, strlen($column->DATA_TYPE)-3);

					switch($size) {
						case 'tiny':
							$size = 8;
							break;
						case 'small':
							$size = 16;
							break;
						case 'medium':
							$size = 24;
							break;
						case 'big':
							$size = 64;
							break;
						default:
							$size = 32;
							break;
					}

					if($meta['unsigned']) {
						$meta['min'] = '0';

						$meta['max'] = bcsub(bcpow(2, $size), 1);
					} else {
						$tmp = bcdiv(bcpow(2, $size), 2);

						$meta['min'] = '-'.$tmp;

						$meta['max'] = bcsub($tmp, '1');
					}
				} elseif(in_array($column->DATA_TYPE, array('datetime', 'date', 'timestamp'))) {
					$meta['type'] = 'date';
				} elseif($column->DATA_TYPE === 'time') {
					$meta['type'] = 'time';
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
