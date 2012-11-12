<?php

namespace Test\GUnit\Extensions\Database\Operation;

/**
 * Executes a mysql 5.5 safe truncate against all tables in a dataset.
 *
 * @package    DbUnit
 * @author     Mike Lively <m@digitalsandwich.com>
 * @copyright  2011 Mike Lively <m@digitalsandwich.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 1.0.0
 */
class MySQLTruncate extends \PHPUnit_Extensions_Database_Operation_Truncate
{
	public function execute(\PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection, \PHPUnit_Extensions_Database_DataSet_IDataSet $dataSet)
	{
		$connection->getConnection()->query("SET @PHAKE_PREV_foreign_key_checks = @@FOREIGN_KEY_CHECKS");
		$connection->getConnection()->query("SET FOREIGN_KEY_CHECKS = 0");

		parent::execute($connection, $dataSet);

		$connection->getConnection()->query("SET FOREIGN_KEY_CHECKS = @PHAKE_PREV_foreign_key_checks");
	}
}