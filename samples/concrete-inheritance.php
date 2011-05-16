<?php
/** 
 * @author Noah Goodrich
 * @date 4/23/11
 * @brief
 * 
 */

require '_init.php';

$students = Gacela::instance()->loadMapper('student')->findAll();

?>
<h3>Concrete Table Inheritance</h3>

<form>
	<label>Student Name</label>
	<input type="text" name="fullName" /><br/>

	<select name="houseId">
	<? foreach($houses as $house): ?>

	<? endforeach; ?>
	</select>
</form>

<table>
	<thead>
		<th>Student</th>
		<th>House</th>
		<th>Options</th>
	</thead>
	<tbody>
<?
foreach($students as $student) {
	echo '<tr>
			<td>'.$student->fullName.'</td>
			<td>'.$student->house->houseName.'</td>
			<td>
				<a href="">Edit</a>
				<a href="">Delete</a>
			</td>
		</tr>';
}
?>
	</tbody>
</table>

