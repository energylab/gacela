<?php
/** 
 * @author Noah Goodrich
 * @date 10/8/11
 * @brief
 * 
 */
 ?>
 
<table>
	<thead>
		<th>Student</th>
		<th>House</th>
		<th>Options</th>
	</thead>
	<tbody>
<?


foreach($this->students as $student) {
	echo '<tr>
			<td>'.$student->fullName.'</td>
			<td>'.$student->house->houseName.'</td>
			<td>
				<a href="concrete-inheritance.php?action=edit&id='.$student->wizardId.'">Edit</a>
				<a href="concrete-inheritance.php?action=delete&id='.$student->wizardId.'">Delete</a>
			</td>
		</tr>';
}
?>
	</tbody>
</table>
