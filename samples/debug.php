<?php
/** 
 * @author Noah Goodrich
 * @date 6/3/11
 * @brief
 * 
 */

require '_init.php';

$mapper = \Gacela::instance()->loadMapper('house');

$houses = $mapper->findAll();

echo 'House Mapper info:';
$mapper->debug(false);

$mapper = \Gacela::instance()->loadMapper('student');

echo 'Student Mapper info:';
$mapper->debug(false);