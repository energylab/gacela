<?php
/** 
 * @author Noah Goodrich
 * @date 10/12/11
 * @brief
 * 
 */
 ?>
 
<a href="/belongsto/form">Add</a>

<table>
	<thead>
	<tr>
		<th>Course</th>
		<th>Teacher</th>
		<th>&nbsp;</th>
	</tr>
	</thead>
	<tbody>
<? foreach($this->courses as $course): ?>
	<tr>
		<td><?= $course->subject ?></td>
		<td><?= $course->teacher->fullName ?></td>
		<td>
			<a href="/belongsto/form/<?= $course->courseId ?>">Edit</a>
			<a href="/belongsto/delete/<?= $course->courseId ?>">Delete</a>
		</td>
	</tr>
<? endforeach ?>
	</tbody>
</table>