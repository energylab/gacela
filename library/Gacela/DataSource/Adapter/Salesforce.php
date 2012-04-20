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

	public function __construct($config)
	{
		parent::__construct($config);

		require $config->soapclient_path.'SforceEnterpriseClient.php';

		$this->_conn = new \SforceEnterpriseClient();

		$this->_conn->createConnection($config->wsdl_path);
		$this->_conn->login($config->username, $config->password);
	}

	//put your code here
	public function load($name)
	{
		$result = $this->_conn->describeSObject($name);

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
