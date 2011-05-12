<?php
/** 
 * @author noah
 * @date 4/23/11
 * @brief
 * 
 */

require '_init.php';

$students = Gacela::instance()->loadMapper('student')->findAll();

?>
	<h3>Concrete Table Inheritance</h3>

	<table>
		<thead>
			<th>Student</th>
			<th>House</th>
		</thead>
		<tbody>
<?
	foreach($students as $student) {
		echo '<tr>
				<td>'.$student->fullName.'</td>
				<td>'.$student->house->houseName.'</td>
			</tr>';
	}
?>
		</tbody>
	</table>

