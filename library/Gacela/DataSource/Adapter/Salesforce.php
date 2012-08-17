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
	protected function _loadConn()
	{
        if(!class_exists('\SforceEnterpriseClient'))
        {
            require_once($this->_config->soapclient_path.'SforceEnterpriseClient.php');
        }

		$this->_conn = new \SforceEnterpriseClient();

		$this->_conn->createConnection($this->_config->wsdl_path);
		$this->_conn->login($this->_config->username, $this->_config->password);
	}

	//put your code here
	public function load($name, $force = false)
	{
		$config = $this->_loadConfig($name, $force);

		if(!is_null($config)) {
			return $config;
		}

		$result = $this->describeSObject($name);

		$_meta = array(
			'name' => $name,
			'primary' => array(),
			'relations' => array(),
			'columns' => array(),
		);

		foreach($result->fields as $field) {
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
				case 'dateTime':
				case 'time':
					$meta['type'] = 'date';
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
				default:
					$meta['type'] = 'string';
					break;
			}

			$_meta['columns'][$field->name] = (object) $meta;
		}

		return $_meta;
	}
}
