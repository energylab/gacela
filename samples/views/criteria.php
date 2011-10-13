<?php
/** 
 * @author Noah Goodrich
 * @date 10/13/11
 * @brief
 * 
 */
 ?>
 
<h2>Criteria Examples</h2>

Total Students: <?= count($this->totalStudents)?> <br/>
Students with no address: <?= count($this->noAddresses)?> <br/>

<h2>Query Examples</h2>

<h3>MySQL Examples</h3>

Active Teachers: <?= count($this->withCourse) ?> <br/>
Active Teachers without an E in their last name: <?= count($this->noE) ?> <br/>
Inactive Teachers: <?= count($this->withoutCourse) ?>