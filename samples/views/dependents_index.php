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
	<tr>
		<th>Name</th>
		<th>Class</th>
		<th>Location</th>
		<th>Options</th>
	</tr>
	</thead>
	<tbody>

<?
foreach($this->wizards as $wiz) {
	echo 	'<tr>
				<td>'.$wiz->fullName.'</td>
				<td>'.get_class($wiz).'</td>
				<td>'.$wiz->locationName.'</td>
				<td>
					<a href="dependents/form/'.$wiz->wizardId.'">Edit</a>
				</td>
			</tr>';
}
?>
		</tbody>
	</table>