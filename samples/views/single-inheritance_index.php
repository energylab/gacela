<?php
/** 
 * @author Noah Goodrich
 * @date 10/8/11
 * @brief
 * 
 */
 ?>

<a href="/singleInheritance/form">Add Teacher</a>
<table>
	<thead>
		<th>Full Name</th>
		<th>Courses</th>
		<th>Model</th>
		<th>Options</th>
	</thead>
	<tbody>
<?

foreach($this->wizards as $wiz) {
	$names = array();

	if(isset($wiz->courses)) {
		foreach($wiz->courses as $course) {
			$names[] = $course->subject;
		}
	}

	$names = join('<br/>', $names);

	echo 	'<tr>
				<td>'.$wiz->fullName.'</td>
				<td>'.$names.'</td>
				<td>'.get_class($wiz).'</td>
				<td>
					<a href="/singleInheritance/form/'.$wiz->wizardId.'">Edit</a>
					<a href="/singleInheritance/delete/'.$wiz->wizardId.'"">Delete</a>
				</td>
			</tr>';
}
?>

	</tbody>
</table>