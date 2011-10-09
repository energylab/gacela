<?php
/** 
 * @author Noah Goodrich
 * @date 10/8/11
 * @brief
 * 
 */
 ?>
 
<?= $message ?>
<?= $errors ?>
<form action="/concrete-inheritance.php" method="post">
	<input type="hidden" name="id" value="<?= isset($student) ? $student->wizardId : null ?>" />
	<label>Student Name</label>
	<input type="text" name="fullName"  value="<?= isset($student) ? $student->fullName : null ?>" /><br/>

	<label>House</label>
	<select name="houseId">
	<? foreach($houses as $house): ?>
		<option value="<?= $house->houseId ?>"><?= $house->houseName ?></option>
	<? endforeach; ?>
	</select><br/>

	<input type="submit" />
</form>