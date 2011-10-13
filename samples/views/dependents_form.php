<?php
/** 
 * @author Noah Goodrich
 * @date 10/8/11
 * @brief
 * 
 */
 ?>

<p><?= $this->message ?></p>
<form method="post" action="/dependents/form/<?= $this->model->wizardId ?>">

	<label>Full Name</label>
	<input type="text" disabled="disabled" value="<?= $this->model->fullName ?>" /><br/>
	
	<label>Location</label>
	<input type="text" name="locationName" value="<?= $this->model->locationName ?>" /><br/>

	<input type="submit" name="save" />
</form>