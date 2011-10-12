<?php
/** 
 * @author Noah Goodrich
 * @date 10/8/11
 * @brief
 * 
 */
 ?>
 
<?= $this->message ?>
<form action="/concreteInheritance/form/<?= $this->student->wizardId ?>" method="post">
	<input type="hidden" name="id" value="<?= $this->student->wizardId ?>" />
	<label>Student Name</label>
	<input type="text" name="fullName"  value="<?= $this->student->fullName ?>" /><br/>

	<label>House</label>
	<select name="houseId">
	<? foreach($this->houses as $house): ?>
		<option value="<?= $house->houseId ?>" <?= $this->student->houseId == $house->houseId ? 'selected="selected"' : '' ?>><?= $house->houseName ?></option>
	<? endforeach; ?>
	</select><br/>

	<input type="submit" />
</form>