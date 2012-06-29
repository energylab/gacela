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
		require $this->_config->soapclient_path.'SforceEnterpriseClient.php';

		$this->_conn = new \SforceEnterpriseClient();

		$this->_conn->createConnection($this->_config->wsdl_path);
		$this->_conn->login($this->_config->username, $this->_config->password);
	}

	//put your code here
	public function load($name)
	{
		$config = $this->_loadConfig($name);

		if(!is_null($config) && !is_integer(key($config['columns']))) {
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
			if($field->deprecatedAndHidden === true OR (!is_null($config) && !in_array($field->name, $config['columns']))) {
				continue;
			}

			if($field->type == 'id') {
				$_meta['primary'][] = $field->name;
			}

			$meta = array_merge(
						self::$_meta,
						array(
							'sequenced' => $field->autoNumber,
							'primary' => (bool) $field->type == 'id',
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
				default:
					$meta['type'] = 'string';
					break;
			}

			$_meta['columns'][$field->name] = (object) $meta;
		}

		return $_meta;
	}
}
