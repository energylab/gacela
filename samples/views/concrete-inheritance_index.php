<?php
/** 
 * @author Noah Goodrich
 * @date 10/8/11
 * @brief
 * 
 */
 ?>

<a href="/concreteInheritance/form/">Add</a>
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
				<a href="/concreteInheritance/form/'.$student->wizardId.'">Edit</a>
				<a href="concreteInheritance/delete/'.$student->wizardId.'">Delete</a>
			</td>
		</tr>';
}
?>
	</tbody>
</table>
