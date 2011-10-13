
<?php
/** 
 * @author Noah Goodrich
 * @date 10/12/11
 * @brief
 * 
 */
 ?>
 
<h3>Courses for <?= $this->teacher->fullName ?></h3>

<table>
	<thead>
	<tr>
		<th>Subject</th>
	</tr>
	</thead>
	<tbody>
<? foreach($this->teacher->courses as $course): ?>
	<tr>
		<td><?= $course->subject ?></td>
	</tr>
<? endforeach ?>
	</tbody>
</table>