<?php

namespace Gacela\GUnit\Extensions\Database;

abstract class TestCase extends \PHPUnit_Extensions_Database_TestCase
{

	public static function tearDownAfterClass()
	{
		\Gacela\Gacela::reset();
	}
	
	public function getSetUpOperation()
	{
		$cascadeTruncates = TRUE; //if you want cascading truncates, false otherwise
		//if unsure choose false

		return new \PHPUnit_Extensions_Database_Operation_Composite(array(
			new Operation\MySQLTruncate($cascadeTruncates),
			\PHPUnit_Extensions_Database_Operation_Factory::INSERT()
		));
	}

	/**
	 * Creates a new Array DataSet with the given $array.
	 *
	 * @param string $xmlFile
	 * @return DataSet\ArrayDataSet
	 */
	protected function createArrayDataSet(array $array)
	{
		return new DataSet\ArrayDataSet($array);
	}
}
