<?php
/** 
 * @author Noah Goodrich
 * @date 10/12/11
 * @brief
 * 
 */
 ?>
 
<table>
	<thead>
	<tr>
		<th>Teacher</th>
		<th>&nbsp;</th>
	</tr>
	</thead>
	<tbody>
<? foreach($this->teachers as $teacher): ?>
	<tr>
		<td><?= $teacher->fullName ?></td>
		<td><a href="/hasmany/teacher/<?= $teacher->wizardId ?>">View Courses</a></td>
	</tr>
	</tbody>
<? endforeach ?>
</table>