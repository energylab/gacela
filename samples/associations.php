<?php
/** 
 * @author noah
 * @date 4/20/11
 * @brief
 * 
 */

require '_init.php';

?>
	<h3>Association Mapping</h3>

	<table
		<thead>
		<tr>
			<th>Student Name</th>
			<th>Courses</th>
			<th>Teacher</th>
		</tr>
		</thead>
		<tbody>


<?

$students = \Gacela::instance()->loadMapper('student')->findAll();

foreach($students as $student) {

	echo '<tr>
			<td>'.$student->fullName.'</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>';

	foreach($student->courses as $course) {
		echo '<tr>
				<td>&nbsp;</td>
				<td>'.$course->subject.'</td>
				<td>'.$course->teacher->fullName.'</td>
			</tr>';
	}

	echo '<tr><td colspan="2">&nbsp;</td></tr>';
}
?>

		</tbody>
	</table>