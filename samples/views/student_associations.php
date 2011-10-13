<?php
/** 
 * @author Noah Goodrich
 * @date 9/8/11
 * @brief
 * 
 */
 ?>
 
<h2>Courses for: <?= $this->student->fullName ?></h2>

<form method="post" action="/associations/student/<?= $this->student->wizardId ?>">
	<label>Subjects:</label>
	<select name="courseId">
	<? foreach($this->courses as $course): ?>
		<option value="<?= $course->courseId ?>"><?= $course->subject ?></option>
	<? endforeach ?>
	</select>

	<input type="submit" value="Add" />
</form>

<table>
	<thead>
	<tr>
		<th>Course</th>
		<th>&nbsp;</th>
	</tr>
	</thead>
	<tbody>
<? foreach($this->student->courses as $course): ?>
	<tr>
		<td><?= $course->subject ?></td>
		<td>
			<span><a href="/associations/remove/<?= $this->student->wizardId ?>/<?= $course->courseId ?>">Remove</a></span>
		</td>
	</tr>
<? endforeach ?>
	</tbody>
</table>