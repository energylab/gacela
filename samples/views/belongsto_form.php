<?php
/** 
 * @author Noah Goodrich
 * @date 10/12/11
 * @brief
 * 
 */
 ?>

<?= $this->message ?>
<form method="post" action="/belongsto/form/<?= $this->course->courseId ?>">
	<label>Subject</label>
	<input name="subject" value="<?= $this->course->subject ?>" /><br/>

	<label>Teacher</label>
	<select name="teacherId">
	<? foreach($this->teachers as $teach): ?>
		<option value="<?= $teach->wizardId ?>" <?= $this->course->wizardId == $teach->wizardId ? 'selected="selected"' : '' ?>><?= $teach->fullName ?></option>
	<? endforeach ?>
	</select><br/>

	<input type="submit" />
</form>