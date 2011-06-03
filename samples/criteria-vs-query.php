<?php
/** 
 * @author Noah Goodrich
 * @date 6/3/11
 * @brief
 * 
 */

require '_init.php';
 
$criteria1 = new \Gacela\Criteria;

$criteria1->equals('role', 'student')
	->isNull('locationName');

$criteria2 = new \Gacela\Criteria;

$criteria2->equals('role', 'student');

$noAddresses = \Gacela::instance()->loadMapper('wizard')->findAll($criteria1);
$totalStudents = \Gacela::instance()->loadMapper('wizard')->findAll($criteria2);

echo 'Total Students: '.count($totalStudents).'<br/>';
echo 'Students with no address: '.count($noAddresses).'<br/>';