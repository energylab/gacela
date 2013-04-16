<?php

/**
 * Description of salesforce
 *
 * @author noah
 * @date $(date)
 */


namespace Gacela\DataSource\Adapter;

class Salesforce extends Adapter
{
	public function loadConnection()
	{
 		if(!class_exists('\SforceEnterpriseClient'))
        	{
            		require_once($this->_config->soapclient_path.'SforceEnterpriseClient.php');
       		}

		if(is_null($this->_conn)) {
			$this->_conn = new \SforceEnterpriseClient();

			$this->_conn->createConnection($this->_config->wsdl_path);
			$this->_conn->login($this->_config->username, $this->_config->password);

			$this->_columns = $this->_singleton->cacheMetaData($this->_config->name.'_columns');

			if(!$this->_columns) {
				$this->_columns = $this->describeSObjects($this->_config->objects);

				if(!is_array($this->_columns)) {
					$this->_columns = array($this->_columns);
				}


				$this->_singleton->cacheMetaData($this->_config->name.'_columns', $this->_columns);
			}
		}
	}

	/**
	 * @param string $name
	 * @param bool $force
	 * @return array
	 */
	public function load($name, $force = false)
	{
		$config = $this->_loadConfig($name, $force);

		if(!is_null($config)) {
			return $config;
		}

		$this->loadConnection();

		$_meta = array(
			'name' => null,
			'primary' => array(),
			'relations' => array(),
			'columns' => array(),
		);

		$resource = null;

		while(is_null($resource) AND current($this->_columns) !== false) {
			$col = current($this->_columns);

			if($col->name == $name) {
				$resource = $col;
				$_meta['name'] = $col->name;
			}

			next($this->_columns);
		}

		if(is_null($_meta['name'])) {
			throw new \Exception("Resource ($name) not found!");
		}

		reset($this->_columns);

		foreach($resource->fields as $field) {
			if($field->deprecatedAndHidden === true) {
				continue;
			}

			if($field->type == 'id') {
				$_meta['primary'][] = $field->name;
			}

			$meta = array_merge(
						self::$_meta,
						array(
							'sequenced' => $field->autoNumber,
							'primary' => $field->type == 'id',
							'null' => $field->nillable,
							'length' => $field->length,
							'precision' => $field->precision,
							'scale' => $field->scale
						)
					);

			switch($field->type) {
				case 'int':
					$meta['type'] = $field->type;
					break;
				case 'double':
				case 'currency':
					$meta['type'] = 'float';
					break;
				case 'date':
				case 'datetime':
				case 'time':
					$meta['type'] = 'date';
					break;
				case 'boolean':
					$meta['type'] = 'bool';
					break;
				case 'picklist':
					$meta['type'] = 'enum';
					$meta['values'] = array();

					if(is_object($field->picklistValues)) {
						if($field->picklistValues->active) {
							$meta['values'][] = $field->picklistValues->value;
						}
					} else {
						foreach($field->picklistValues as $v) {
							if($v->active) {
								$meta['values'][] = $v->value;
							}
						}
					}

					break;
				case 'id':
					$meta['type'] = 'guid';
					break;
				default:
					$meta['type'] = 'string';
					break;
			}

			$_meta['columns'][$field->name] = (object) $meta;
		}

		return $_meta;
	}
}
