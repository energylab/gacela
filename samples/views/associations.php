<?php
/** 
 * @author Noah Goodrich
 * @date 9/8/11
 * @brief
 * 
 */
 ?>
 
<table>
	<thead>
	<tr>
		<th>Student</th>
		<th>&nbsp;</th>
	</tr>
	</thead>
	<tbody>
<? foreach($this->students as $student): ?>
	<tr>
		<td><?= $student->fullName ?></td>
		<td>
			<span><a href="/associations/student/<?= $student->wizardId ?>">View Courses</a></span>
		</td>
	</tr>
<? endforeach ?>
	</tbody>
</table>