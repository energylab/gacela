<?php
/** 
 * @author Noah Goodrich
 * @date 6/3/11
 * @brief
 * 
 */

require '_init.php';

echo '<h1>Criteria Examples</h1>';

$criteria1 = new \Gacela\Criteria;

// Limit to only students who have no address specified
$criteria1->equals('role', 'student')
	->isNull('locationName');

$criteria2 = new \Gacela\Criteria;

// Pull back all wizards who are students
$criteria2->equals('role', 'student');

$noAddresses = \Gacela::instance()->loadMapper('wizard')->findAll($criteria1);
$totalStudents = \Gacela::instance()->loadMapper('wizard')->findAll($criteria2);

echo 'Total Students: '.count($totalStudents).'<br/>';
echo 'Students with no address: '.count($noAddresses).'<br/>';

echo '<h1>Query Examples</h1>';

echo '<h2>MySQL Examples</h2>';

$withCourse = \Gacela::instance()->loadMapper('teacher')->findAllWithCourse();
$withoutCourse = \Gacela::instance()->loadMapper('teacher')->findAllWithoutCourse();

$criteria = new \Gacela\Criteria;

$criteria->notLike('lName', 'e');

$noE = \Gacela::instance()->loadMapper('teacher')->findAllWithCourse($criteria);

echo 'Active Teachers: '.count($withCourse).'<br/>';
echo 'Active Teachers without an E in their last name: '.count($noE).'<br/>';
echo 'Inactive Teachers: '.count($withoutCourse);
