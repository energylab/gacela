<?php
/** 
 * @author Noah Goodrich
 * @date 10/8/11
 * @brief
 * 
 */
 ?>
 
<form method="post" enctype="multipart/form-data" action="<?= $_SERVER['PHP_SELF']?>">
	<input type="hidden" name="id" value="<?= $model->wizardId ?>" />

	<label>Full Name</label>
	<input type="text" name="fullName" /><br/>

	<label>Role</label>
	<select name="role">
		<option></option>
		<option>student</option>
		<option>teacher</option>
	</select><br/>

	<label>Location</label>
	<input type="text" name="locationName" /><br/>

	<input type="submit" name="save" />
</form>